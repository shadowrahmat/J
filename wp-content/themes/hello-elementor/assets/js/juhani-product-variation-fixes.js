(function($) {
	'use strict';

	function normalize(value) {
		return (value || '').toString();
	}

	function getSelectedAttributes($form) {
		var selected = {};

		$form.find('select[name^="attribute_"]').each(function() {
			var $select = $(this);
			selected[$select.attr('name')] = normalize($select.val());
		});

		return selected;
	}

	function variationMatches(variation, selected, targetName) {
		if (!variation || !variation.attributes || variation.is_in_stock === false || variation.is_purchasable === false) {
			return false;
		}

		return Object.keys(selected).every(function(name) {
			if (name === targetName || !selected[name]) {
				return true;
			}

			var variationValue = normalize(variation.attributes[name]);
			return !variationValue || variationValue === selected[name];
		});
	}

	function updateVariationOptions($form) {
		var variations = $form.data('product_variations');

		if (!Array.isArray(variations) && window.juhaniVariationData && String(window.juhaniVariationData.productId) === String($form.data('product_id'))) {
			variations = window.juhaniVariationData.variations;
		}

		if (!Array.isArray(variations) || !variations.length) {
			return;
		}

		var selected = getSelectedAttributes($form);

		$form.find('select[name^="attribute_"]').each(function() {
			var select = this;
			var $select = $(select);
			var targetName = $select.attr('name');
			var currentValue = normalize($select.val());
			var hasCurrentValue = false;

			$select.find('option').each(function() {
				var option = this;
				var value = normalize(option.value);

				if (!value) {
					option.disabled = false;
					option.hidden = false;
					restoreOptionLabel(option);
					return;
				}

				var isAvailable = variations.some(function(variation) {
					if (!variationMatches(variation, selected, targetName)) {
						return false;
					}

					var variationValue = normalize(variation.attributes[targetName]);
					return !variationValue || variationValue === value;
				});

				option.disabled = !isAvailable;
				option.hidden = false;

				if (isAvailable) {
					restoreOptionLabel(option);
				} else {
					markOptionUnavailable(option);
				}

				if (isAvailable && value === currentValue) {
					hasCurrentValue = true;
				}
			});

			if (currentValue && !hasCurrentValue) {
				$select.val('').trigger('change.select2');
			}
		});

		toggleFriendlyNotice($form);
		updateAttributeHelp($form);
	}

	function restoreOptionLabel(option) {
		if (option.dataset.juhaniLabel) {
			option.text = option.dataset.juhaniLabel;
		}
	}

	function markOptionUnavailable(option) {
		if (!option.dataset.juhaniLabel) {
			option.dataset.juhaniLabel = option.text;
		}

		option.text = option.dataset.juhaniLabel + ' - not available';
	}

	function updateAttributeHelp($form) {
		var $makingType = $form.find('select[name="attribute_pa_making-type"]');
		var $gitType = $form.find('select[name="attribute_pa_git-type"]');

		if (!$makingType.length || !$gitType.length) {
			return;
		}

		var message = '';
		var makingTypeValue = $makingType.val();

		if (makingTypeValue === 'machine-made') {
			message = 'For Machine Made, choose Comilla Bandha or Jam Bandha.';
		} else if (makingTypeValue === 'hand-made') {
			message = 'For Hand Made, choose Handmade Git.';
		}

		var $help = $form.find('.juhani-variation-helper');

		if (!$help.length) {
			$help = $('<div class="juhani-variation-helper" aria-live="polite"></div>');
			$gitType.closest('td, .value').append($help);
		}

		$help.text(message).toggle(!!message);
	}

	function toggleFriendlyNotice($form) {
		var selectedCount = 0;
		var filledCount = 0;

		$form.find('select[name^="attribute_"]').each(function() {
			selectedCount += 1;
			if ($(this).val()) {
				filledCount += 1;
			}
		});

		$form.toggleClass('juhani-variation-in-progress', filledCount > 0 && filledCount < selectedCount);
	}

	function initVariationForm() {
		var $form = $(this);

		updateVariationOptions($form);

		$form.on('change', 'select[name^="attribute_"]', function() {
			window.setTimeout(function() {
				updateVariationOptions($form);
			}, 0);
		});

		$form.on('woocommerce_update_variation_values reset_data hide_variation show_variation', function() {
			window.setTimeout(function() {
				updateVariationOptions($form);
			}, 0);
		});
	}

	$(function() {
		$('form.variations_form').each(initVariationForm);
	});
})(jQuery);
