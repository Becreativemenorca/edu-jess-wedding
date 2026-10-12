<section class="bcw-page-head" data-reveal>
    <p class="bcw-eyebrow">La boda</p>
    <h1 class="bcw-page-title">El día, a nuestra manera.</h1>
    <?php if ( $data->get( 'bcw_venue' ) ) : ?><p><?php echo esc_html( $data->get( 'bcw_venue' ) ); ?> · <?php echo esc_html( $data->get( 'bcw_location' ) ); ?></p><?php endif; ?>
</section>

<section class="bcw-cards">
<?php
$rows = $data->wedding_day();
if ( ! $rows ) :
?>
    <div class="bcw-empty">Los detalles de la boda se publicarán aquí cuando estén confirmados.</div>
<?php
else :
    foreach ( $rows as $row ) :
        $image = ! empty( $row['image'] ) ? wp_get_attachment_image_url( absint( $row['image'] ), 'large' ) : '';
        $meta = array_filter( array( $row['time'] ?? '', $row['place'] ?? '' ) );
?>
    <article class="bcw-card" data-reveal>
        <?php if ( $image ) : ?><div class="bcw-card-image"><img src="<?php echo esc_url( $image ); ?>" alt=""></div><?php endif; ?>
        <?php if ( $meta ) : ?><div class="bcw-card-meta"><?php echo esc_html( implode( ' · ', $meta ) ); ?></div><?php endif; ?>
        <?php if ( ! empty( $row['moment'] ) ) : ?><h2><?php echo esc_html( $row['moment'] ); ?></h2><?php endif; ?>
        <?php if ( ! empty( $row['text'] ) ) : ?><p><?php echo nl2br( esc_html( $row['text'] ) ); ?></p><?php endif; ?>
    </article>
<?php
    endforeach;
endif;
?>
</section>
