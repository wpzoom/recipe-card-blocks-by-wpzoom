<?php
/**
 * Class Lite vs PRO Page
 *
 * @since   2.2.0
 * @package WPZOOM_Recipe_Card_Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for lite-vs-pro page.
 */
class WPZOOM_Lite_vs_PRO {

	/**
	 * The Constructor.
	 */
	public function __construct() {

		// Check what page we are on.
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

		// Only load if we are actually on the settings page.
		if ( 'wpzoom-recipe-card-vs-pro' === $page ) {
			add_action( 'wpzoom_rcb_admin_page', array( $this, 'lite_vs_pro_page' ) );

			// Include admin scripts & styles
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		}
	}

	public function lite_vs_pro_page() {
		// check user capabilities
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		?>
        <script>

          jQuery(document).ready(function($){

                $( function() {
                  $( "#tabs" ).tabs();
                } );

          });

          </script>


        <div class="wpz-onboard_wrapper">
            <div id="tabs">

                    <div class="wpz-onboard_header">
                        <div class="wpz-onboard_title-wrapper">
                            <h1 class="wpz-onboard_title"><svg width="30" height="30" viewBox="0 0 500 500" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_408_204)"><circle cx="250" cy="250" r="250" fill="#E1581A"/><path fill-rule="evenodd" clip-rule="evenodd" d="M252.597 100C221.304 100 193.611 117.861 178.955 146.232C140.058 146.561 110 180.667 110 221.822C110 251.163 125.719 277.528 149.758 289.881V381.396C149.758 390.834 156.879 399 166.3 399H333.775C343.196 399 350.317 390.834 350.317 381.396V267.062C374.304 254.662 390 228.316 390 199.037C390 157.678 358.917 123.483 320.067 123.483C316.597 123.483 313.132 123.77 309.694 124.334C294.308 108.794 274.007 100 252.597 100ZM204.614 170.955C212.223 149.098 231.113 135.208 252.597 135.208C267.726 135.208 281.464 142.141 291.342 154.94C295.598 160.449 302.673 162.77 309.186 160.524C312.757 159.302 316.424 158.691 320.067 158.691C340.096 158.691 356.916 176.495 356.916 199.037C356.916 217.48 345.452 233.354 329.465 237.933C322.085 240.053 317.233 247.147 317.233 254.931V302.003H182.842V277.716C182.842 269.917 177.976 262.803 170.558 260.702C154.57 256.173 143.084 240.307 143.084 221.822C143.084 199.629 160.141 181.436 179.524 181.436C181.644 181.436 183.782 181.663 185.883 182.105L185.89 182.107C194.008 183.801 201.858 178.878 204.614 170.955ZM182.842 363.792V337.211H317.233V363.792H182.842Z" fill="white"/></g><defs><clipPath id="clip0_408_204"><rect width="500" height="500" fill="white"/></clipPath></defs></svg> Recipe Card Blocks <span>Free</span></h1>

                            <h2 class="wpz-onboard_framework-version">
                                <?php printf( esc_html__( 'v. %s', 'recipe-card-blocks-by-wpzoom' ), WPZOOM_RCB_VERSION ); ?>
                            </h2>
                        </div>

                        <ul class="wpz-onboard_tabs">
                            <li class="wpz-onboard_tab wpz-onboard_tab-quick-start"><a href="#quick-start" title="Quick Start"><svg width="18" height="18" viewBox="0 0 13 15" fill="none" xmlns="https://www.w3.org/2000/svg"><path d="M0.166992 14.5V0.333332H7.66699L8.00033 2H12.667V10.3333H6.83366L6.50033 8.66667H1.83366V14.5H0.166992ZM8.20866 8.66667H11.0003V3.66667H6.62533L6.29199 2H1.83366V7H7.87533L8.20866 8.66667Z" fill="#000"></path></svg> <?php esc_html_e( 'PRO Features', 'recipe-card-blocks-by-wpzoom' ); ?></a></li>

                            <li class="wpz-onboard_tab wpz-onboard_tab-theme-child"><a href="#vs-pro" title="Free vs. PRO"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="https://www.w3.org/2000/svg"><path clip-rule="evenodd" d="M15 5.75C11.5482 5.75 8.75 8.54822 8.75 12C8.75 15.4518 11.5482 18.25 15 18.25C15.9599 18.25 16.8674 18.0341 17.6782 17.6489C18.0523 17.4712 18.4997 17.6304 18.6774 18.0045C18.8552 18.3787 18.696 18.8261 18.3218 19.0038C17.3141 19.4825 16.1873 19.75 15 19.75C10.7198 19.75 7.25 16.2802 7.25 12C7.25 7.71979 10.7198 4.25 15 4.25C19.2802 4.25 22.75 7.71979 22.75 12C22.75 12.7682 22.638 13.5115 22.429 14.2139C22.3108 14.6109 21.8932 14.837 21.4962 14.7188C21.0992 14.6007 20.8731 14.1831 20.9913 13.7861C21.1594 13.221 21.25 12.6218 21.25 12C21.25 8.54822 18.4518 5.75 15 5.75Z" fill="black" fill-rule="evenodd"/><path clip-rule="evenodd" d="M5.25 5C5.25 4.58579 5.58579 4.25 6 4.25H15C15.4142 4.25 15.75 4.58579 15.75 5C15.75 5.41421 15.4142 5.75 15 5.75H6C5.58579 5.75 5.25 5.41421 5.25 5Z" fill="black" fill-rule="evenodd"/><path clip-rule="evenodd" d="M4.75 8.5C4.75 8.08579 5.08579 7.75 5.5 7.75H8.5C8.91421 7.75 9.25 8.08579 9.25 8.5C9.25 8.91421 8.91421 9.25 8.5 9.25H5.5C5.08579 9.25 4.75 8.91421 4.75 8.5Z" fill="black" fill-rule="evenodd"/><path clip-rule="evenodd" d="M1.25 8.5C1.25 8.08579 1.58579 7.75 2 7.75H3.5C3.91421 7.75 4.25 8.08579 4.25 8.5C4.25 8.91421 3.91421 9.25 3.5 9.25H2C1.58579 9.25 1.25 8.91421 1.25 8.5Z" fill="black" fill-rule="evenodd"/><path clip-rule="evenodd" d="M3.25 12.5C3.25 12.0858 3.58579 11.75 4 11.75H8C8.41421 11.75 8.75 12.0858 8.75 12.5C8.75 12.9142 8.41421 13.25 8 13.25H4C3.58579 13.25 3.25 12.9142 3.25 12.5Z" fill="black" fill-rule="evenodd"/><path clip-rule="evenodd" d="M12.376 8.58397C12.5151 8.37533 12.7492 8.25 13 8.25H17C17.2508 8.25 17.4849 8.37533 17.624 8.58397L19.624 11.584C19.792 11.8359 19.792 12.1641 19.624 12.416L17.624 15.416C17.4849 15.6247 17.2508 15.75 17 15.75H13C12.7492 15.75 12.5151 15.6247 12.376 15.416L10.376 12.416C10.208 12.1641 10.208 11.8359 10.376 11.584L12.376 8.58397ZM13.4014 9.75L11.9014 12L13.4014 14.25H16.5986L18.0986 12L16.5986 9.75H13.4014Z" fill="black" fill-rule="evenodd"/></svg> <?php esc_html_e( 'Free vs. PRO', 'recipe-card-blocks-by-wpzoom' ); ?></a></li>

