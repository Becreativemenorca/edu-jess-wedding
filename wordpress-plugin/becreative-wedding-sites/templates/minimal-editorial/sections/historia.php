<section class="bcw-page-head" data-reveal>
    <p class="bcw-eyebrow">Nuestra historia</p>
    <h1 class="bcw-page-title">Todo lo que nos trajo hasta aquí.</h1>
</section>

<section class="bcw-story-list">
<?php
$rows = $data->story();
if ( ! $rows ) :
?>
    <div class="bcw-empty">Esta historia todavía no tiene capítulos publicados.</div>
<?php
else :
    foreach ( $rows as $row ) :
        $layout = ! empty( $row['layout'] ) && in_array( $row['layout'], array( 'standard', 'feature', 'collage' ), true )
            ? $row['layout']
            : 'standard';

        $images = array();
        foreach ( array( 'image', 'image_2', 'image_3' ) as $image_key ) {
            if ( ! empty( $row[ $image_key ] ) ) {
                $url = wp_get_attachment_image_url( absint( $row[ $image_key ] ), 'large' );
                if ( $url ) {
                    $images[] = $url;
                }
            }
        }
?>
    <article class="bcw-story-item is-<?php echo esc_attr( $layout ); ?>" data-reveal>
        <div class="bcw-story-copy">
            <?php if ( ! empty( $row['year'] ) ) : ?><span class="bcw-story-year"><?php echo esc_html( $row['year'] ); ?></span><?php endif; ?>
            <?php if ( ! empty( $row['title'] ) ) : ?><h2><?php echo esc_html( $row['title'] ); ?></h2><?php endif; ?>
            <?php if ( ! empty( $row['text'] ) ) : ?><p><?php echo nl2br( esc_html( $row['text'] ) ); ?></p><?php endif; ?>
        </div>

        <?php if ( $images ) : ?>
        <div class="bcw-story-gallery">
            <?php foreach ( $images as $image ) : ?>
                <img src="<?php echo esc_url( $image ); ?>" alt="">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </article>
<?php
    endforeach;
endif;
?>
</section>
