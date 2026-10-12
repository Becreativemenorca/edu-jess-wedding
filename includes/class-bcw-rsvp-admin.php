<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BCW_RSVP_Admin {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
    }

    public static function admin_menu() {
        add_submenu_page(
            'edit.php?post_type=' . BCW_Wedding_Post_Type::POST_TYPE,
            'Confirmaciones',
            'Confirmaciones',
            'edit_posts',
            'bcw-confirmaciones',
            array( __CLASS__, 'render_page' )
        );
    }

    public static function render_page() {
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( 'No tienes permiso para ver esta página.' );
        }

        $weddings = get_posts(
            array(
                'post_type'      => BCW_Wedding_Post_Type::POST_TYPE,
                'post_status'    => array( 'publish', 'draft', 'private' ),
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
            )
        );

        echo '<div class="wrap">';
        echo '<h1>Confirmaciones de bodas</h1>';
        echo '<p>Resumen central de las respuestas RSVP. Cada boda mantiene sus datos separados.</p>';

        if ( ! $weddings ) {
            echo '<p>Todavía no hay bodas creadas.</p></div>';
            return;
        }

        echo '<table class="widefat striped"><thead><tr>';
        echo '<th>Boda</th><th>Respuestas</th><th>Asisten</th><th>No asisten</th><th>Niños</th><th>Transporte</th><th>Acciones</th>';
        echo '</tr></thead><tbody>';

        foreach ( $weddings as $wedding ) {
            $stats = BCW_RSVP::stats( $wedding->ID );
            $data  = new BCW_Wedding_Data( $wedding->ID );

            echo '<tr>';
            echo '<td><strong>' . esc_html( $data->names() ? $data->names() : $wedding->post_title ) . '</strong>';
            if ( $data->date_display() ) {
                echo '<br><small>' . esc_html( $data->date_display() ) . '</small>';
            }
            echo '</td>';
            echo '<td>' . esc_html( $stats['total'] ) . '</td>';
            echo '<td>' . esc_html( $stats['yes'] ) . '</td>';
            echo '<td>' . esc_html( $stats['no'] ) . '</td>';
            echo '<td>' . esc_html( $stats['children'] ) . '</td>';
            echo '<td>' . esc_html( $stats['transport'] ) . '</td>';
            echo '<td>';
            echo '<a class="button button-small" href="' . esc_url( BCW_Wedding_Router::url( $wedding->ID, 'panel' ) ) . '" target="_blank">Ver</a> ';
            echo '<a class="button button-small" href="' . esc_url( BCW_RSVP::export_url( $wedding->ID ) ) . '">CSV</a>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
    }
}
