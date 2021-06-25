(function ($, wpzoomRecipeCard) {
	"use scrict";

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

		atts = { ...defaults, ...atts };

		const urlParts = wpzoomRecipeCard.homeURL.split(/\?(.+)/);
		let printUrl = urlParts[0];

		if (wpzoomRecipeCard.permalinks) {
			printUrl += "wpzoom_rcb_print/" + atts["recipe-id"] + "/";

			if (urlParts[1]) {
				printUrl += "?" + urlParts[1];
				printUrl += "&block-type=" + atts["block-type"];
				printUrl += "&block-id=" + atts["block-id"];

				if (atts["servings"]) {
					printUrl += "&servings=" + atts["servings"];
				}
				if (atts["reusable-block-id"]) {
					printUrl +=
						"&reusable-block-id=" + atts["reusable-block-id"];
				}
			} else {
				printUrl += "?block-type=" + atts["block-type"];
				printUrl += "&block-id=" + atts["block-id"];

				if (atts["servings"]) {
					printUrl += "&servings=" + atts["servings"];
				}
				if (atts["reusable-block-id"]) {
					printUrl +=
						"&reusable-block-id=" + atts["reusable-block-id"];
				}
			}
		} else {
			printUrl += "?wpzoom_rcb_print=" + recipeID;
			printUrl += "&block-type=" + atts["block-type"];
			printUrl += "&block-id=" + atts["block-id"];

			if (atts["servings"]) {
				printUrl += "&servings=" + atts["servings"];
			}
			if (atts["reusable-block-id"]) {
				printUrl += "&reusable-block-id=" + atts["reusable-block-id"];
			}
			if (urlParts[1]) {
				printUrl += "&" + urlParts[1];
			}
		}

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

				if (recipeID) {
					e.preventDefault();
					wpzoom_print_recipe({
						"recipe-id": recipeID,
						"reusable-block-id": reusableBlockID,
						servings,
						"block-type": blockType,
						"block-id": blockId,
					});
				}
			});
		});
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
