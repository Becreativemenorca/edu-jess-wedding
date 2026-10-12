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

    public function hero_image_url( $size = 'full' ) {
        $id = absint( $this->get( 'bcw_hero_image_id' ) );
        return $id ? wp_get_attachment_image_url( $id, $size ) : '';
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
