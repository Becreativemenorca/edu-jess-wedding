<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BCW_Wedding_Data {

    private $post_id;

    public function __construct( $post_id ) {
        $this->post_id = absint( $post_id );
    }

    public function id() {
        return $this->post_id;
    }

    public function get( $key, $default = '' ) {
        $value = get_post_meta( $this->post_id, $key, true );
        return '' === $value ? $default : $value;
    }

    public function rows( $key ) {
        $rows = $this->get( $key, array() );
        return is_array( $rows ) ? $rows : array();
    }

    public function partner_1() {
        return $this->get( 'bcw_partner_1' );
    }

    public function partner_2() {
        return $this->get( 'bcw_partner_2' );
    }

    public function names() {
        return trim( $this->partner_1() . ' & ' . $this->partner_2(), ' &' );
    }

    public function date_raw() {
        return $this->get( 'bcw_date' );
    }

    public function date_display() {
        $date = $this->date_raw();
        if ( ! $date ) {
            return '';
        }

        $timestamp = strtotime( $date );
        return $timestamp ? wp_date( 'd · m · Y', $timestamp ) : $date;
    }

    public function image_url( $meta_key, $size = 'full' ) {
        $id = absint( $this->get( $meta_key ) );
        return $id ? wp_get_attachment_image_url( $id, $size ) : '';
    }

    public function hero_image_url( $size = 'full' ) {
        return $this->image_url( 'bcw_hero_image_id', $size );
    }

    public function closing_image_url( $size = 'full' ) {
        return $this->image_url( 'bcw_closing_image_id', $size );
    }

    public function invitation_image_url( $size = 'full' ) {
        return $this->image_url( 'bcw_invitation_image_id', $size );
    }

    public function og_image_url( $size = 'full' ) {
        return $this->image_url( 'bcw_og_image_id', $size );
    }

    public function entry_cover_image_url( $size = 'full' ) {
        return $this->image_url( 'bcw_entry_cover_image_id', $size );
    }

    public function sections() {
        $defaults = array(
            'historia'    => 1,
            'la-boda'     => 1,
            'menorca'     => 1,
            'informacion' => 1,
            'rsvp'        => 1,
        );

        $saved = $this->get( 'bcw_sections', $defaults );
        if ( ! is_array( $saved ) ) {
            return $defaults;
        }

        foreach ( $defaults as $key => $default ) {
            $defaults[ $key ] = ! empty( $saved[ $key ] ) ? 1 : 0;
        }

        return $defaults;
    }

    public function section_enabled( $section ) {
        if ( 'inicio' === $section || 'panel' === $section ) {
            return true;
        }

        $sections = $this->sections();
        return ! empty( $sections[ $section ] );
    }

    public function story() {
        return $this->rows( 'bcw_story' );
    }

    public function wedding_day() {
        return $this->rows( 'bcw_wedding_day' );
    }

    public function menorca() {
        return $this->rows( 'bcw_menorca' );
    }

    public function information() {
        return $this->rows( 'bcw_information' );
    }
}
