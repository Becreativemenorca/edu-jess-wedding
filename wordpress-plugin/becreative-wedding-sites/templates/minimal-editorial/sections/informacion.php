<section class="bcw-page-head" data-reveal>
    <p class="bcw-eyebrow">Información</p>
    <h1 class="bcw-page-title">Todo lo práctico, en un solo lugar.</h1>
</section>

<section class="bcw-info-list">
<?php
$rows = $data->information();
if ( ! $rows ) :
?>
    <div class="bcw-empty">Iremos añadiendo aquí la información práctica a medida que esté confirmada.</div>
<?php
else :
    foreach ( $rows as $index => $row ) :
?>
    <article class="bcw-info-item" data-reveal>
        <span><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
        <div>
            <?php if ( ! empty( $row['title'] ) ) : ?><h2><?php echo esc_html( $row['title'] ); ?></h2><?php endif; ?>
            <?php if ( ! empty( $row['text'] ) ) : ?><p><?php echo nl2br( esc_html( $row['text'] ) ); ?></p><?php endif; ?>
        </div>
    </article>
<?php
    endforeach;
endif;
?>
</section>
