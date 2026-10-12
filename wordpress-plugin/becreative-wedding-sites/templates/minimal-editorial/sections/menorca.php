<section class="bcw-page-head" data-reveal>
    <p class="bcw-eyebrow">Nuestra Menorca</p>
    <h1 class="bcw-page-title">Menorca, a nuestra manera.</h1>
    <p>Los lugares que forman parte de nuestra historia y que queremos compartir con vosotros.</p>
</section>

<section class="bcw-cards">
<?php
$rows = $data->menorca();
if ( ! $rows ) :
?>
    <div class="bcw-empty">Muy pronto compartiremos nuestros lugares favoritos de Menorca.</div>
<?php
else :
    foreach ( $rows as $row ) :
        $image = ! empty( $row['image'] ) ? wp_get_attachment_image_url( absint( $row['image'] ), 'large' ) : '';
?>
    <article class="bcw-card" data-reveal>
        <?php if ( $image ) : ?><div class="bcw-card-image"><img src="<?php echo esc_url( $image ); ?>" alt=""></div><?php endif; ?>
        <?php if ( ! empty( $row['category'] ) ) : ?><div class="bcw-card-meta"><?php echo esc_html( $row['category'] ); ?></div><?php endif; ?>
        <?php if ( ! empty( $row['place'] ) ) : ?><h2><?php echo esc_html( $row['place'] ); ?></h2><?php endif; ?>
        <?php if ( ! empty( $row['text'] ) ) : ?><p><?php echo nl2br( esc_html( $row['text'] ) ); ?></p><?php endif; ?>
        <?php if ( ! empty( $row['url'] ) ) : ?><a class="bcw-button" href="<?php echo esc_url( $row['url'] ); ?>" target="_blank" rel="noopener">Ver lugar</a><?php endif; ?>
    </article>
<?php
    endforeach;
endif;
?>
</section>