                        </ul>
                    </div>

                    <div class="wpz-onboard_content-wrapper">
                        <div class="wpz-onboard_content">

                            <div class="wpz-onboard_content-main">
                                <div id="quick-start" class="wpz-onboard_content-main-tab">

                                    <div class="theme-info-wrap welcome-section">

                                        <h3 class="wpz-onboard_content-main-title"><?php esc_html_e( 'Welcome, foodies!', 'recipe-card-blocks-by-wpzoom' ); ?> 👋</h3>
                                        <p class="wpz-onboard_content-main-intro"><?php esc_html_e( 'Thank you for installing the free version of our plugin! You\'ve already taken the first step towards making your food blog a go-to resource for mouthwatering recipes with the Recipe Card Blocks plugin. But why stop there when you can give your readers and your blog the gourmet treatment with the PRO version?', 'recipe-card-blocks-by-wpzoom' ); ?></p>

                                        <p class="section_footer">
                                            <a href="<?php echo esc_url( __( 'https://recipecard.io/pricing/?utm_source=wpadmin&utm_medium=about-rcb-page&utm_campaign=upgrade-premium', 'recipe-card-blocks-by-wpzoom' ) ); ?>" target="_blank" class="button button-primary">
                                                <?php esc_html_e( 'Get the PRO version &rarr;', 'recipe-card-blocks-by-wpzoom' ); ?>
                                            </a>

                                        </p>

                                    </div>

                                        <div class="theme-info-wrap">

                                            <h3 class="wpz-onboard_content-main-title"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="https://www.w3.org/2000/svg">
                                                <mask id="mask0_3409_3568" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
                                                <rect width="24" height="24" fill="#D9D9D9"></rect>
                                                </mask>
                                                <g mask="url(#mask0_3409_3568)">
                                                <path d="M19 9L17.75 6.25L15 5L17.75 3.75L19 1L20.25 3.75L23 5L20.25 6.25L19 9ZM19 23L17.75 20.25L15 19L17.75 17.75L19 15L20.25 17.75L23 19L20.25 20.25L19 23ZM9 20L6.5 14.5L1 12L6.5 9.5L9 4L11.5 9.5L17 12L11.5 14.5L9 20ZM9 15.15L10 13L12.15 12L10 11L9 8.85L8 11L5.85 12L8 13L9 15.15Z" fill="#e15819"></path>
                                                </g>
                                                </svg>  <?php esc_html_e( 'PRO Features', 'recipe-card-blocks-by-wpzoom' ); ?></h3>

                                                <div class="wpz-grid-wrap">

