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
        add_meta_box(
            'bcw-main-data',
            'Datos principales',
            array( __CLASS__, 'render_main' ),
            BCW_Wedding_Post_Type::POST_TYPE,
            'normal',
            'high'
        );

        add_meta_box(
            'bcw-story',
            'Nuestra historia',
            array( __CLASS__, 'render_story' ),
            BCW_Wedding_Post_Type::POST_TYPE,
            'normal',
            'default'
        );

        add_meta_box(
            'bcw-wedding-day',
            'La boda',
            array( __CLASS__, 'render_wedding_day' ),
            BCW_Wedding_Post_Type::POST_TYPE,
            'normal',
            'default'
        );

        add_meta_box(
            'bcw-menorca',
            'Nuestra Menorca',
            array( __CLASS__, 'render_menorca' ),
            BCW_Wedding_Post_Type::POST_TYPE,
            'normal',
            'default'
        );

        add_meta_box(
            'bcw-information',
            'Información práctica',
            array( __CLASS__, 'render_information' ),
            BCW_Wedding_Post_Type::POST_TYPE,
            'normal',
            'default'
        );

        add_meta_box(
            'bcw-rsvp',
            'RSVP',
            array( __CLASS__, 'render_rsvp' ),
            BCW_Wedding_Post_Type::POST_TYPE,
            'side',
            'default'
        );
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

        self::image_picker(
            'bcw_hero_image_id',
            'Imagen de portada',
            absint( self::value( $post->ID, 'bcw_hero_image_id' ) )
        );
        echo '</div>';
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
        self::render_repeater(
            'bcw_story',
            self::rows( $post->ID, 'bcw_story' ),
            array(
                'year'  => 'Año / fecha',
                'title' => 'Título',
                'text'  => 'Texto',
                'image' => 'Imagen',
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
            $value = isset( $row[ $field_key ] ) ? $row[ $field_key ] : '';
            $input_name = $name . '[' . $index . '][' . $field_key . ']';

            echo '<label class="bcw-field">';
            echo '<span>' . esc_html( $label ) . '</span>';

            if ( 'text' === $field_key ) {
                echo '<textarea rows="3" name="' . esc_attr( $input_name ) . '">' . esc_textarea( $value ) . '</textarea>';
            } elseif ( 'image' === $field_key ) {
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
            } else {
                echo '<input type="text" name="' . esc_attr( $input_name ) . '" value="' . esc_attr( $value ) . '">';
            }

            echo '</label>';
        }

        echo '</div>';
        echo '<button type="button" class="button-link-delete bcw-remove-row">Eliminar bloque</button>';
        echo '</div>';
    }

    public static function render_rsvp( $post ) {
        $enabled  = '1' === self::value( $post->ID, 'bcw_rsvp_enabled', '1' );
        $deadline = self::value( $post->ID, 'bcw_rsvp_deadline' );

        echo '<p><label><input type="checkbox" name="bcw_rsvp_enabled" value="1"' . checked( $enabled, true, false ) . '> Activar RSVP</label></p>';
        echo '<p><label><strong>Fecha límite</strong><br><input type="date" name="bcw_rsvp_deadline" value="' . esc_attr( $deadline ) . '"></label></p>';
        echo '<p class="description">El guardado real de respuestas se añadirá después de validar la plantilla y el flujo de edición.</p>';
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
            'bcw_template',
            'bcw_rsvp_deadline',
        );

        foreach ( $simple_text as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
            }
        }

        if ( isset( $_POST['bcw_intro'] ) ) {
            update_post_meta( $post_id, 'bcw_intro', sanitize_textarea_field( wp_unslash( $_POST['bcw_intro'] ) ) );
        }

        update_post_meta( $post_id, 'bcw_hero_image_id', isset( $_POST['bcw_hero_image_id'] ) ? absint( $_POST['bcw_hero_image_id'] ) : 0 );
        update_post_meta( $post_id, 'bcw_rsvp_enabled', isset( $_POST['bcw_rsvp_enabled'] ) ? '1' : '0' );

        $repeaters = array(
            'bcw_story'       => array( 'year', 'title', 'text', 'image' ),
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
                    } elseif ( 'image' === $field ) {
                        $clean[ $field ] = absint( $value );
                    } elseif ( 'url' === $field ) {
                        $clean[ $field ] = esc_url_raw( $value );
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
    }
}
