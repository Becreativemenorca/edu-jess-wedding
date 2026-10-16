<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BCW_Wedding_Post_Type {

    const POST_TYPE = 'bcw_wedding';

    public static function register() {
        $labels = array(
            'name'               => 'Bodas',
            'singular_name'      => 'Boda',
            'menu_name'          => 'Bodas',
            'name_admin_bar'     => 'Boda',
            'add_new'            => 'Añadir nueva',
            'add_new_item'       => 'Añadir nueva boda',
            'new_item'           => 'Nueva boda',
            'edit_item'          => 'Editar boda',
            'view_item'          => 'Ver boda',
            'all_items'          => 'Todas las bodas',
            'search_items'       => 'Buscar bodas',
            'not_found'          => 'No se han encontrado bodas.',
            'not_found_in_trash' => 'No hay bodas en la papelera.',
        );

        register_post_type(
            self::POST_TYPE,
            array(
                'labels'             => $labels,
                'public'             => true,
                'show_in_rest'       => false,
                'menu_icon'          => 'dashicons-heart',
                'supports'           => array( 'title', 'thumbnail' ),
                'has_archive'        => false,
                'exclude_from_search'=> true,
                'rewrite'            => array(
                    'slug'       => 'bodas',
                    'with_front' => false,
                ),
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => true,
            )
        );
    }
}
