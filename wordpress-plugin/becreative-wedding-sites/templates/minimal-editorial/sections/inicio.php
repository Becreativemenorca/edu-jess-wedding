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
