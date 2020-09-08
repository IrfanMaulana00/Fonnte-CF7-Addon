<?php
/**
 * [fonnte_cf7_submit description]
 * @param  [type] $WPCF7_ContactForm [description]
 * @return [type]                    [description]
 */
function fonnte_cf7_submit( $WPCF7_ContactForm ){
	$currentformInstance  	= WPCF7_ContactForm::get_current();
	$contactformsubmition 	= WPCF7_Submission::get_instance();
	$form_id 				= $currentformInstance->id();

	$fonnte_enable 			= get_post_meta( $form_id, '_fonnte_enable', true );
	$admin_number 			= get_post_meta( $form_id, '_fonnte_admin_number', true );
	$admin_message 			= get_post_meta( $form_id, '_fonnte_admin_message', true );

	$user_name 				= get_post_meta( $form_id, '_fonnte_user_name', true );
	$user_number 			= get_post_meta( $form_id, '_fonnte_user_number', true );
	$user_message 			= get_post_meta( $form_id, '_fonnte_user_message', true );

	// $url = $contactformsubmition->get_meta( 'url' );

	if ( $fonnte_enable == 'on' ) {
		$cf7frmdt = array();

		if ( $contactformsubmition ) {
			$cf7frmdt = $contactformsubmition->get_posted_data(); 
		}

		foreach ( $cf7frmdt as $key => $value ) {

			if ( is_array( $value ) ) {
				$value = array_filter( $value );
				if ( !empty( $value ) ) {
					$value = '- ' . implode( " \n - ", $value );
				} else {
					$value = "";
				}
			}

			$user_message = str_replace( '['.$key.']', $value, $user_message );
			$admin_message = str_replace( '['.$key.']', $value, $admin_message );

			if ( $user_number == '['.$key.']') {
				$user_number = str_replace( '['.$key.']', $value, $user_number );
			}

			if ( $user_name == '['.$key.']') {
				$user_name = str_replace( '['.$key.']', $value, $user_name );
			}
		}
		
		$user_phones = array(
			array(
				'nama'	=> $user_name,
				'nomer'	=> $user_number
			)
		);

		$admin_phones = array(
			array(
				'nama'	=> 'admin - ' . get_bloginfo( 'name' ),
				'nomer'	=> $admin_number
			)
		);

		// write_log( $user_message );
		// write_log( $user_phones );
		// write_log( $admin_phones );
		fonnte_cf7_send( $user_phones, $user_message );
		fonnte_cf7_send( $admin_phones, $admin_message );
	}
}
add_action( 'wpcf7_before_send_mail', 'fonnte_cf7_submit', 10, 1 );

/**
 * [fonnte_cf7_panels description]
 * @param  [type] $panels [description]
 * @return [type]         [description]
 */
function fonnte_cf7_panels( $panels ) {
	$panels['fonnte-panel'] = array(
		'title' 	=> 'Fonnte Settings',
		'callback' 	=> 'fonnte_cf7_panel_callback',
	);

	return $panels;
}
add_filter( 'wpcf7_editor_panels', 'fonnte_cf7_panels' );

/**
 * [fonnte_cf7_panel_callback description]
 * @param  [type] $post [description]
 * @return [type]       [description]
 */
