<?php
/**
 * Template: Accent Color Header
 *
 * @since       2.8.2
 * 
 * @package     WPZOOM_Recipe_Card_Blocks
 * @subpackage  WPZOOM_Recipe_Card_Blocks/templates/recipe
 */
?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( trim( $RecipeCardClassName ) ); ?>">
    <div class="recipe-card-header-container">
        <?php if ( ! empty( $attachment ) ): ?>
            <div class="recipe-card-image">
                <figure>
                    <?php echo $attachment; ?>
                </figure>
            </div><!-- /.recipe-card-image -->
        <?php endif ?>

        <h2 class="recipe-card-title">
            <?php echo ! empty( $stored_data['recipeTitle'] ) ? strip_tags( $stored_data['recipeTitle'] ) : strip_tags( $stored_data['recipe_title'] ); ?>
        </h2>

        <?php
            if ( ! empty( $stored_data['summary'] ) ):
                $summary_class = WPZOOM_Helpers::classNames( array(
                    'recipe-card-summary',
                    array(
                        'no-print' => '0' == WPZOOM_Settings::get('wpzoom_rcb_settings_print_show_summary_text')
                    )
                ) );
        ?>
            <p class="<?php echo esc_attr( $summary_class ) ?>"><?php echo $stored_data['summary'] ?></p>
        <?php endif ?>

        <?php
            if ( $settings['pin_btn'] ) {
                echo WPZOOM_Premium_Recipe_Card_Block::get_pinterest_button( array( 'url' => $pin_image), $recipe_permalink, $pin_description ); 
            }
            if ( $settings['print_btn'] ) {
                echo WPZOOM_Premium_Recipe_Card_Block::get_print_button( $id, array( 'title' => __( 'Print directions', 'wpzoom-recipe-card' ) ) );
            }
        ?>

        <div class="recipe-card-heading">
            <?php if ( $settings['displayAuthor'] ): ?>
                <span class="recipe-card-author"><?php echo __( 'Recipe by', 'wpzoom-recipe-card' ) .' '. $custom_author_name ?></span>
            <?php endif ?>
            <?php echo wpzoom_rating_stars( $stored_data['recipe_ID'] ); ?>
            <?php
                if ( $settings['displayCourse'] ) {
                    echo WPZOOM_Premium_Recipe_Card_Block::get_recipe_terms( 'wpzoom_rcb_courses' );
                }
                if ( $settings['displayCuisine'] ) {
                    echo WPZOOM_Premium_Recipe_Card_Block::get_recipe_terms( 'wpzoom_rcb_cuisines' );
                }
                if ( $settings['displayDifficulty'] ) {
                    echo WPZOOM_Premium_Recipe_Card_Block::get_recipe_terms( 'wpzoom_rcb_difficulties' );
                }
            ?>
        </div><!-- /.recipe-card-heading -->
    </div><!-- /.recipe-card-header-container -->
    <div class="recipe-card-content-container">
        <?php
            if ( ! empty( $detail_items ) ):
                $details_class = WPZOOM_Helpers::classNames( array(
                    'recipe-card-details',
                    array(
                        'no-print' => '0' == WPZOOM_Settings::get('wpzoom_rcb_settings_print_show_details')
                    )
                ) );
        ?>
            <div class="<?php echo esc_attr( $details_class ) ?>">
                <div class="details-items"><?php echo $detail_items ?></div>
            </div>
        <?php endif ?>

        <?php echo $food_labels_content_top; ?>

        <?php
            if ( ! empty( $ingredient_items ) ):
                $ingredients_class = WPZOOM_Helpers::classNames( array(
                    'ingredients-list',
                    'layout-'. $settings['ingredientsLayout']
                ) );
        ?>
            <div class="recipe-card-ingredients">
                <h3 class="ingredients-title"><?php echo $stored_data['ingredientsTitle'] ?></h3>
                <ul class="<?php echo esc_attr( $ingredients_class ) ?>"><?php echo $ingredient_items ?></ul>
            </div>
        <?php endif ?>

        <?php if ( ! empty( $direction_items ) ): ?>
            <div class="recipe-card-directions">
                <h3 class="directions-title"><?php echo $stored_data['directionsTitle'] ?></h3>
                <ul class="directions-list"><?php echo $direction_items ?></ul>
            </div>
        <?php endif ?>

        <?php if ( ! empty( $recipe_card_video ) ): ?>
            <div class="recipe-card-video no-print">
                <h3 class="video-title"><?php echo $stored_data['videoTitle'] ?></h3>
                <?php echo $recipe_card_video ?>
            </div>
        <?php endif ?>

        <?php if ( ! empty( $notes_items ) ): ?>
            <div class="recipe-card-notes">
                <h3 class="notes-title"><?php echo $stored_data['notesTitle'] ?></h3>
                <ul class="recipe-card-notes-list"><?php echo $notes_items ?></ul>
            </div>
        <?php endif ?>

        <?php echo $food_labels_content_bottom; ?>
    </div><!-- /.recipe-card-content-container -->
    <div class="recipe-card-footer-container">
        <?php echo $cta_content; ?>

        <?php if ( '1' !== WPZOOM_Settings::get('wpzoom_rcb_settings_footer_copyright') ): ?>
            <div class="footer-copyright">
                <p><?php _e( 'Recipe Card plugin by ', 'wpzoom-recipe-card' ) ?><a href="https://www.wpzoom.com/plugins/recipe-card-blocks/" target="_blank" rel="nofollow noopener noreferrer">WPZOOM</a></p>
            </div>
        <?php endif ?>
    </div><!-- /.recipe-card-footer-container -->

    <?php if ( ! empty( $json_ld ) ): ?>
        <script type="application/ld+json"><?php echo wp_json_encode( $json_ld ) ?></script>
    <?php endif ?>
