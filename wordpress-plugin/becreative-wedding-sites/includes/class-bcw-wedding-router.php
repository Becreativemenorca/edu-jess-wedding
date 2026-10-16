<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BCW_Wedding_Router {

    private static $sections = array(
        'inicio',
        'historia',
        'la-boda',
        'menorca',
        'informacion',
        'rsvp',
        'panel',
    );

    public static function init() {
        add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ), 20 );
        add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
        add_filter( 'template_include', array( __CLASS__, 'template_include' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ), 999 );
    }

    public static function add_rewrite_rules() {
        add_rewrite_rule(
            '^bodas/([^/]+)/(historia|la-boda|menorca|informacion|rsvp|panel)/?$',
            'index.php?post_type=' . BCW_Wedding_Post_Type::POST_TYPE . '&name=$matches[1]&bcw_section=$matches[2]',
            'top'
        );
    }

    public static function query_vars( $vars ) {
        $vars[] = 'bcw_section';
        return $vars;
    }

    public static function enqueue_assets() {
        if ( ! is_singular( BCW_Wedding_Post_Type::POST_TYPE ) ) {
            return;
        }

        wp_enqueue_style(
            'bcw-minimal-editorial',
            BCW_WEDDING_SITES_URL . 'assets/css/minimal-editorial.css',
            array(),
            BCW_WEDDING_SITES_VERSION
        );

        wp_enqueue_script(
            'bcw-wedding-front',
            BCW_WEDDING_SITES_URL . 'assets/js/frontend.js',
            array(),
            BCW_WEDDING_SITES_VERSION,
            true
        );
    }

    public static function template_include( $template ) {
        if ( is_singular( BCW_Wedding_Post_Type::POST_TYPE ) ) {
            $plugin_template = BCW_WEDDING_SITES_DIR . 'templates/minimal-editorial/router.php';
            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        }

        return $template;
    }

    public static function section() {
        $section = sanitize_key( get_query_var( 'bcw_section' ) );
        return in_array( $section, self::$sections, true ) ? $section : 'inicio';
    }

    public static function url( $post_id, $section = 'inicio' ) {
        $permalink = get_permalink( $post_id );

        if ( ! $permalink ) {
            $permalink = add_query_arg(
                array(
                    'post_type' => BCW_Wedding_Post_Type::POST_TYPE,
                    'p'         => absint( $post_id ),
                ),
                home_url( '/' )
            );
        }

        /*
         * Draft/private weddings can use query-string permalinks in WordPress.
         * Appending "/historia/" to one of those produces an invalid URL, so
         * keep section navigation in query args until the wedding is published.
         */
        if ( false !== strpos( $permalink, '?' ) ) {
            if ( 'inicio' === $section ) {
                return remove_query_arg( 'bcw_section', $permalink );
            }

            return add_query_arg( 'bcw_section', sanitize_key( $section ), $permalink );
        }

        $base = trailingslashit( $permalink );

        if ( 'inicio' === $section ) {
            return $base;
        }

        return $base . sanitize_key( $section ) . '/';
    }
}
