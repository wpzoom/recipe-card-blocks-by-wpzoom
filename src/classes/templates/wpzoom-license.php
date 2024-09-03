<?php
wp_enqueue_script( 'wpzoom-rcb-user-script' );
wp_enqueue_style( 'wpzoom-rcb-user-styles' );

$site_url   = home_url();
$parsed_url = parse_url( $site_url );

?>

<div class="wpz-onboard_wrapper">
	<div class="wpz-onboard_header">
		<div class="wpz-onboard_title-wrapper">
			<h1 class="wpz-onboard_title"><svg width="30" height="30" viewBox="0 0 500 500" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_408_204)"><circle cx="250" cy="250" r="250" fill="#E1581A"/><path fill-rule="evenodd" clip-rule="evenodd" d="M252.597 100C221.304 100 193.611 117.861 178.955 146.232C140.058 146.561 110 180.667 110 221.822C110 251.163 125.719 277.528 149.758 289.881V381.396C149.758 390.834 156.879 399 166.3 399H333.775C343.196 399 350.317 390.834 350.317 381.396V267.062C374.304 254.662 390 228.316 390 199.037C390 157.678 358.917 123.483 320.067 123.483C316.597 123.483 313.132 123.77 309.694 124.334C294.308 108.794 274.007 100 252.597 100ZM204.614 170.955C212.223 149.098 231.113 135.208 252.597 135.208C267.726 135.208 281.464 142.141 291.342 154.94C295.598 160.449 302.673 162.77 309.186 160.524C312.757 159.302 316.424 158.691 320.067 158.691C340.096 158.691 356.916 176.495 356.916 199.037C356.916 217.48 345.452 233.354 329.465 237.933C322.085 240.053 317.233 247.147 317.233 254.931V302.003H182.842V277.716C182.842 269.917 177.976 262.803 170.558 260.702C154.57 256.173 143.084 240.307 143.084 221.822C143.084 199.629 160.141 181.436 179.524 181.436C181.644 181.436 183.782 181.663 185.883 182.105L185.89 182.107C194.008 183.801 201.858 178.878 204.614 170.955ZM182.842 363.792V337.211H317.233V363.792H182.842Z" fill="white"/></g><defs><clipPath id="clip0_408_204"><rect width="500" height="500" fill="white"/></clipPath></defs></svg> Recipe Card Blocks <span>Free</span></h1>
			<h2 class="wpz-onboard_framework-version">
				<?php printf( esc_html__( 'v. %s', 'recipe-card-blocks-by-wpzoom' ), WPZOOM_RCB_VERSION ); ?>
			</h2>
		</div>
		<div class="wpz-onboard_quick_links">                    
			<ul class="wpz-onboard_tabs">
			<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpzoom-recipe-card-settings') ); ?>"><svg width="18" height="18" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
	<path opacity="0.3" d="M28.9201 12.9L27.8701 11.085L25.9651 11.85L24.3751 12.495L23.0101 11.445C22.4251 10.995 21.8101 10.635 21.1651 10.38L19.5751 9.735L19.3351 8.04L19.0501 6H16.9501L16.6651 8.025L16.4251 9.72L14.8351 10.38C14.2201 10.635 13.6051 10.995 12.9601 11.475L11.6101 12.495L10.0351 11.865L8.13008 11.085L7.08008 12.9L8.70008 14.16L10.0351 15.21L9.82508 16.905C9.78008 17.355 9.75008 17.7 9.75008 18C9.75008 18.3 9.78008 18.645 9.82508 19.095L10.0351 20.79L8.70008 21.84L7.08008 23.1L8.13008 24.915L10.0351 24.15L11.6251 23.505L12.9901 24.555C13.5751 25.005 14.1901 25.365 14.8351 25.62L16.4251 26.265L16.6651 27.96L16.9501 30H19.0351L19.3201 27.975L19.5601 26.28L21.1501 25.635C21.7651 25.38 22.3801 25.02 23.0251 24.54L24.3751 23.52L25.9351 24.15L27.8401 24.915L28.8901 23.1L27.2701 21.84L25.9351 20.79L26.1451 19.095C26.2051 18.63 26.2201 18.315 26.2201 18C26.2201 17.685 26.1901 17.355 26.1451 16.905L25.9351 15.21L27.2701 14.16L28.9201 12.9ZM18.0001 24C14.6851 24 12.0001 21.315 12.0001 18C12.0001 14.685 14.6851 12 18.0001 12C21.3151 12 24.0001 14.685 24.0001 18C24.0001 21.315 21.3151 24 18.0001 24Z" fill="black" />
	<path d="M29.1452 19.47C29.2052 18.99 29.2502 18.51 29.2502 18C29.2502 17.49 29.2052 17.01 29.1452 16.53L32.3102 14.055C32.5952 13.83 32.6702 13.425 32.4902 13.095L29.4902 7.905C29.3552 7.665 29.1002 7.53 28.8302 7.53C28.7402 7.53 28.6502 7.545 28.5752 7.575L24.8402 9.075C24.0602 8.475 23.2202 7.98 22.3052 7.605L21.7352 3.63C21.6902 3.27 21.3752 3 21.0002 3H15.0002C14.6252 3 14.3102 3.27 14.2652 3.63L13.6952 7.605C12.7802 7.98 11.9402 8.49 11.1602 9.075L7.42525 7.575C7.33525 7.545 7.24525 7.53 7.15525 7.53C6.90025 7.53 6.64525 7.665 6.51025 7.905L3.51025 13.095C3.31525 13.425 3.40525 13.83 3.69025 14.055L6.85525 16.53C6.79525 17.01 6.75025 17.505 6.75025 18C6.75025 18.495 6.79525 18.99 6.85525 19.47L3.69025 21.945C3.40525 22.17 3.33025 22.575 3.51025 22.905L6.51025 28.095C6.64525 28.335 6.90025 28.47 7.17025 28.47C7.26025 28.47 7.35025 28.455 7.42525 28.425L11.1602 26.925C11.9402 27.525 12.7802 28.02 13.6952 28.395L14.2652 32.37C14.3102 32.73 14.6252 33 15.0002 33H21.0002C21.3752 33 21.6902 32.73 21.7352 32.37L22.3052 28.395C23.2202 28.02 24.0602 27.51 24.8402 26.925L28.5752 28.425C28.6652 28.455 28.7552 28.47 28.8452 28.47C29.1002 28.47 29.3552 28.335 29.4902 28.095L32.4902 22.905C32.6702 22.575 32.5952 22.17 32.3102 21.945L29.1452 19.47ZM26.1752 16.905C26.2352 17.37 26.2502 17.685 26.2502 18C26.2502 18.315 26.2202 18.645 26.1752 19.095L25.9652 20.79L27.3002 21.84L28.9202 23.1L27.8702 24.915L25.9652 24.15L24.4052 23.52L23.0552 24.54C22.4102 25.02 21.7952 25.38 21.1802 25.635L19.5902 26.28L19.3502 27.975L19.0502 30H16.9502L16.6652 27.975L16.4252 26.28L14.8352 25.635C14.1902 25.365 13.5902 25.02 12.9902 24.57L11.6252 23.52L10.0352 24.165L8.13025 24.93L7.08025 23.115L8.70025 21.855L10.0352 20.805L9.82525 19.11C9.78025 18.645 9.75025 18.3 9.75025 18C9.75025 17.7 9.78025 17.355 9.82525 16.905L10.0352 15.21L8.70025 14.16L7.08025 12.9L8.13025 11.085L10.0352 11.85L11.5952 12.48L12.9452 11.46C13.5902 10.98 14.2052 10.62 14.8202 10.365L16.4102 9.72L16.6502 8.025L16.9502 6H19.0352L19.3202 8.025L19.5602 9.72L21.1502 10.365C21.7952 10.635 22.3952 10.98 22.9952 11.43L24.3602 12.48L25.9502 11.835L27.8552 11.07L28.9052 12.885L27.3002 14.16L25.9652 15.21L26.1752 16.905ZM18.0002 12C14.6852 12 12.0002 14.685 12.0002 18C12.0002 21.315 14.6852 24 18.0002 24C21.3152 24 24.0002 21.315 24.0002 18C24.0002 14.685 21.3152 12 18.0002 12ZM18.0002 21C16.3502 21 15.0002 19.65 15.0002 18C15.0002 16.35 16.3502 15 18.0002 15C19.6502 15 21.0002 16.35 21.0002 18C21.0002 19.65 19.6502 21 18.0002 21Z" fill="black" />
	</svg><?php  esc_html_e( 'Settings', 'recipe-card-blocks-by-wpzoom' ); ?></a></li>
			<li class="ui-tabs-active"><a href="<?php echo esc_url( admin_url( 'admin.php?page=admin-license') ); ?>"><svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M13.33 13.9924C17.0427 13.82 19.9999 10.7553 19.9999 7C19.9999 3.13401 16.8659 0 12.9999 0C10.2045 0 7.79182 1.63858 6.66992 4.00764C6.69726 4.00637 6.72464 4.00526 6.75206 4.00431C3.0009 4.13489 0 7.217 0 11C0 14.866 3.13401 18 7 18C9.7954 18 12.2081 16.3614 13.33 13.9924ZM13.9412 11.9115C16.2526 11.4712 17.9999 9.43966 17.9999 7C17.9999 4.23858 15.7613 2 12.9999 2C11.2581 2 9.7243 2.89066 8.82912 4.24139C11.808 5.04564 14 7.76684 14 11C14 11.3089 13.98 11.6132 13.9412 11.9115ZM7.11834 4.00098C7.07895 4.00033 7.03947 4 6.99991 4L7.11834 4.00098ZM6.50708 11.6027L6.88329 10.5131C6.94745 10.3275 7.21926 10.3275 7.28342 10.5131L7.65963 11.6027C7.74243 11.8422 7.88195 12.0598 8.06712 12.2383C8.25229 12.4168 8.47803 12.5512 8.72643 12.6309L9.85564 12.9937C10.0481 13.0556 10.0481 13.3177 9.85564 13.3796L8.72584 13.7424C8.47749 13.8223 8.25183 13.9568 8.06676 14.1354C7.88169 14.314 7.7423 14.5317 7.65963 14.7713L7.28342 15.8603C7.26958 15.9009 7.24279 15.9363 7.20687 15.9614C7.17094 15.9865 7.12771 16 7.08335 16C7.03899 16 6.99577 15.9865 6.95984 15.9614C6.92392 15.9363 6.89713 15.9009 6.88329 15.8603L6.50708 14.7707C6.42433 14.5312 6.2849 14.3137 6.09984 14.1352C5.91477 13.9567 5.68916 13.8222 5.44086 13.7424L4.31107 13.3796C4.26892 13.3663 4.23222 13.3404 4.2062 13.3058C4.18018 13.2712 4.16617 13.2295 4.16617 13.1867C4.16617 13.1439 4.18018 13.1022 4.2062 13.0676C4.23222 13.0329 4.26892 13.0071 4.31107 12.9937L5.44086 12.6309C5.68916 12.5511 5.91477 12.4167 6.09984 12.2382C6.2849 12.0597 6.42433 11.8421 6.50708 11.6027ZM4.63012 7.64491C4.63848 7.62055 4.65459 7.59935 4.67616 7.58433C4.69773 7.5693 4.72366 7.56122 4.75027 7.56122C4.77688 7.56122 4.80281 7.5693 4.82438 7.58433C4.84595 7.59935 4.86206 7.62055 4.87042 7.64491L5.09615 8.29854C5.19706 8.58991 5.43386 8.81829 5.736 8.9156L6.41376 9.13329C6.43902 9.14136 6.461 9.1569 6.47658 9.1777C6.49216 9.1985 6.50054 9.22351 6.50054 9.24917C6.50054 9.27483 6.49216 9.29984 6.47658 9.32064C6.461 9.34144 6.43902 9.35698 6.41376 9.36504L5.736 9.58273C5.5869 9.63045 5.45141 9.71109 5.34033 9.81822C5.22925 9.92534 5.14563 10.056 5.09615 10.1998L4.87042 10.8534C4.86206 10.8778 4.84595 10.899 4.82438 10.914C4.80281 10.929 4.77688 10.9371 4.75027 10.9371C4.72366 10.9371 4.69773 10.929 4.67616 10.914C4.65459 10.899 4.63848 10.8778 4.63012 10.8534L4.40439 10.1998C4.35491 10.056 4.2713 9.92534 4.16021 9.81822C4.04913 9.71109 3.91364 9.63045 3.76454 9.58273L3.08678 9.36504C3.06152 9.35698 3.03954 9.34144 3.02396 9.32064C3.00839 9.29984 3 9.27483 3 9.24917C3 9.22351 3.00839 9.1985 3.02396 9.1777C3.03954 9.1569 3.06152 9.14136 3.08678 9.13329L3.76454 8.9156C3.91364 8.86788 4.04913 8.78725 4.16021 8.68012C4.2713 8.57299 4.35491 8.44233 4.40439 8.29854L4.63012 7.64491ZM8.75326 7.05485C8.75902 7.03883 8.76981 7.02494 8.78413 7.01511C8.79845 7.00528 8.81559 7 8.83317 7C8.85074 7 8.86788 7.00528 8.88221 7.01511C8.89653 7.02494 8.90732 7.03883 8.91307 7.05485L9.06356 7.49022C9.13063 7.68485 9.2887 7.83729 9.49051 7.90197L9.94196 8.0471C9.95857 8.05265 9.97297 8.06306 9.98317 8.07687C9.99336 8.09068 9.99883 8.10721 9.99883 8.12416C9.99883 8.14111 9.99336 8.15764 9.98317 8.17146C9.97297 8.18527 9.95857 8.19567 9.94196 8.20123L9.49051 8.34635C9.39115 8.37841 9.30086 8.43229 9.22677 8.50375C9.15268 8.5752 9.0968 8.66227 9.06356 8.7581L8.91307 9.19348C8.90732 9.2095 8.89653 9.22338 8.88221 9.23321C8.86788 9.24304 8.85074 9.24833 8.83317 9.24833C8.81559 9.24833 8.79845 9.24304 8.78413 9.23321C8.76981 9.22338 8.75902 9.2095 8.75326 9.19348L8.60277 8.7581C8.56953 8.66227 8.51366 8.5752 8.43957 8.50375C8.36547 8.43229 8.27519 8.37841 8.17582 8.34635L7.72495 8.20123C7.70834 8.19567 7.69394 8.18527 7.68375 8.17146C7.67356 8.15764 7.66808 8.14111 7.66808 8.12416C7.66808 8.10721 7.67356 8.09068 7.68375 8.07687C7.69394 8.06306 7.70834 8.05265 7.72495 8.0471L8.1764 7.90197C8.37821 7.83729 8.53628 7.68485 8.60336 7.49022L8.75326 7.05485Z" fill="#242628"/>