</div><!-- /.wp-block-wpzoom-recipe-card-block-recipe-card -->

<style id="wpzoom-rcb-block-template-<?php echo esc_attr( $style ) ?>-inline-css" type="text/css">
    <?php
        $custom_css = '';
        $block_class_name = ".wp-block-wpzoom-recipe-card-block-recipe-card.is-style-{$style}";
        $accent_bg_color_header = $settings['accent_bg_color_header'];
        $accent_text_color_header = $settings['accent_text_color_header'];
        $image_border_color = $settings['image_border_color'];
        $pinterest_bg_color = $settings['pinterest_bg_color'];
        $pinterest_text_color = $settings['pinterest_text_color'];
        $print_bg_color = $settings['print_bg_color'];
        $print_text_color = $settings['print_text_color'];
        $recipe_title_color = $settings['recipe_title_color'];
        $meta_color = $settings['meta_color'];
        $rating_stars_color = $settings['rating_stars_color'];

        $custom_css .= "{$block_class_name} .recipe-card-header-container {
            background-color: {$accent_bg_color_header} !important;
            color: {$accent_text_color_header} !important;
        }\n";
        $custom_css .= "{$block_class_name} .recipe-card-header-container .recipe-card-title {
            color: {$recipe_title_color} !important;
        }\n";
        $custom_css .= "{$block_class_name} .wpzoom-rating-stars-average {
            color: {$meta_color} !important;
        }\n";

        if ( ! empty( $rating_stars_color ) ) {
            $custom_css .= "{$block_class_name} ul.wpzoom-rating-stars>li.fa-star {
                color: {$rating_stars_color} !important;
            }\n";
        }

        if ( ! empty( $attachment ) ) {
            $custom_css .= "{$block_class_name} .recipe-card-header-container .recipe-card-image figure img {
                border: 10px solid {$image_border_color} !important;
            }\n";
        }

        if ( $settings['displayCourse'] ) {
            $custom_css .= "{$block_class_name} .recipe-card-header-container .recipe-card-course {
                color: {$accent_text_color_header} !important;
            }\n";
            $custom_css .= "{$block_class_name} .recipe-card-header-container .recipe-card-course mark {
                color: {$meta_color} !important;
            }\n";
        }

        if ( $settings['displayCuisine'] ) {
            $custom_css .= "{$block_class_name} .recipe-card-header-container .recipe-card-cuisine {
                color: {$accent_text_color_header} !important;
            }\n";
            $custom_css .= "{$block_class_name} .recipe-card-header-container .recipe-card-cuisine mark {
                color: {$meta_color} !important;
            }\n";
            $custom_css .= "{$block_class_name} .recipe-card-header-container .recipe-card-cuisine::before {
                color: {$accent_text_color_header} !important;
            }\n";
        }

        if ( $settings['displayDifficulty'] ) {
            $custom_css .= "{$block_class_name} .recipe-card-header-container .recipe-card-difficulty {
                color: {$accent_text_color_header} !important;
            }\n";
            $custom_css .= "{$block_class_name} .recipe-card-header-container .recipe-card-difficulty mark {
                color: {$meta_color} !important;
            }\n";
            $custom_css .= "{$block_class_name} .recipe-card-header-container .recipe-card-difficulty::before {
                color: {$accent_text_color_header} !important;
            }\n";
        }

        if ( ! empty( $detail_items ) ) {
            $custom_css .= "{$block_class_name} .recipe-card-details .details-items .detail-item .detail-item-icon {
                color: {$accent_bg_color_header} !important;
            }\n";
        }

        if ( $settings['pin_btn'] ) {
            $custom_css .= "{$block_class_name} .wpzoom-recipe-card-pinit .btn-pinit-link {
                background-color: {$pinterest_bg_color} !important;
                color: {$pinterest_text_color} !important;
            }\n";
            $custom_css .= "{$block_class_name} .wpzoom-recipe-card-pinit .btn-pinit-link .wpzoom-rcb-pinit-icon {
                fill: {$pinterest_text_color};
            }\n";
        }

        if ( $settings['print_btn'] ) {
            $custom_css .= "{$block_class_name} .wpzoom-recipe-card-print-link .btn-print-link {
                background-color: {$print_bg_color} !important;
                color: {$print_text_color} !important;
            }\n";
            $custom_css .= "{$block_class_name} .wpzoom-recipe-card-print-link .btn-print-link .wpzoom-rcb-print-icon {
                fill: {$print_text_color};
            }\n";
        }

        if ( ! empty( $ingredient_items ) ) {
            $custom_css .= "{$block_class_name} .ingredients-list > li .tick-circle {
                border-color: {$accent_bg_color_header};
            }\n";
            $custom_css .= "{$block_class_name} .ingredients-list > li.ticked .tick-circle {
                border-color: {$accent_bg_color_header} !important;
                background-color: {$accent_bg_color_header};
            }\n";
        }

        if ( ! empty( $notes_items ) ) {
            $custom_css .= "{$block_class_name} .recipe-card-notes .recipe-card-notes-list>li::before {
                background-color: {$accent_bg_color_header};
                color: {$accent_text_color_header};
            }";
        }

        echo $custom_css;
    ?>
</style>