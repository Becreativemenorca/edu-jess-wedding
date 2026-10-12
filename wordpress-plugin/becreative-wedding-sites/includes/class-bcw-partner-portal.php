<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BCW_Partner_Portal {

    public static function install_role() {
        add_role(
            'bcw_couple',
            'Pareja · BeCreative Weddings',
            array(
                'read' => true,
            )
        );
    }

    public static function init() {
        add_action( 'admin_init', array( __CLASS__, 'keep_couples_out_of_admin' ) );
        add_filter( 'show_admin_bar', array( __CLASS__, 'hide_admin_bar_for_couples' ) );
    }

    public static function ensure_user_access( $wedding_id, $email, $notify = false ) {
        $email = sanitize_email( $email );
        if ( ! $email || ! is_email( $email ) ) {
            return new WP_Error( 'invalid_email', 'Introduce un email válido para el acceso de la pareja.' );
        }

        $user = get_user_by( 'email', $email );
        $created = false;

        if ( ! $user ) {
            $email_parts = explode( '@', $email );
            $base_login  = sanitize_user( $email_parts[0], true );
            if ( ! $base_login ) {
                $base_login = 'pareja';
            }

            $login = $base_login;
            $i = 1;
            while ( username_exists( $login ) ) {
                $login = $base_login . $i;
                $i++;
            }

            $user_id = wp_create_user( $login, wp_generate_password( 32, true, true ), $email );
            if ( is_wp_error( $user_id ) ) {
                return $user_id;
            }

            $user = get_user_by( 'id', $user_id );
            $user->set_role( 'bcw_couple' );
            $created = true;
        }

        update_user_meta( $user->ID, 'bcw_wedding_id', absint( $wedding_id ) );
        update_post_meta( $wedding_id, 'bcw_portal_user_id', absint( $user->ID ) );

        if ( $notify && $created ) {
            wp_new_user_notification( $user->ID, null, 'user' );
        }

        return array(
            'user_id'  => absint( $user->ID ),
            'created'  => $created,
            'email'    => $email,
        );
    }

    public static function hide_admin_bar_for_couples( $show ) {
        $user = wp_get_current_user();
        return in_array( 'bcw_couple', (array) $user->roles, true ) ? false : $show;
    }

    public static function keep_couples_out_of_admin() {
        if ( wp_doing_ajax() ) {
            return;
        }

        $user = wp_get_current_user();
        if ( ! in_array( 'bcw_couple', (array) $user->roles, true ) ) {
            return;
        }

        $wedding_id = absint( get_user_meta( $user->ID, 'bcw_wedding_id', true ) );
        if ( $wedding_id ) {
            wp_safe_redirect( BCW_Wedding_Router::url( $wedding_id, 'panel' ) );
            exit;
        }
    }

    public static function render( $wedding_id ) {
        if ( '1' !== get_post_meta( $wedding_id, 'bcw_portal_enabled', true ) && ! current_user_can( 'manage_options' ) ) {
            status_header( 404 );
            echo '<div class="bcw-empty">El panel privado no está activado para esta boda.</div>';
            return;
        }

        if ( ! is_user_logged_in() ) {
            echo '<section class="bcw-page-head"><p class="bcw-eyebrow">Panel privado</p><h1 class="bcw-page-title">Acceso de la pareja.</h1></section>';
            echo '<section class="bcw-portal-login">';
            wp_login_form(
                array(
                    'redirect' => BCW_Wedding_Router::url( $wedding_id, 'panel' ),
                    'remember' => true,
                )
            );
            echo '</section>';
            return;
        }

        if ( ! BCW_RSVP::can_view_wedding( $wedding_id ) ) {
            status_header( 403 );
            echo '<div class="bcw-empty">No tienes acceso a las confirmaciones de esta boda.</div>';
            return;
        }

        $stats = BCW_RSVP::stats( $wedding_id );
        $rows  = BCW_RSVP::get_rows( $wedding_id );

        echo '<section class="bcw-page-head"><p class="bcw-eyebrow">Panel privado</p><h1 class="bcw-page-title">Confirmaciones.</h1></section>';
        echo '<section class="bcw-portal">';

        echo '<div class="bcw-stats">';
        self::stat( 'Respuestas', $stats['total'] );
        self::stat( 'Asistirán', $stats['yes'] );
        self::stat( 'No asistirán', $stats['no'] );
        self::stat( 'Niños', $stats['children'] );
        self::stat( 'Transporte', $stats['transport'] );
        self::stat( 'Con alergias', $stats['allergies'] );
        echo '</div>';

        echo '<p><a class="bcw-button" href="' . esc_url( BCW_RSVP::export_url( $wedding_id ) ) . '">Exportar CSV</a></p>';

        if ( ! $rows ) {
            echo '<div class="bcw-empty">Todavía no hay respuestas.</div>';
            echo '</section>';
            return;
        }

        echo '<div class="bcw-table-wrap"><table class="bcw-table"><thead><tr>';
        echo '<th>Nombre</th><th>Asiste</th><th>Acompañantes</th><th>Niños</th><th>Transporte</th><th>Alergias</th><th>Fecha</th>';
        echo '</tr></thead><tbody>';

        foreach ( $rows as $row ) {
            echo '<tr>';
            echo '<td><strong>' . esc_html( $row['guest_name'] ) . '</strong>';
            if ( $row['email'] ) {
                echo '<br><small>' . esc_html( $row['email'] ) . '</small>';
            }
            echo '</td>';
            echo '<td>' . esc_html( 'yes' === $row['attendance'] ? 'Sí' : 'No' ) . '</td>';
            echo '<td>' . esc_html( $row['companions'] ) . '</td>';
            echo '<td>' . esc_html( $row['children_count'] ) . '</td>';
            echo '<td>' . esc_html( 'yes' === $row['transport'] ? 'Sí' : ( 'no' === $row['transport'] ? 'No' : '—' ) ) . '</td>';
            echo '<td>' . esc_html( $row['allergies'] ? $row['allergies'] : '—' ) . '</td>';
            echo '<td>' . esc_html( wp_date( 'd/m/Y H:i', strtotime( $row['created_at'] ) ) ) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table></div>';
        echo '</section>';
    }

    private static function stat( $label, $value ) {
        echo '<article><strong>' . esc_html( $value ) . '</strong><span>' . esc_html( $label ) . '</span></article>';
    }
}
