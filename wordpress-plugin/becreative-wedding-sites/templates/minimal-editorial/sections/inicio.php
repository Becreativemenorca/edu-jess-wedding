<?php
$hero = $data->hero_image_url( 'full' );
?>
<section class="bcw-hero">
    <?php if ( $hero ) : ?>
        <img src="<?php echo esc_url( $hero ); ?>" alt="<?php echo esc_attr( $names ); ?>">
    <?php endif; ?>
    <div class="bcw-hero-shade"></div>
    <div class="bcw-hero-copy" data-reveal>
        <p class="bcw-eyebrow"><?php echo esc_html( trim( $data->get( 'bcw_venue' ) . ' · ' . $data->get( 'bcw_location' ), ' ·' ) ); ?></p>
        <h1><?php echo esc_html( $data->partner_1() ); ?> <em>&</em> <?php echo esc_html( $data->partner_2() ); ?></h1>
        <p class="bcw-date"><?php echo esc_html( $date ); ?></p>
        <?php if ( $data->get( 'bcw_tagline' ) ) : ?>
            <p class="bcw-tagline"><?php echo esc_html( $data->get( 'bcw_tagline' ) ); ?></p>
        <?php endif; ?>
    </div>
</section>

<?php if ( $data->get( 'bcw_intro' ) ) : ?>
<section class="bcw-intro" data-reveal>
    <p><?php echo esc_html( $data->get( 'bcw_intro' ) ); ?></p>
</section>
<?php endif; ?>


<?php
$home_links = array(
    'historia' => array(
        'label' => 'Nuestra historia',
        'text'  => 'Todo lo que nos trajo hasta aquí.',
    ),
    'la-boda' => array(
        'label' => 'La boda',
        'text'  => 'Dónde y cómo celebraremos este día.',
    ),
    'menorca' => array(
        'label' => 'Nuestra Menorca',
        'text'  => 'Los lugares que queremos compartir contigo.',
    ),
    'informacion' => array(
        'label' => 'Información',
        'text'  => 'Todo lo que necesitas saber para organizarte.',
    ),
);
?>
<section class="bcw-home-nav" aria-labelledby="bcw-home-nav-title" data-reveal>
    <div class="bcw-home-nav-head">
        <p class="bcw-eyebrow">La invitación</p>
        <h2 id="bcw-home-nav-title">Descubre nuestra boda.</h2>
        <p>Recorre la historia, conoce todos los detalles y confirma tu asistencia cuando estés listo.</p>
    </div>

    <div class="bcw-home-nav-grid">
        <?php foreach ( $home_links as $slug => $item ) : ?>
            <?php if ( ! $data->section_enabled( $slug ) ) { continue; } ?>
            <a class="bcw-home-nav-card" href="<?php echo esc_url( BCW_Wedding_Router::url( $post_id, $slug ) ); ?>">
                <span class="bcw-home-nav-label"><?php echo esc_html( $item['label'] ); ?></span>
                <span class="bcw-home-nav-text"><?php echo esc_html( $item['text'] ); ?></span>
                <span class="bcw-home-nav-arrow" aria-hidden="true">→</span>
            </a>
        <?php endforeach; ?>

        <?php if ( $data->section_enabled( 'rsvp' ) ) : ?>
            <a class="bcw-home-nav-card bcw-home-nav-rsvp" href="<?php echo esc_url( BCW_Wedding_Router::url( $post_id, 'rsvp' ) ); ?>" style="color:#fffaf5!important;-webkit-text-fill-color:#fffaf5!important;">
                <span class="bcw-rsvp-cta-copy" style="color:#fffaf5!important;-webkit-text-fill-color:#fffaf5!important;opacity:1!important;visibility:visible!important;">
                    <span class="bcw-home-nav-label" style="color:#fffaf5!important;-webkit-text-fill-color:#fffaf5!important;opacity:1!important;visibility:visible!important;">Confirmar asistencia</span>
                    <span class="bcw-home-nav-text" style="color:#fffaf5!important;-webkit-text-fill-color:#fffaf5!important;opacity:.82!important;visibility:visible!important;">Cuando lo tengas claro, cuéntanos si podrás acompañarnos.</span>
                </span>
                <span class="bcw-home-nav-arrow" aria-hidden="true" style="color:#fffaf5!important;-webkit-text-fill-color:#fffaf5!important;opacity:1!important;visibility:visible!important;">→</span>
            </a>
        <?php endif; ?>
    </div>
</section>
