<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BCW_Wedding_Fields {

    const NONCE_ACTION = 'bcw_save_wedding';
    const NONCE_NAME   = 'bcw_wedding_nonce';

    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
        add_action( 'save_post_' . BCW_Wedding_Post_Type::POST_TYPE, array( __CLASS__, 'save' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
    }

    public static function enqueue_admin_assets( $hook ) {
        if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
            return;
        }

        $screen = get_current_screen();
        if ( ! $screen || BCW_Wedding_Post_Type::POST_TYPE !== $screen->post_type ) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script(
            'bcw-wedding-admin',
            BCW_WEDDING_SITES_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            BCW_WEDDING_SITES_VERSION,
            true
        );
        wp_enqueue_style(
            'bcw-wedding-admin',
            BCW_WEDDING_SITES_URL . 'assets/css/admin.css',
            array(),
            BCW_WEDDING_SITES_VERSION
        );
    }

    public static function add_meta_boxes() {
        add_meta_box( 'bcw-main-data', 'Datos principales', array( __CLASS__, 'render_main' ), BCW_Wedding_Post_Type::POST_TYPE, 'normal', 'high' );
        add_meta_box( 'bcw-sections', 'Secciones activas', array( __CLASS__, 'render_sections' ), BCW_Wedding_Post_Type::POST_TYPE, 'side', 'high' );
        add_meta_box( 'bcw-story', 'Nuestra historia', array( __CLASS__, 'render_story' ), BCW_Wedding_Post_Type::POST_TYPE, 'normal', 'default' );
        add_meta_box( 'bcw-wedding-day', 'La boda', array( __CLASS__, 'render_wedding_day' ), BCW_Wedding_Post_Type::POST_TYPE, 'normal', 'default' );
        add_meta_box( 'bcw-menorca', 'Destino / Nuestra Menorca', array( __CLASS__, 'render_menorca' ), BCW_Wedding_Post_Type::POST_TYPE, 'normal', 'default' );
        add_meta_box( 'bcw-information', 'Información práctica', array( __CLASS__, 'render_information' ), BCW_Wedding_Post_Type::POST_TYPE, 'normal', 'default' );
        add_meta_box( 'bcw-identity', 'Identidad visual', array( __CLASS__, 'render_identity' ), BCW_Wedding_Post_Type::POST_TYPE, 'normal', 'default' );
        add_meta_box( 'bcw-invitation', 'Invitación digital', array( __CLASS__, 'render_invitation' ), BCW_Wedding_Post_Type::POST_TYPE, 'normal', 'default' );
        add_meta_box( 'bcw-rsvp', 'RSVP', array( __CLASS__, 'render_rsvp' ), BCW_Wedding_Post_Type::POST_TYPE, 'side', 'default' );
        add_meta_box( 'bcw-portal', 'Acceso de los novios', array( __CLASS__, 'render_portal' ), BCW_Wedding_Post_Type::POST_TYPE, 'side', 'default' );
    }

    private static function value( $post_id, $key, $default = '' ) {
        $value = get_post_meta( $post_id, $key, true );
        return '' === $value ? $default : $value;
    }

    public static function render_main( $post ) {
        wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

        $fields = array(
            'bcw_partner_1' => array( 'label' => 'Nombre 1', 'type' => 'text' ),
            'bcw_partner_2' => array( 'label' => 'Nombre 2', 'type' => 'text' ),
            'bcw_date'      => array( 'label' => 'Fecha', 'type' => 'date' ),
            'bcw_venue'     => array( 'label' => 'Lugar / finca', 'type' => 'text' ),
            'bcw_location'  => array( 'label' => 'Destino', 'type' => 'text' ),
            'bcw_tagline'   => array( 'label' => 'Frase principal', 'type' => 'text' ),
            'bcw_intro'     => array( 'label' => 'Texto de bienvenida', 'type' => 'textarea' ),
            'bcw_closing'   => array( 'label' => 'Frase final', 'type' => 'text' ),
        );

        echo '<div class="bcw-grid">';
        foreach ( $fields as $key => $field ) {
            $value = self::value( $post->ID, $key );
            echo '<label class="bcw-field">';
            echo '<span>' . esc_html( $field['label'] ) . '</span>';

            if ( 'textarea' === $field['type'] ) {
                echo '<textarea name="' . esc_attr( $key ) . '" rows="4">' . esc_textarea( $value ) . '</textarea>';
            } else {
                echo '<input type="' . esc_attr( $field['type'] ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
            }
            echo '</label>';
        }

        $template = self::value( $post->ID, 'bcw_template', 'minimal-editorial' );
        echo '<label class="bcw-field"><span>Plantilla</span><select name="bcw_template">';
        echo '<option value="minimal-editorial"' . selected( $template, 'minimal-editorial', false ) . '>Minimal Editorial</option>';
        echo '<option value="mediterranean"' . selected( $template, 'mediterranean', false ) . ' disabled>Mediterranean (próximamente)</option>';
        echo '<option value="editorial"' . selected( $template, 'editorial', false ) . ' disabled>Editorial (próximamente)</option>';
        echo '</select></label>';

        self::image_picker( 'bcw_hero_image_id', 'Imagen de portada', absint( self::value( $post->ID, 'bcw_hero_image_id' ) ) );
        self::image_picker( 'bcw_closing_image_id', 'Imagen final', absint( self::value( $post->ID, 'bcw_closing_image_id' ) ) );
        echo '</div>';
    }

    public static function render_sections( $post ) {
        $defaults = array(
            'historia'    => 1,
            'la-boda'     => 1,
            'menorca'     => 1,
            'informacion' => 1,
            'rsvp'        => 1,
        );

        $saved = self::value( $post->ID, 'bcw_sections', $defaults );
        if ( ! is_array( $saved ) ) {
            $saved = $defaults;
        }

        $labels = array(
            'historia'    => 'Nuestra historia',
            'la-boda'     => 'La boda',
            'menorca'     => 'Destino / Menorca',
            'informacion' => 'Información',
            'rsvp'        => 'RSVP',
        );

        foreach ( $labels as $key => $label ) {
            echo '<p><label><input type="checkbox" name="bcw_sections[' . esc_attr( $key ) . ']" value="1"' . checked( ! empty( $saved[ $key ] ), true, false ) . '> ' . esc_html( $label ) . '</label></p>';
        }

        echo '<p class="description">Cada boda puede tener solo las secciones que necesite.</p>';
    }

    private static function image_picker( $name, $label, $image_id = 0 ) {
        $url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';

        echo '<div class="bcw-field bcw-image-field">';
        echo '<span>' . esc_html( $label ) . '</span>';
        echo '<input class="bcw-image-id" type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $image_id ) . '">';
        echo '<div class="bcw-image-preview">';
        if ( $url ) {
            echo '<img src="' . esc_url( $url ) . '" alt="">';
        }
        echo '</div>';
        echo '<button type="button" class="button bcw-pick-image">Elegir imagen</button> ';
        echo '<button type="button" class="button-link-delete bcw-remove-image">Quitar</button>';
        echo '</div>';
    }

    private static function rows( $post_id, $key ) {
        $rows = get_post_meta( $post_id, $key, true );
        return is_array( $rows ) ? $rows : array();
    }

    public static function render_story( $post ) {
        $story_title = self::value( $post->ID, 'bcw_story_title', 'Todo lo que nos trajo hasta aquí.' );
        $story_intro = self::value( $post->ID, 'bcw_story_intro' );

        echo '<div class="bcw-grid" style="margin-bottom:24px">';
        echo '<label class="bcw-field"><span>Título de “Nuestra historia”</span><input type="text" name="bcw_story_title" value="' . esc_attr( $story_title ) . '"></label>';
        echo '<label class="bcw-field"><span>Introducción de “Nuestra historia”</span><textarea name="bcw_story_intro" rows="4">' . esc_textarea( $story_intro ) . '</textarea></label>';
        echo '</div>';

        self::render_repeater(
            'bcw_story',
            self::rows( $post->ID, 'bcw_story' ),
            array(
                'year'    => 'Año / fecha',
                'title'   => 'Título',
                'text'    => 'Texto',
                'layout'  => 'Tratamiento visual',
                'image'   => 'Imagen principal',
                'image_2' => 'Imagen secundaria',
                'image_3' => 'Imagen adicional',
            ),
            'Añadir capítulo'
        );
    }

    public static function render_wedding_day( $post ) {
        self::render_repeater(
            'bcw_wedding_day',
            self::rows( $post->ID, 'bcw_wedding_day' ),
            array(
                'moment' => 'Momento',
                'place'  => 'Espacio',
                'time'   => 'Hora opcional',
                'text'   => 'Texto',
                'image'  => 'Imagen',
            ),
            'Añadir momento'
        );
    }

    public static function render_menorca( $post ) {
        self::render_repeater(
            'bcw_menorca',
            self::rows( $post->ID, 'bcw_menorca' ),
            array(
                'category' => 'Categoría',
                'place'    => 'Lugar',
                'text'     => 'Por qué es especial',
                'image'    => 'Imagen',
                'url'      => 'Enlace opcional',
            ),
            'Añadir lugar'
        );
    }

    public static function render_information( $post ) {
        self::render_repeater(
            'bcw_information',
            self::rows( $post->ID, 'bcw_information' ),
            array(
                'title' => 'Título',
                'text'  => 'Información',
            ),
            'Añadir bloque'
        );
    }

    private static function render_repeater( $name, $rows, $fields, $button_label ) {
        echo '<div class="bcw-repeater" data-name="' . esc_attr( $name ) . '">';
        echo '<div class="bcw-repeater-rows">';

        foreach ( $rows as $index => $row ) {
            self::render_repeater_row( $name, $index, $row, $fields );
        }

        echo '</div>';
        echo '<button type="button" class="button bcw-add-row">' . esc_html( $button_label ) . '</button>';
        echo '<script type="text/html" class="bcw-row-template">';
        self::render_repeater_row( $name, '__INDEX__', array(), $fields );
        echo '</script>';
        echo '</div>';
    }

    private static function render_repeater_row( $name, $index, $row, $fields ) {
        echo '<div class="bcw-repeater-row">';
        echo '<div class="bcw-row-fields">';

        foreach ( $fields as $field_key => $label ) {
            $value      = isset( $row[ $field_key ] ) ? $row[ $field_key ] : '';
            $input_name = $name . '[' . $index . '][' . $field_key . ']';

            echo '<label class="bcw-field">';
            echo '<span>' . esc_html( $label ) . '</span>';

            if ( 'text' === $field_key ) {
                echo '<textarea rows="3" name="' . esc_attr( $input_name ) . '">' . esc_textarea( $value ) . '</textarea>';
            } elseif ( in_array( $field_key, array( 'image', 'image_2', 'image_3' ), true ) ) {
                $image_id  = absint( $value );
                $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
                echo '<div class="bcw-image-field">';
                echo '<input class="bcw-image-id" type="hidden" name="' . esc_attr( $input_name ) . '" value="' . esc_attr( $image_id ) . '">';
                echo '<div class="bcw-image-preview">';
                if ( $image_url ) {
                    echo '<img src="' . esc_url( $image_url ) . '" alt="">';
                }
                echo '</div>';
                echo '<button type="button" class="button bcw-pick-image">Elegir imagen</button> ';
                echo '<button type="button" class="button-link-delete bcw-remove-image">Quitar</button>';
                echo '</div>';
            } elseif ( 'layout' === $field_key ) {
                echo '<select name="' . esc_attr( $input_name ) . '">';
                echo '<option value="standard"' . selected( $value, 'standard', false ) . '>Estándar</option>';
                echo '<option value="feature"' . selected( $value, 'feature', false ) . '>Destacado</option>';
                echo '<option value="collage"' . selected( $value, 'collage', false ) . '>Collage</option>';
                echo '</select>';
            } else {
                echo '<input type="text" name="' . esc_attr( $input_name ) . '" value="' . esc_attr( $value ) . '">';
            }

            echo '</label>';
        }

        echo '</div>';
        echo '<button type="button" class="button-link-delete bcw-remove-row">Eliminar bloque</button>';
        echo '</div>';
    }

    public static function render_identity( $post ) {
        $enabled = '1' === self::value( $post->ID, 'bcw_identity_enabled', '0' );

        echo '<div class="bcw-grid">';
        echo '<label class="bcw-field"><span><input type="checkbox" name="bcw_identity_enabled" value="1"' . checked( $enabled, true, false ) . '> Activar identidad visual para esta boda</span></label>';
        echo '<div class="bcw-field"><span>Cómo se usa</span><p class="description">El símbolo principal aparece de forma sutil al inicio de Nuestra historia. El detalle secundario aparece en Nuestra Menorca. Recomendado: PNG sin fondo y trazos limpios.</p></div>';
        self::image_picker( 'bcw_identity_primary_image_id', 'Símbolo principal (ej. Faro de Cavalleria)', absint( self::value( $post->ID, 'bcw_identity_primary_image_id' ) ) );
        self::image_picker( 'bcw_identity_secondary_image_id', 'Detalle secundario (ej. alcaparra)', absint( self::value( $post->ID, 'bcw_identity_secondary_image_id' ) ) );
        echo '</div>';
    }

    public static function render_invitation( $post ) {
        $enabled = '1' === self::value( $post->ID, 'bcw_invitation_enabled', '0' );
        $cover   = '1' === self::value( $post->ID, 'bcw_entry_cover_enabled', '0' );

        echo '<div class="bcw-grid">';
        echo '<label class="bcw-field"><span><input type="checkbox" name="bcw_invitation_enabled" value="1"' . checked( $enabled, true, false ) . '> Activar invitación digital para esta boda</span></label>';
        echo '<label class="bcw-field"><span><input type="checkbox" name="bcw_entry_cover_enabled" value="1"' . checked( $cover, true, false ) . '> Mostrar portada de entrada antes de la web</span></label>';

        self::image_picker( 'bcw_invitation_image_id', 'Imagen de invitación para compartir', absint( self::value( $post->ID, 'bcw_invitation_image_id' ) ) );
        self::image_picker( 'bcw_og_image_id', 'Imagen de vista previa al compartir enlace', absint( self::value( $post->ID, 'bcw_og_image_id' ) ) );
        self::image_picker( 'bcw_entry_cover_image_id', 'Imagen de portada de entrada', absint( self::value( $post->ID, 'bcw_entry_cover_image_id' ) ) );
        echo '</div>';
        echo '<p class="description">Estas opciones son por boda. Si no se activan, la web funciona normalmente sin invitación ni portada previa.</p>';
    }

    public static function render_rsvp( $post ) {
        $enabled  = '1' === self::value( $post->ID, 'bcw_rsvp_enabled', '1' );
        $deadline = self::value( $post->ID, 'bcw_rsvp_deadline' );
        $fields   = BCW_RSVP::enabled_fields( $post->ID );

        echo '<p><label><input type="checkbox" name="bcw_rsvp_enabled" value="1"' . checked( $enabled, true, false ) . '> Activar RSVP</label></p>';
        echo '<p><label><strong>Fecha límite</strong><br><input type="date" name="bcw_rsvp_deadline" value="' . esc_attr( $deadline ) . '"></label></p>';
        echo '<hr><strong>Preguntar por:</strong>';

        $labels = array(
            'email'         => 'Email',
            'companions'    => 'Acompañantes',
            'children'      => 'Niños',
            'transport'     => 'Transporte',
            'arrival'       => 'Llegada',
            'departure'     => 'Salida',
            'allergies'     => 'Alergias / intolerancias',
            'special_needs' => 'Necesidades especiales',
        );

        foreach ( $labels as $key => $label ) {
            echo '<p><label><input type="checkbox" name="bcw_rsvp_fields[' . esc_attr( $key ) . ']" value="1"' . checked( ! empty( $fields[ $key ] ), true, false ) . '> ' . esc_html( $label ) . '</label></p>';
        }
    }

    public static function render_portal( $post ) {
        $enabled = '1' === self::value( $post->ID, 'bcw_portal_enabled', '0' );
        $email   = self::value( $post->ID, 'bcw_portal_email' );
        $user_id = absint( self::value( $post->ID, 'bcw_portal_user_id' ) );

        echo '<p><label><input type="checkbox" name="bcw_portal_enabled" value="1"' . checked( $enabled, true, false ) . '> Permitir panel privado</label></p>';
        echo '<p><label><strong>Email de acceso</strong><br><input type="email" name="bcw_portal_email" value="' . esc_attr( $email ) . '" style="width:100%"></label></p>';

        if ( $user_id ) {
            $user = get_user_by( 'id', $user_id );
            if ( $user ) {
                echo '<p class="description">Usuario asociado: <strong>' . esc_html( $user->user_email ) . '</strong></p>';
                echo '<p><a href="' . esc_url( BCW_Wedding_Router::url( $post->ID, 'panel' ) ) . '" target="_blank">Abrir panel ↗</a></p>';
            }
        }

        echo '<button type="submit" name="bcw_create_couple_access" value="1" class="button">Crear / asociar acceso</button>';
        echo '<p class="description">Si el email no tiene usuario, WordPress creará uno y enviará el enlace para establecer contraseña.</p>';
    }

    public static function save( $post_id ) {
        if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $simple_text = array(
            'bcw_partner_1',
            'bcw_partner_2',
            'bcw_date',
            'bcw_venue',
            'bcw_location',
            'bcw_tagline',
            'bcw_story_title',
            'bcw_closing',
            'bcw_template',
            'bcw_rsvp_deadline',
            'bcw_portal_email',
        );

        foreach ( $simple_text as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                $value = 'bcw_portal_email' === $key
                    ? sanitize_email( wp_unslash( $_POST[ $key ] ) )
                    : sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
                update_post_meta( $post_id, $key, $value );
            }
        }

        if ( isset( $_POST['bcw_intro'] ) ) {
            update_post_meta( $post_id, 'bcw_intro', sanitize_textarea_field( wp_unslash( $_POST['bcw_intro'] ) ) );
        }

        if ( isset( $_POST['bcw_story_intro'] ) ) {
            update_post_meta( $post_id, 'bcw_story_intro', sanitize_textarea_field( wp_unslash( $_POST['bcw_story_intro'] ) ) );
        }

        $image_fields = array(
            'bcw_hero_image_id',
            'bcw_closing_image_id',
            'bcw_invitation_image_id',
            'bcw_og_image_id',
            'bcw_entry_cover_image_id',
            'bcw_identity_primary_image_id',
            'bcw_identity_secondary_image_id',
        );

        foreach ( $image_fields as $key ) {
            update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? absint( $_POST[ $key ] ) : 0 );
        }

        $toggle_fields = array(
            'bcw_rsvp_enabled',
            'bcw_portal_enabled',
            'bcw_invitation_enabled',
            'bcw_entry_cover_enabled',
            'bcw_identity_enabled',
        );

        foreach ( $toggle_fields as $key ) {
            update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '0' );
        }

        $section_keys = array( 'historia', 'la-boda', 'menorca', 'informacion', 'rsvp' );
        $sections = array();
        foreach ( $section_keys as $key ) {
            $sections[ $key ] = ! empty( $_POST['bcw_sections'][ $key ] ) ? 1 : 0;
        }
        update_post_meta( $post_id, 'bcw_sections', $sections );

        $rsvp_keys = array( 'email', 'companions', 'children', 'transport', 'arrival', 'departure', 'allergies', 'special_needs' );
        $rsvp_fields = array();
        foreach ( $rsvp_keys as $key ) {
            $rsvp_fields[ $key ] = ! empty( $_POST['bcw_rsvp_fields'][ $key ] ) ? 1 : 0;
        }
        update_post_meta( $post_id, 'bcw_rsvp_fields', $rsvp_fields );

        $repeaters = array(
            'bcw_story'       => array( 'year', 'title', 'text', 'layout', 'image', 'image_2', 'image_3' ),
            'bcw_wedding_day' => array( 'moment', 'place', 'time', 'text', 'image' ),
            'bcw_menorca'     => array( 'category', 'place', 'text', 'image', 'url' ),
            'bcw_information' => array( 'title', 'text' ),
        );

        foreach ( $repeaters as $key => $allowed_fields ) {
            $clean_rows = array();
            $raw_rows = isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : array();

            foreach ( $raw_rows as $row ) {
                if ( ! is_array( $row ) ) {
                    continue;
                }

                $clean = array();
                foreach ( $allowed_fields as $field ) {
                    $value = isset( $row[ $field ] ) ? $row[ $field ] : '';

                    if ( 'text' === $field ) {
                        $clean[ $field ] = sanitize_textarea_field( $value );
                    } elseif ( in_array( $field, array( 'image', 'image_2', 'image_3' ), true ) ) {
                        $clean[ $field ] = absint( $value );
                    } elseif ( 'url' === $field ) {
                        $clean[ $field ] = esc_url_raw( $value );
                    } elseif ( 'layout' === $field ) {
                        $clean[ $field ] = in_array( $value, array( 'standard', 'feature', 'collage' ), true ) ? $value : 'standard';
                    } else {
                        $clean[ $field ] = sanitize_text_field( $value );
                    }
                }

                if ( array_filter( $clean ) ) {
                    $clean_rows[] = $clean;
                }
            }

            update_post_meta( $post_id, $key, $clean_rows );
        }

        if ( ! empty( $_POST['bcw_create_couple_access'] ) && ! empty( $_POST['bcw_portal_email'] ) ) {
            BCW_Partner_Portal::ensure_user_access(
                $post_id,
                sanitize_email( wp_unslash( $_POST['bcw_portal_email'] ) ),
                true
            );
        }
    }
}
