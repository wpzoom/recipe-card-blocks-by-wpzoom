<?php
wp_enqueue_script( 'wpzoom-rcb-license-script' );
wp_enqueue_style( 'wpzoom-rcb-license-styles' );

$site_url = home_url();
$parsed_url = parse_url( $site_url );

?>

<div class="vtop-bar">
	<div class="v-bar">
		<h2><?php esc_html_e( 'Recipe Card Block PRO', 'recipe-card-blocks-by-wpzoom' ); ?></h2>
		<p><?php esc_html_e( 'V. 5.1.3', 'recipe-card-blocks-by-wpzoom' ); ?></p>
	</div>
	<h4><?php esc_html_e( 'License', 'recipe-card-blocks-by-wpzoom' ); ?></h4>
</div>
<div id="request-processing" class="hidden"></div>
<?php
$license_data = get_transient( 'wpzoom_rcb_plugin_license_data' );
if ( ! $license_data || $license_data->license !== 'valid' ) {
	?>
	<div class="vcard">
		<div class="vcard-top">
			<img src="<?php echo plugin_dir_url( __FILE__ ); ?>/assets/img/active.svg" alt="">
			<h2><?php esc_html_e( 'Activate your license', 'recipe-card-blocks-by-wpzoom' ); ?></h2>
		</div>
		<div class="vcard-body">
			<p><?php esc_html_e( 'Connect now and activate your license to experience seamless features and exclusive benefits.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
			<a href="#" class="vsign"><?php esc_html_e( '', 'recipe-card-blocks-by-wpzoom' ); ?>Connect & Activate</a>
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
	<div class="vpopup-fixed vcont hidden">
		<div class="vpopup-container">
			<div class="vpopup-content">
				<div class="vpop-head">
					<h2><?php esc_html_e( 'Activate PRO Features', 'recipe-card-blocks-by-wpzoom' ); ?></h2>
					<div class="vclose-button">
						<img src="<?php echo plugin_dir_url( __FILE__ ); ?>/assets/img/close.svg">
					</div>
				</div>
				<div class="vpop-body">
					<div class="vpop-prag">
						<p><?php esc_html_e( 'Connect your WordPress site to the Recipe Card Blocks Dashboard and activate your license to use all the PRO features.', 'recipe-card-blocks-by-wpzoom' ); ?></p>
					</div>
					<div class="vlinks">
						<div class="vlinks-row">
							<div>
								<img src="<?php echo plugin_dir_url( __FILE__ ); ?>/assets/img/site.svg"/>
							</div>
							<div class="vlinks-body">
								<h2><?php echo esc_html( $parsed_url['host'] ?? $site_url ); ?></h2>
							</div>
						</div>
						<div class="vlinks-row">
							<div>
								<img src="<?php echo plugin_dir_url( __FILE__ ); ?>/assets/img/user.svg" class="rcb-pro-user-avatar"/>
							</div>
							<div class="vlinks-body">
								<h2 class="rcb-pro-user-name"><?php esc_html_e( 'Alex Todd', 'recipe-card-blocks-by-wpzoom' ); ?></h2>
								<a href="#" onclick="window.location.reload();"><?php esc_html_e( 'Switch user', 'recipe-card-blocks-by-wpzoom' ); ?></a>
							</div>
						</div>
					</div>
					<a class="conn" href="#"><?php esc_html_e( 'Connect', 'recipe-card-blocks-by-wpzoom' ); ?></a>
				</div>
			</div>
		</div>
	</div>
	<?php
} else {
	$customer_email = $license_data->customer_email;
	$expires = strtotime( $license_data->expires );
	$status = $license_data->license;
	$credits = get_option( 'wpzoom_credits' );
	$total_credits = $credits['total'] ?? '';
	$remaining_credits = $credits['remaining'] ?? '';
	?>
	<div class="vcard">
		<div class="vcard-top">
			<img src="<?php echo plugin_dir_url( __FILE__ ); ?>/assets/img/active.svg">
			<h2><?php esc_html_e( 'Your license', 'recipe-card-blocks-by-wpzoom' ); ?></h2>
		</div>
		<div class="vcard-body">
			<div class="vstatus">
				<div class="grey"><?php esc_html_e( 'Status:', 'recipe-card-blocks-by-wpzoom' ); ?></div>
				<div class="green"><?php echo ucfirst( esc_html( $status ) ); ?></div>
			</div>
			<div class="vexpire">
				<div class="grey"><?php esc_html_e( 'Expiration date:', 'recipe-card-blocks-by-wpzoom' ); ?></div>
				<div class="black"><?php echo date_i18n( get_option( 'date_format' ), $expires ); ?></div>
			</div>
			<div class="vuser">
				<div class="grey"><?php esc_html_e( 'Connected to:', 'recipe-card-blocks-by-wpzoom' ); ?></div>
				<div class="black"><?php echo esc_html( $customer_email ); ?></div>
				<div class="orange dconnect"><?php esc_html_e( 'Switch user', 'recipe-card-blocks-by-wpzoom' ); ?></div>
			</div>
			<div class="vconnect">
				<a class="connect" href="<?php echo esc_url( WPZOOM_RCB_STORE_URL ) ?>account/" target="_blank"><?php esc_html_e( 'My account', 'recipe-card-blocks-by-wpzoom' ); ?></a>
				<a class="dconnect" href="#"><?php esc_html_e( 'Disconnect license', 'recipe-card-blocks-by-wpzoom' ); ?></a>
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
				if ( empty( $total_credits ) && empty( $remaining_credits ) ) {
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