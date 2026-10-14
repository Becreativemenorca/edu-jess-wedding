<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BCW_Wedding_Transfer {

    const FORMAT_VERSION = 1;

    private static $single_image_keys = array(
        'bcw_hero_image_id',
        'bcw_closing_image_id',
        'bcw_invitation_image_id',
        'bcw_og_image_id',
        'bcw_entry_cover_image_id',
        'bcw_identity_primary_image_id',
        'bcw_identity_secondary_image_id',
    );

    private static $repeater_image_fields = array(
        'bcw_story'       => array( 'image', 'image_2', 'image_3' ),
        'bcw_wedding_day' => array( 'image' ),
        'bcw_menorca'     => array( 'image' ),
    );

    private static $excluded_meta_keys = array(
        'bcw_portal_user_id',
        'bcw_portal_email',
        'bcw_portal_enabled',
    );

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
        add_action( 'admin_post_bcw_wedding_export', array( __CLASS__, 'handle_export' ) );
        add_action( 'admin_post_bcw_wedding_import', array( __CLASS__, 'handle_import' ) );
    }

    public static function admin_menu() {
        add_submenu_page(
            'edit.php?post_type=' . BCW_Wedding_Post_Type::POST_TYPE,
            'Importar / Exportar',
            'Importar / Exportar',
            'edit_posts',
            'bcw-transfer',
            array( __CLASS__, 'render_page' )
        );
    }

    public static function render_page() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( 'No tienes permiso para ver esta página.' );
        }

        $notice  = isset( $_GET['bcw_transfer'] ) ? sanitize_key( wp_unslash( $_GET['bcw_transfer'] ) ) : '';
        $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

        echo '<div class="wrap">';
        echo '<h1>Importar / Exportar bodas</h1>';
        echo '<p>Mueve una boda completa entre entornos sin copiar todo WordPress. Incluye contenido, configuración e imágenes. <strong>No incluye respuestas RSVP ni credenciales del panel privado.</strong></p>';

        if ( 'imported' === $notice && $post_id ) {
            echo '<div class="notice notice-success is-dismissible"><p>Boda importada correctamente. <a href="' . esc_url( get_edit_post_link( $post_id ) ) . '">Editar boda</a></p></div>';
        } elseif ( 'error' === $notice ) {
            $message = isset( $_GET['message'] ) ? sanitize_text_field( wp_unslash( $_GET['message'] ) ) : 'No se pudo importar la boda.';
            echo '<div class="notice notice-error"><p>' . esc_html( $message ) . '</p></div>';
        }

        echo '<hr>';
        echo '<h2>Importar una boda</h2>';
        echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
        echo '<input type="hidden" name="action" value="bcw_wedding_import">';
        wp_nonce_field( 'bcw_wedding_import', 'bcw_wedding_import_nonce' );
        echo '<p><input type="file" name="bcw_wedding_package" accept=".zip,application/zip" required></p>';
        echo '<p><label><input type="checkbox" name="update_existing" value="1" checked> Actualizar la boda si este paquete ya fue importado anteriormente</label></p>';
        submit_button( 'Importar boda', 'primary', 'submit', false );
        echo '</form>';

        echo '<hr>';
        echo '<h2>Exportar una boda</h2>';

        $weddings = get_posts(
            array(
                'post_type'      => BCW_Wedding_Post_Type::POST_TYPE,
                'post_status'    => array( 'publish', 'draft', 'private' ),
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
            )
        );

        if ( ! $weddings ) {
            echo '<p>Todavía no hay bodas para exportar.</p>';
            echo '</div>';
            return;
        }

        echo '<table class="widefat striped"><thead><tr><th>Boda</th><th>Estado</th><th>Acción</th></tr></thead><tbody>';
        foreach ( $weddings as $wedding ) {
            $data       = new BCW_Wedding_Data( $wedding->ID );
            $status_obj = get_post_status_object( $wedding->post_status );

            echo '<tr>';
            echo '<td><strong>' . esc_html( $data->names() ? $data->names() : $wedding->post_title ) . '</strong>';
            if ( $data->date_display() ) {
                echo '<br><small>' . esc_html( $data->date_display() ) . '</small>';
            }
            echo '</td>';
            echo '<td>' . esc_html( $status_obj ? $status_obj->label : $wedding->post_status ) . '</td>';
            echo '<td><a class="button button-primary" href="' . esc_url( self::export_url( $wedding->ID ) ) . '">Descargar paquete</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</div>';
    }

    public static function export_url( $wedding_id ) {
        return wp_nonce_url(
            add_query_arg(
                array(
                    'action'     => 'bcw_wedding_export',
                    'wedding_id' => absint( $wedding_id ),
                ),
                admin_url( 'admin-post.php' )
            ),
            'bcw_wedding_export_' . absint( $wedding_id )
        );
    }

    public static function handle_export() {
        $wedding_id = isset( $_GET['wedding_id'] ) ? absint( $_GET['wedding_id'] ) : 0;

        if (
            ! $wedding_id
            || BCW_Wedding_Post_Type::POST_TYPE !== get_post_type( $wedding_id )
            || ! current_user_can( 'edit_post', $wedding_id )
        ) {
            wp_die( 'No tienes permiso para exportar esta boda.' );
        }

        check_admin_referer( 'bcw_wedding_export_' . $wedding_id );

        $result = self::build_export_archive( $wedding_id );
        if ( is_wp_error( $result ) ) {
            wp_die( esc_html( $result->get_error_message() ) );
        }

        nocache_headers();
        header( 'Content-Type: application/zip' );
        header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $result['filename'] ) . '"' );
        header( 'Content-Length: ' . filesize( $result['path'] ) );

        readfile( $result['path'] );
        @unlink( $result['path'] );
        exit;
    }

    public static function build_export_archive( $wedding_id ) {
        $post = get_post( $wedding_id );
        if ( ! $post || BCW_Wedding_Post_Type::POST_TYPE !== $post->post_type ) {
            return new WP_Error( 'invalid_wedding', 'La boda no existe.' );
        }

        $uuid = get_post_meta( $wedding_id, 'bcw_uuid', true );
        if ( ! $uuid ) {
            $uuid = wp_generate_uuid4();
            update_post_meta( $wedding_id, 'bcw_uuid', $uuid );
        }

        $meta      = self::exportable_meta( $wedding_id );
        $media_ids = self::collect_media_ids( $meta );

        $base = self::make_temp_dir( 'bcw-export-' );
        if ( is_wp_error( $base ) ) {
            return $base;
        }

        $media_dir = trailingslashit( $base ) . 'media';
        wp_mkdir_p( $media_dir );

        $media_manifest = array();
        $files_for_zip   = array();

        foreach ( $media_ids as $attachment_id ) {
            $file = get_attached_file( $attachment_id );
            if ( ! $file || ! file_exists( $file ) ) {
                continue;
            }

            $filename = absint( $attachment_id ) . '-' . sanitize_file_name( basename( $file ) );
            $relative = 'media/' . $filename;
            $target   = trailingslashit( $base ) . $relative;

            if ( ! copy( $file, $target ) ) {
                self::remove_dir( $base );
                return new WP_Error( 'media_copy_failed', 'No se pudo preparar una de las imágenes para exportarla.' );
            }

            $attachment = get_post( $attachment_id );
            $media_manifest[ (string) $attachment_id ] = array(
                'path'        => $relative,
                'filename'    => basename( $file ),
                'mime_type'   => get_post_mime_type( $attachment_id ),
                'title'       => $attachment ? $attachment->post_title : '',
                'caption'     => $attachment ? $attachment->post_excerpt : '',
                'description' => $attachment ? $attachment->post_content : '',
                'alt'         => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
                'hash'        => hash_file( 'sha256', $file ),
            );

            $files_for_zip[] = $target;
        }

        $manifest = array(
            'format'         => 'becreative-wedding',
            'format_version' => self::FORMAT_VERSION,
            'exported_at'    => gmdate( 'c' ),
            'wedding'        => array(
                'uuid'   => $uuid,
                'title'  => $post->post_title,
                'slug'   => $post->post_name,
                'status' => $post->post_status,
                'meta'   => $meta,
            ),
            'media'          => $media_manifest,
        );

        $manifest_path = trailingslashit( $base ) . 'manifest.json';
        $written = file_put_contents(
            $manifest_path,
            wp_json_encode( $manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
        );

        if ( false === $written ) {
            self::remove_dir( $base );
            return new WP_Error( 'manifest_failed', 'No se pudo crear el archivo de exportación.' );
        }

        $files_for_zip[] = $manifest_path;

        require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';

        $tmp = wp_tempnam( 'bcw-wedding' );
        if ( ! $tmp ) {
            self::remove_dir( $base );
            return new WP_Error( 'zip_temp_failed', 'No se pudo crear un archivo temporal.' );
        }

        @unlink( $tmp );
        $zip_path = $tmp . '.zip';

        $archive = new PclZip( $zip_path );
        $created = $archive->create(
            $files_for_zip,
            PCLZIP_OPT_REMOVE_PATH,
            $base
        );

        self::remove_dir( $base );

        if ( 0 === $created ) {
            @unlink( $zip_path );
            return new WP_Error( 'zip_failed', 'No se pudo crear el ZIP de la boda.' );
        }

        return array(
            'path'     => $zip_path,
            'filename' => sanitize_title( $post->post_title ) . '-becreative-wedding.zip',
        );
    }

    public static function handle_import() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( 'No tienes permiso para importar bodas.' );
        }

        $nonce = isset( $_POST['bcw_wedding_import_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['bcw_wedding_import_nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'bcw_wedding_import' ) ) {
            wp_die( 'Solicitud no válida.' );
        }

        if ( empty( $_FILES['bcw_wedding_package']['tmp_name'] ) || ! empty( $_FILES['bcw_wedding_package']['error'] ) ) {
            self::redirect_import_error( 'Selecciona un paquete ZIP válido.' );
        }

        $tmp_name = $_FILES['bcw_wedding_package']['tmp_name'];
        $name     = isset( $_FILES['bcw_wedding_package']['name'] ) ? sanitize_file_name( $_FILES['bcw_wedding_package']['name'] ) : '';

        if ( 'zip' !== strtolower( pathinfo( $name, PATHINFO_EXTENSION ) ) ) {
            self::redirect_import_error( 'El archivo debe ser un ZIP exportado por BeCreative Wedding Sites.' );
        }

        $result = self::import_archive( $tmp_name, ! empty( $_POST['update_existing'] ) );

        if ( is_wp_error( $result ) ) {
            self::redirect_import_error( $result->get_error_message() );
        }

        wp_safe_redirect(
            add_query_arg(
                array(
                    'post_type'    => BCW_Wedding_Post_Type::POST_TYPE,
                    'page'         => 'bcw-transfer',
                    'bcw_transfer' => 'imported',
                    'post_id'      => absint( $result ),
                ),
                admin_url( 'edit.php' )
            )
        );
        exit;
    }

    public static function import_archive( $zip_path, $update_existing = true ) {
        require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $base = self::make_temp_dir( 'bcw-import-' );
        if ( is_wp_error( $base ) ) {
            return $base;
        }

        $archive  = new PclZip( $zip_path );
        $contents = $archive->listContent();

        if ( 0 === $contents || ! is_array( $contents ) ) {
            self::remove_dir( $base );
            return new WP_Error( 'invalid_zip', 'No se pudo leer el paquete ZIP.' );
        }

        foreach ( $contents as $entry ) {
            $filename = isset( $entry['filename'] ) ? (string) $entry['filename'] : '';
            $normalized = str_replace( '\\', '/', $filename );

            if (
                '' === $normalized
                || 0 === strpos( $normalized, '/' )
                || false !== strpos( $normalized, '../' )
                || preg_match( '#^[A-Za-z]:/#', $normalized )
            ) {
                self::remove_dir( $base );
                return new WP_Error( 'unsafe_zip', 'El ZIP contiene una ruta no válida.' );
            }
        }

        $extracted = $archive->extract( PCLZIP_OPT_PATH, $base );
        if ( 0 === $extracted ) {
            self::remove_dir( $base );
            return new WP_Error( 'extract_failed', 'No se pudo extraer el paquete.' );
        }

        $manifest_path = trailingslashit( $base ) . 'manifest.json';
        if ( ! file_exists( $manifest_path ) ) {
            self::remove_dir( $base );
            return new WP_Error( 'missing_manifest', 'Este ZIP no contiene una boda válida de BeCreative.' );
        }

        $manifest = json_decode( file_get_contents( $manifest_path ), true );

        if (
            ! is_array( $manifest )
            || 'becreative-wedding' !== ( $manifest['format'] ?? '' )
            || self::FORMAT_VERSION !== (int) ( $manifest['format_version'] ?? 0 )
            || empty( $manifest['wedding']['uuid'] )
            || ! isset( $manifest['wedding']['meta'] )
            || ! is_array( $manifest['wedding']['meta'] )
        ) {
            self::remove_dir( $base );
            return new WP_Error( 'invalid_manifest', 'El paquete de boda no tiene un formato compatible.' );
        }

        $wedding = $manifest['wedding'];
        $uuid    = sanitize_text_field( $wedding['uuid'] );

        $post_id = 0;
        if ( $update_existing ) {
            $existing = get_posts(
                array(
                    'post_type'      => BCW_Wedding_Post_Type::POST_TYPE,
                    'post_status'    => 'any',
                    'posts_per_page' => 1,
                    'meta_key'       => 'bcw_uuid',
                    'meta_value'     => $uuid,
                    'fields'         => 'ids',
                )
            );
            if ( $existing ) {
                $post_id = absint( $existing[0] );
            }
        }

        $postarr = array(
            'post_type'   => BCW_Wedding_Post_Type::POST_TYPE,
            'post_title'  => sanitize_text_field( $wedding['title'] ?? 'Boda importada' ),
            'post_name'   => sanitize_title( $wedding['slug'] ?? '' ),
            'post_status' => $post_id ? get_post_status( $post_id ) : 'draft',
        );

        if ( $post_id ) {
            $postarr['ID'] = $post_id;
            $post_id = wp_update_post( $postarr, true );
        } else {
            $post_id = wp_insert_post( $postarr, true );
        }

        if ( is_wp_error( $post_id ) ) {
            self::remove_dir( $base );
            return $post_id;
        }

        update_post_meta( $post_id, 'bcw_uuid', $uuid );

        $media_map = array();
        $media     = isset( $manifest['media'] ) && is_array( $manifest['media'] ) ? $manifest['media'] : array();

        foreach ( $media as $old_id => $item ) {
            if ( empty( $item['path'] ) ) {
                continue;
            }

            $source = trailingslashit( $base ) . ltrim( str_replace( '\\', '/', $item['path'] ), '/' );
            $real_base   = realpath( $base );
            $real_source = realpath( $source );

            if ( ! $real_base || ! $real_source || 0 !== strpos( $real_source, $real_base ) || ! file_exists( $real_source ) ) {
                continue;
            }

            if ( ! empty( $item['hash'] ) && hash_file( 'sha256', $real_source ) !== $item['hash'] ) {
                continue;
            }

            $attachment_id = self::find_attachment_by_hash( $item['hash'] ?? '' );

            if ( ! $attachment_id ) {
                $sideload = array(
                    'name'     => sanitize_file_name( $item['filename'] ?? basename( $real_source ) ),
                    'tmp_name' => $real_source,
                );

                $attachment_id = media_handle_sideload(
                    $sideload,
                    $post_id,
                    sanitize_text_field( $item['description'] ?? '' ),
                    array(
                        'post_title'   => sanitize_text_field( $item['title'] ?? '' ),
                        'post_excerpt' => sanitize_textarea_field( $item['caption'] ?? '' ),
                    )
                );

                if ( is_wp_error( $attachment_id ) ) {
                    continue;
                }

                if ( ! empty( $item['alt'] ) ) {
                    update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $item['alt'] ) );
                }

                if ( ! empty( $item['hash'] ) ) {
                    update_post_meta( $attachment_id, '_bcw_source_hash', sanitize_text_field( $item['hash'] ) );
                }
            }

            $media_map[ (string) $old_id ] = absint( $attachment_id );
        }

        $meta = self::sanitize_import_meta( $wedding['meta'] );
        $meta = self::remap_media_ids( $meta, $media_map );

        foreach ( $meta as $key => $value ) {
            update_post_meta( $post_id, $key, $value );
        }

        update_post_meta( $post_id, 'bcw_portal_enabled', '0' );
        delete_post_meta( $post_id, 'bcw_portal_email' );
        delete_post_meta( $post_id, 'bcw_portal_user_id' );

        self::remove_dir( $base );

        return absint( $post_id );
    }

    private static function exportable_meta( $wedding_id ) {
        $all = get_post_meta( $wedding_id );
        $out = array();

        foreach ( $all as $key => $values ) {
            if ( 0 !== strpos( $key, 'bcw_' ) || in_array( $key, self::$excluded_meta_keys, true ) || 'bcw_uuid' === $key ) {
                continue;
            }

            $out[ $key ] = get_post_meta( $wedding_id, $key, true );
        }

        return $out;
    }

    private static function sanitize_import_meta( $meta ) {
        $clean = array();

        foreach ( $meta as $key => $value ) {
            $key = sanitize_key( $key );

            if ( 0 !== strpos( $key, 'bcw_' ) || in_array( $key, self::$excluded_meta_keys, true ) || 'bcw_uuid' === $key ) {
                continue;
            }

            $clean[ $key ] = self::sanitize_meta_value( $value );
        }

        return $clean;
    }

    private static function sanitize_meta_value( $value ) {
        if ( is_array( $value ) ) {
            $clean = array();
            foreach ( $value as $key => $item ) {
                $clean[ is_string( $key ) ? sanitize_key( $key ) : $key ] = self::sanitize_meta_value( $item );
            }
            return $clean;
        }

        if ( is_bool( $value ) || is_int( $value ) || is_float( $value ) ) {
            return $value;
        }

        return is_string( $value ) ? sanitize_textarea_field( $value ) : '';
    }

    private static function collect_media_ids( $meta ) {
        $ids = array();

        foreach ( self::$single_image_keys as $key ) {
            if ( ! empty( $meta[ $key ] ) ) {
                $ids[] = absint( $meta[ $key ] );
            }
        }

        foreach ( self::$repeater_image_fields as $meta_key => $fields ) {
            if ( empty( $meta[ $meta_key ] ) || ! is_array( $meta[ $meta_key ] ) ) {
                continue;
            }

            foreach ( $meta[ $meta_key ] as $row ) {
                if ( ! is_array( $row ) ) {
                    continue;
                }
                foreach ( $fields as $field ) {
                    if ( ! empty( $row[ $field ] ) ) {
                        $ids[] = absint( $row[ $field ] );
                    }
                }
            }
        }

        return array_values( array_unique( array_filter( $ids ) ) );
    }

    private static function remap_media_ids( $meta, $media_map ) {
        foreach ( self::$single_image_keys as $key ) {
            if ( ! empty( $meta[ $key ] ) ) {
                $old = (string) absint( $meta[ $key ] );
                $meta[ $key ] = isset( $media_map[ $old ] ) ? absint( $media_map[ $old ] ) : 0;
            }
        }

        foreach ( self::$repeater_image_fields as $meta_key => $fields ) {
            if ( empty( $meta[ $meta_key ] ) || ! is_array( $meta[ $meta_key ] ) ) {
                continue;
            }

            foreach ( $meta[ $meta_key ] as $index => $row ) {
                if ( ! is_array( $row ) ) {
                    continue;
                }

                foreach ( $fields as $field ) {
                    if ( empty( $row[ $field ] ) ) {
                        continue;
                    }

                    $old = (string) absint( $row[ $field ] );
                    $meta[ $meta_key ][ $index ][ $field ] = isset( $media_map[ $old ] ) ? absint( $media_map[ $old ] ) : 0;
                }
            }
        }

        return $meta;
    }

    private static function find_attachment_by_hash( $hash ) {
        if ( ! $hash ) {
            return 0;
        }

        $posts = get_posts(
            array(
                'post_type'      => 'attachment',
                'post_status'    => 'inherit',
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'meta_key'       => '_bcw_source_hash',
                'meta_value'     => sanitize_text_field( $hash ),
            )
        );

        return $posts ? absint( $posts[0] ) : 0;
    }

    private static function make_temp_dir( $prefix ) {
        $base = trailingslashit( get_temp_dir() ) . sanitize_file_name( $prefix . wp_generate_uuid4() );

        if ( ! wp_mkdir_p( $base ) ) {
            return new WP_Error( 'temp_dir_failed', 'No se pudo crear la carpeta temporal.' );
        }

        return $base;
    }

    private static function remove_dir( $dir ) {
        if ( ! $dir || ! is_dir( $dir ) ) {
            return;
        }

        $items = scandir( $dir );
        if ( ! is_array( $items ) ) {
            return;
        }

        foreach ( $items as $item ) {
            if ( '.' === $item || '..' === $item ) {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if ( is_dir( $path ) ) {
                self::remove_dir( $path );
            } else {
                @unlink( $path );
            }
        }

        @rmdir( $dir );
    }

    private static function redirect_import_error( $message ) {
        wp_safe_redirect(
            add_query_arg(
                array(
                    'post_type'    => BCW_Wedding_Post_Type::POST_TYPE,
                    'page'         => 'bcw-transfer',
                    'bcw_transfer' => 'error',
                    'message'      => rawurlencode( $message ),
                ),
                admin_url( 'edit.php' )
            )
        );
        exit;
    }
}