                                                    <div class="section">
                                                        <h4>
                                                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path opacity="0.3" d="M18.8699 4C17.4999 5.19 14.9999 6.12 11.9999 6.12C8.99988 6.12 6.49988 5.19 5.12988 4H18.8699Z" fill="#E1581A"/>
                                                            <path d="M14 11V8C18.56 7.42 22 4.9 22 2H2C2 4.9 5.44 7.42 10 8V11C6.32 11.73 2 14.61 2 22H8V20H4.13C5.06 13.17 10.78 12.8 12 12.8C13.22 12.8 18.94 13.17 19.87 20H16V22H22C22 14.61 17.68 11.73 14 11ZM18.87 4C17.5 5.19 15 6.12 12 6.12C9 6.12 6.5 5.19 5.13 4H18.87ZM12 22C10.9 22 10 21.1 10 20C10 19.45 10.22 18.95 10.59 18.59C11.39 17.79 16 16 16 16C16 16 14.21 20.61 13.41 21.41C13.05 21.78 12.55 22 12 22Z" fill="#E1581A"/>
                                                            </svg> <?php esc_html_e( 'Unit Conversion', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                        </h4>
                                                        <p class="about">

                                                            <a href="https://recipecard.io/pricing/?utm_source=wpadmin&utm_medium=about-rcb-page&utm_campaign=unitconversionfeature" title="Unit Conversion" target="_blank"><img src="https://recipecard.io/wp-content/themes/wpzoom-rcb/images/recipe-block/unit-conversion.png" alt="<?php echo esc_attr__( 'Unit Conversion', 'recipe-card-blocks-by-wpzoom' ); ?>" /></a>

                                                            <?php esc_html_e( 'The conversion info is a must-have for any kitchen enthusiast. Let your readers convert recipes between US Customary and Metric units. Our plugin integrates an API that automatically calculates the needed values for you.', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                        </p>
                                                        <p class="section_footer">
                                                            <a href="<?php echo esc_url( __( 'https://recipecard.io/features/unit-conversion/', 'recipe-card-blocks-by-wpzoom' ) ); ?>" target="_blank" class="button button-primary">
                                                                <?php esc_html_e( 'Learn More &rarr;', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                            </a>

                                                        </p>
                                                    </div>



                                                    <div class="section">

                                                        <h4>
                                                            <svg baseProfile="tiny" height="26" id="Layer_1" fill="#e15819" version="1.2" viewBox="0 0 24 24" width="26" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g><g><path d="M9.362,9.158c0,0-3.16,0.35-5.268,0.584c-0.19,0.023-0.358,0.15-0.421,0.343s0,0.394,0.14,0.521    c1.566,1.429,3.919,3.569,3.919,3.569c-0.002,0-0.646,3.113-1.074,5.19c-0.036,0.188,0.032,0.387,0.196,0.506    c0.163,0.119,0.373,0.121,0.538,0.028c1.844-1.048,4.606-2.624,4.606-2.624s2.763,1.576,4.604,2.625    c0.168,0.092,0.378,0.09,0.541-0.029c0.164-0.119,0.232-0.318,0.195-0.505c-0.428-2.078-1.071-5.191-1.071-5.191    s2.353-2.14,3.919-3.566c0.14-0.131,0.202-0.332,0.14-0.524s-0.23-0.319-0.42-0.341c-2.108-0.236-5.269-0.586-5.269-0.586    s-1.31-2.898-2.183-4.83c-0.082-0.173-0.254-0.294-0.456-0.294s-0.375,0.122-0.453,0.294C10.671,6.26,9.362,9.158,9.362,9.158z"/></g></g></svg> <?php esc_html_e( 'Recipe Star Rating', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                        </h4>
                                                        <p class="about">

                                                            <a href="https://recipecard.io/pricing/?utm_source=wpadmin&utm_medium=about-rcb-page&utm_campaign=starrating" title="Unit Conversion" target="_blank"><img src="https://recipecard.io/wp-content/themes/wpzoom-rcb/images/recipe-block/feat6.png" alt="<?php echo esc_attr__( 'Star Rating', 'recipe-card-blocks-by-wpzoom' ); ?>" /></a>

                                                            <?php esc_html_e( 'This feature enables readers to rate your recipes. This will not only boosts engagement by allowing users to share their feedback but also helps build trust with potential readers by displaying authentic, user-generated ratings.', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                        </p>

                                                        <p class="section_footer">

                                                           <a href="<?php echo esc_url( __( 'https://recipecard.io/features/star-rating/', 'recipe-card-blocks-by-wpzoom' ) ); ?>" target="_blank" class="button button-primary">
                                                              <?php esc_html_e( 'Learn More &rarr;', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                           </a>

                                                        </p>

                                                    </div>

                                                    <div class="section">
                                                        <h4>
                                                            <svg width="26" height="26" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M42 10C39.78 9.3 37.34 9 35 9C31.1 9 26.9 9.8 24 12C21.1 9.8 16.9 9 13 9C9.1 9 4.9 9.8 2 12V41.3C2 41.8 2.5 42.3 3 42.3C3.2 42.3 3.3 42.2 3.5 42.2C6.2 40.9 10.1 40 13 40C16.9 40 21.1 40.8 24 43C26.7 41.3 31.6 40 35 40C38.3 40 41.7 40.6 44.5 42.1C44.7 42.2 44.8 42.2 45 42.2C45.5 42.2 46 41.7 46 41.2V12C44.8 11.1 43.5 10.5 42 10ZM6 37V14C8.2 13.3 10.6 13 13 13C15.68 13 19.26 13.82 22 14.98V37.98C19.26 36.82 15.68 36 13 36C10.6 36 8.2 36.3 6 37ZM42 37C39.8 36.3 37.4 36 35 36C32.32 36 28.74 36.82 26 37.98V14.98C28.74 13.8 32.32 13 35 13C37.4 13 39.8 13.3 42 14V37Z" fill="#e15819"/>
                                                            <path opacity="0.3" d="M22 14.98C19.26 13.82 15.68 13 13 13C10.6 13 8.2 13.3 6 14V37C8.2 36.3 10.6 36 13 36C15.68 36 19.26 36.82 22 37.98V14.98Z" fill="#e15819"/>
                                                            <path d="M35 21C36.76 21 38.46 21.18 40 21.52V18.48C38.42 18.18 36.72 18 35 18C32.44 18 30.08 18.32 28 18.94V22.08C29.98 21.38 32.36 21 35 21ZM35 26.32C36.76 26.32 38.46 26.5 40 26.84V23.8C38.42 23.5 36.72 23.32 35 23.32C32.44 23.32 30.08 23.64 28 24.26V27.4C29.98 26.72 32.36 26.32 35 26.32ZM35 31.66C36.76 31.66 38.46 31.84 40 32.18V29.14C38.42 28.84 36.72 28.66 35 28.66C32.44 28.66 30.08 28.98 28 29.6V32.74C29.98 32.04 32.36 31.66 35 31.66Z" fill="#e15819"/>
                                                            </svg> <?php esc_html_e( 'Recipe Index Block', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                        </h4>
                                                        <p class="about">
                                                            <a href="https://recipecard.io/pricing/?utm_source=wpadmin&utm_medium=about-rcb-page&utm_campaign=unitconversionfeature" title="Unit Conversion" target="_blank"><img src="https://recipecard.io/wp-content/themes/wpzoom-rcb/images/recipe-block/index.png" alt="<?php echo esc_attr__( 'REcipe index block', 'recipe-card-blocks-by-wpzoom' ); ?>" /></a>

                                                            <?php esc_html_e( 'A unique block, designed to enhance the organization and display of recipes on your WordPress site. This block serves as a powerful tool for food bloggers, culinary enthusiasts, and anyone looking to showcase their collection of recipes in a more structured and visually appealing manner.', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                        </p>

                                                        <p class="section_footer">

                                                           <a href="<?php echo esc_url( __( 'https://recipecard.io/features/recipe-index/', 'recipe-card-blocks-by-wpzoom' ) ); ?>" target="_blank" class="button button-primary">
                                                               <?php esc_html_e( 'Learn More &rarr;', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                           </a>

                                                        </p>
                                                    </div>

                                                    <div class="section">
                                                        <h4>
                                                            <svg width="26" height="26" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path opacity="0.3" d="M10 38H38V10H10V38ZM22 14H34V18H22V14ZM22 22H34V26H22V22ZM22 30H34V34H22V30ZM14 14H18V18H14V14ZM14 22H18V26H14V22ZM14 30H18V34H14V30Z" fill="#E1581A"/>
                                                            <path d="M22 14H34V18H22V14ZM22 22H34V26H22V22ZM22 30H34V34H22V30ZM14 14H18V18H14V14ZM14 22H18V26H14V22ZM14 30H18V34H14V30ZM40.2 6H7.8C6.8 6 6 6.8 6 7.8V40.2C6 41 6.8 42 7.8 42H40.2C41 42 42 41 42 40.2V7.8C42 6.8 41 6 40.2 6ZM38 38H10V10H38V38Z" fill="#E1581A"/>
                                                            </svg> <?php esc_html_e( 'Recipe Roundups', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                        </h4>
                                                        <p class="about">

                                                            <a href="https://recipecard.io/pricing/?utm_source=wpadmin&utm_medium=about-rcb-page&utm_campaign=unitconversionfeature" title="Unit Conversion" target="_blank"><img src="https://recipecard.io/wp-content/themes/wpzoom-rcb/images/recipe-block/roundups.png" alt="<?php echo esc_attr__( 'REcipe roundups', 'recipe-card-blocks-by-wpzoom' ); ?>" /></a>

                                                            <?php esc_html_e( 'The Recipe Roundups feature in the PRO version of the Recipe Card Blocks plugin allows food bloggers to curate and showcase collections of recipes around specific themes, seasons, or ingredients. This feature makes it easy to organize and present grouped content that highlights your best recipes or explores a particular culinary trend.', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                        </p>

                                                        <p class="section_footer">

                                                           <a href="<?php echo esc_url( __( 'https://recipecard.io/documentation/how-to-create-recipe-roundups-using-the-recipe-summary-block/', 'recipe-card-blocks-by-wpzoom' ) ); ?>" target="_blank" class="button button-primary">
                                                               <?php esc_html_e( 'Learn More &rarr;', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                           </a>

                                                        </p>
                                                    </div>

                                                    <div class="section">
                                                        <h4>
                                                            <svg style="enable-background:new 0 0 64 64;" version="1.1" width="26" height="26" viewBox="0 0 64 64" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="rice_cooker"/><g id="kettle"/><g id="cutting_board"/><g id="mug"/><g id="scale"/><g id="rolling_pin"/><g id="spatula"><g><g><path d="M51.0081,3.8605L39.0495,15.8194     c-1.911,1.911-2.347,4.758-1.3118,7.0945c0.3049,0.6985,0.7445,1.3473,1.3118,1.9146c0.6594,0.6595,1.4323,1.1452,2.2584,1.4536     c2.269,0.8474,4.9316,0.3652,6.7504-1.4536l11.9585-11.9589c2.4782-2.4783,2.4782-6.5343,0-9.009     C57.5387,1.3822,53.4863,1.3822,51.0081,3.8605L51.0081,3.8605z" style="fill-rule:evenodd;clip-rule:evenodd;fill:#E67D40;"/></g><g><path d="M37.7377,22.9138L24.5135,36.1385l3.4674,3.4675     L41.3079,26.282c-0.8261-0.3085-1.599-0.7942-2.2584-1.4536C38.4823,24.2611,38.0426,23.6123,37.7377,22.9138L37.7377,22.9138z" style="fill-rule:evenodd;clip-rule:evenodd;fill:#E0E0E0;"/></g><g><path d="M15.7883,33.5077L3.3795,45.9169     c-1.6734,1.677-1.6734,4.4177,0,6.0947l8.7323,8.7289c1.6734,1.677,4.414,1.677,6.091,0l12.4088-12.4091     c1.677-1.6735,1.677-4.4177,0-6.0911l-2.6307-2.6343l-3.4674-3.4675l-2.6307-2.6307     C20.2059,31.8342,17.4653,31.8342,15.7883,33.5077L15.7883,33.5077z" style="fill-rule:evenodd;clip-rule:evenodd;fill:#F7F7F7;"/></g><g><path d="M39.1701,24.9489L26.2472,37.8722l1.7337,1.7337     L41.3079,26.282C40.5315,25.9913,39.8047,25.5481,39.1701,24.9489L39.1701,24.9489z" style="fill-rule:evenodd;clip-rule:evenodd;fill:#C9C7C7;"/></g><g><path d="M28.8779,40.5029c1.677,1.677,1.677,4.4177,0,6.0947     L16.469,59.0067c-1.6699,1.6699-4.3998,1.677-6.0768,0.0142l1.7195,1.7196c1.6734,1.677,4.414,1.677,6.091,0l12.4088-12.4091     c1.677-1.6735,1.677-4.4177,0-6.0911L28.8779,40.5029z" style="fill-rule:evenodd;clip-rule:evenodd;fill:#E0E0E0;"/></g><g><path d="M56.6027,11.1287L44.6441,23.0911     c-1.5068,1.5068-3.5986,2.0954-5.5627,1.7692c0.0284,0.0319,0.0603,0.0603,0.0886,0.0886     c0.6346,0.5992,1.3614,1.0424,2.1379,1.3331c2.269,0.8474,4.9316,0.3652,6.7504-1.4536l11.9585-11.9589     c2.4782-2.4783,2.4782-6.5343,0-9.009c-0.9714-0.9715-2.1804-1.56-3.4461-1.7727c0.0106,0.0106,0.0213,0.0213,0.0319,0.0319     C59.0809,4.5979,59.0809,8.654,56.6027,11.1287L56.6027,11.1287z" style="fill-rule:evenodd;clip-rule:evenodd;fill:#D15F1E;"/></g><g><path d="M20.1223,39.4307c-0.3906-0.3906-1.0234-0.3906-1.414,0l-9.3954,9.3954c-0.3906,0.3906-0.3906,1.0234,0,1.414     c0.1953,0.1953,0.4511,0.2929,0.707,0.2929s0.5117-0.0976,0.707-0.2929l9.3954-9.3954     C20.5129,40.4541,20.5129,39.8213,20.1223,39.4307z"/><path d="M14.0567,55.0231c0.1953,0.1953,0.4511,0.293,0.707,0.293c0.2558,0,0.5117-0.0977,0.707-0.293l9.3989-9.3954     c0.3906-0.3901,0.3906-1.0234,0-1.414s-1.0234-0.3906-1.414,0l-9.3989,9.3954C13.6661,53.9992,13.6661,54.6325,14.0567,55.0231z"/><path d="M60.7239,3.1536c-2.8739-2.8743-7.5489-2.8734-10.4227,0L38.3424,15.1123c-2.0392,2.0396-2.6235,4.9844-1.7683,7.5508     L24.5131,34.7248l-1.9232-1.9241c0-0.0005-0.0005-0.0005-0.0005-0.0005c-2.0697-2.0658-5.4377-2.0658-7.5079,0.0005     L2.6717,45.2102c-2.0653,2.0702-2.0653,5.4377,0.001,7.5083l8.7314,8.728c0.9975,0.9999,2.329,1.5512,3.7488,1.5517     c0.001,0,0.002,0,0.0034,0c1.4203,0,2.7537-0.5507,3.7537-1.5507l12.408-12.408c1.0004-0.9985,1.5512-2.3314,1.5512-3.7537     s-0.5508-2.7552-1.5507-3.7527l-1.9248-1.9257l12.1928-12.1896c0.6331,0.1735,1.292,0.2695,1.9676,0.2695     c1.973,0,3.824-0.7641,5.2111-2.1517l11.9588-11.9593c1.3876-1.3871,2.1517-3.2386,2.1517-5.2126     C62.8756,6.3892,62.1115,4.5387,60.7239,3.1536z M30.8692,45.2858c0,0.8872-0.3423,1.7172-0.9648,2.3387L17.496,60.0335     c-0.6225,0.622-1.453,0.9648-2.3397,0.9648c-0.001,0-0.0015,0-0.0024,0c-0.8852-0.0005-1.7143-0.3428-2.3348-0.9648     L4.0876,51.305c-1.2885-1.2909-1.2885-3.3909-0.001-4.6814l12.4084-12.408c0.645-0.644,1.4926-0.9663,2.3407-0.9663     c0.8476,0,1.6952,0.3222,2.3407,0.9663l8.7285,8.7324C30.5269,43.5687,30.8692,44.3987,30.8692,45.2858z M39.6192,26.5565     L27.9807,38.192l-2.0531-2.0536l11.5872-11.5877C38.0712,25.345,38.7981,26.0403,39.6192,26.5565z M59.3099,12.1623     L47.3511,24.1216c-1.0097,1.0097-2.3583,1.5658-3.7971,1.5658s-2.7879-0.5561-3.7976-1.5658     c-2.0936-2.0941-2.0936-5.5011,0-7.5953L51.7151,4.5675c2.0936-2.0936,5.5007-2.0931,7.5953,0.0005     c1.0092,1.0078,1.5653,2.3558,1.5653,3.7957S60.3196,11.1526,59.3099,12.1623z"/></g></g></g><g id="ricebowl"/><g id="bowl"/><g id="knife"/><g id="oven"/><g id="frying_pan"/><g id="buthcer_knife"/><g id="soup_spoon"/><g id="mixer"/></svg> <?php esc_html_e( 'Recipe Equipment', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                        </h4>
                                                        <p class="about">

                                                            <a href="https://recipecard.io/pricing/?utm_source=wpadmin&utm_medium=about-rcb-page&utm_campaign=equipmentfeature" title="Unit Conversion" target="_blank"><img src="https://recipecard.io/wp-content/themes/wpzoom-rcb/images/recipe-block/equipment.png" alt="<?php echo esc_attr__( 'Equipment', 'recipe-card-blocks-by-wpzoom' ); ?>" /></a>

                                                            <br />
                                                            <br />
                                                            <?php esc_html_e( 'This feature enriches your recipes by allowing you to list and showcase the specific tools and equipment needed to prepare each dish.', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                        </p>

                                                        <p class="section_footer">

                                                           <a href="<?php echo esc_url( __( 'https://recipecard.io/features/recipe-equipment/', 'recipe-card-blocks-by-wpzoom' ) ); ?>" target="_blank" class="button button-primary">
                                                               <?php esc_html_e( 'Learn More &rarr;', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                           </a>

                                                        </p>
                                                    </div>

                                                    <div class="section">
                                                        <h4>
                                                            <svg style="enable-background:new 0 0 16 16;" fill="#e15819" version="1.1" width="26" viewBox="0 0 16 16" xml:space="preserve" xmlns="https://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="Guide"/><g id="Layer_2"><g><path d="M6,6c0-0.93-0.64-1.71-1.5-1.93V2.5C4.5,2.22,4.28,2,4,2S3.5,2.22,3.5,2.5v1.57C2.64,4.29,2,5.07,2,6s0.64,1.71,1.5,1.93    v5.57C3.5,13.78,3.72,14,4,14s0.5-0.22,0.5-0.5V7.93C5.36,7.71,6,6.93,6,6z M4,7C3.45,7,3,6.55,3,6s0.45-1,1-1s1,0.45,1,1    S4.55,7,4,7z"/><path d="M8.5,9.07V2.5C8.5,2.22,8.28,2,8,2S7.5,2.22,7.5,2.5v6.57C6.64,9.29,6,10.07,6,11s0.64,1.71,1.5,1.93v0.57    C7.5,13.78,7.72,14,8,14s0.5-0.22,0.5-0.5v-0.57c0.86-0.22,1.5-1,1.5-1.93S9.36,9.29,8.5,9.07z M8,12c-0.55,0-1-0.45-1-1    s0.45-1,1-1s1,0.45,1,1S8.55,12,8,12z"/><path d="M14,5c0-0.93-0.64-1.71-1.5-1.93V2.5C12.5,2.22,12.28,2,12,2s-0.5,0.22-0.5,0.5v0.57C10.64,3.29,10,4.07,10,5    s0.64,1.71,1.5,1.93v6.57c0,0.28,0.22,0.5,0.5,0.5s0.5-0.22,0.5-0.5V6.93C13.36,6.71,14,5.93,14,5z M12,6c-0.55,0-1-0.45-1-1    s0.45-1,1-1s1,0.45,1,1S12.55,6,12,6z"/></g></g></svg> <?php esc_html_e( '5 Recipe Card Styles', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                        </h4>
                                                        <p class="about">

                                                            <a href="https://recipecard.io/pricing/?utm_source=wpadmin&utm_medium=about-rcb-page&utm_campaign=unitconversionfeature" title="Unit Conversion" target="_blank"><img src="https://recipecard.io/wp-content/themes/wpzoom-rcb/images/recipe-block/styles.png" alt="<?php echo esc_attr__( 'REcipe styles', 'recipe-card-blocks-by-wpzoom' ); ?>" /></a>

                                                            <?php esc_html_e( 'Choose your favorite Recipe Card style! No more boring and outdated designs that can turn your readers away. Recipe Card Blocks includes 5 modern styles easily customized to match your branding.', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                        </p>

                                                        <p class="section_footer">

                                                            <a href="<?php echo esc_url( __( 'https://demo.recipecard.io/', 'recipe-card-blocks-by-wpzoom' ) ); ?>" target="_blank" class="button button-primary">
                                                                  <?php esc_html_e( 'View Demo &rarr;', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                              </a>


                                                        </p>
                                                    </div>

                                                </div><!-- /.wpz-grid-wrap -->

                                                <span class="many-more"><?php esc_html_e( 'And many other premium features...', 'recipe-card-blocks-by-wpzoom' ); ?></span>

                                                <a href="<?php echo esc_url( __( 'https://recipecard.io/pricing/?utm_source=wpadmin&utm_medium=about-rcb-page&utm_campaign=footerbtn', 'recipe-card-blocks-by-wpzoom' ) ); ?>" target="_blank" class="button button-large button-primary">
                                                    <?php esc_html_e( 'Get the PRO version today &rarr;', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                </a>


                                        </div><!-- /.theme-info-wrap -->


                                </div>

                                <div id="vs-pro" class="wpz-onboard_content-main-tab">

                                    <div class="theme-info-wrap">
                                        <h3 class="plugin-comparison-intro">
                                            <?php esc_html_e( 'Recipe Card Block Free vs. PRO', 'recipe-card-blocks-by-wpzoom' ); ?>
                                        </h3>
                                        <p>
                                            <?php esc_html_e( 'Take your recipes to the next level with Recipe Card Blocks PRO!', 'recipe-card-blocks-by-wpzoom' ); ?>
                                           </p>
                                        <p>
                                            <?php esc_html_e( 'Unlock premium features and extend the functionalities & look of your food blog.', 'recipe-card-blocks-by-wpzoom' ); ?>
                                        </p>

                                    <div class="theme-comparison">


                                                    <table class="plugin-comparison-table">
                                                        <thead class="theme-comparison-header">
                                                            <tr>
                                                                <th class="table-feature-title"><h3><?php esc_html_e( 'Feature', 'recipe-card-blocks-by-wpzoom' ); ?></h3></th>
                                                                <th><h3><?php esc_html_e( 'Free', 'recipe-card-blocks-by-wpzoom' ); ?></h3></th>
                                                                <th><h3><?php esc_html_e( 'PRO', 'recipe-card-blocks-by-wpzoom' ); ?></h3></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'Color Schemes', 'recipe-card-blocks-by-wpzoom' ); ?></h3></td>
                                                                <td><?php esc_html_e( '1', 'recipe-card-blocks-by-wpzoom' ); ?></td>
                                                                <td><?php esc_html_e( '4 + Unlimited Colors', 'recipe-card-blocks-by-wpzoom' ); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'Recipe Card Styles', 'recipe-card-blocks-by-wpzoom' ); ?> <span class="table-new-promo">POPULAR FEATURE</span></h3></td>
                                                                <td><?php esc_html_e( '3', 'recipe-card-blocks-by-wpzoom' ); ?></td>
                                                                <td><?php esc_html_e( '5', 'recipe-card-blocks-by-wpzoom' ); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'Schema Markup', 'recipe-card-blocks-by-wpzoom' ); ?></h3></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'Video Support', 'recipe-card-blocks-by-wpzoom' ); ?></h3></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'Bulk Add Ingredients & Directions', 'recipe-card-blocks-by-wpzoom' ); ?></h3></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'Inline Structured Data Validator', 'recipe-card-blocks-by-wpzoom' ); ?></h3></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'Elementor Support', 'recipe-card-blocks-by-wpzoom' ); ?></h3></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><strong><?php esc_html_e( 'Star Rating', 'recipe-card-blocks-by-wpzoom' ); ?></strong> <span class="table-new-promo">POPULAR FEATURE</span></h3></td>
                                                                <td><span class="dashicons dashicons-no"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'Cook Mode', 'recipe-card-blocks-by-wpzoom' ); ?></h3></td>
                                                                <td><span class="dashicons dashicons-no"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'Comments Rating', 'recipe-card-blocks-by-wpzoom' ); ?></h3></td>
                                                                <td><span class="dashicons dashicons-no"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'Social Call-to-actions', 'recipe-card-blocks-by-wpzoom' ); ?></h3></td>
                                                                <td><span class="dashicons dashicons-no"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><strong><?php esc_html_e( 'Adjustable Servings', 'recipe-card-blocks-by-wpzoom' ); ?></strong></h3></td>
                                                                <td><span class="dashicons dashicons-no"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'Food Labels', 'recipe-card-blocks-by-wpzoom' ); ?></h3></td>
                                                                <td><span class="dashicons dashicons-no"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'Image Gallery & Lightbox', 'recipe-card-blocks-by-wpzoom' ); ?></h3></td>
                                                                <td><span class="dashicons dashicons-no"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'Premium Support', 'recipe-card-blocks-by-wpzoom' ); ?></h3></td>
                                                                <td><span class="dashicons dashicons-no"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'Advanced Pinterest Settings', 'recipe-card-blocks-by-wpzoom' ); ?></h3></td>
                                                                <td><span class="dashicons dashicons-no"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'Nutrition Info', 'recipe-card-blocks-by-wpzoom' ); ?></h3></td>
                                                                <td><span class="dashicons dashicons-no"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><strong><?php esc_html_e( 'Unit Conversion', 'recipe-card-blocks-by-wpzoom' ); ?></strong> <span class="table-new-promo">PROFESSIONAL PLAN</span></h3></td>
                                                                <td><span class="dashicons dashicons-no"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'Equipment', 'recipe-card-blocks-by-wpzoom' ); ?></h3></td>
                                                                <td><span class="dashicons dashicons-no"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><strong><?php esc_html_e( 'Recipe Roundups', 'recipe-card-blocks-by-wpzoom' ); ?></strong> <span class="table-new-promo">POPULAR FEATURE</span></h3></td>
                                                                <td><span class="dashicons dashicons-no"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'Recipe Submissions', 'recipe-card-blocks-by-wpzoom' ); ?></h3></td>
                                                                <td><span class="dashicons dashicons-no"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><strong><?php esc_html_e( 'Recipe Index Block', 'recipe-card-blocks-by-wpzoom' ); ?></strong> <span class="table-new-promo">NEW FEATURE</span></h3></td>
                                                                <td><span class="dashicons dashicons-no"></span></td>
                                                                <td><span class="dashicons dashicons-yes"></span></td>
                                                            </tr>

                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'WooCommerce Integration', 'recipe-card-blocks-by-wpzoom' ); ?> <span class="table-new-promo">COMING SOON</span></h3></td>
                                                                <td><span class="dashicons dashicons-no"></span></td>
                                                                <td><em><?php esc_html_e( 'Coming Soon', 'recipe-card-blocks-by-wpzoom' ); ?></em></td>
                                                            </tr>
                                                            <tr>
                                                                <td class="table-index"><h3><?php esc_html_e( 'Support', 'recipe-card-blocks-by-wpzoom' ); ?></h3></td>
                                                                <td><?php esc_html_e( 'Community Forum', 'recipe-card-blocks-by-wpzoom' ); ?></td>
                                                                <td><?php esc_html_e( 'Email Support', 'recipe-card-blocks-by-wpzoom' ); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td></td>
                                                                <td></td>
                                                                <td>
                                                                    <a href="<?php echo WPZOOM_Plugin_Activator::get_upgrade_url(); ?>" target="_blank" class="button button-primary">
                                                                        <?php esc_html_e( 'Upgrade to PRO', 'recipe-card-blocks-by-wpzoom' ); ?>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>

