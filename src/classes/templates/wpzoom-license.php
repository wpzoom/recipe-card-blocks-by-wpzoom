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
			<li class="ui-tabs-active"><a href="<?php echo esc_url( admin_url( 'admin.php?page=admin-license') ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
	<path opacity="0.3" fillRule="evenodd" clipRule="evenodd" d="M11.2116 12.5764C13.856 12.5764 15.9998 10.4326 15.9998 7.7882C15.9998 5.14375 13.856 3 11.2116 3C9.35072 3 7.73778 4.06155 6.94531 5.61211C9.54951 5.65863 11.6463 7.78421 11.6463 10.3995C11.6463 11.1831 11.4581 11.9227 11.1244 12.5756C11.1534 12.5761 11.1825 12.5764 11.2116 12.5764Z" fill="#2EA55F" />
	<path fillRule="evenodd" clipRule="evenodd" d="M11.4975 12.7443C14.282 12.615 16.4999 10.3165 16.4999 7.5C16.4999 4.60051 14.1494 2.25 11.2499 2.25C9.15338 2.25 7.34386 3.47893 6.50244 5.25573C6.52295 5.25478 6.54348 5.25394 6.56405 5.25323C3.75067 5.35117 1.5 7.66275 1.5 10.5C1.5 13.3995 3.85051 15.75 6.75 15.75C8.84655 15.75 10.6561 14.5211 11.4975 12.7443ZM11.9559 11.1837C13.6895 10.8534 14.9999 9.32975 14.9999 7.5C14.9999 5.42893 13.321 3.75 11.2499 3.75C9.94357 3.75 8.79323 4.41799 8.12184 5.43105C10.356 6.03423 12 8.07513 12 10.5C12 10.7317 11.985 10.9599 11.9559 11.1837ZM6.83876 5.25074C6.80921 5.25025 6.7796 5.25 6.74994 5.25L6.83876 5.25074ZM6.38031 10.952L6.66247 10.1348C6.71059 9.99561 6.91444 9.99561 6.96256 10.1348L7.24472 10.952C7.30683 11.1316 7.41146 11.2949 7.55034 11.4287C7.68922 11.5626 7.85852 11.6634 8.04482 11.7232L8.89173 11.9953C9.03609 12.0417 9.03609 12.2383 8.89173 12.2847L8.04438 12.5568C7.85812 12.6167 7.68887 12.7176 7.55007 12.8516C7.41127 12.9855 7.30672 13.1488 7.24472 13.3284L6.96256 14.1452C6.95219 14.1757 6.93209 14.2022 6.90515 14.221C6.87821 14.2399 6.84579 14.25 6.81252 14.25C6.77925 14.25 6.74683 14.2399 6.71988 14.221C6.69294 14.2022 6.67284 14.1757 6.66247 14.1452L6.38031 13.328C6.31825 13.1484 6.21368 12.9852 6.07488 12.8514C5.93608 12.7175 5.76687 12.6167 5.58065 12.5568L4.7333 12.2847C4.70169 12.2747 4.67416 12.2553 4.65465 12.2293C4.63513 12.2034 4.62462 12.1721 4.62462 12.14C4.62462 12.1079 4.63513 12.0767 4.65465 12.0507C4.67416 12.0247 4.70169 12.0053 4.7333 11.9953L5.58065 11.7232C5.76687 11.6633 5.93608 11.5625 6.07488 11.4286C6.21368 11.2948 6.31825 11.1316 6.38031 10.952ZM4.97259 7.98368C4.97886 7.96541 4.99094 7.94951 5.00712 7.93825C5.0233 7.92698 5.04275 7.92091 5.0627 7.92091C5.08266 7.92091 5.10211 7.92698 5.11828 7.93825C5.13446 7.94951 5.14655 7.96541 5.15282 7.98368L5.32211 8.4739C5.39779 8.69244 5.5754 8.86372 5.802 8.9367L6.31032 9.09997C6.32926 9.10602 6.34575 9.11767 6.35743 9.13327C6.36912 9.14887 6.37541 9.16763 6.37541 9.18687C6.37541 9.20612 6.36912 9.22488 6.35743 9.24048C6.34575 9.25608 6.32926 9.26773 6.31032 9.27378L5.802 9.43705C5.69018 9.47284 5.58856 9.53332 5.50525 9.61366C5.42194 9.69401 5.35923 9.792 5.32211 9.89985L5.15282 10.3901C5.14655 10.4083 5.13446 10.4242 5.11828 10.4355C5.10211 10.4468 5.08266 10.4528 5.0627 10.4528C5.04275 10.4528 5.0233 10.4468 5.00712 10.4355C4.99094 10.4242 4.97886 10.4083 4.97259 10.3901L4.80329 9.89985C4.76618 9.792 4.70347 9.69401 4.62016 9.61366C4.53685 9.53332 4.43523 9.47284 4.32341 9.43705L3.81509 9.27378C3.79614 9.26773 3.77966 9.25608 3.76797 9.24048C3.75629 9.22488 3.75 9.20612 3.75 9.18687C3.75 9.16763 3.75629 9.14887 3.76797 9.13327C3.77966 9.11767 3.79614 9.10602 3.81509 9.09997L4.32341 8.9367C4.43523 8.90091 4.53685 8.84043 4.62016 8.76009C4.70347 8.67974 4.76618 8.58175 4.80329 8.4739L4.97259 7.98368ZM8.06494 7.54113C8.06926 7.52912 8.07735 7.51871 8.08809 7.51133C8.09884 7.50396 8.11169 7.5 8.12487 7.5C8.13806 7.5 8.15091 7.50396 8.16165 7.51133C8.1724 7.51871 8.18049 7.52912 8.18481 7.54113L8.29767 7.86767C8.34798 8.01364 8.46653 8.12796 8.61788 8.17648L8.95647 8.28533C8.96893 8.28949 8.97973 8.29729 8.98737 8.30765C8.99502 8.31801 8.99913 8.33041 8.99913 8.34312C8.99913 8.35584 8.99502 8.36823 8.98737 8.37859C8.97973 8.38895 8.96893 8.39675 8.95647 8.40092L8.61788 8.50976C8.54336 8.53381 8.47564 8.57422 8.42007 8.62781C8.36451 8.6814 8.3226 8.74671 8.29767 8.81858L8.18481 9.14511C8.18049 9.15712 8.1724 9.16754 8.16165 9.17491C8.15091 9.18228 8.13806 9.18624 8.12487 9.18624C8.11169 9.18624 8.09884 9.18228 8.08809 9.17491C8.07735 9.16754 8.06926 9.15712 8.06494 9.14511L7.95208 8.81858C7.92715 8.74671 7.88524 8.6814 7.82967 8.62781C7.77411 8.57422 7.70639 8.53381 7.63187 8.50976L7.29371 8.40092C7.28126 8.39675 7.27046 8.38895 7.26281 8.37859C7.25517 8.36823 7.25106 8.35584 7.25106 8.34312C7.25106 8.33041 7.25517 8.31801 7.26281 8.30765C7.27046 8.29729 7.28126 8.28949 7.29371 8.28533L7.6323 8.17648C7.78366 8.12796 7.90221 8.01364 7.95252 7.86767L8.06494 7.54113Z" fill="black" />
	</svg><?php  esc_html_e( 'AI Credits', 'recipe-card-blocks-by-wpzoom' ); ?></a></li>
			<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpzoom-recipe-card-vs-pro#quick-start' ) ) ?>" title="Quick Start"><svg width="18" height="18" viewBox="0 0 13 15" fill="none" xmlns="https://www.w3.org/2000/svg"><path d="M0.166992 14.5V0.333332H7.66699L8.00033 2H12.667V10.3333H6.83366L6.50033 8.66667H1.83366V14.5H0.166992ZM8.20866 8.66667H11.0003V3.66667H6.62533L6.29199 2H1.83366V7H7.87533L8.20866 8.66667Z" fill="#000"></path></svg> <?php esc_html_e( 'PRO Features', 'recipe-card-blocks-by-wpzoom' ); ?></a></li>
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
			<h2><?php esc_html_e( 'Connect', 'recipe-card-blocks-by-wpzoom' ); ?></h2>
		</div>
		<div class="vcard-body">
			<p><?php esc_html_e( 'Connect to recipecard.io to get your status and amount of the credits', 'recipe-card-blocks-by-wpzoom' ); ?></p>
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
									esc_html__( 'Enter your login details from %s below' , 'recipe-card-blocks-by-wpzoom' ),
									'<a href="' . esc_url( WPZOOM_RCB_STORE_URL ) . '" target="_blank">' . WPZOOM_RCB_STORE_URL . '</a>'
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
			<a href="<?php echo esc_url( WPZOOM_RCB_STORE_URL ) ?>account/ai-credits/" target="_blank"><?php esc_html_e( 'Buy more AI credits', 'recipe-card-blocks-by-wpzoom' ); ?></a>
		</div>
	</div>
	<?php
}