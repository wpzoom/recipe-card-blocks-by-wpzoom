<?php
/**
 * Recipes Post Saver
 *
 * @since   2.8.2
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPZOOM_Recipe_Post_Saver' ) ) {
	/**
	 * Main WPZOOM_Recipe_Post_Saver Class.
	 *
	 * @since 2.8.2
	 */
	class WPZOOM_Recipe_Post_Saver {

		/**
		 * This plugin's instance.
		 *
		 * @var WPZOOM_Recipe_Post_Saver
		 * @since 2.8.2
		 */
		private static $instance;

		/**
		 * Provides singleton instance.
		 *
		 * @since 2.8.2
		 * @return self instance
		 */
		public static function instance() {			

			if ( null === self::$instance ) {
				self::$instance = new WPZOOM_Recipe_Post_Saver();
			}

			return self::$instance;
		}

		/**
		 * The Constructor.
		 */
		public function __construct() {
		
			add_action( 'save_post', array( __CLASS__, 'duplicate_recipe_to_custom_post' ), 10, 2 );
			add_action( 'save_post', array( __CLASS__, 'update_parent_recipe_card' ), 10, 2 );
			add_action( 'save_post', array( __CLASS__, 'check_cpt_block' ), 10, 2 );
			
		}

		/**
		 * Check if the page/post content has the CPT block.
		 *
		 * @since    1.0.0
		 * @param    array $post_id post ID with recipe card block in content.
		 */
		public static function check_cpt_block( $post_id, $post ) {

			// If this is a revision, get real post
			$revision_parent = wp_is_post_revision( $post );
			if ( $revision_parent ) {
				$post = get_post( $revision_parent );
			}	

			if( 'post' !== $post->post_type && 'page' !== $post->post_type ) {
				return;
			}

			//Check it's not an auto save routine
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return;

			//Perform permission checks! For example:
			if ( ! current_user_can( 'edit_post', $post_id ) ) 
			return;

			//Don't do anything if there is no recipe card
			if( ! has_block( 'wpzoom-recipe-card/recipe-block-from-posts', $post ) ) {
				//return;
			}

			$existing_recipes = get_posts(
				array(  
					'post_type'   => 'wpzoom_rcb',
					'numberposts' => -1
				)
			);

			foreach( $existing_recipes as $key => $recipe ) {
				$postsIds = get_post_meta( $recipe->ID, '_wpzoom_rcb_used_in', true );
				if( !empty( $postsIds ) ) {
					$postsIds = explode( ",", $postsIds );
					if ( ( $searchedId = array_search( $post->ID, $postsIds ) ) !== false ) {
						unset( $postsIds[ $searchedId ] );
					}
					$postsIds = implode( ',', $postsIds );
				}
				update_post_meta( $recipe->ID, '_wpzoom_rcb_used_in', $postsIds );
			}

			$gutenberg_matches = array();
			$gutenberg_patern = '/<!--\s+wp:(wpzoom\-recipe\-card\/recipe\-block\-from\-posts)(\s+(\{.*?\}))?\s+(\/)?-->/';
			preg_match_all( $gutenberg_patern, $post->post_content, $matches );

			if( isset( $matches[0] ) ) {
				$block = $matches[0];
			}
			if ( isset( $matches[3] ) ) {
				foreach ( $matches[3] as $block_attributes_json ) {
					if ( ! empty( $block_attributes_json ) ) {
						$attributes = json_decode( $block_attributes_json, true );
					}
				}
			}

			if( isset( $attributes['postId'] ) ) {

				$recipe_id = $attributes['postId'];

				$used_in = get_post_meta( $recipe_id, '_wpzoom_rcb_used_in', true );
				if( !empty( $used_in ) ) {
					$postsArr = explode( ",", $used_in );
				}
				$postsArr[] = $post->ID;
				$postsArr = array_unique( $postsArr );
				$meta_value = implode( ',', $postsArr );

				//print_r( array( $postsArr ) );
				update_post_meta( $recipe_id, '_wpzoom_rcb_used_in', $meta_value );
				
			}
			else {
				return;
			}
		
		}

		/**
		 * Recipe Card blocks in a content string, in order of appearance,
		 * nested blocks included.
		 *
		 * Recipe Card blocks are dynamic, so each one is a self-closing comment
		 * delimiter; its attribute JSON escapes "--", "<" and ">", so the lazy
		 * match cannot stop inside it.
		 *
		 * @since 3.7.0
		 * @param string $content Post content.
		 * @return array[] Each card: `delimiter` (the block's comment), `offset`
		 *                 (byte offset in $content), `attrs`, `key` (recipeKey).
		 */
		public static function get_recipe_cards( $content ) {

			$cards = array();

			if ( empty( $content ) || false === strpos( $content, 'wp:wpzoom-recipe-card/block-recipe-card' ) ) {
				return $cards;
			}

			$pattern = '/<!--\s+wp:wpzoom\-recipe\-card\/block\-recipe\-card(\s+(\{.*?\}))?\s+(\/)?-->/';

			if ( ! preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) ) {
				return $cards;
			}

			foreach ( $matches as $match ) {
				$attrs = array();

				if ( isset( $match[2] ) && is_array( $match[2] ) && '' !== $match[2][0] ) {
					$decoded = json_decode( $match[2][0], true );
					if ( is_array( $decoded ) ) {
						$attrs = $decoded;
					}
				}

				$cards[] = array(
					'delimiter' => $match[0][0],
					'offset'    => (int) $match[0][1],
					'attrs'     => $attrs,
					'key'       => isset( $attrs['recipeKey'] ) && is_scalar( $attrs['recipeKey'] ) ? (string) $attrs['recipeKey'] : '',
				);
			}

			return $cards;
		}

		/**
		 * Recipe post of each Recipe Card in a post.
		 *
		 * 1. A card with a key gets the recipe post created for that key.
		 * 2. A card without a key (saved before 7.5.0, or by a tool that does
		 *    not set one) gets the next remaining recipe post: the primary one
		 *    when it is the post's only keyless card, which keeps the recipe
		 *    post existing sites sync to; in creation order when there are
		 *    several, so each keeps the recipe post created for it.
		 * 3. In a post with a single card, a card with a key and no recipe post
		 *    of its own takes over a remaining recipe post that has no key, so a
		 *    card that gains a key never ends up with a duplicate recipe post.
		 *    Never with several cards: a new card must not take over an old
		 *    duplicate that other pages may still embed.
		 *
		 * Both sync directions use this, so a recipe post always writes back
		 * to the card it was filled from.
		 *
		 * @since 3.7.0
		 * @param int     $parent_id Post holding the cards.
		 * @param array[] $cards     Result of get_recipe_cards().
		 * @param bool    $fresh     Read recipe posts from the database, not the
		 *                           request cache (the saver always does).
		 * @return int[] Recipe post ID per card index, 0 for cards without one.
		 */
		public static function match_cards_to_twins( $parent_id, $cards, $fresh = true ) {

			$result = array_fill( 0, count( $cards ), 0 );
			$twins  = WPZOOM_Helpers::get_recipe_twins( $parent_id, $fresh );

			if ( empty( $twins ) ) {
				return $result;
			}

			$twin_keys = array();
			foreach ( $twins as $twin_id ) {
				$twin_keys[ $twin_id ] = (string) get_post_meta( $twin_id, WPZOOM_Helpers::CARD_KEY_META, true );
			}

			$used = array();

			$keyless_cards = 0;
			foreach ( $cards as $card ) {
				if ( '' === $card['key'] ) {
					$keyless_cards++;
				}
			}

			// Creation order: keyless recipe posts oldest first, then keyed ones.
			$keyless_twins = array();
			$keyed_twins   = array();
			foreach ( $twins as $twin_id ) {
				if ( '' === $twin_keys[ $twin_id ] ) {
					$keyless_twins[] = $twin_id;
				} else {
					$keyed_twins[] = $twin_id;
				}
			}
			sort( $keyless_twins );
			$creation_order = array_merge( $keyless_twins, $keyed_twins );

			$next_twin = function( $keyless_only, $order ) use ( $twin_keys, &$used ) {
				foreach ( $order as $twin_id ) {
					if ( empty( $used[ $twin_id ] ) && ( ! $keyless_only || '' === $twin_keys[ $twin_id ] ) ) {
						return $twin_id;
					}
				}
				return 0;
			};

			foreach ( $cards as $i => $card ) {
				if ( '' === $card['key'] ) {
					continue;
				}
				foreach ( $twins as $twin_id ) {
					if ( empty( $used[ $twin_id ] ) && $twin_keys[ $twin_id ] === $card['key'] ) {
						$result[ $i ]      = $twin_id;
						$used[ $twin_id ] = true;
						break;
					}
				}
			}

			foreach ( $cards as $i => $card ) {
				if ( '' !== $card['key'] || $result[ $i ] ) {
					continue;
				}
				$twin_id = $next_twin( false, $keyless_cards > 1 ? $creation_order : $twins );
				if ( $twin_id ) {
					$result[ $i ]      = $twin_id;
					$used[ $twin_id ] = true;
				}
			}

			foreach ( $cards as $i => $card ) {
				if ( 1 !== count( $cards ) || '' === $card['key'] || $result[ $i ] ) {
					continue;
				}
				$twin_id = $next_twin( true, $twins );
				if ( $twin_id ) {
					$result[ $i ]      = $twin_id;
					$used[ $twin_id ] = true;
				}
			}

			return $result;
		}

		/**
		 * Copy each Recipe Card of a post to its recipe post (wpzoom_rcb),
		 * creating recipe posts for cards that have none.
		 *
		 * @since 1.0.0
		 * @since 3.7.0 Every card is synced to its own recipe post, matching
		 *              Recipe Card Blocks PRO 7.5.0, which allows several cards
		 *              per post. Before, only one card was synced.
		 * @param int     $post_id Post ID.
		 * @param WP_Post $post    Post object.
		 */
		public static function duplicate_recipe_to_custom_post( $post_id, $post ) {

			// If this is a revision, get real post
			$revision_parent = wp_is_post_revision( $post );
			if ( $revision_parent ) {
				$post = get_post( $revision_parent );
			}	

			if( 'post' !== $post->post_type && 'page' !== $post->post_type ) {
				return;
			}
			
			//If moved to trash
			if( 'trash' === get_post_status( $post_id ) ) {
				return;
			}

			//Check it's not an auto save routine
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return;

			//Perform permission checks! For example:
			if ( ! current_user_can('edit_post', $post_id) ) 
			return;

			$cards = self::get_recipe_cards( $post->post_content );

			if ( empty( $cards ) ) {
				return;
			}

			$twins = self::match_cards_to_twins( $post->ID, $cards );

			// unhook this function so it doesn't loop infinitely
			remove_action( 'save_post', array( __CLASS__, 'duplicate_recipe_to_custom_post' ), 10 );

			foreach ( $cards as $i => $card ) {
				$name = ! empty( $card['attrs']['recipeTitle'] ) && is_string( $card['attrs']['recipeTitle'] ) ? $card['attrs']['recipeTitle'] : esc_html__( 'Unknown', 'recipe-card-blocks-by-wpzoom' );

				$recipe = array(
					'name'     => $name,
					'recipe'   => $card['delimiter'],
					'url'      => get_edit_post_link( $post->ID ),
					'parentId' => $post->ID,
					'cardKey'  => $card['key'],
				);

				if ( $twins[ $i ] ) {
					// A card that gained a key took over a recipe post without one.
					if ( '' !== $card['key'] && '' === (string) get_post_meta( $twins[ $i ], WPZOOM_Helpers::CARD_KEY_META, true ) ) {
						update_post_meta( $twins[ $i ], WPZOOM_Helpers::CARD_KEY_META, $card['key'] );
					}
					self::update_recipe_post( $recipe, $twins[ $i ] );
				} else {
					self::create_recipe_post( $recipe );
				}
			}

			add_action( 'save_post', array( __CLASS__, 'duplicate_recipe_to_custom_post' ), 10, 2 );

			WPZOOM_Helpers::flush_recipe_twins_cache( $post->ID );
		}

		/**
		 * Copy a saved recipe post (wpzoom_rcb) back to the Recipe Card it was
		 * created from, in its parent post.
		 *
		 * @since 1.0.0
		 * @since 3.7.0 Only the matching card is replaced, and nothing is saved
		 *              when it already holds the same content. Before, every
		 *              card in the parent was overwritten.
		 * @param int     $post_id Post ID.
		 * @param WP_Post $post    Post object.
		 */
		public static function update_parent_recipe_card( $post_id, $post ) {

			// Don't create any post if option is off
			if ( '1' !== WPZOOM_Settings::get( 'wpzoom_rcb_settings_synchronize_recipe_post' ) ) {
				return null;
			}

			// If this is a revision, get real post
			$revision_parent = wp_is_post_revision( $post );
			if ( $revision_parent ) {
				$post = get_post( $revision_parent );
			}	

			if( 'wpzoom_rcb' !== $post->post_type ) {
				return;
			}
			
			if( 'trash' === get_post_status( $post_id ) ) {
				return;
			}

			//Check it's not an auto save routine
			if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return;

			//Perform permission checks! For example:
			if ( ! current_user_can('edit_post', $post_id) ) 
			return;

			$recipe_cards = self::get_recipe_cards( $post->post_content );
			if ( empty( $recipe_cards ) ) {
				return;
			}

			//Check if there is any parent post/page
			$parent_id = WPZOOM_Helpers::get_recipe_parent_id( $post->ID );
			if ( ! $parent_id ) {
				return;
			}

			$parent_post = get_post( $parent_id );
			if ( ! $parent_post ) {
				return;
			}

			$parent_content = (string) $parent_post->post_content;
			$parent_cards   = self::get_recipe_cards( $parent_content );
			if ( empty( $parent_cards ) ) {
				return;
			}

			$index = array_search( (int) $post->ID, self::match_cards_to_twins( $parent_id, $parent_cards ), true );
			if ( false === $index ) {
				return;
			}

			$target   = $parent_cards[ $index ];
			$new_card = $recipe_cards[0]['delimiter'];
			if ( $target['delimiter'] === $new_card ) {
				return;
			}

			$update_parent_content = substr_replace( $parent_content, $new_card, $target['offset'], strlen( $target['delimiter'] ) );

			// wp_update_post() unslashes its input: without wp_slash() the escapes
			// in block attributes would lose their backslash.
			$updated_parent_args = array(
				'ID'           => $parent_id,
				'post_content' => wp_slash( $update_parent_content ),
			);

			remove_action( 'save_post', array( __CLASS__, 'update_parent_recipe_card' ), 10 );
			
			wp_update_post( $updated_parent_args );
			
			add_action( 'save_post', array( __CLASS__, 'update_parent_recipe_card' ), 10, 2 );				

		}

		/**
		 * Create a new custom post recipe.
		 *
		 * @since    1.0.0
		 * @param		 array $recipe Recipe fields to save.
		 */
		public static function create_recipe_post( $recipe, $scan = false ) {

			// Don't create any post if option is off	
			if ( !$scan && '1' !== WPZOOM_Settings::get('wpzoom_rcb_settings_create_recipe_post') ) {
				return null;
			}

			if( empty( $recipe ) ) {
				return null;
			}

			$post = array(
				'post_title'   => WPZOOM_Helpers::deserialize_block_attributes( $recipe['name'] ),
				'post_name'    => WPZOOM_Helpers::deserialize_block_attributes( $recipe['name'] ),
				'post_content' => wp_slash( $recipe['recipe'] ),
				'post_type'    => 'wpzoom_rcb',
				'post_status'  => 'publish',
			);
			
			
			$recipe_id = wp_insert_post( $post );
			update_post_meta( $recipe_id, '_wpzoom_rcb_parent_post_id', $recipe['parentId'] );
			update_post_meta( $recipe_id, '_wpzoom_rcb_has_parent', true );
			if ( ! empty( $recipe['cardKey'] ) ) {
				update_post_meta( $recipe_id, WPZOOM_Helpers::CARD_KEY_META, $recipe['cardKey'] );
			}

			WPZOOM_Helpers::flush_recipe_twins_cache( $recipe['parentId'] );

			return $recipe_id;

		}

		/**
		 * Update the custom post recipe.
		 *
		 * @since    1.0.0
		 * @since    3.7.0 Takes the recipe post ID.
		 * @param array $recipe    Recipe fields to save.
		 * @param int   $recipe_id Recipe post to update; when omitted, the
		 *                         parent's primary recipe post.
		 */
		public static function update_recipe_post( $recipe, $recipe_id = 0 ) {

			// Don't update recipe post if option is off
			if ( '1' !== WPZOOM_Settings::get('wpzoom_rcb_settings_update_recipe_post') ) {
				return null;
			}

			if( empty( $recipe ) ) {
				return null;
			}

			if ( ! $recipe_id ) {
				$recipe_id = WPZOOM_Helpers::get_recipe_twin_id( $recipe['parentId'] );
			}

			if ( ! $recipe_id ) {
				return false;
			}

			$recipe_post = array(
				'ID'           => $recipe_id,
				'post_name'    => WPZOOM_Helpers::deserialize_block_attributes( $recipe['name'] ),
				'post_content' => wp_slash( $recipe['recipe'] ),
			);
			
			$recipe_id = wp_update_post( $recipe_post );
			update_post_meta( $recipe_id, '_wpzoom_rcb_has_parent', true );

			return $recipe_id;

		}

		/**
		 * Whether a post has a recipe post created from its Recipe Card.
		 *
		 * @since 1.0.0
		 * @since 3.7.0 A meta query instead of loading every recipe post.
		 * @param int $post_id Post ID.
		 * @return bool
		 */
		public static function recipe_post_exists( $post_id ) {

			if( empty( $post_id ) ) {
				return false;
			}

			return (bool) WPZOOM_Helpers::get_recipe_twin_id( $post_id );

		}
	}
}

WPZOOM_Recipe_Post_Saver::instance();