                                            </div>

                                        </div>
                                </div>

                            </div>

                            <div class="wpz-onboard_content-side">

                                <div class="wpz-onboard_content-side-section discover-premium">
                                    <h3 class="wpz-onboard_content-side-section-title icon-docs">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="https://www.w3.org/2000/svg">
                                        <mask id="mask0_3409_3568" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="24" height="24">
                                        <rect width="24" height="24" fill="#D9D9D9"/>
                                        </mask>
                                        <g mask="url(#mask0_3409_3568)">
                                        <path d="M19 9L17.75 6.25L15 5L17.75 3.75L19 1L20.25 3.75L23 5L20.25 6.25L19 9ZM19 23L17.75 20.25L15 19L17.75 17.75L19 15L20.25 17.75L23 19L20.25 20.25L19 23ZM9 20L6.5 14.5L1 12L6.5 9.5L9 4L11.5 9.5L17 12L11.5 14.5L9 20ZM9 15.15L10 13L12.15 12L10 11L9 8.85L8 11L5.85 12L8 13L9 15.15Z" fill="white"/>
                                        </g>
                                        </svg> <?php esc_html_e( 'Recipe Card Blocks PRO', 'recipe-card-blocks-by-wpzoom' ); ?></h3>
                                    <p class="wpz-onboard_content-side-section-content"><?php esc_html_e( 'Unlock advanced customization options with the PRO version to make your recipe cards truly unique. Add videos, nutritional facts, and more to engage your readers like never before!', 'recipe-card-blocks-by-wpzoom' ); ?></p>

