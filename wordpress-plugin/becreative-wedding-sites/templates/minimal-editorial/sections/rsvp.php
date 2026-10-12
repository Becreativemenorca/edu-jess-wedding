<section class="bcw-page-head" data-reveal>
    <p class="bcw-eyebrow">RSVP</p>
    <h1 class="bcw-page-title">Nos encantará contar contigo.</h1>
    <p>La confirmación online se activará cuando el formulario definitivo y sus campos estén aprobados.</p>
</section>

<section class="bcw-rsvp-box" data-reveal>
<?php if ( '1' === $data->get( 'bcw_rsvp_enabled', '1' ) ) : ?>
    <div class="bcw-empty">
        <strong>RSVP preparado, todavía no operativo.</strong>
        <p>No mostramos un formulario falso: primero conectaremos el guardado real de respuestas y después activaremos esta página.</p>
        <?php if ( $data->get( 'bcw_rsvp_deadline' ) ) : ?>
            <p>Fecha límite configurada: <?php echo esc_html( $data->get( 'bcw_rsvp_deadline' ) ); ?></p>
        <?php endif; ?>
    </div>
<?php else : ?>
    <div class="bcw-empty">La confirmación todavía no está abierta.</div>
<?php endif; ?>
</section>
