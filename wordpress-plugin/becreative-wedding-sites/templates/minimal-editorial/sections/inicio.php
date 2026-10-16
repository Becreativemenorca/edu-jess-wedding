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
    'historia'    => 'Nuestra historia',
    'la-boda'     => 'La boda',
    'menorca'     => 'Nuestra Menorca',
    'informacion' => 'Información',
);
?>
<section class="bcw-home-nav" aria-labelledby="bcw-home-nav-title" data-reveal>
    <div class="bcw-home-nav-head">
        <p class="bcw-eyebrow">La invitación</p>
        <h2 id="bcw-home-nav-title">Todo lo que necesitas para este día.</h2>
    </div>

    <div class="bcw-home-nav-list">
        <?php foreach ( $home_links as $slug => $label ) : ?>
            <?php if ( ! $data->section_enabled( $slug ) ) { continue; } ?>
            <a class="bcw-home-nav-link" href="<?php echo esc_url( BCW_Wedding_Router::url( $post_id, $slug ) ); ?>">
                <span class="bcw-home-nav-label"><?php echo esc_html( $label ); ?></span>
                <span class="bcw-home-nav-arrow" aria-hidden="true">→</span>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ( $data->section_enabled( 'rsvp' ) ) : ?>
        <a class="bcw-home-nav-rsvp" href="<?php echo esc_url( BCW_Wedding_Router::url( $post_id, 'rsvp' ) ); ?>">
            <span class="bcw-home-nav-label">Confirmar asistencia</span>
            <span class="bcw-home-nav-arrow" aria-hidden="true">→</span>
        </a>
    <?php endif; ?>
</section>
