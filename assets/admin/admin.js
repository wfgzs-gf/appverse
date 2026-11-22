/**
 * App editor media picker.
 */
(function ($) {
	'use strict';

	$(function () {
		var $input = $('#appverse_screenshots');
		var $list = $('#appverse-shots-list');
		var frame;

		function render(items) {
			$list.empty();
			var ids = [];

			items.forEach(function (item) {
				var attachment = item.toJSON ? item.toJSON() : item;
				ids.push(attachment.id);

				var url = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
				var $figure = $('<figure>').attr('data-id', attachment.id);
				$('<img>').attr('src', url).attr('alt', '').appendTo($figure);
				$('<button type="button" class="button-link av-admin-shots__remove" aria-label="移除截图">×</button>').appendTo($figure);
				$figure.appendTo($list);
			});

			$input.val(ids.join(','));
		}

		$('#appverse-shots-add').on('click', function (event) {
			event.preventDefault();

			if (frame) {
				frame.open();
				return;
			}

			frame = wp.media({
				title: '选择应用截图',
				button: { text: '使用这些截图' },
				library: { type: 'image' },
				multiple: 'add'
			});

			frame.on('open', function () {
				var selection = frame.state().get('selection');
				selection.reset();

				var current = $input.val().split(',').filter(Boolean);
				current.forEach(function (id) {
					var attachment = wp.media.attachment(id);
					attachment.fetch();
					selection.add(attachment);
				});
			});

			frame.on('select', function () {
				render(frame.state().get('selection').models);
			});

			frame.open();
		});

		$list.on('click', '.av-admin-shots__remove', function (event) {
			event.preventDefault();
			var id = String($(this).closest('figure').attr('data-id'));
			var ids = $input.val().split(',').filter(function (value) {
				return value && value !== id;
			});
			$input.val(ids.join(','));
			$(this).closest('figure').remove();
		});
	});
})(jQuery);
