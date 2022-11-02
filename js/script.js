jQuery(function($){
		$('#swpsmtp_export_settings_btn').click(function (e) {
		e.preventDefault();
		$('#swpsmtp_export_settings_frm').submit();
	});

	$('#swpsmtp_import_settings_btn').click(function (e) {
		e.preventDefault();
		$('#swpsmtp_import_settings_select_file').click();
	});

	$('#swpsmtp_import_settings_select_file').change(function (e) {
		e.preventDefault();
		$('#swpsmtp_import_settings_frm').submit();
	});

	
});