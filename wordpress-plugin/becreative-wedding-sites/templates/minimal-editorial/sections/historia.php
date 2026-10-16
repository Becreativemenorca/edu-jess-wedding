<section class="bcw-page-head bcw-page-head-story" data-reveal>
    <p class="bcw-eyebrow">Nuestra historia</p>
    <h1 class="bcw-page-title"><?php echo esc_html( $data->get( 'bcw_story_title', 'Todo lo que nos trajo hasta aquí.' ) ); ?></h1>
    <?php if ( $data->get( 'bcw_story_intro' ) ) : ?>
        <p><?php echo nl2br( esc_html( $data->get( 'bcw_story_intro' ) ) ); ?></p>
    <?php endif; ?>
    <?php
    if ( '1' === $data->get( 'bcw_identity_enabled', '0' ) ) :
        $identity_primary = $data->image_url( 'bcw_identity_primary_image_id', 'medium' );
        if ( $identity_primary ) :
    ?>
        <img class="bcw-identity-mark bcw-identity-primary" src="<?php echo esc_url( $identity_primary ); ?>" alt="" aria-hidden="true">
    <?php
        endif;
    endif;
    ?>
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
        foreach ( array( 'image', 'image_2', 'image_3', 'image_4' ) as $image_key ) {
            if ( ! empty( $row[ $image_key ] ) ) {
                $url = wp_get_attachment_image_url( absint( $row[ $image_key ] ), 'large' );
                if ( $url ) {
                    $images[] = $url;
                }
            }
        }
        $image_count = count( $images );
?>
    <article class="bcw-story-item is-<?php echo esc_attr( $layout ); ?> has-<?php echo esc_attr( $image_count ); ?>-images" data-reveal>
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
