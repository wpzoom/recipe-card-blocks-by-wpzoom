<?php
wp_enqueue_script( 'wpzoom-rcb-user-script' );
wp_enqueue_style( 'wpzoom-rcb-user-styles' );

$site_url   = home_url();
$parsed_url = parse_url( $site_url );

?>

<div class="vtop-bar">
	<div class="v-bar">
		<h2><?php esc_html_e( 'Recipe Card Blocks (Lite)', 'recipe-card-blocks-by-wpzoom' ); ?></h2>
		<p><?php esc_html_e( 'v ', 'recipe-card-blocks-by-wpzoom' ); echo WPZOOM_RCB_VERSION; ?></p>
	</div>
	<h4><?php esc_html_e( 'AI Credits', 'recipe-card-blocks-by-wpzoom' ); ?></h4>
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