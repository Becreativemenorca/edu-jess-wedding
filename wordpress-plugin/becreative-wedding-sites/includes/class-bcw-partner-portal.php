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
