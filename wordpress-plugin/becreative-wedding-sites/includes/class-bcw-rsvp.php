<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BCW_RSVP {

    public static function table_name() {
        global $wpdb;
        return $wpdb->prefix . 'bcw_rsvp';
    }

    public static function install_table() {
        global $wpdb;

        $table_name      = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            wedding_id bigint(20) unsigned NOT NULL,
            submission_uuid char(36) NOT NULL,
            guest_name varchar(190) NOT NULL,
            email varchar(190) NOT NULL DEFAULT '',
            attendance varchar(20) NOT NULL DEFAULT '',
            companions text NULL,
            children_count smallint(5) unsigned NOT NULL DEFAULT 0,
            transport varchar(20) NOT NULL DEFAULT '',
            arrival varchar(190) NOT NULL DEFAULT '',
            departure varchar(190) NOT NULL DEFAULT '',
            allergies text NULL,
            special_needs text NULL,
            answers longtext NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY wedding_id (wedding_id),
            KEY attendance (attendance),
            KEY email (email)
        ) {$charset_collate};";

        dbDelta( $sql );
    }

    public static function init() {
        add_action( 'admin_post_nopriv_bcw_rsvp_submit', array( __CLASS__, 'handle_submit' ) );
        add_action( 'admin_post_bcw_rsvp_submit', array( __CLASS__, 'handle_submit' ) );
        add_action( 'admin_post_bcw_rsvp_export', array( __CLASS__, 'handle_export' ) );
        add_action( 'admin_post_bcw_rsvp_delete', array( __CLASS__, 'handle_delete' ) );
    }

    public static function enabled_fields( $wedding_id ) {
        $defaults = array(
            'email'         => true,
            'companions'    => true,
            'children'      => true,
            'transport'     => true,
            'arrival'       => false,
            'departure'     => false,
            'allergies'     => true,
            'special_needs' => true,
        );

        $saved = get_post_meta( $wedding_id, 'bcw_rsvp_fields', true );
        if ( ! is_array( $saved ) ) {
            return $defaults;
        }

        foreach ( $defaults as $key => $default ) {
            $defaults[ $key ] = ! empty( $saved[ $key ] );
        }

        return $defaults;
    }

    public static function render_form( $wedding_id ) {
        if ( '1' !== get_post_meta( $wedding_id, 'bcw_rsvp_enabled', true ) ) {
            echo '<div class="bcw-empty">La confirmación todavía no está abierta.</div>';
            return;
        }

        $fields   = self::enabled_fields( $wedding_id );
        $deadline = get_post_meta( $wedding_id, 'bcw_rsvp_deadline', true );
        $status   = isset( $_GET['bcw_rsvp'] ) ? sanitize_key( wp_unslash( $_GET['bcw_rsvp'] ) ) : '';

        if ( 'ok' === $status ) {
            echo '<div class="bcw-rsvp-success" role="status"><strong>Gracias.</strong><p>Hemos recibido vuestra respuesta.</p></div>';
        } elseif ( 'error' === $status ) {
            echo '<div class="bcw-rsvp-error" role="alert">No hemos podido guardar la respuesta. Inténtalo de nuevo.</div>';
        }

        if ( $deadline ) {
            echo '<p class="bcw-rsvp-deadline">Fecha límite: ' . esc_html( wp_date( 'd · m · Y', strtotime( $deadline ) ) ) . '</p>';
        }

        echo '<form class="bcw-rsvp-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
        echo '<input type="hidden" name="action" value="bcw_rsvp_submit">';
        echo '<input type="hidden" name="wedding_id" value="' . esc_attr( $wedding_id ) . '">';
        wp_nonce_field( 'bcw_rsvp_submit_' . $wedding_id, 'bcw_rsvp_nonce' );

        echo '<div class="bcw-hp" aria-hidden="true"><label>No rellenar<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>';

        self::text_field( 'guest_name', 'Nombre y apellidos', true );

        if ( $fields['email'] ) {
            self::text_field( 'email', 'Email', false, 'email' );
        }

        echo '<fieldset><legend>¿Asistirás?</legend>';
        echo '<label><input type="radio" name="attendance" value="yes" required> Sí</label>';
        echo '<label><input type="radio" name="attendance" value="no" required> No</label>';
        echo '</fieldset>';

        if ( $fields['companions'] ) {
            self::textarea_field( 'companions', 'Acompañantes', 'Escribe sus nombres si procede.' );
        }

        if ( $fields['children'] ) {
            echo '<label><span>Número de niños</span><input type="number" min="0" max="20" name="children_count" value="0"></label>';
        }

        if ( $fields['transport'] ) {
            echo '<fieldset><legend>¿Necesitas transporte organizado?</legend>';
            echo '<label><input type="radio" name="transport" value="yes"> Sí</label>';
            echo '<label><input type="radio" name="transport" value="no"> No</label>';
            echo '</fieldset>';
        }

        if ( $fields['arrival'] ) {
            self::text_field( 'arrival', 'Llegada aproximada', false );
        }

        if ( $fields['departure'] ) {
            self::text_field( 'departure', 'Salida aproximada', false );
        }

        if ( $fields['allergies'] ) {
            self::textarea_field( 'allergies', 'Alergias o intolerancias', 'Indica a quién corresponde cada una.' );
        }

        if ( $fields['special_needs'] ) {
            self::textarea_field( 'special_needs', 'Necesidades especiales', 'Solo si necesitamos tenerlo en cuenta para la celebración.' );
        }

        echo '<label class="bcw-rsvp-consent"><input type="checkbox" name="privacy_consent" value="1" required> Confirmo que los datos enviados pueden utilizarse para gestionar esta boda y sus necesidades logísticas.</label>';
        echo '<button class="bcw-button" type="submit">Enviar confirmación</button>';
        echo '</form>';
    }

    private static function text_field( $name, $label, $required = false, $type = 'text' ) {
        echo '<label><span>' . esc_html( $label ) . '</span><input type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '"' . ( $required ? ' required' : '' ) . '></label>';
    }

    private static function textarea_field( $name, $label, $placeholder = '' ) {
        echo '<label><span>' . esc_html( $label ) . '</span><textarea name="' . esc_attr( $name ) . '" rows="3" placeholder="' . esc_attr( $placeholder ) . '"></textarea></label>';
    }

    public static function handle_submit() {
        $wedding_id = isset( $_POST['wedding_id'] ) ? absint( $_POST['wedding_id'] ) : 0;

        if ( ! $wedding_id || BCW_Wedding_Post_Type::POST_TYPE !== get_post_type( $wedding_id ) ) {
            wp_die( 'Boda no válida.' );
        }

        $nonce = isset( $_POST['bcw_rsvp_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['bcw_rsvp_nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'bcw_rsvp_submit_' . $wedding_id ) ) {
            wp_die( 'Solicitud no válida.' );
        }

        if ( ! empty( $_POST['website'] ) ) {
            self::redirect_after_submit( $wedding_id, 'ok' );
        }

        if ( empty( $_POST['privacy_consent'] ) ) {
            self::redirect_after_submit( $wedding_id, 'error' );
        }

        $guest_name = isset( $_POST['guest_name'] ) ? sanitize_text_field( wp_unslash( $_POST['guest_name'] ) ) : '';
        $attendance = isset( $_POST['attendance'] ) ? sanitize_key( wp_unslash( $_POST['attendance'] ) ) : '';

        if ( ! $guest_name || ! in_array( $attendance, array( 'yes', 'no' ), true ) ) {
            self::redirect_after_submit( $wedding_id, 'error' );
        }

        $fields = self::enabled_fields( $wedding_id );
        $now    = current_time( 'mysql' );

        $row = array(
            'wedding_id'      => $wedding_id,
            'submission_uuid' => wp_generate_uuid4(),
            'guest_name'      => $guest_name,
            'email'           => $fields['email'] && isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
            'attendance'      => $attendance,
            'companions'      => $fields['companions'] && isset( $_POST['companions'] ) ? sanitize_textarea_field( wp_unslash( $_POST['companions'] ) ) : '',
            'children_count'  => $fields['children'] && isset( $_POST['children_count'] ) ? min( 20, absint( $_POST['children_count'] ) ) : 0,
            'transport'       => $fields['transport'] && isset( $_POST['transport'] ) ? sanitize_key( wp_unslash( $_POST['transport'] ) ) : '',
            'arrival'         => $fields['arrival'] && isset( $_POST['arrival'] ) ? sanitize_text_field( wp_unslash( $_POST['arrival'] ) ) : '',
            'departure'       => $fields['departure'] && isset( $_POST['departure'] ) ? sanitize_text_field( wp_unslash( $_POST['departure'] ) ) : '',
            'allergies'       => $fields['allergies'] && isset( $_POST['allergies'] ) ? sanitize_textarea_field( wp_unslash( $_POST['allergies'] ) ) : '',
            'special_needs'   => $fields['special_needs'] && isset( $_POST['special_needs'] ) ? sanitize_textarea_field( wp_unslash( $_POST['special_needs'] ) ) : '',
            'answers'         => wp_json_encode( array() ),
            'created_at'      => $now,
            'updated_at'      => $now,
        );

        global $wpdb;
        $inserted = $wpdb->insert(
            self::table_name(),
            $row,
            array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
        );

        if ( $inserted ) {
            self::send_notification( $wedding_id, $row );
            self::send_guest_confirmation( $wedding_id, $row );
        }

        self::redirect_after_submit( $wedding_id, $inserted ? 'ok' : 'error' );
    }

    private static function send_notification( $wedding_id, $row ) {
        $to = get_post_meta( $wedding_id, 'bcw_rsvp_notification_email', true );
        if ( ! $to ) {
            $to = get_option( 'admin_email' );
        }

        $to = sanitize_email( $to );
        if ( ! $to || ! is_email( $to ) ) {
            return;
        }

        $wedding_name = get_the_title( $wedding_id );
        $attendance   = 'yes' === $row['attendance'] ? 'Sí' : 'No';
        $transport    = 'yes' === $row['transport'] ? 'Sí' : ( 'no' === $row['transport'] ? 'No' : '—' );

        $subject = sprintf( '[%s] Nueva confirmación: %s', $wedding_name, $row['guest_name'] );

        $lines = array(
            'Nueva confirmación de asistencia',
            '',
            'Boda: ' . $wedding_name,
            'Nombre: ' . $row['guest_name'],
            'Asiste: ' . $attendance,
            'Email: ' . ( $row['email'] ? $row['email'] : '—' ),
            'Acompañantes: ' . ( $row['companions'] ? $row['companions'] : '—' ),
            'Niños: ' . absint( $row['children_count'] ),
            'Transporte: ' . $transport,
            'Llegada: ' . ( $row['arrival'] ? $row['arrival'] : '—' ),
            'Salida: ' . ( $row['departure'] ? $row['departure'] : '—' ),
            'Alergias / intolerancias: ' . ( $row['allergies'] ? $row['allergies'] : '—' ),
            'Necesidades especiales: ' . ( $row['special_needs'] ? $row['special_needs'] : '—' ),
            '',
            'La respuesta también está guardada en WordPress: Bodas → Confirmaciones.',
        );

        wp_mail( $to, $subject, implode( "\n", $lines ) );
    }

    private static function send_guest_confirmation( $wedding_id, $row ) {
        $guest_email = isset( $row['email'] ) ? sanitize_email( $row['email'] ) : '';
        if ( ! $guest_email || ! is_email( $guest_email ) ) {
            return;
        }

        $data       = new BCW_Wedding_Data( $wedding_id );
        $names      = $data->names() ? $data->names() : get_the_title( $wedding_id );
        $date       = $data->date_display();
        $location   = $data->get( 'bcw_location' );
        $attendance = 'yes' === $row['attendance'] ? 'Sí, asistirás.' : 'Hemos recibido que no podrás asistir.';

        $subject = sprintf( 'Confirmación recibida · %s', $names );

        $lines = array(
            'Hola ' . $row['guest_name'] . ',',
            '',
            'Hemos recibido correctamente tu confirmación para la boda de ' . $names . '.',
            '',
            'Asistencia: ' . $attendance,
        );

        if ( $date || $location ) {
            $lines[] = 'Boda: ' . trim( $location . ( $location && $date ? ' · ' : '' ) . $date );
        }

        $lines[] = '';
        $lines[] = 'Puedes volver a consultar la invitación aquí:';
        $lines[] = BCW_Wedding_Router::url( $wedding_id );
        $lines[] = '';
        $lines[] = 'Gracias por responder.';

        wp_mail( $guest_email, $subject, implode( "\n", $lines ) );
    }

    private static function redirect_after_submit( $wedding_id, $status ) {
        $url = add_query_arg(
            'bcw_rsvp',
            $status,
            BCW_Wedding_Router::url( $wedding_id, 'rsvp' )
        );
        wp_safe_redirect( $url );
        exit;
    }

    public static function delete_url( $wedding_id, $response_id ) {
        return wp_nonce_url(
            add_query_arg(
                array(
                    'action'      => 'bcw_rsvp_delete',
                    'wedding_id'  => absint( $wedding_id ),
                    'response_id' => absint( $response_id ),
                ),
                admin_url( 'admin-post.php' )
            ),
            'bcw_rsvp_delete_' . absint( $wedding_id ) . '_' . absint( $response_id )
        );
    }

    public static function handle_delete() {
        $wedding_id  = isset( $_GET['wedding_id'] ) ? absint( $_GET['wedding_id'] ) : 0;
        $response_id = isset( $_GET['response_id'] ) ? absint( $_GET['response_id'] ) : 0;

        if (
            ! $wedding_id
            || ! $response_id
            || BCW_Wedding_Post_Type::POST_TYPE !== get_post_type( $wedding_id )
            || ! current_user_can( 'edit_post', $wedding_id )
        ) {
            wp_die( 'No tienes permiso para borrar esta confirmación.' );
        }

        check_admin_referer( 'bcw_rsvp_delete_' . $wedding_id . '_' . $response_id );

        global $wpdb;
        $deleted = $wpdb->delete(
            self::table_name(),
            array(
                'id'         => $response_id,
                'wedding_id' => $wedding_id,
            ),
            array( '%d', '%d' )
        );

        $url = add_query_arg(
            'bcw_rsvp_deleted',
            $deleted ? '1' : '0',
            BCW_Wedding_Router::url( $wedding_id, 'panel' )
        );

        wp_safe_redirect( $url );
        exit;
    }

    public static function get_rows( $wedding_id ) {
        global $wpdb;
        $table = self::table_name();
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE wedding_id = %d ORDER BY created_at DESC",
                $wedding_id
            ),
            ARRAY_A
        );
    }

    public static function stats( $wedding_id ) {
        $rows = self::get_rows( $wedding_id );
        $stats = array(
            'total'      => count( $rows ),
            'yes'        => 0,
            'no'         => 0,
            'children'   => 0,
            'transport'  => 0,
            'allergies'  => 0,
        );

        foreach ( $rows as $row ) {
            if ( 'yes' === $row['attendance'] ) {
                $stats['yes']++;
            }
            if ( 'no' === $row['attendance'] ) {
                $stats['no']++;
            }
            $stats['children'] += absint( $row['children_count'] );
            if ( 'yes' === $row['transport'] ) {
                $stats['transport']++;
            }
            if ( ! empty( $row['allergies'] ) ) {
                $stats['allergies']++;
            }
        }

        return $stats;
    }

    public static function can_view_wedding( $wedding_id, $user_id = 0 ) {
        $user_id = $user_id ? absint( $user_id ) : get_current_user_id();

        if ( user_can( $user_id, 'manage_options' ) || user_can( $user_id, 'edit_post', $wedding_id ) ) {
            return true;
        }

        return $wedding_id === absint( get_user_meta( $user_id, 'bcw_wedding_id', true ) );
    }

    public static function export_url( $wedding_id ) {
        return wp_nonce_url(
            add_query_arg(
                array(
                    'action'     => 'bcw_rsvp_export',
                    'wedding_id' => $wedding_id,
                ),
                admin_url( 'admin-post.php' )
            ),
            'bcw_rsvp_export_' . $wedding_id
        );
    }

    public static function handle_export() {
        $wedding_id = isset( $_GET['wedding_id'] ) ? absint( $_GET['wedding_id'] ) : 0;

        if ( ! $wedding_id || ! self::can_view_wedding( $wedding_id ) ) {
            wp_die( 'No tienes permiso para exportar estas respuestas.' );
        }

        check_admin_referer( 'bcw_rsvp_export_' . $wedding_id );

        $rows = self::get_rows( $wedding_id );
        $filename = sanitize_file_name( get_the_title( $wedding_id ) . '-rsvp.csv' );

        nocache_headers();
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

        $output = fopen( 'php://output', 'w' );
        fputs( $output, "\xEF\xBB\xBF" );

        fputcsv( $output, array(
            'Nombre',
            'Email',
            'Asiste',
            'Acompañantes',
            'Niños',
            'Transporte',
            'Llegada',
            'Salida',
            'Alergias / intolerancias',
            'Necesidades especiales',
            'Fecha de respuesta',
        ) );

        foreach ( $rows as $row ) {
            fputcsv( $output, array(
                $row['guest_name'],
                $row['email'],
                $row['attendance'],
                $row['companions'],
                $row['children_count'],
                $row['transport'],
                $row['arrival'],
                $row['departure'],
                $row['allergies'],
                $row['special_needs'],
                $row['created_at'],
            ) );
        }

        fclose( $output );
        exit;
    }
}