</svg> <?php  esc_html_e( 'AI Credits', 'recipe-card-blocks-by-wpzoom' ); ?></a></li>
			<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpzoom-recipe-card-vs-pro' ) ) ?>" title="Quick Start"><svg width="18" height="18" viewBox="0 0 13 15" fill="none" xmlns="https://www.w3.org/2000/svg"><path d="M0.166992 14.5V0.333332H7.66699L8.00033 2H12.667V10.3333H6.83366L6.50033 8.66667H1.83366V14.5H0.166992ZM8.20866 8.66667H11.0003V3.66667H6.62533L6.29199 2H1.83366V7H7.87533L8.20866 8.66667Z" fill="#000"></path></svg> <?php esc_html_e( 'PRO Features', 'recipe-card-blocks-by-wpzoom' ); ?></a></li>
			<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpzoom-recipe-card-vs-pro#vs-pro' ) ) ?>" title="Free vs. PRO"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="https://www.w3.org/2000/svg"><path clip-rule="evenodd" d="M15 5.75C11.5482 5.75 8.75 8.54822 8.75 12C8.75 15.4518 11.5482 18.25 15 18.25C15.9599 18.25 16.8674 18.0341 17.6782 17.6489C18.0523 17.4712 18.4997 17.6304 18.6774 18.0045C18.8552 18.3787 18.696 18.8261 18.3218 19.0038C17.3141 19.4825 16.1873 19.75 15 19.75C10.7198 19.75 7.25 16.2802 7.25 12C7.25 7.71979 10.7198 4.25 15 4.25C19.2802 4.25 22.75 7.71979 22.75 12C22.75 12.7682 22.638 13.5115 22.429 14.2139C22.3108 14.6109 21.8932 14.837 21.4962 14.7188C21.0992 14.6007 20.8731 14.1831 20.9913 13.7861C21.1594 13.221 21.25 12.6218 21.25 12C21.25 8.54822 18.4518 5.75 15 5.75Z" fill="black" fill-rule="evenodd"/><path clip-rule="evenodd" d="M5.25 5C5.25 4.58579 5.58579 4.25 6 4.25H15C15.4142 4.25 15.75 4.58579 15.75 5C15.75 5.41421 15.4142 5.75 15 5.75H6C5.58579 5.75 5.25 5.41421 5.25 5Z" fill="black" fill-rule="evenodd"/><path clip-rule="evenodd" d="M4.75 8.5C4.75 8.08579 5.08579 7.75 5.5 7.75H8.5C8.91421 7.75 9.25 8.08579 9.25 8.5C9.25 8.91421 8.91421 9.25 8.5 9.25H5.5C5.08579 9.25 4.75 8.91421 4.75 8.5Z" fill="black" fill-rule="evenodd"/><path clip-rule="evenodd" d="M1.25 8.5C1.25 8.08579 1.58579 7.75 2 7.75H3.5C3.91421 7.75 4.25 8.08579 4.25 8.5C4.25 8.91421 3.91421 9.25 3.5 9.25H2C1.58579 9.25 1.25 8.91421 1.25 8.5Z" fill="black" fill-rule="evenodd"/><path clip-rule="evenodd" d="M3.25 12.5C3.25 12.0858 3.58579 11.75 4 11.75H8C8.41421 11.75 8.75 12.0858 8.75 12.5C8.75 12.9142 8.41421 13.25 8 13.25H4C3.58579 13.25 3.25 12.9142 3.25 12.5Z" fill="black" fill-rule="evenodd"/><path clip-rule="evenodd" d="M12.376 8.58397C12.5151 8.37533 12.7492 8.25 13 8.25H17C17.2508 8.25 17.4849 8.37533 17.624 8.58397L19.624 11.584C19.792 11.8359 19.792 12.1641 19.624 12.416L17.624 15.416C17.4849 15.6247 17.2508 15.75 17 15.75H13C12.7492 15.75 12.5151 15.6247 12.376 15.416L10.376 12.416C10.208 12.1641 10.208 11.8359 10.376 11.584L12.376 8.58397ZM13.4014 9.75L11.9014 12L13.4014 14.25H16.5986L18.0986 12L16.5986 9.75H13.4014Z" fill="black" fill-rule="evenodd"/></svg> <?php esc_html_e( 'Free vs. PRO', 'recipe-card-blocks-by-wpzoom' ); ?></a></li>
			</ul>
		</div>
	</div>