                                    <ul>
                                        <li><span class="dashicons dashicons-yes"></span> Adjustable Servings</li>
                                        <li><span class="dashicons dashicons-yes"></span> Unit Conversion</li>
                                        <li><span class="dashicons dashicons-yes"></span> Recipe Roundups</li>
                                        <li><span class="dashicons dashicons-yes"></span> Recipe Index Block</li>
                                        <li><span class="dashicons dashicons-yes"></span> Star Rating</li>
                                        <li><span class="dashicons dashicons-yes"></span> Equipment</li>
                                        <li><span class="dashicons dashicons-yes"></span> ...and many more</li>
                                    </ul>
                                    <div class="wpz-onboard_content-side-section-button">
                                        <a href="<?php echo esc_url( __( 'https://recipecard.io/features/?utm_source=wpadmin&utm_medium=about-rcb-page&utm_campaign=upgrade-premium', 'recipe-card-blocks-by-wpzoom' ) ); ?>" target="_blank" class="button button-primary"><?php esc_html_e( 'Discover All PRO Features &rarr;', 'recipe-card-blocks-by-wpzoom' ); ?></a>

                                    </div>

                                </div>

                                <?php
                                $current_user = wp_get_current_user();
                                ?>

                                <div id="mlb2-5662656" class="ml-form-embedContainer ml-subscribe-form ml-subscribe-form-5662656">
                                  <div class="ml-form-align-center">
                                    <div class="ml-form-embedWrapper embedForm">
                                      <div class="ml-form-embedHeader">
                                        <img src="https://bucket.mlcdn.com/a/3719/3719705/images/104575be896e33ca3f3a2b2f44b82f43eacd25d4.png" border="0" style="display:block">
                                        <style>
                                          @media only screen and (max-width:460px){.ml-form-embedHeader{display:none!important}}
                                        </style>
                                      </div>
                                      <div class="ml-form-embedBody ml-form-embedBodyDefault row-form">
                                        <div class="ml-form-embedContent" style="">
                                          <h4><?php _e('Subscribe to our Newsletter!', 'recipe-card-blocks-by-wpzoom'); ?></h4>
                                          <p><?php _e('Receive plugin updates and get for <strong>free</strong> our email course <strong>"How to start, maintain & optimize a food blog"</strong>.', 'recipe-card-blocks-by-wpzoom'); ?><br></p>
                                         </div>
                                        <form class="ml-block-form" action="https://static.mailerlite.com/webforms/submit/w5r9l0" data-code="w5r9l0" method="post" target="_blank">
                                          <div class="ml-form-formContent">
                                            <div class="ml-form-fieldRow ml-last-item">
                                              <div class="ml-field-group ml-field-email ml-validate-email ml-validate-required">
                                                <input aria-label="email" aria-required="true" type="email" value="<?php echo esc_attr($current_user->user_email); ?>" class="form-control" data-inputmask="" name="fields[email]" placeholder="Enter your email address" autocomplete="email">
                                              </div>
                                            </div>
                                          </div>
                                          <input type="hidden" name="ml-submit" value="1">
                                          <div class="ml-form-embedSubmit">
                                            <button type="submit" class="primary"><?php _e('Subscribe', 'recipe-card-blocks-by-wpzoom'); ?></button>
                                            <button disabled="disabled" style="display:none" type="button" class="loading"> <div class="ml-form-embedSubmitLoad"></div> <span class="sr-only">Loading...</span> </button>
                                          </div>
                                          <input type="hidden" name="anticsrf" value="true">
                                        </form>
                                      </div>
                                      <div class="ml-form-successBody row-success" style="display:none">
                                        <div class="ml-form-successContent">
                                          <h4><?php _e('Thank you!', 'recipe-card-blocks-by-wpzoom'); ?></h4>
                                          <p><?php _e('You have successfully joined our subscriber list.', 'recipe-card-blocks-by-wpzoom'); ?></p>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <script>
                                  function ml_webform_success_5662656(){var r=ml_jQuery||jQuery;r(".ml-subscribe-form-5662656 .row-success").show(),r(".ml-subscribe-form-5662656 .row-form").hide()}
                                </script>
                                <img src="https://track.mailerlite.com/webforms/o/5662656/w5r9l0?v1651224972" width="1" height="1" style="max-width:1px;max-height:1px;visibility:hidden;padding:0;margin:0;display:block" alt="." border="0">
                                <script src="https://static.mailerlite.com/js/w/webforms.min.js?v9b62042f798751c8de86a784eab23614" type="text/javascript"></script>

