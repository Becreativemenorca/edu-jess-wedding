<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $post;

$post_id = $post ? absint( $post->ID ) : 0;

if ( ! $post_id || BCW_Wedding_Post_Type::POST_TYPE !== get_post_type( $post_id ) ) {
    status_header( 404 );
    nocache_headers();
    include get_404_template();
    exit;
}

$data    = new BCW_Wedding_Data( $post_id );
$section = BCW_Wedding_Router::section();
$names   = $data->names();
$date    = $data->date_display();

if ( ! $data->section_enabled( $section ) ) {
    status_header( 404 );
    nocache_headers();
    include get_404_template();
    exit;
}


?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo esc_html( $names ? $names . ' · Be Creative Weddings' : 'Boda · Be Creative Weddings' ); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'bcw-wedding bcw-template-minimal-editorial' ); ?>>
<?php wp_body_open(); ?>

<?php
$show_entry_cover = (
    'inicio' === $section
    && '1' === $data->get( 'bcw_invitation_enabled', '0' )
    && '1' === $data->get( 'bcw_entry_cover_enabled', '0' )
);

if ( $show_entry_cover ) :
    $entry_style = $data->get( 'bcw_entry_cover_style', 'editorial' );

    if ( 'photo' === $entry_style ) :
        $entry_image = $data->entry_cover_image_url( 'full' );
        if ( ! $entry_image ) {
            $entry_image = $data->hero_image_url( 'full' );
        }
?>
<div class="bcw-entry-cover bcw-entry-cover-photo" data-entry-cover data-wedding-id="<?php echo esc_attr( $post_id ); ?>">
    <?php if ( $entry_image ) : ?>
        <img src="<?php echo esc_url( $entry_image ); ?>" alt="">
    <?php endif; ?>
    <div class="bcw-entry-shade"></div>
    <div class="bcw-entry-copy">
        <p class="bcw-eyebrow"><?php echo esc_html( $data->get( 'bcw_location' ) ); ?></p>
        <h1><?php echo esc_html( $names ); ?></h1>
        <p><?php echo esc_html( $date ); ?></p>
        <button type="button" class="bcw-button bcw-entry-button" data-enter-wedding>Entrar</button>
    </div>
</div>
<?php
    else :
        $identity_primary   = '1' === $data->get( 'bcw_identity_enabled', '0' ) ? $data->image_url( 'bcw_identity_primary_image_id', 'medium' ) : '';
        $identity_secondary = '1' === $data->get( 'bcw_identity_enabled', '0' ) ? $data->image_url( 'bcw_identity_secondary_image_id', 'medium' ) : '';
?>
<div class="bcw-entry-cover bcw-entry-cover-editorial" data-entry-cover data-wedding-id="<?php echo esc_attr( $post_id ); ?>">
    <div class="bcw-entry-editorial-inner">
        <?php if ( $identity_secondary ) : ?>
            <img class="bcw-entry-botanical" src="<?php echo esc_url( $identity_secondary ); ?>" alt="" aria-hidden="true">
        <?php endif; ?>

        <div class="bcw-entry-editorial-copy">
            <p class="bcw-eyebrow"><?php echo esc_html( $data->get( 'bcw_location' ) ); ?></p>
            <h1><?php echo esc_html( $names ); ?></h1>
            <span class="bcw-entry-divider" aria-hidden="true"></span>
            <p class="bcw-entry-date"><?php echo esc_html( $date ); ?></p>

            <?php if ( $identity_primary ) : ?>
                <img class="bcw-entry-lighthouse" src="<?php echo esc_url( $identity_primary ); ?>" alt="" aria-hidden="true">
            <?php endif; ?>

            <button type="button" class="bcw-button bcw-entry-button" data-enter-wedding>Entrar</button>
        </div>
    </div>
</div>
<?php
    endif;
endif;
?>

<header class="bcw-header">
    <a class="bcw-brand" href="<?php echo esc_url( BCW_Wedding_Router::url( $post_id ) ); ?>">
        <strong><?php echo esc_html( $names ); ?></strong>
        <small><?php echo esc_html( $date ); ?></small>
    </a>

    <button class="bcw-menu-button" data-menu-button aria-expanded="false" aria-label="Abrir menú">Menú</button>

    <nav class="bcw-nav" data-menu>
        <?php
        $links = array(
            'historia'    => 'Nuestra historia',
            'la-boda'     => 'La boda',
            'menorca'     => 'Nuestra Menorca',
            'informacion' => 'Información',
            'rsvp'        => 'RSVP',
        );
        foreach ( $links as $slug => $label ) :
            if ( ! $data->section_enabled( $slug ) ) {
                continue;
            }
            ?>
            <a href="<?php echo esc_url( BCW_Wedding_Router::url( $post_id, $slug ) ); ?>" <?php echo $section === $slug ? 'aria-current="page"' : ''; ?>>
                <?php echo esc_html( $label ); ?>
            </a>
        <?php endforeach; ?>
    </nav>
</header>

<main class="bcw-main">
<?php
$template_file = BCW_WEDDING_SITES_DIR . 'templates/minimal-editorial/sections/' . $section . '.php';
if ( file_exists( $template_file ) ) {
    include $template_file;
} else {
    include BCW_WEDDING_SITES_DIR . 'templates/minimal-editorial/sections/inicio.php';
}
?>
</main>

<?php if ( 'panel' !== $section ) : ?>
<footer class="bcw-footer">
    <strong><?php echo esc_html( $names ); ?></strong>
    <p><?php echo esc_html( trim( $data->get( 'bcw_location' ) . ( $date ? ' · ' . $date : '' ) ) ); ?></p>
</footer>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