</div>

<div id="request-processing" class="hidden"></div>
<?php
$user_data = get_transient( 'wpzoom_rcb_plugin_user_data' );

if ( ! $user_data ) {
	?>
	<div class="vcard">
		<div class="vcard-top">
			<img src="<?php echo plugin_dir_url( __FILE__ ); ?>/assets/img/active.svg" alt="">
			<h2><?php esc_html_e( 'Connect your account', 'recipe-card-blocks-by-wpzoom' ); ?></h2>
		</div>
		<div class="vcard-body">
			<p><?php esc_html_e( 'Connect to recipecard.io to get your status and the amount of credits', 'recipe-card-blocks-by-wpzoom' ); ?></p>
			<a href="#" class="vsign"><?php esc_html_e( '', 'recipe-card-blocks-by-wpzoom' ); ?>Connect</a>
		</div>
	</div>
	<div class="vpopup-fixed vsign hidden">
		<div class="vpopup-container">
			<div class="vpopup-content">
				<div class="vpop-head">
					<h2><?php esc_html_e( 'Sign In', 'recipe-card-blocks-by-wpzoom' ); ?></h2>
					<div class="vclose-button">
						<img src="<?php echo plugin_dir_url( __FILE__ ); ?>/assets/img/close.svg" alt="">
					</div>
				</div>
				<div class="vpop-body">
					<div class="vpop-prag">
						<p>
							<?php 
								printf(
									/* translators: %s: RCB store URL */
									esc_html__( 'Enter your login details from %s below.' , 'recipe-card-blocks-by-wpzoom' ),
									'<a href="' . esc_url( WPZOOM_RCB_STORE_URL ) . '" target="_blank">recipecard.io</a>'
								);
							?>
						</p>
                        <p>
                            <?php
                                printf(
                                    /* translators: %s: RCB store URL */
                                    esc_html__( 'No account? %s' , 'recipe-card-blocks-by-wpzoom' ),
                                    '<a href="' . esc_url( WPZOOM_RCB_STORE_URL ) . 'register/" target="_blank">Sign up today!</a>'
                                );
                            ?>
                        </p>
					</div>
					<form id="rcb-pro-login-user">
						<div class="vinput-group">
							<label><?php esc_html_e( 'Email or username', 'recipe-card-blocks-by-wpzoom' ); ?></label>
							<input type="text" name="user_login" value="">
						</div>
						<div class="vinput-group">
							<label><?php esc_html_e( 'Password', 'recipe-card-blocks-by-wpzoom' ); ?></label>
							<input type="password" name="password" value="">
						</div>
						<div class="vinput-group">
							<a href="<?php echo esc_url( WPZOOM_RCB_STORE_URL ) ?>wp-login.php?action=lostpassword" target="_blank"><?php esc_html_e( 'Forgot password?', 'recipe-card-blocks-by-wpzoom' ); ?></a>
						</div>
						<div class="vinput-group">
							<input type="submit" name="submit" value="<?php esc_html_e( 'Sign In', 'recipe-card-blocks-by-wpzoom' ); ?>">
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<?php
} else {	
	$customer_email    = $user_data->user->email;
	$credits           = get_option( 'wpzoom_credits' );
	$free_credits      = $credits['free_credits'] ?? '';
	$total_credits     = $credits['total'] ?? '';
	$remaining_credits = $credits['remaining'] ?? '';
	?>
	<div class="vcard">
		<div class="vcard-top">
			<img src="<?php echo plugin_dir_url( __FILE__ ); ?>/assets/img/active.svg">
			<h2><?php esc_html_e( 'Status: ', 'recipe-card-blocks-by-wpzoom' ); ?>Active</h2>
		</div>
		<div class="vcard-body">
			<div class="vuser">
				<div class="grey"><?php esc_html_e( 'Connected to:', 'recipe-card-blocks-by-wpzoom' ); ?></div>
				<div class="black"><?php echo esc_html( $customer_email ); ?></div>
			</div>
			<div class="vconnect">
				<a class="connect" href="<?php echo esc_url( WPZOOM_RCB_STORE_URL ) ?>account/" target="_blank"><?php esc_html_e( 'My account', 'recipe-card-blocks-by-wpzoom' ); ?></a>
				<a class="dconnect" href="#"><?php esc_html_e( 'Log out', 'recipe-card-blocks-by-wpzoom' ); ?></a>
			</div>
		</div>
	</div>
	<div class="vcard">
		<div class="vcard-top">
			<img src="<?php echo plugin_dir_url( __FILE__ ); ?>/assets/img/credit.svg">
			<h2><?php esc_html_e( 'AI Credits', 'recipe-card-blocks-by-wpzoom' ); ?></h2>
		</div>
		<div class="vcard-body">
			<p><?php esc_html_e( 'Generating a recipe costs 1 AI Credit. Buy an AI credit plan from our website that best fits your needs.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
			<div class="vscore">
				<?php
				if ( ! empty( $free_credits ) ) {
					?>
					<span class="grey"><?php esc_html_e( 'Free credits:', 'recipe-card-blocks-by-wpzoom' ); ?></span>
					<span class="vsuper"><?php echo esc_html( $free_credits ); ?></span>
					<?php
				}
				if ( ! empty( $remaining_credits ) ) {
					?>
					<span class="vsuper"><?php echo esc_html( $remaining_credits ); ?></span>
					<?php
				}
				if ( ! empty( $total_credits ) ) {
					?>
					<span class="grey">/ <?php echo esc_html( $total_credits ); ?> <?php esc_html_e( 'remaining', 'recipe-card-blocks-by-wpzoom' ); ?></span>
					<?php
				}
				if ( empty( $total_credits ) && empty( $remaining_credits ) && empty( $free_credits ) ) {
					?>
					<span class="vsuper">0</span>
					<span class="grey">/ 0 <?php esc_html_e( 'remaining', 'recipe-card-blocks-by-wpzoom' ); ?></span>
					<?php
				}
				?>
			</div>
			<a href="<?php echo esc_url( WPZOOM_RCB_STORE_URL ) ?>account/ai-credits/" target="_blank"><?php esc_html_e( 'Buy AI credits', 'recipe-card-blocks-by-wpzoom' ); ?></a>
		</div>
	</div>
	<?php
}