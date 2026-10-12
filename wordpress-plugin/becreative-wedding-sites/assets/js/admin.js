jQuery(function ($) {
  $('.bcw-repeater').each(function () {
    const repeater = $(this);
    const rows = repeater.find('.bcw-repeater-rows');
    const template = repeater.find('.bcw-row-template').html();

    repeater.on('click', '.bcw-add-row', function () {
      const index = rows.children('.bcw-repeater-row').length;
      rows.append(template.replaceAll('__INDEX__', index));
    });

    repeater.on('click', '.bcw-remove-row', function () {
      $(this).closest('.bcw-repeater-row').remove();
    });
  });

  $(document).on('click', '.bcw-pick-image', function () {
    const field = $(this).closest('.bcw-image-field');
    const input = field.find('.bcw-image-id');
    const preview = field.find('.bcw-image-preview');

    const frame = wp.media({
      title: 'Elegir imagen',
      button: { text: 'Usar esta imagen' },
      multiple: false
    });

    frame.on('select', function () {
      const attachment = frame.state().get('selection').first().toJSON();
      input.val(attachment.id);
      preview.html('<img src="' + (attachment.sizes?.medium?.url || attachment.url) + '" alt="">');
    });

    frame.open();
  });

  $(document).on('click', '.bcw-remove-image', function () {
    const field = $(this).closest('.bcw-image-field');
    field.find('.bcw-image-id').val('');
    field.find('.bcw-image-preview').empty();
  });
});