                                <div class="wpz-onboard_content-side-section">
                                    <h3 class="wpz-onboard_content-side-section-title icon-docs">
                                        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="https://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.96074 2H19.9019C20.9965 2 21.8921 2.9 21.8921 4V16C21.8921 17.1 20.9965 18 19.9019 18H7.96074C6.86614 18 5.97055 17.1 5.97055 16V4C5.97055 2.9 6.86614 2 7.96074 2ZM1.99017 6H3.98036V20H17.9117V22H3.98036C2.88576 22 1.99017 21.1 1.99017 20V6ZM19.9019 16H7.96075V4H19.9019V16ZM17.9117 9H9.95093V11H17.9117V9ZM9.95093 12H13.9313V14H9.95093V12ZM17.9117 6H9.95093V8H17.9117V6Z"></path>
                                        </svg> <?php esc_html_e( 'Need help?', 'recipe-card-blocks-by-wpzoom' ); ?></h3>
                                    <p class="wpz-onboard_content-side-section-content"><?php esc_html_e( 'Documentation is the place where you’ll find the information needed to setup the plugin quickly, and other details about plugin-specific features. You can also get in touch with our team by contacting us through our website.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
                                    <div class="wpz-onboard_content-side-section-button">
                                        <a href="https://recipecard.io/documentation/" title="Read documentation" target="_blank" class="button"><?php esc_html_e( 'Documentation', 'recipe-card-blocks-by-wpzoom' ); ?></a> <a href="https://recipecard.io/support/tickets/" title="Open Support Desk" target="_blank" class="button"><?php esc_html_e( 'Support Desk', 'recipe-card-blocks-by-wpzoom' ); ?></a>

                                    </div>

                                </div>

                                <div class="wpz-onboard_content-side-section">
                                    <h3 class="wpz-onboard_content-side-section-title icon-assist">
                                        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="https://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M15.9216 2H2.98533C2.43803 2 1.99023 2.45 1.99023 3V17L5.97062 13H15.9216C16.4689 13 16.9167 12.55 16.9167 12V3C16.9167 2.45 16.4689 2 15.9216 2ZM14.9265 4V11H5.14473L3.98047 12.17V4H14.9265ZM18.9068 6H20.897C21.4443 6 21.8921 6.45 21.8921 7V22L17.9117 18H6.96568C6.41837 18 5.97058 17.55 5.97058 17V15H18.9068V6Z"></path>
                                        </svg> <?php esc_html_e( 'Walkthrough Video', 'recipe-card-blocks-by-wpzoom' ); ?></h3>
                                    <p class="wpz-onboard_content-side-section-content"><?php esc_html_e( 'Below you can find a quick video tutorial that will guide you through configuring basic things in the plugin after installing it.', 'recipe-card-blocks-by-wpzoom' ); ?></p>

                                    <iframe width="800" height="464" src="https://www.youtube.com/embed/eQK48J4BK0A" title="How To Add a Recipe Posts on WordPress for SEO A Step-by-Step Guide" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>


                                </div>


                                <div class="wpz-onboard_content-side-section">
                                    <h3 class="wpz-onboard_content-side-section-title icon-follow">
                                        <svg width="24" height="24" viewBox="0 0 24 24" xmlns="https://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M20.8971 9H14.618L15.5633 4.43L15.5932 4.11C15.5932 3.7 15.424 3.32 15.1553 3.05L14.1005 2L7.55281 8.59C7.18462 8.95 6.9657 9.45 6.9657 10V20C6.9657 21.1 7.86129 22 8.95589 22H17.9118C18.7377 22 19.4442 21.5 19.7427 20.78L22.7479 13.73C22.8375 13.5 22.8872 13.26 22.8872 13V11C22.8872 9.9 21.9917 9 20.8971 9ZM20.897 13L17.9117 20H8.95587V10L13.2746 5.66003L12.17 11H20.897V13ZM4.9755 10H0.995117V22H4.9755V10Z"></path>
                                        </svg> Follow Us
                                    </h3>
                                    <p class="wpz-onboard_content-side-section-content">Follow us on social media for plugin updates and SEO tips!</p>
                                    <div class="wpz-onboard_content-side-section-button">
                                        <a href="https://twitter.com/recipeblock" target="_blank" title="Twitter" class="button button-smaller button-rounded"><svg  width="18" height="18" role="img" fill="#fff" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><title>X</title><path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z"/></svg></a>
                                        <a href="https://facebook.com/recipeblock" target="_blank" title="Facebook" class="button button-smaller button-rounded"><svg width="18" height="18" fill="#fff" role="img" viewBox="0 0 24 24" xmlns="https://www.w3.org/2000/svg"><title>Facebook</title><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"></path></svg> <span class="icon-text">Facebook</span></a>
                                        <a href="https://www.facebook.com/groups/recipeblock" target="_blank" title="Facebook" class="button button-smaller button-rounded"><svg width="18" height="18" fill="#fff" role="img" viewBox="0 0 24 24" xmlns="https://www.w3.org/2000/svg"><title>Facebook</title><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"></path></svg> <span class="icon-text">Facebook</span></a>
                                        <a href="https://instagram.com/recipecardblocks" target="_blank" title="Instagram" class="button button-smaller button-rounded"><span class="dashicons dashicons-instagram"></span> <span class="icon-text">Instagram</span></a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div> <!-- /#tabs -->

                <div class="wpz-onboard_footer">
                    <h3 class="wpz-onboard_footer-logo"><a href="https://wpzoom.com/" title="WPZOOM">WPZOOM</a></h3>

                    <ul class="wpz-onboard_footer-links">
                        <li class="wpzoom-new-admin_settings-footer-links-themes"><a href="https://www.wpzoom.com/themes/" target="_blank" title="<?php _e( 'Check out our WordPress themes', 'recipe-card-blocks-by-wpzoom' ); ?>"><?php _e( 'Our Themes', 'recipe-card-blocks-by-wpzoom' ); ?></a></li>
                        <li class="wpzoom-new-admin_settings-footer-links-plugins"><a href="https://www.wpzoom.com/plugins/" target="_blank" title="<?php _e( 'Check out our other WordPress plugins', 'recipe-card-blocks-by-wpzoom' ); ?>"><?php _e( 'Our Plugins', 'recipe-card-blocks-by-wpzoom' ); ?></a></li>
                        <li class="wpzoom-new-admin_settings-footer-links-blog"><a href="https://recipecard.io/blog/" target="_blank" title="<?php _e( 'See the latest updates on our blog', 'recipe-card-blocks-by-wpzoom' ); ?>"><?php _e( 'Blog', 'recipe-card-blocks-by-wpzoom' ); ?></a></li>
                        <li class="wpzoom-new-admin_settings-footer-links-support"><a href="https://recipecard.io/support/" target="_blank" title="<?php _e( 'Get support', 'recipe-card-blocks-by-wpzoom' ); ?>"><?php _e( 'Support', 'recipe-card-blocks-by-wpzoom' ); ?></a></li>
                    </ul>
                </div>
            </div>


		<?php
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @param string $hook
	 */
	public function scripts( $hook ) {
		$pos = strpos( $hook, 'wpzoom-recipe-card-vs-pro' );

		if ( $pos === false ) {
			return;
		}

        wp_enqueue_script("jquery-ui");
        wp_enqueue_script("jquery-ui-tabs");

		wp_enqueue_style(
			'wpzoom-rcb-admin-style',
			untrailingslashit( WPZOOM_RCB_PLUGIN_URL ) . '/dist/assets/admin/css/style.css',
			array(),
			WPZOOM_RCB_VERSION
		);
	}
}

new WPZOOM_Lite_vs_PRO();
