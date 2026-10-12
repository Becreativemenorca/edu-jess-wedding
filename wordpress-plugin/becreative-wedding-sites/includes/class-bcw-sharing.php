<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BCW_Sharing {

    public static function init() {
        add_filter( 'wpseo_opengraph_title', array( __CLASS__, 'yoast_title' ) );
        add_filter( 'wpseo_opengraph_desc', array( __CLASS__, 'yoast_description' ) );
        add_filter( 'wpseo_opengraph_image', array( __CLASS__, 'yoast_image' ) );
        add_action( 'wp_head', array( __CLASS__, 'fallback_open_graph' ), 4 );
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

    public static function fallback_open_graph() {
        if ( defined( 'WPSEO_VERSION' ) ) {
            return;
        }

        $data = self::wedding_data();
        if ( ! $data ) {
            return;
        }

        $title = trim( $data->names() . ( $data->date_display() ? ' · ' . $data->date_display() : '' ) );
        $image = $data->og_image_url( 'full' );
        if ( ! $image ) {
            $image = $data->hero_image_url( 'full' );
        }

        echo "\n" . '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
        echo '<meta property="og:type" content="website">' . "\n";
        echo '<meta property="og:url" content="' . esc_url( BCW_Wedding_Router::url( $data->id() ) ) . '">' . "\n";

        if ( $data->get( 'bcw_tagline' ) ) {
            echo '<meta property="og:description" content="' . esc_attr( $data->get( 'bcw_tagline' ) ) . '">' . "\n";
        }

        if ( $image ) {
            echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
        }
    }
}
