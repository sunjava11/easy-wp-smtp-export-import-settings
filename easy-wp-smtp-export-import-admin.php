<?php

class EasyWPSMTP_ExportImport_admin_menu {
	
	protected static $instance = null;

	public function __construct( ) {
				
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	
		
		add_action("easy_wp_smtp_export_import_settings",array($this,"export_import_settings"));
		add_action("easy_wp_smtp_export_import_hidden_forms_settings",array($this,"easy_wp_smtp_export_import_hidden_forms"));
	}
	
	public function admin_enqueue_scripts( $hook ) {
		// Load only on ?page=swpsmtp_settings
		if ( 'settings_page_swpsmtp_settings' !== $hook ) {
			return;
		}
		
		$core           = EasyWPSMTP_ExportImport_Main::get_instance();
		$plugin_data    = get_file_data( $core->file, array( 'Version' => 'Version' ), false );
		$plugin_version = $plugin_data['Version'];
		
		wp_register_script( 'swpsmtp_export_import_admin_js', plugins_url( 'js/script.js', __FILE__ ), array(), $plugin_version, true );
		wp_enqueue_script( 'swpsmtp_export_import_admin_js' );
	}
	
	public function export_import_settings()
	{
		?>
		<tr valign="top">
									<th scope="row"><?php esc_html_e( 'Export\Import Settings', 'easy-wp-smtp' ); ?></th>
									<td>
										<button id="swpsmtp_export_settings_btn" type="button" class="button"><?php esc_html_e( 'Export Settings', 'easy-wp-smtp' ); ?></button>
										<p class="description"><?php esc_html_e( 'Use this to export plugin settings to a file.', 'easy-wp-smtp' ); ?></p>
										<p></p>
										<button id="swpsmtp_import_settings_btn" type="button" class="button"><?php esc_html_e( 'Import Settings', 'easy-wp-smtp' ); ?></button>
										<p class="description"><?php esc_html_e( 'Use this to import plugin settings from a file. Note this would replace all your existing settings, so use with caution.', 'easy-wp-smtp' ); ?></p>
									</td>
								</tr>
		<?php
	}


	public function easy_wp_smtp_export_import_hidden_forms()
	{?>
		
			<form id="swpsmtp_export_settings_frm" style="display: none;" method="POST">
				<input type="hidden" name="swpsmtp_export_settings" value="1">
				<?php wp_nonce_field( 'easy_wp_smtp_export_settings', 'easy_wp_smtp_export_settings_nonce' ); ?>
			</form>

			<form id="swpsmtp_import_settings_frm" style="display: none;" method="POST" enctype="multipart/form-data">
				<input type="hidden" name="swpsmtp_import_settings" value="1">
				<input id="swpsmtp_import_settings_select_file" type="file" name="swpsmtp_import_settings_file">
				<?php wp_nonce_field( 'easy_wp_smtp_import_settings', 'easy_wp_smtp_import_settings_nonce' ); ?>
			</form>
	<?php
	}

	


}
