jQuery(function ($) {
  $('.bcw-repeater').each(function () {
    const repeater = $(this);
    const rows = repeater.find('.bcw-repeater-rows');
    const template = repeater.find('.bcw-row-template').html();

    function nextIndex() {
      let max = -1;
      rows.find(':input[name]').each(function () {
        const match = $(this).attr('name').match(/\[(\d+)\]/);
        if (match) max = Math.max(max, parseInt(match[1], 10));
      });
      return max + 1;
    }

    repeater.on('click', '.bcw-add-row', function () {
      rows.append(template.replaceAll('__INDEX__', nextIndex()));
    });

    repeater.on('click', '.bcw-remove-row', function () {
      $(this).closest('.bcw-repeater-row').remove();
    });
  });

  $(document).on('click', '.bcw-pick-image', function () {
    const field = $(this).closest('.bcw-image-field');
    const input = field.find('.bcw-image-id').first();
    const preview = field.find('.bcw-image-preview').first();

    const frame = wp.media({
      title: 'Elegir imagen',
      button: { text: 'Usar esta imagen' },
      multiple: false
    });

    frame.on('select', function () {
      const attachment = frame.state().get('selection').first().toJSON();
      input.val(attachment.id);
      const url = attachment.sizes && attachment.sizes.medium
        ? attachment.sizes.medium.url
        : attachment.url;
      preview.html('<img src="' + url + '" alt="">');
    });

    frame.open();
  });

  $(document).on('click', '.bcw-remove-image', function () {
    const field = $(this).closest('.bcw-image-field');
    field.find('.bcw-image-id').first().val('');
    field.find('.bcw-image-preview').first().empty();
  });
});
