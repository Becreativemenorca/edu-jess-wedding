<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BCW_Sharing {

    public static function init() {
        add_filter( 'wpseo_opengraph_title', array( __CLASS__, 'yoast_title' ) );
        add_filter( 'wpseo_opengraph_desc', array( __CLASS__, 'yoast_description' ) );
        add_filter( 'wpseo_opengraph_image', array( __CLASS__, 'yoast_image' ) );
        add_filter( 'wpseo_opengraph_url', array( __CLASS__, 'yoast_url' ) );
        add_filter( 'wp_robots', array( __CLASS__, 'robots_noindex' ) );
        add_filter( 'wp_sitemaps_post_types', array( __CLASS__, 'exclude_from_core_sitemap' ) );
        add_filter( 'wpseo_sitemap_exclude_post_type', array( __CLASS__, 'exclude_from_yoast_sitemap' ), 10, 2 );
        add_filter( 'wpseo_frontend_presenters', array( __CLASS__, 'strip_yoast_social_presenters' ) );
        add_action( 'wp_head', array( __CLASS__, 'fallback_open_graph' ), 1 );
    }

    private static function wedding_data() {
        if ( ! is_singular( BCW_Wedding_Post_Type::POST_TYPE ) ) {
            return null;
        }

        return new BCW_Wedding_Data( get_queried_object_id() );
    }

    public static function yoast_title( $title ) {
        $data = self::wedding_data();
        if ( ! $data ) {
            return $title;
        }

        return trim( $data->names() . ( $data->date_display() ? ' · ' . $data->date_display() : '' ) );
    }

    public static function yoast_description( $description ) {
        $data = self::wedding_data();
        if ( ! $data || ! $data->get( 'bcw_tagline' ) ) {
            return $description;
        }

        return $data->get( 'bcw_tagline' );
    }

    public static function yoast_image( $image ) {
        $data = self::wedding_data();
        if ( ! $data ) {
            return $image;
        }

        $custom = $data->og_image_url( 'full' );
        if ( ! $custom ) {
            $custom = $data->hero_image_url( 'full' );
        }

        return $custom ? $custom : $image;
    }

    public static function yoast_url( $url ) {
        $data = self::wedding_data();
        if ( ! $data ) {
            return $url;
        }

        return BCW_Wedding_Router::url( $data->id() );
    }

    public static function robots_noindex( $robots ) {
        if ( is_singular( BCW_Wedding_Post_Type::POST_TYPE ) ) {
            $robots['noindex'] = true;
            unset( $robots['index'] );
        }

        return $robots;
    }

    public static function exclude_from_core_sitemap( $post_types ) {
        unset( $post_types[ BCW_Wedding_Post_Type::POST_TYPE ] );
        return $post_types;
    }

    public static function exclude_from_yoast_sitemap( $excluded, $post_type ) {
        if ( BCW_Wedding_Post_Type::POST_TYPE === $post_type ) {
            return true;
        }

        return $excluded;
    }

    public static function strip_yoast_social_presenters( $presenters ) {
        if ( ! self::wedding_data() ) {
            return $presenters;
        }

        return array_values(
            array_filter(
                $presenters,
                static function( $presenter ) {
                    $class = is_object( $presenter ) ? get_class( $presenter ) : '';
                    return false === strpos( $class, '\\Open_Graph\\' )
                        && false === strpos( $class, '\\Twitter\\' );
                }
            )
        );
    }

    public static function fallback_open_graph() {
        $data = self::wedding_data();
        if ( ! $data ) {
            return;
        }

        $title = trim( $data->names() . ( $data->date_display() ? ' · ' . $data->date_display() : '' ) );
        $url   = BCW_Wedding_Router::url( $data->id() );
        $image = $data->og_image_url( 'full' );
        if ( ! $image ) {
            $image = $data->hero_image_url( 'full' );
        }

        $description = $data->get( 'bcw_tagline' );
        if ( ! $description ) {
            $description = $data->get( 'bcw_closing' );
        }
        if ( ! $description ) {
            $description = $data->get( 'bcw_intro' );
        }

        echo "\n<!-- BeCreative Wedding social preview -->\n";
        echo '<meta property="og:locale" content="es_ES">' . "\n";
        echo '<meta property="og:type" content="website">' . "\n";
        echo '<meta property="og:site_name" content="Be Creative Weddings">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";

        if ( $description ) {
            echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
        }

        if ( $image ) {
            echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
            echo '<meta property="og:image:secure_url" content="' . esc_url( $image ) . '">' . "\n";

            $image_id = absint( $data->get( 'bcw_og_image_id' ) );
            if ( ! $image_id ) {
                $image_id = absint( $data->get( 'bcw_hero_image_id' ) );
            }

            if ( $image_id ) {
                $meta = wp_get_attachment_metadata( $image_id );
                $mime = get_post_mime_type( $image_id );
                if ( $mime ) {
                    echo '<meta property="og:image:type" content="' . esc_attr( $mime ) . '">' . "\n";
                }
                if ( ! empty( $meta['width'] ) && ! empty( $meta['height'] ) ) {
                    echo '<meta property="og:image:width" content="' . esc_attr( absint( $meta['width'] ) ) . '">' . "\n";
                    echo '<meta property="og:image:height" content="' . esc_attr( absint( $meta['height'] ) ) . '">' . "\n";
                }
                $alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
                if ( $alt ) {
                    echo '<meta property="og:image:alt" content="' . esc_attr( $alt ) . '">' . "\n";
                }
            }

            echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
            echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
        }

        echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
        if ( $description ) {
            echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";
        }
        echo "<!-- /BeCreative Wedding social preview -->\n";
    }
}
