<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $post;

$post_id = $post ? $post->ID : 0;
if ( ! $post_id && get_query_var( 'bcw_wedding' ) ) {
    $wedding = get_page_by_path( sanitize_title( get_query_var( 'bcw_wedding' ) ), OBJECT, BCW_Wedding_Post_Type::POST_TYPE );
    $post_id = $wedding ? $wedding->ID : 0;
}

if ( ! $post_id ) {
    status_header( 404 );
    nocache_headers();
    include get_404_template();
    exit;
}

$data    = new BCW_Wedding_Data( $post_id );
$section = BCW_Wedding_Router::section();
$names   = $data->names();
$date    = $data->date_display();

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

<footer class="bcw-footer">
    <strong><?php echo esc_html( $names ); ?></strong>
    <p><?php echo esc_html( trim( $data->get( 'bcw_location' ) . ( $date ? ' · ' . $date : '' ) ) ); ?></p>
</footer>

<?php wp_footer(); ?>
</body>
</html>
