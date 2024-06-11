<div class="wrap">
	<h2>Fonnte Add-On Settings</h2>
	<div class="card">
		<form method="post">
			<p>
				<label>Fonnte Token</label>
				<input type="password" name="fonnte_cf7_token" class="widefat" placehoder="token" value="<?php echo get_option( 'fonnte_cf7_token' ); ?>">
			</p>
			<p>
				<label>Url Logo Default</label>
				<input type="text" name="fonnte_cf7_logo_default" class="widefat" value="<?php echo get_option( 'fonnte_cf7_logo_default' ); ?>">
			</p>
			<?php wp_nonce_field( 'fonnte_cf7_save', 'fonnte_cf7_nonce' ); ?>
			<?php submit_button( null, 'primary', 'submit', true, null ); ?>
		</form>
	</div>
</div>