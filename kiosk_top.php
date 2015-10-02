<?

?>
<html>
	<head>
		<title>IM Library Kiosk</title>
		<link rel="stylesheet" href="<?= $db->common_url; ?>/stylesheet.css" type="text/css">
		<script type="text/javascript" src="<?= $db->url_root; ?>/soundmanager.js"></script>
		<script type="text/javascript" src="<?= $db->common_url; ?>/soundmanager/script/soundmanager2-nodebug-jsmin.js"></script>
		<script type="text/javascript">
			soundManager.url = '<?= $db->common_url; ?>/soundmanager/swf/';
			soundManager.debugMode = false;
			soundManager.onload = function() {
				<?
					if($db->kiosk_sound != "")
					{
						?>
						soundManager.play('kioskSound', '<?= $db->kiosk_sound; ?>');
						<?
					}; // end if
				?>
			}
		</script>
		<style type="text/css">
			body				{margin:0px;font-family:trebuchet ms;}
			.content			{padding:0px;}
			<?
				if($kiosk_wait_mode)
				{
					?>
					body				{background-color:000000;color:FFFFFF;}
					<?
				}; // end if
			?>
			.application_logo a				{color:006600;}
			.application_logo a:hover	{color:006600;}

			.action_bar						{text-align:center;padding:10px 25px 10px 25px;}
			.action_heading					{font-size:24pt;color:000000;}
			.action_status_success		{font-size:30pt;font-weight:bold;background-color:009900;color:FFFFFF;padding:0px 20px 0px 20px;}
			.action_status_failure			{font-size:30pt;font-weight:bold;background-color:FF0000;color:FFFFFF;padding:0px 20px 0px 20px;}
			.action_mode						{font-size:48pt;background-color:000099;color:FFFFFF;padding:0px 20px 0px 20px;}
			.action_message				{font-size:20pt;color:333300;}
			.action_instructions				{font-size:48pt;color:000066;}
			.action_instructions_small	{font-size:20pt;color:000066;}
			.action_instructions_special	{font-size:26pt;color:000066;background-color:FFDD00;padding:0px 20px 0px 20px;}
			.action_warning					{font-size:26pt;color:990000;font-weight:bold;}
			.break								{margin-top:20px;}

			.kiosk_key_message			{font-family:comic sans ms;font-size:48pt;padding-left:15px;padding-right:15px;font-weight:bold;}
			.kiosk_blue_message			{background-color:0000FF;color:FFFFFF;}
			.kiosk_wait_message			{font-family:trebuchet ms;font-size:48pt;text-transform:uppercase;text-align:center;}
			.kiosk_pin_input					{font-size:58pt;text-align:left;width:360px;}
			.kiosk_pin_keypad				{font-size:10pt;background-color:9999FF;border-color:EEEEFF;color:FFFFFF;width:300px;height:400px;font-weight:bold;border-radius:10px;border-style:inset;border-width:5px;padding:10px;}
			.kiosk_pin_key					{cursor:hand;font-size:58pt;text-align:center;background-color:3333FF;border-color:6666FF;color:FFFFFF;width:90px;height:100px;font-weight:bold;border-radius:10px;border-style:outset;border-width:5px;}
			.kiosk_pin_key_enter			{cursor:hand;font-size:35pt;text-align:center;background-color:3333FF;border-color:6666FF;color:FFFFFF;width:200px;height:100px;font-weight:bold;border-radius:10px;border-style:outset;border-width:5px;}

			.container							{text-align:center;}

			.large_input						{font-size:14pt;}

			.lookup_value						{font-weight:bold;padding:0px 0px 0px 0px;}

			.dialog_title							{font-size:36pt;text-align:center;}
			.dialog_input						{text-align:center;}
			.dialog_input input				{font-size:36pt;text-align:center;}
			.dialog_instructions				{font-size:24pt;text-align:center;}

			.title									{font-size:28pt;text-align:center;}

			.edit_error							{background-color:#F00;color:#FFF;}

			.wait_message						{font-size:36pt;position:absolute;top:0px;left:0px;width:100%;height:100%;text-align:center;background-color:FFFFFF;padding-top:200px;display:none;}
			.inline_wait_message				{font-size:18pt;display:none;font-style:normal;}

			.kioskmessagebar					{background-color:006600;color:FFFFFF;}
			.kioskmessagebar td				{font-size:24pt;padding:10px 20px 10px 20px;}

			<?
				if($accounts->computer_record->library_kiosk == "Y")
				{
					?>
					.search_input					{font-size:24pt;}
					.search_button				{font-size:24pt;}
					<?
				}
				else
				{
					?>
					.search_input					{font-size:12pt;}
					.search_button				{font-size:12pt;}
					<?
				}; // end if
			?>
		</style>
		<script language="javascript">
			function show_wait_message()
			{
				document.getElementById('wait_message').style.display = "inline";
			}; // end function
			var query_entry_start = 0;
			var query_entry_end = 0;
			function submit_search()
			{
				show_wait_message();
				query_entry_end = new Date();
				query_entry_length = query_entry_end.valueOf() - query_entry_start.valueOf();
				if(document.getElementById('query_input').value.length == <?= $db->barcode_length; ?>)
				{
					document.location.href = "<?= $db->url_root; ?>/search.php/query/" + document.getElementById('query_input').value + "/" + query_entry_length;
				}
				else
				{
					document.location.href = "<?= $db->url_root; ?>/search.php/query/" + document.getElementById('query_input').value;
				}; // end if
			}; // end function
			var signout_counter = 0;
			function reset_activity()
			{
				signout_counter = 0;
			}; // end function
			function check_activity()
			{
				signout_counter++;
				<?
					if($_SESSION['library_account_ID'] > 0 && $accounts->computer_record->library_kiosk == "Y")
					{
						?>
						if(parent.kiosk && signout_counter > <?= $db->activity_timeout; ?>)
						{
							parent.queue_command("10000000");
						}; // end if
						<?
					}; // end if
					if($db->home_reset > 0)
					{
						?>
						if(parent.kiosk && signout_counter > <?= $db->home_reset; ?>)
						{
							parent.queue_command("goto_home");
						}; // end if
						<?
					}; // end if
				?>
				setTimeout("check_activity()", 1000);
			}; // end function
			window.onkeydown = reset_activity;
			window.onmousemove = reset_activity;
			setTimeout("check_activity()", 1000);
		</script>
	</head>
	<body><?
?>