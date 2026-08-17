<?php
/**
 * Recipe SEO Health dashboard.
 *
 * Scans all recipes for Google rich-result readiness (required and
 * recommended structured data fields) and renders an actionable checklist.
 *
 * @since   3.5.0
 * @package WPZOOM_Recipe_Card_Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPZOOM_SEO_Health' ) ) {

class WPZOOM_SEO_Health {

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ), 20 );
	}

	/**
	 * Register the submenu page.
	 */
	public static function register_menu() {
		add_submenu_page(
			WPZOOM_RCB_SETTINGS_PAGE,
			__( 'Recipe SEO Health', 'recipe-card-blocks-by-wpzoom' ),
			__( 'SEO Health', 'recipe-card-blocks-by-wpzoom' ),
			'edit_posts',
			'wpzoom-recipe-seo-health',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * The checks we run on every recipe.
	 *
	 * @return array check_id => [ label, level (required|recommended), description ]
	 */
	public static function get_checks() {
		return array(
			'image'       => array( __( 'Image', 'recipe-card-blocks-by-wpzoom' ), 'required', __( 'Google requires a recipe image for rich results.', 'recipe-card-blocks-by-wpzoom' ) ),
			'image_size'  => array( __( 'Large image', 'recipe-card-blocks-by-wpzoom' ), 'recommended', __( 'Google recommends images at least 1200px wide for best results.', 'recipe-card-blocks-by-wpzoom' ) ),
			'description' => array( __( 'Description', 'recipe-card-blocks-by-wpzoom' ), 'recommended', __( 'A short summary helps Google understand and display your recipe.', 'recipe-card-blocks-by-wpzoom' ) ),
			'prep_time'   => array( __( 'Prep time', 'recipe-card-blocks-by-wpzoom' ), 'recommended', __( 'Prep time appears directly in search results.', 'recipe-card-blocks-by-wpzoom' ) ),
			'cook_time'   => array( __( 'Cook time', 'recipe-card-blocks-by-wpzoom' ), 'recommended', __( 'Cook time appears directly in search results.', 'recipe-card-blocks-by-wpzoom' ) ),
			'calories'    => array( __( 'Calories', 'recipe-card-blocks-by-wpzoom' ), 'recommended', __( 'Calorie info can appear in the search snippet.', 'recipe-card-blocks-by-wpzoom' ) ),
			'servings'    => array( __( 'Servings', 'recipe-card-blocks-by-wpzoom' ), 'recommended', __( 'The recipe yield is a recommended schema property.', 'recipe-card-blocks-by-wpzoom' ) ),
			'course'      => array( __( 'Course', 'recipe-card-blocks-by-wpzoom' ), 'recommended', __( 'The recipe category (e.g. dinner, dessert) is a recommended property.', 'recipe-card-blocks-by-wpzoom' ) ),
			'cuisine'     => array( __( 'Cuisine', 'recipe-card-blocks-by-wpzoom' ), 'recommended', __( 'The recipe cuisine is a recommended property.', 'recipe-card-blocks-by-wpzoom' ) ),
			'video'       => array( __( 'Video', 'recipe-card-blocks-by-wpzoom' ), 'recommended', __( 'Recipes with video are eligible for video rich results.', 'recipe-card-blocks-by-wpzoom' ) ),
			'ratings'     => array( __( 'Ratings', 'recipe-card-blocks-by-wpzoom' ), 'recommended', __( 'Recipes need at least one rating to show stars in Google. Comment ratings are enabled in the free plugin.', 'recipe-card-blocks-by-wpzoom' ) ),
		);
	}

	/**
	 * Scan all recipes and evaluate the checks.
	 *
	 * @return array List of result rows.
	 */
	public static function scan_recipes() {
		$results = array();

		$recipes = get_posts(
			array(
				'post_type'      => 'wpzoom_rcb',
				'posts_per_page' => 500,
				'post_status'    => array( 'publish', 'draft' ),
			)
		);

		foreach ( $recipes as $recipe ) {
			$attrs = self::get_recipe_block_attrs( $recipe->post_content );

			if ( null === $attrs ) {
				continue;
			}

			$parent_id = (int) get_post_meta( $recipe->ID, '_wpzoom_rcb_parent_post_id', true );
			$edit_id   = $parent_id ? $parent_id : $recipe->ID;

			$title = get_the_title( $recipe );
			if ( '' === $title && ! empty( $attrs['recipeTitle'] ) ) {
				$title = wp_strip_all_tags( $attrs['recipeTitle'] );
			}
			if ( '' === $title && $parent_id ) {
				$title = get_the_title( $parent_id );
			}
			if ( '' === $title ) {
				$title = __( '(no title)', 'recipe-card-blocks-by-wpzoom' );
			}

			$results[] = array(
				'title'     => $title,
				'edit_link' => (string) get_edit_post_link( $edit_id, 'raw' ),
				'view_link' => $parent_id ? (string) get_permalink( $parent_id ) : '',
				'checks'    => self::evaluate_recipe( $attrs, $parent_id ? $parent_id : $recipe->ID ),
			);
		}

		return $results;
	}

	/**
	 * Extract the recipe card block attributes from post content.
	 *
	 * @param string $content Post content.
	 * @return array|null Attributes or null when no recipe block found.
	 */
	private static function get_recipe_block_attrs( $content ) {
		if ( ! has_block( 'wpzoom-recipe-card/block-recipe-card', $content ) ) {
			return null;
		}

		$blocks = parse_blocks( $content );

		foreach ( $blocks as $block ) {
			if ( 'wpzoom-recipe-card/block-recipe-card' === $block['blockName'] ) {
				return is_array( $block['attrs'] ) ? $block['attrs'] : array();
			}
		}

		return null;
	}

	/**
	 * Evaluate all checks for one recipe.
	 *
	 * @param array $attrs     Recipe block attributes.
	 * @param int   $rating_id Post ID used for rating lookups.
	 * @return array check_id => bool (true = passing)
	 */
	private static function evaluate_recipe( $attrs, $rating_id ) {
		$details = isset( $attrs['details'] ) && is_array( $attrs['details'] ) ? $attrs['details'] : array();

		$detail_value = function ( $index ) use ( $details ) {
			return isset( $details[ $index ]['value'] ) && '' !== trim( (string) $details[ $index ]['value'] );
		};

		$has_image  = ! empty( $attrs['hasImage'] ) && ! empty( $attrs['image'] );
		$image_wide = false;

		if ( $has_image && ! empty( $attrs['image']['id'] ) ) {
			$meta       = wp_get_attachment_metadata( (int) $attrs['image']['id'] );
			$image_wide = ! empty( $meta['width'] ) && (int) $meta['width'] >= 1200;
		}

		$votes = 0;
		if ( class_exists( 'WPZOOM_Comment_Rating' ) && '1' === WPZOOM_Settings::get( 'wpzoom_rcb_settings_comment_ratings' ) ) {
			$votes = (int) WPZOOM_Comment_Rating::get_total_votes( $rating_id );
		}

		return array(
			'image'       => $has_image,
			'image_size'  => $image_wide,
			'description' => ! empty( $attrs['summary'] ) || ! empty( $attrs['jsonSummary'] ),
			'prep_time'   => $detail_value( 1 ),
			'cook_time'   => $detail_value( 2 ),
			'calories'    => $detail_value( 3 ),
			'servings'    => $detail_value( 0 ),
			'course'      => ! empty( $attrs['course'] ),
			'cuisine'     => ! empty( $attrs['cuisine'] ),
			'video'       => ! empty( $attrs['hasVideo'] ),
			'ratings'     => $votes > 0,
		);
	}

	/**
	 * Render the SEO Health admin page.
	 */
	public static function render_page() {
		$checks  = self::get_checks();
		$results = self::scan_recipes();

		$total     = count( $results );
		$eligible  = 0;
		$optimized = 0;

		foreach ( $results as $row ) {
			$required_ok = true;
			$all_ok      = true;
			foreach ( $checks as $check_id => $check ) {
				if ( ! $row['checks'][ $check_id ] ) {
					$all_ok = false;
					if ( 'required' === $check[1] ) {
						$required_ok = false;
					}
				}
			}
			if ( $required_ok ) {
				$eligible++;
			}
			if ( $all_ok ) {
				$optimized++;
			}
		}
		?>
		<div class="wrap wpzoom-rcb-seo-health">
			<h1><?php esc_html_e( 'Recipe SEO Health', 'recipe-card-blocks-by-wpzoom' ); ?></h1>
			<p><?php esc_html_e( 'This report checks every recipe against Google\'s structured data requirements and recommendations for recipe rich results (stars, cook time and calories in search results).', 'recipe-card-blocks-by-wpzoom' ); ?></p>

			<div class="wpzoom-seo-health-cards">
				<div class="wpzoom-seo-health-card">
					<span class="wpzoom-seo-health-number"><?php echo absint( $total ); ?></span>
					<span><?php esc_html_e( 'Recipes scanned', 'recipe-card-blocks-by-wpzoom' ); ?></span>
				</div>
				<div class="wpzoom-seo-health-card is-good">
					<span class="wpzoom-seo-health-number"><?php echo absint( $eligible ); ?></span>
					<span><?php esc_html_e( 'Eligible for rich results', 'recipe-card-blocks-by-wpzoom' ); ?></span>
				</div>
				<div class="wpzoom-seo-health-card is-great">
					<span class="wpzoom-seo-health-number"><?php echo absint( $optimized ); ?></span>
					<span><?php esc_html_e( 'Fully optimized', 'recipe-card-blocks-by-wpzoom' ); ?></span>
				</div>
			</div>

			<?php if ( empty( $results ) ) : ?>
				<p><?php esc_html_e( 'No recipes found yet. Create your first recipe with the Recipe Card block and it will show up here.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
			<?php else : ?>
			<table class="widefat striped wpzoom-seo-health-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Recipe', 'recipe-card-blocks-by-wpzoom' ); ?></th>
						<?php foreach ( $checks as $check_id => $check ) : ?>
							<th title="<?php echo esc_attr( $check[2] ); ?>">
								<?php echo esc_html( $check[0] ); ?>
								<?php if ( 'required' === $check[1] ) : ?><span class="wpzoom-seo-required">*</span><?php endif; ?>
							</th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $results as $row ) : ?>
						<tr>
							<td class="wpzoom-seo-health-recipe">
								<a href="<?php echo esc_url( $row['edit_link'] ); ?>"><strong><?php echo esc_html( $row['title'] ); ?></strong></a>
							</td>
							<?php foreach ( $checks as $check_id => $check ) : ?>
								<td class="wpzoom-seo-health-check">
									<?php if ( $row['checks'][ $check_id ] ) : ?>
										<span class="wpzoom-seo-pass" title="<?php esc_attr_e( 'OK', 'recipe-card-blocks-by-wpzoom' ); ?>">✓</span>
									<?php else : ?>
										<a class="wpzoom-seo-fail" href="<?php echo esc_url( $row['edit_link'] ); ?>" title="<?php echo esc_attr( $check[2] ); ?>">✕</a>
									<?php endif; ?>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p class="description"><?php esc_html_e( '* Required by Google for recipe rich results. Hover a column header or a ✕ to see why it matters; click a ✕ to edit that recipe.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
			<?php endif; ?>
		</div>
		<style>
			.wpzoom-seo-health-cards {
				display: flex;
				gap: 16px;
				margin: 16px 0 20px;
			}

			.wpzoom-seo-health-card {
				background: #fff;
				border: 1px solid #dcdcde;
				border-radius: 6px;
				padding: 14px 22px;
				display: flex;
				flex-direction: column;
				min-width: 140px;
			}

			.wpzoom-seo-health-card.is-good { border-left: 4px solid #dba617; }
			.wpzoom-seo-health-card.is-great { border-left: 4px solid #00a32a; }

			.wpzoom-seo-health-number {
				font-size: 28px;
				font-weight: 700;
				line-height: 1.2;
			}

			.wpzoom-seo-health-table th { white-space: nowrap; }
			.wpzoom-seo-required { color: #d63638; }
			.wpzoom-seo-health-check { text-align: center; }
			.wpzoom-seo-pass { color: #00a32a; font-weight: 700; }
			.wpzoom-seo-fail { color: #d63638; font-weight: 700; text-decoration: none; }
			.wpzoom-seo-fail:hover { color: #b32d2e; }
		</style>
		<?php
	}
}

WPZOOM_SEO_Health::init();

}
