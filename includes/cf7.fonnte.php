<?php

include( FONNTE_CF7_ADDON_DIR . 'includes/dompdf/vendor/autoload.php' );
use Dompdf\Dompdf;

function fonnte_cf7_pdf($format, $cf7frmdt, $custom = "off", $images = array()) {
	
	$html = "";
	foreach ( $cf7frmdt as $key => $value ) {
	    if ( is_array( $value ) ) {
			$value = array_filter( $value );
			if ( !empty( $value ) ) {
				$value = '- ' . implode( " \n - ", $value );
			} else {
				$value = "-";
			}
		}
		
		if ( isset($images[$key]) ) {
			$format = str_replace( '['.$key.']', '<img src="'.$images[$key].'" width="150px" />', $format );
		} else {
			$format = str_replace( '['.$key.']', ((strlen(str_replace("\r\n", "", $value)) > 0) ? $value : "-"), $format );
		}
	}
	
	if ( $custom != "on" ) {
		
		preg_match_all("/\\x60([^\\x60]+)/", $format, $matches);
		foreach ( $matches[0] as $data ) {
			$split = explode(":", str_replace("`", "", $data));
			$kolom = ((isset($split[0])) ? $split[0] : "");
			$isi = ((isset($split[1]) and $split[1] != "\r\n") ? str_replace("`$kolom:", "", $data) : "-");
			
			$html .= '<div style="clear:both; position:relative; margin: 10px;">
				<div style="position:absolute; left:0pt; width:192pt;padding:5px">
				'.$kolom.'
				</div>
				<div style="margin-left:200pt;'.(( strpos($isi, "img src=") === false ) ? 'background:#127AB9;' : '').'color:white;padding:5px 10px">
				'.$isi.'
				</div>
			</div>';
		}
		
		return $html;
	}

	return $format;
}


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
	$buat_pdf 				= get_post_meta( $form_id, '_fonnte_buat_pdf', true );
	$logoPdf 				= get_post_meta( $form_id, '_fonnte_teks_logo', true );
	$previewPdf 			= get_post_meta( $form_id, '_fonnte_preview_pdf', true );

	if ( $logoPdf == null ) {
		$logoPdf			= get_option( 'fonnte_cf7_logo_default' );
	}

	$type 					= pathinfo($logoPdf, PATHINFO_EXTENSION);
	$b64image 				= base64_encode(file_get_contents($logoPdf));
	$base64_logo 			= 'data:image/' . $type . ';base64,' . $b64image;
	
	$kirimKeUser 			= get_post_meta( $form_id, '_fonnte_kirim_pdf_ke_user', true );
	$kirimKeAdmin 			= get_post_meta( $form_id, '_fonnte_kirim_pdf_ke_admin', true );
	$statusCustom 			= get_post_meta( $form_id, '_fonnte_custom_format_pdf', true );
	
	$admin_number 			= get_post_meta( $form_id, '_fonnte_admin_number', true );
	$admin_message 			= get_post_meta( $form_id, '_fonnte_admin_message', true );

	$user_name 				= get_post_meta( $form_id, '_fonnte_user_name', true );
	$user_number 			= get_post_meta( $form_id, '_fonnte_user_number', true );
	$user_message 			= get_post_meta( $form_id, '_fonnte_user_message', true );

	$cf7frmdt = array();
	if ( $contactformsubmition ) {
		$cf7frmdt = $contactformsubmition->get_posted_data();
		$cf7images = $contactformsubmition->uploaded_files();
		$imagesUpload = array();
		foreach ( $cf7images as $key => $value ) {
			$type 		= pathinfo($value, PATHINFO_EXTENSION);
			$b64image 	= base64_encode(file_get_contents($value));
			$base64 	= 'data:image/' . $type . ';base64,' . $b64image;
			$imagesUpload[$key] = $base64;
		}
	}
	
	//file_put_contents("a.json", implode("||", $a)); exit;

	// $url = $contactformsubmition->get_meta( 'url' );
	$typeMessage	= "text";
	
	if ( $buat_pdf == "on" ) {
		if ( $statusCustom == "on" ) {
			$formatPdf 	= get_post_meta( $form_id, '_fonnte_html_pdf', true );
			$htmlpdf	= fonnte_cf7_pdf($formatPdf, $cf7frmdt, $statusCustom, $imagesUpload);
			$htmlpdf	= str_replace("[pdf-logo]", $base64_logo, $htmlpdf);
		} else {
			$formatPdf 	= get_post_meta( $form_id, '_fonnte_format_pdf', true );
			$htmlpdf	= fonnte_cf7_pdf($formatPdf, $cf7frmdt, $statusCustom, $imagesUpload);
			$htmlpdf = '<html lang="en">
			<head>
				<!-- Required meta tags -->
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width, initial-scale=1">
			
				<!-- Bootstrap CSS -->
				<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
				<title>Information!</title>
			</head>
			<body style="margin-top:25px">
				<img src="'.$base64_logo.'" max-width="50%" max-height="100px"/>
				<br>
				<h2 class="container">Information</h2>
				<hr>
				<div class="container" style="font-size: 20px;">
				'.$htmlpdf.'
				</div>
				<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
			</body>
			</html>';
		}
		
		$dompdf = new Dompdf();
		$dompdf->loadHtml($htmlpdf);
	  
		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper('A4', 'portrait');
	  
		// Render the HTML as PDF
		$dompdf->render();
	  
		$output = $dompdf->output();
		$fileName = time()."-".rand().".pdf";
		if ( $previewPdf == "on" ) {
			$fileName = "preview-$form_id.pdf";
		}
		$fullUrl = wp_upload_dir()['path']."/$fileName";
		file_put_contents($fullUrl, $output);
	}

	if ( $fonnte_enable == 'on' ) {

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
		
		if ( $kirimKeUser == "on" and $buat_pdf == "on" ) {
			fonnte_cf7_send( $user_phones, $user_message, "file", wp_get_upload_dir()['url']."/$fileName" );
		} else {
			fonnte_cf7_send( $user_phones, $user_message );
		}

		if ( $kirimKeAdmin == "on" and $buat_pdf == "on" ) {
			fonnte_cf7_send( $admin_phones, $admin_message, "file", wp_get_upload_dir()['url']."/$fileName" );
		} else {
			fonnte_cf7_send( $admin_phones, $admin_message );
		}
		
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

	$statusCustom = get_post_meta( $post->id(), '_fonnte_custom_format_pdf', true );
	$htmlpdf	= get_post_meta( $post->id(), '_fonnte_html_pdf', true );
	if ( empty($htmlpdf) ) {
		$htmlpdf 	= '<html lang="en">
			<head>
				<!-- Required meta tags -->
				<meta charset="utf-8">
				<meta name="viewport" content="width=device-width, initial-scale=1">
			
				<!-- Bootstrap CSS -->
				<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
				<title>Information!</title>
			</head>
			<body style="margin-top:25px">
				<img src="[pdf-logo]" width="150px" />
				<br>
				<h2 class="container">Information</h2>
				<hr>
				<div class="container" style="font-size: 20px;">
					<div style="clear:both; position:relative; margin: 10px;">
						<div style="position:absolute; left:0pt; width:192pt;padding:5px">
						Nama
						</div>
						<div style="margin-left:200pt;background:#127AB9;color:white;padding:5px 10px">
						[your-name]
					</div>
					</div>
				</div>
				<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"></script>
			</body>
			</html>';
	}

	?>
	<h2><?php echo esc_html( __( 'Fonnte Settings', 'contact-form-7' ) ); ?></h2>
	<fieldset>
		<legend>Pengaturan integrasi Fonnte dan Contact Form 7</legend>

		<?php if ( '' == get_option( 'fonnte_cf7_token' ) ) : ?>
			<p style="color:red">Anda belum mengaktifkan Fonnte. Silahkan aktifkan add-on Fonnte dengan memasukkan token pada halaman pengaturan.</p>
		<?php endif; ?>

		<p>
			<label><input type="checkbox" name="fonnte_enable" <?php checked(  get_post_meta( $post->id(), '_fonnte_enable', true ), 'on' ); ?>> Aktifkan Fonnte</label>
		</p>

		<p>
			<label>Nomor WhatsApp Admin</label>
			<input type="text" class="large-text" id="fonnte_admin_number" name="fonnte_admin_number" value="<?php echo get_post_meta( $post->id(), '_fonnte_admin_number', true ); ?>">
		</p>

		<p>
			<label><input type="checkbox" name="fonnte_buat_pdf" <?php checked(  get_post_meta( $post->id(), '_fonnte_buat_pdf', true ), 'on' ); ?>> Buat PDF</label>
		</p>

		<p>
			<label><input type="checkbox" name="fonnte_kirim_pdf_ke_user" <?php checked(  get_post_meta( $post->id(), '_fonnte_kirim_pdf_ke_user', true ), 'on' ); ?>> Kirim PDF Ke User</label>
		</p>

		<p>
			<label><input type="checkbox" name="fonnte_kirim_pdf_ke_admin" <?php checked(  get_post_meta( $post->id(), '_fonnte_kirim_pdf_ke_admin', true ), 'on' ); ?>> Kirim PDF Ke Admin</label>
		</p>

		<p>
			<label><input type="checkbox" name="fonnte_preview_pdf" <?php checked(  get_post_meta( $post->id(), '_fonnte_preview_pdf', true ), 'on' ); ?>> Preview PDF</label>
		</p>
		
		<?php if ( get_post_meta( $post->id(), '_fonnte_preview_pdf', true ) == "on" ) { ?>
		<p>
			<label>URL Preview : <a target="_BLANK" href="<?php echo wp_upload_dir()['url']."/preview-".$post->id().".pdf";?>"><?php echo wp_upload_dir()['url']."/preview-".$post->id().".pdf";?></a></label>
		</p>
		<?php } ?>

		<p>
			<label>Url Logo Untuk PDF</label>
			<input type="text" class="large-text" id="fonnte_teks_logo" name="fonnte_teks_logo" value="<?php echo get_post_meta( $post->id(), '_fonnte_teks_logo', true ); ?>">
		</p>

		<p>
			<label><input type="checkbox" name="fonnte_custom_format_pdf" id="fonnte_custom_format_pdf" onclick="myFunction()" <?php checked(  $statusCustom, 'on' ); ?>> Custom Format PDF</label>
		</p>

		<script>
		function myFunction() {
			var checkBox	= document.getElementById("fonnte_custom_format_pdf");
			var original 	= document.getElementById("formatOriginal");
			var custom 		= document.getElementById("formatCustom");
			original.style.display = "none";
			custom.style.display = "none";

			if (checkBox.checked == true){
				custom.style.display = "block";
			} else {
				original.style.display = "block";
			}
		}
		</script>

		<div id="formatOriginal" <?php echo (($statusCustom == "on") ? 'style="display: none;"' : ''); ?>>
		<p>
			<label>Data Format PDF</label>
			<textarea id="fonnte_format_pdf" name="fonnte_format_pdf" placeholder="`Nama:[your-name]" cols="100" rows="6" class="large-text"><?php echo get_post_meta( $post->id(), '_fonnte_format_pdf', true ); ?></textarea>
			<small>Contoh : `Nama:[your-name]</small><br>
			<small>Bisa menggunakan tag name yang ada pada form. <?php $post->suggest_mail_tags( $args['name'] ); ?></small>
		</p>
		</div>

		<div id="formatCustom" <?php echo (($statusCustom == "on") ? '' : 'style="display: none;"'); ?>>
		<p>
			<label>HTML Format PDF</label>
			<textarea id="fonnte_html_pdf" name="fonnte_html_pdf" cols="100" rows="10" class="large-text"><?php echo $htmlpdf; ?></textarea>
			<small>Bisa menggunakan tag name yang ada pada form. <?php $post->suggest_mail_tags( $args['name'] ); ?></small>
		</p>
		</div>

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
	update_post_meta( $form_id, '_fonnte_buat_pdf', $args['fonnte_buat_pdf'] );
	update_post_meta( $form_id, '_fonnte_teks_logo', $args['fonnte_teks_logo'] );
	update_post_meta( $form_id, '_fonnte_kirim_pdf_ke_user', $args['fonnte_kirim_pdf_ke_user'] );
	update_post_meta( $form_id, '_fonnte_kirim_pdf_ke_admin', $args['fonnte_kirim_pdf_ke_admin'] );
	update_post_meta( $form_id, '_fonnte_custom_format_pdf', $args['fonnte_custom_format_pdf'] );
	update_post_meta( $form_id, '_fonnte_preview_pdf', $args['fonnte_preview_pdf'] );
	if ( $args['fonnte_custom_format_pdf'] == 'on' ) {
		update_post_meta( $form_id, '_fonnte_html_pdf', $args['fonnte_html_pdf'] );
	} else {
		update_post_meta( $form_id, '_fonnte_format_pdf', $args['fonnte_format_pdf'] );
	}

	update_post_meta( $form_id, '_fonnte_admin_number', $args['fonnte_admin_number'] );
	update_post_meta( $form_id, '_fonnte_admin_message', $args['fonnte_admin_message'] );

	update_post_meta( $form_id, '_fonnte_user_name', $args['fonnte_user_name'] );
	update_post_meta( $form_id, '_fonnte_user_number', $args['fonnte_user_number'] );
	update_post_meta( $form_id, '_fonnte_user_message', $args['fonnte_user_message'] );
}
add_action( 'wpcf7_save_contact_form', 'fonnte_cf7_save_form', 10, 3 );

function fonnte_cf7_admin_style() {
    $current_screen = get_current_screen();

    if ( strpos($current_screen->base, "toplevel_page_wpcf7") !== false) {
		if ( isset($_FILES) and count($_FILES) > 0 ) {
			print_r($_FILES); exit;
		}
	}
}
add_action('admin_enqueue_scripts', 'fonnte_cf7_admin_style');


/**
 * [fonnte_cf7_menu description]
 * @return [type] [description]
 */
function fonnte_cf7_menu() {
	add_submenu_page( 'wpcf7', 'Fonnte', 'Fonnte', 'administrator', 'fonnte_cf7', 'fonnte_cf7_callback' );
}
add_action( 'admin_menu', 'fonnte_cf7_menu' );

function fonnte_cf7_callback() {
	fonnte_cf7_save_settings();
	include( FONNTE_CF7_ADDON_DIR . 'includes/cf7.fonnte.settings.php' );
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
if ( isset( $_POST['fonnte_cf7_logo_default'] ) ) {
	update_option( 'fonnte_cf7_logo_default', esc_attr( $_POST['fonnte_cf7_logo_default'] ) );
}

function fonnte_cf7_send( $phones = array(), $text, $type = "text", $file = "" ) {
	$apiurl = 'https://api.fonnte.com/send';
	$token 	= get_option( 'fonnte_cf7_token' );

	$args 	= array(
		'headers' => array(
			'Authorization' => $token
		),
	);

	if ( $type == "text" ) {
		$args['body'] = array(
			'target' 	=> $phones[0]['nomer'],
			'message' 	=> $text,
			'domain' 	=> get_site_url()
		);
	} else {
		$args['body'] = array(
			'target' 	=> $phones[0]['nomer'],
			'url' 		=> $file,
			'message' 	=> $text,
			'domain' 	=> get_site_url()
		);
	}

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