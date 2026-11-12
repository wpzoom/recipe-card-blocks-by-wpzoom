(function ($, wpzoomRecipeCard) {
	"use scrict";

	function wpzoom_set_correct_ids_to_recipe_shortcode() {
		$('.wpzoom-rcb-post-shortcode').each(function( index ) {
			var parent_id = $(this).data('parent-id');

			$(this).find('.btn-print-link').attr( 'data-recipe-id', parent_id );
			$(this).find('.wpzoom-rating-stars-container').attr( 'data-recipe-id', parent_id );

		});

		$('.wpzoom-custom-recipe-card-post').each(function( index ) {
			var recipe_id = $(this).data('recipe-post');
			$('.wpzoom-recipe-snippet-button.wp-block-wpzoom-recipe-card-block-print-recipe').attr( 'data-recipe-id', recipe_id );
			$(this).find('.btn-print-link').attr( 'data-recipe-id', recipe_id );
		});

	}

	function wpzoom_set_servings_size_to_print_button() {
		const servings_size = $(document)
			.find(".wpzoom-recipe-card-print-link .btn-print-link")
			.data("servings-size");

		if (servings_size) {
			$(document)
				.find(".wp-block-wpzoom-recipe-card-block-print-recipe")
				.data("servings-size", servings_size);
		}
	}

	function wpzoom_print_recipe(atts) {
		const defaults = {
			servings: 0,
			"reusable-block-id": 0,
			"recipe-id": 0,
			"block-type": "recipe-card",
			"block-id": "wpzoom-recipe-card",
		};

		atts = {
			...defaults,
			...atts,
		};

		const urlParts = wpzoomRecipeCard.homeURL.split(/\?(.+)/);
		let printUrl = wpzoom_print_add_url_args(atts, urlParts[0], urlParts);

		const print_window = window.open(printUrl, "_blank");
		print_window.wpzoomRecipeCard = wpzoomRecipeCard;
		print_window.onload = function () {
			print_window.focus();
			print_window.document.title = document.title;
			print_window.history.pushState(
				"",
				"Print Recipe",
				location.href.replace(location.hash, "")
			);

			setTimeout(function () {
				print_window.print();
			}, 500);

			print_window.onfocus = function () {
				setTimeout(function () {
					print_window.close();
				}, 500);
			};
		};
	}

	function wpzoom_print_add_url_args(atts, url, urlParts) {
		if (!atts["recipe-id"]) {
			return url;
		}
		if (wpzoomRecipeCard.permalinks) {
			url += `wpzoom_rcb_print/${atts["recipe-id"]}/`;

			if (urlParts[1]) {
				url += `?${urlParts[1]}`;
				for (const property in atts) {
					url += `&${property}=${atts[property]}`;
				}
			} else {
				for (const property in atts) {
					if (!url.match(/\?./)) {
						url += `?${property}=${atts[property]}`;
					} else {
						url += `&${property}=${atts[property]}`;
					}
				}
			}
		} else {
			url += `?wpzoom_rcb_print=${atts["recipe-id"]}`;
			for (const property in atts) {
				if (!url.match(/\?./)) {
					url += `?${property}=${atts[property]}`;
				} else {
					url += `&${property}=${atts[property]}`;
				}
			}
			if (urlParts[1]) {
				url += `&${urlParts[1]}`;
			}
		}

		return url;
	}

	$(document).ready(function () {
		wpzoom_set_servings_size_to_print_button();

		$(
			".wp-block-wpzoom-recipe-card-block-ingredients .ingredients-list li, .wp-block-wpzoom-recipe-card-block-recipe-card .ingredients-list li"
		).click(function (e) {
			// Don't do any actions if clicked on link
			if (e.target.nodeName === "A") {
				return;
			}
			$(this).toggleClass("ticked");
		});

		let instances = 0;

		$(
			".wp-block-wpzoom-recipe-card-block-ingredients .ingredients-list li, .wp-block-wpzoom-recipe-card-block-recipe-card .ingredients-list li"
		).on("mouseover", function (e) {
			const $ingredientName = $(this).find(".ingredient-item-name");
			const hasStrikeThrough = $ingredientName.hasClass(
				"is-strikethrough-active"
			);

			// Check if strikethrough is disabled
			if (instances === 0 && !hasStrikeThrough) {
				instances = 0;
				return;
			}

			// Remove strike through if hover on link
			if (e.target.nodeName === "A") {
				$ingredientName.removeClass("is-strikethrough-active");
			} else {
				if (!hasStrikeThrough) {
					$ingredientName.addClass("is-strikethrough-active");
				}
			}

			instances++;
		});

		$(
			".wpzoom-recipe-card-print-link .btn-print-link, .wp-block-wpzoom-recipe-card-block-print-recipe"
		).each(function () {
			const $printBtn = $(this);

			$printBtn.on("click", function (e) {
				const $this = $(this);
				const recipeID = $this.data("recipe-id");
				const servings = $this.data("servings-size");
				const reusableBlockID = $this.data("reusable-block-id");

				const isRecipeCardBlock = $this.parents(
					".wp-block-wpzoom-recipe-card-block-recipe-card"
				).length;
				const isIngredientsBlock = $this.parents(
					".wp-block-wpzoom-recipe-card-block-ingredients"
				).length;
				const isDirectionsBlock = $this.parents(
					".wp-block-wpzoom-recipe-card-block-directions"
				).length;
				const isSnippetButton = $this.hasClass(
					"wp-block-wpzoom-recipe-card-block-print-recipe"
				);

				let blockType;
				let blockId;

				if (isRecipeCardBlock) {
					blockType = "recipe-card";
					blockId = $this
						.parents(
							".wp-block-wpzoom-recipe-card-block-recipe-card"
						)
						.attr("id");
				} else if (isIngredientsBlock) {
					blockType = "ingredients-block";
					blockId = $this
						.parents(
							".wp-block-wpzoom-recipe-card-block-ingredients"
						)
						.attr("id");
				} else if (isDirectionsBlock) {
					blockType = "directions-block";
					blockId = $this
						.parents(
							".wp-block-wpzoom-recipe-card-block-directions"
						)
						.attr("id");
				} else if (isSnippetButton) {
					blockType = "recipe-card";
					blockId = $this
						.attr("href")
						.substr(1, $this.attr("href").length);
				}

				// Print attributes.
				const atts = {};
				if (recipeID) {
					atts["recipe-id"] = recipeID;
				}
				if (reusableBlockID) {
					atts["reusable-block-id"] = reusableBlockID;
				}
				if (servings) {
					atts["servings"] = servings;
				}
				if (blockType) {
					atts["block-type"] = blockType;
				}
				if (blockId) {
					atts["block-id"] = blockId;
				}

				if (recipeID) {
					e.preventDefault();
					wpzoom_print_recipe(atts);
				}
			});
		});
		wpzoom_set_correct_ids_to_recipe_shortcode();

		// Cook Mode - keep the screen awake while cooking (NoSleep.js).
		// Cook Mode keeps the whole page awake, so every card's toggle (3.7.0: a
		// page can show several) switches it, and all toggles show its state.
		if (typeof NoSleep === "function") {
			var noSleep = new NoSleep();
			var toggleSelector = ".wpzoom-rcb-cook-mode-toggle, #toggle_button";
			var wakeLockEnabled = false;

			$(document).on("click", toggleSelector, function () {
				if ($(this).is(":checked")) {
					if (!wakeLockEnabled) {
						noSleep.enable();
						wakeLockEnabled = true;
					}
				} else if (wakeLockEnabled) {
					noSleep.disable();
					wakeLockEnabled = false;
				}
				$(toggleSelector).not(this).prop("checked", wakeLockEnabled);
			});

			if ("1" == wpzoomRecipeCard.defaultCookMode) {
				$(toggleSelector).first().trigger("click");
			}
		}

		// Cook Mode help (4.0.0, as in PRO): the "?" next to the toggle shows a short explanation.
		// It closes on a second click, a click anywhere else, Escape, or when the window is resized.
		function wpzoomPlaceCookModeHelp($toggle, $tip) {
			var tip = $tip[0],
				holder = tip.offsetParent || tip.parentNode,
				card = $toggle.closest(".wp-block-wpzoom-recipe-card-block-recipe-card")[0] || document.documentElement;

			// Measured from a known spot, so the last position does not skew the width.
			tip.style.left = "0px";

			var holderRect = holder.getBoundingClientRect(),
				toggleRect = $toggle[0].getBoundingClientRect(),
				cardRect = card.getBoundingClientRect(),
				width = tip.offsetWidth,
				center = toggleRect.left + toggleRect.width / 2,
				// Under the "?", centered on it, but never past the card or the window.
				min = Math.max(cardRect.left, 0) + 8,
				max = Math.min(cardRect.right, document.documentElement.clientWidth) - 8,
				left = Math.max(min, Math.min(center - width / 2, max - width));

			tip.style.left = Math.round(left - holderRect.left) + "px";
			tip.style.top = Math.round(toggleRect.bottom - holderRect.top + 9) + "px";
			tip.style.setProperty("--wpzoom-help-arrow", Math.round(center - left) + "px");
		}

		function wpzoomSetCookModeHelp($toggle, open) {
			$toggle.each(function () {
				var $one = $(this),
					$tip = $one.siblings(".wpzoom-nosleep-help-tip");

				$one.attr("aria-expanded", open ? "true" : "false");
				$tip.prop("hidden", !open);

				if (open && $tip.length) {
					wpzoomPlaceCookModeHelp($one, $tip);
				}
			});
		}

		$(document).on("click", function (e) {
			var $toggle = $(e.target).closest(".wpzoom-nosleep-help-toggle"),
				$open = $('.wpzoom-nosleep-help-toggle[aria-expanded="true"]');

			if ($toggle.length) {
				e.preventDefault();
				wpzoomSetCookModeHelp($open.not($toggle), false);
				wpzoomSetCookModeHelp($toggle, "true" !== $toggle.attr("aria-expanded"));
			} else if ($open.length && !$(e.target).closest(".wpzoom-nosleep-help-tip").length) {
				wpzoomSetCookModeHelp($open, false);
			}
		});

		$(document).on("keydown", function (e) {
			var $open = $('.wpzoom-nosleep-help-toggle[aria-expanded="true"]');

			if ($open.length && ("Escape" === e.key || 27 === e.keyCode)) {
				wpzoomSetCookModeHelp($open, false);
				$open.first().trigger("focus");
			}
		});

		$(window).on("resize", function () {
			wpzoomSetCookModeHelp($('.wpzoom-nosleep-help-toggle[aria-expanded="true"]'), false);
		});

		// Move auto-inserted rating stars into the theme's post meta area.
		var $elementToMove = $("[data-js-move=true]:first-child");
		var elementTarget = $elementToMove.data("js-selector");
		if ($elementToMove.length && elementTarget) {
			$elementToMove.appendTo(elementTarget);
		}
	});
})(jQuery, wpzoomRecipeCard);

/**
 * Make embeds responsive so they don't overflow their container.
 * Add max-width & max-height to <iframe> elements, depending on their width & height props.
 *
 * @see Twenty Twenty-One file responsive-embeds.js.
 * @since 2.7.8
 *
 * @return {void}
 */
function recipeCardResponsiveEmbeds() {
	let proportion, parentWidth;

	// Loop iframe elements.
	document.querySelectorAll("iframe").forEach(function (iframe) {
		// Only continue if the iframe has a width & height defined.
		if (iframe.width && iframe.height) {
			// Calculate the proportion/ratio based on the width & height.
			proportion = parseFloat(iframe.width) / parseFloat(iframe.height);
			// Get the parent element's width.
			parentWidth = parseFloat(
				window
					.getComputedStyle(iframe.parentElement, null)
					.width.replace("px", "")
			);
			// Set the max-width & height.
			iframe.style.maxWidth = "100%";
			iframe.style.maxHeight =
				Math.round(parentWidth / proportion).toString() + "px";
		}
	});
}

// Run on initial load.
recipeCardResponsiveEmbeds();

// Run on resize.
window.onresize = recipeCardResponsiveEmbeds;
