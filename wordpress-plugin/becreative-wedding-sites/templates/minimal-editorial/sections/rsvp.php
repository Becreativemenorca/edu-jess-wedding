<section class="bcw-page-head" data-reveal>
    <p class="bcw-eyebrow">Confirmar asistencia</p>
    <h1 class="bcw-page-title">Nos encantará contar contigo.</h1>
    <p>Confirma tu asistencia y cualquier detalle que necesitemos tener en cuenta para la celebración.</p>
</section>

<section class="bcw-rsvp-box" data-reveal>
    <?php BCW_RSVP::render_form( $post_id ); ?>
</section>

<?php
$closing_image = $data->closing_image_url( 'full' );
$closing_text  = $data->get( 'bcw_closing' );

if ( $closing_image || $closing_text ) :
?>
<section class="bcw-closing" data-reveal>
    <?php if ( $closing_image ) : ?><img src="<?php echo esc_url( $closing_image ); ?>" alt=""><?php endif; ?>
    <div class="bcw-closing-copy" style="color:#fff!important;-webkit-text-fill-color:#fff!important;">
        <p class="bcw-eyebrow" style="color:#fff!important;-webkit-text-fill-color:#fff!important;"><?php echo esc_html( trim( $data->get( 'bcw_location' ) . ( $date ? ' · ' . $date : '' ) ) ); ?></p>
        <?php if ( $closing_text ) : ?><h2 style="color:#fff!important;-webkit-text-fill-color:#fff!important;"><?php echo esc_html( $closing_text ); ?></h2><?php endif; ?>
    </div>
</section>
<?php endif; ?>
