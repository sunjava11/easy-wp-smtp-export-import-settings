<?php

/**
 * Plugin Name: Easy Wp SMTP Export Import Settings
 * Plugin URI: https://wp-ecommerce.net/
 * Description: Easy Wp SMTP Export Import Settings
 * Version: 1.0.0
 * Author: wpecommerce
 * Author URI: https://wp-ecommerce.net/
 * License: GPL2
 * Text Domain: easy-wp-smtp-export-import
 * Domain Path: /languages
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; //Exit if accessed directly
}

class EasyWPSMTP_ExportImport_Main {

	public $helper;
	public $EasyWPSMTP;
	public $file;
	public $ADDON_SHORT_NAME   = 'Export Import';
	public $ADDON_FULL_NAME    = 'Export Import';
	public $MIN_EasyWPSMTP_VER = '1.5.2';	
	protected static $instance = null;


	public function __construct() {
		$this->file = __FILE__;
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );				
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		self::$instance = $this;

		if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {			
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
		
		
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function plugins_loaded() {
		if ( class_exists( 'EasyWPSMTP' ) ) {
			$this->EasyWPSMTP = EasyWPSMTP::get_instance();
			$this->util  = new EasyWPSMTP_Utils( $this );
			//check minimum required core plugin version
			if ( ! $this->util->check_ver() ) {				
				return false;
			}
			

			if ( is_admin() ) {
				require_once plugin_dir_path( $this->file ) . 'easy-wp-smtp-export-import-admin.php';
				new EasyWPSMTP_ExportImport_admin_menu( $this->util );
			} 
		}
	}

	public function validate_log_file_name($file_name)
	{
		
		$folder_dir_filename = explode("\\",$file_name);

		
						
		if(sizeof($folder_dir_filename)==2)
		{
		
			if($folder_dir_filename[0]=="logs")
			{
				//checking if file extention is .txt
				$log_file_ext=explode(".",$folder_dir_filename[1]);

				$log_file_ext = $log_file_ext[sizeof($log_file_ext)-1];

				
				if($log_file_ext=="txt")
				{
					return true;
				}
			}
		}

		return false;
	}
	
	public function admin_init()
	{
		//check if this is export settings request
			$is_export_settings = filter_input( INPUT_POST, 'swpsmtp_export_settings', FILTER_SANITIZE_NUMBER_INT );
			if ( $is_export_settings ) {
				check_admin_referer( 'easy_wp_smtp_export_settings', 'easy_wp_smtp_export_settings_nonce' );
				$data                           = array();
				$opts                           = get_option( 'swpsmtp_options', array() );
				$data['swpsmtp_options']        = $opts;
				$swpsmtp_pass_encrypted         = get_option( 'swpsmtp_pass_encrypted', false );
				$data['swpsmtp_pass_encrypted'] = $swpsmtp_pass_encrypted;
				if ( $swpsmtp_pass_encrypted ) {
					$swpsmtp_enc_key         = get_option( 'swpsmtp_enc_key', false );
					$data['swpsmtp_enc_key'] = $swpsmtp_enc_key;
				}
				$smtp_test_mail         = get_option( 'smtp_test_mail', array() );
				$data['smtp_test_mail'] = $smtp_test_mail;
				$out                    = array();
				$out['data']            = wp_json_encode( $data );
				$out['ver']             = 2;
				$out['checksum']        = md5( $out['data'] );

				$filename = 'easy_wp_smtp_settings.json';
				header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
				header( 'Content-Type: application/json' );
			
				echo wp_json_encode( $out );
				exit;
			}

			$is_import_settings = filter_input( INPUT_POST, 'swpsmtp_import_settings', FILTER_SANITIZE_NUMBER_INT );
			if ( $is_import_settings ) {
				check_admin_referer( 'easy_wp_smtp_import_settings', 'easy_wp_smtp_import_settings_nonce' );
				$err_msg = __( 'Error occurred during settings import', 'easy-wp-smtp' );
				if ( empty( $_FILES['swpsmtp_import_settings_file'] ) ) {
					echo esc_html( $err_msg );
					wp_die();
				}
				$in_raw = file_get_contents( $_FILES['swpsmtp_import_settings_file']['tmp_name'] ); //phpcs:ignore

				
				try {
					
					$in = json_decode( $in_raw, true );
					
					//if json_decode has errors
					if ( json_last_error() !== 0 ) {

						echo __("Error importing the settings file. Please re-export the file",'easy-wp-smtp');
						wp_die();
					}
					if ( empty( $in['data'] ) ) {
						echo esc_html( $err_msg );
						wp_die();
					}
					if ( empty( $in['checksum'] ) ) {
						echo esc_html( $err_msg );
						wp_die();
					}
					if ( md5( $in['data'] ) !== $in['checksum'] ) {
						echo esc_html( $err_msg );
						wp_die();
					}
					$data = json_decode( $in['data'], true );

					//if json_decode has errors
					if ( json_last_error() !== 0 ) {
						echo __("Error importing the settings file. Please re-export the file",'easy-wp-smtp');
						wp_die();
					}

					//validating log file name					
					if(isset($data["swpsmtp_options"]) && isset($data["swpsmtp_options"]["smtp_settings"]) && isset($data["swpsmtp_options"]["smtp_settings"]["log_file_name"]))
					{
						$log_file_name = $data["swpsmtp_options"]["smtp_settings"]["log_file_name"];
						if($this->validate_log_file_name($log_file_name)==false)
						{
							echo __("Error importing the settings file. Please re-export the file",'easy-wp-smtp');
							wp_die();
						}						
					}					

					update_option( 'swpsmtp_options', $data['swpsmtp_options'] );
					update_option( 'swpsmtp_pass_encrypted', $data['swpsmtp_pass_encrypted'] );
					if ( $data['swpsmtp_pass_encrypted'] ) {
						update_option( 'swpsmtp_enc_key', $data['swpsmtp_enc_key'] );
					}
					update_option( 'smtp_test_mail', $data['smtp_test_mail'] );
					set_transient( 'easy_wp_smtp_settings_import_success', true, 60 * 60 );
					$url = admin_url() . 'options-general.php?page=swpsmtp_settings';
					wp_safe_redirect( $url );
					exit;
				} catch ( Exception $ex ) {
					echo esc_html( $err_msg );
					wp_die();
				}
			}
	}


	public function admin_notices() {

		$settings_import_notice = get_transient( 'easy_wp_smtp_settings_import_success' );
		if ( $settings_import_notice ) {
			delete_transient( 'easy_wp_smtp_settings_import_success' );
			?>
		<div class="updated">
			<p><?php echo esc_html( __( 'Settings have been imported successfully.', 'easy-wp-smtp-export-import' ) ); ?></p>
		</div>
			<?php
		}
	}




}

new EasyWPSMTP_ExportImport_Main();