function fonnte_cf7_panel_callback( $post, $args = '' ) { 
	$args = wp_parse_args( $args, array(
		'id' => 'wpcf7-mail',
		'name' => 'mail',
		'title' => __( 'Mail', 'contact-form-7' ),
		'use' => null,
	) );
	?>
	<h2><?php echo esc_html( __( 'Fonnte Settings', 'contact-form-7' ) ); ?></h2>
	<fieldset>
		<legend>Pengaturan integrasi Fonnte dan Contact Form 7</legend>

		<?php if ( '' == get_option( 'fonnte_cf7_token' ) ) : ?>
			<p style="color:red">Anda belum mengaktifkan Fonnte. Silahkan aktifkan add-on Fontte dengan memasukkan token pada halaman pengaturan.</p>
		<?php endif; ?>

		<p>
			<label><input type="checkbox" name="fonnte_enable" <?php checked(  get_post_meta( $post->id(), '_fonnte_enable', true ), 'on' ); ?>> Aktifkan Fonnte</label>
		</p>

		<p>
			<label>Nomor WhatsApp Admin</label>
			<input type="text" class="large-text" id="fonnte_admin_number" name="fonnte_admin_number" value="<?php echo get_post_meta( $post->id(), '_fonnte_admin_number', true ); ?>">
		</p>

		<p>
			<label>Pesan untuk Admin</label>
			<textarea id="fonnte_admin_message" name="fonnte_admin_message" cols="100" rows="6" class="large-text"><?php echo get_post_meta( $post->id(), '_fonnte_admin_message', true ); ?></textarea>
			<small>Bisa menggunakan tag name yang ada pada form. <?php $post->suggest_mail_tags( $args['name'] ); ?></small>
		</p>

		<p>
			<label>Nama User</label>
			<input type="text" class="large-text" id="fonnte_user_name" name="fonnte_user_name" value="<?php echo get_post_meta( $post->id(), '_fonnte_user_name', true ); ?>">
			<small>Bisa menggunakan tag name yang ada pada form. <?php $post->suggest_mail_tags( $args['name'] ); ?></small>
		</p>

		<p>
			<label>Nomor WhatsApp User</label>
			<input type="text" class="large-text" id="fonnte_user_number" name="fonnte_user_number" value="<?php echo get_post_meta( $post->id(), '_fonnte_user_number', true ); ?>">
			<small>Bisa menggunakan tag name yang ada pada form. <?php $post->suggest_mail_tags( $args['name'] ); ?></small>
		</p>

		<p>
			<label>Pesan untuk User</label>
			<textarea id="fonnte_user_message" name="fonnte_user_message" cols="100" rows="6" class="large-text"><?php echo get_post_meta( $post->id(), '_fonnte_user_message', true ); ?></textarea>
			<small>Bisa menggunakan tag name yang ada pada form. <?php $post->suggest_mail_tags( $args['name'] ); ?></small>
		</p>
	</fieldset>
	<?php
}

/**
 * [fonnte_cf7_save_form description]
 * @param  [type] $contact_form [description]
 * @param  [type] $args         [description]
 * @param  [type] $context      [description]
 * @return [type]               [description]
 */
function fonnte_cf7_save_form( $contact_form, $args, $context ) {
	$form_id = $args['id'];

	update_post_meta( $form_id, '_fonnte_enable', $args['fonnte_enable'] );

	update_post_meta( $form_id, '_fonnte_admin_number', $args['fonnte_admin_number'] );
	update_post_meta( $form_id, '_fonnte_admin_message', $args['fonnte_admin_message'] );

	update_post_meta( $form_id, '_fonnte_user_name', $args['fonnte_user_name'] );
	update_post_meta( $form_id, '_fonnte_user_number', $args['fonnte_user_number'] );
	update_post_meta( $form_id, '_fonnte_user_message', $args['fonnte_user_message'] );
}
add_action( 'wpcf7_save_contact_form', 'fonnte_cf7_save_form', 10, 3 );

/**
 * [fonnte_cf7_menu description]
 * @return [type] [description]
 */
function fonnte_cf7_menu() {
	add_submenu_page( 'wpcf7', 'Fontte', 'Fontte', 'administrator', 'fontte_cf7', 'fonnte_cf7_callback' );
}
add_action( 'admin_menu', 'fonnte_cf7_menu' );

function fonnte_cf7_callback() {
	fonnte_cf7_save_settings();
	include( FONTTE_CF7_ADDON_DIR . 'includes/cf7.fonnte.settings.php' );
}

function fonnte_cf7_save_settings() {
	if ( ! isset( $_POST['fonnte_cf7_nonce'] ) || ! wp_verify_nonce( $_POST['fonnte_cf7_nonce'], 'fonnte_cf7_save' ) 
) {
		return;
}
}

if ( isset( $_POST['fonnte_cf7_token'] ) ) {
	update_option( 'fonnte_cf7_token', esc_attr( $_POST['fonnte_cf7_token'] ) );
}

function fonnte_cf7_send( $phones = array(), $text ) {
	$apiurl = 'https://fonnte.com/api/api-undangan.php';
	$token 	= get_option( 'fonnte_cf7_token' );

	$args 	= array(
		'headers' => array(
			'Authorization' => $token
		),
	);

	$args['body'] = array(
		'data' 	=> json_encode( $phones ),
		'text' 	=> $text
	);

	$response = wp_remote_post( $apiurl, $args );
	if ( !is_wp_error( $response ) ) {
		$response = json_decode( wp_remote_retrieve_body( $response ) );
		if ( $response->status === true ) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function fonnte_cf7_referer( $form_tag ) {
	if ( $form_tag['name'] == 'referer' ) {
		$form_tag['values'][] = htmlspecialchars( get_permalink() );
	}
	return $form_tag;
}

if ( !is_admin() ) {
	add_filter( 'wpcf7_form_tag', 'fonnte_cf7_referer' );
}

function write_log($log) {
	if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
		if (is_array($log) || is_object($log)) {
			error_log(print_r($log, true));
		} else {
			error_log($log);
		}
	}
}