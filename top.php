<?

?>
<html>
	<head>
		<title><?= ($title != "" ? $title . " - " : ""); ?>IM Library</title>
		<link rel="stylesheet" href="<?= $db->common_url; ?>/stylesheet.css" type="text/css" MEDIA=screen>
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
			.application_logo a				{color:006600;}
			.application_logo a:hover	{color:006600;}

			.action_bar						{text-align:center;padding:10px 25px 10px 25px;}
			.action_heading					{font-size:24pt;color:000000;}
			.action_status_success		{font-size:30pt;font-weight:bold;background-color:009900;color:FFFFFF;padding:0px 20px 0px 20px;}
			.action_status_failure			{font-size:30pt;font-weight:bold;background-color:FF0000;color:FFFFFF;padding:0px 20px 0px 20px;}
			.action_mode						{font-size:48pt;background-color:000099;color:FFFFFF;padding:0px 20px 0px 20px;}
			.action_message				{font-size:20pt;color:333300;}
			.action_instructions				{font-size:26pt;color:000066;}
			.action_instructions_small	{font-size:20pt;color:000066;}
			.action_instructions_special	{font-size:26pt;color:000066;background-color:FFDD00;padding:0px 20px 0px 20px;}
			.action_warning					{font-size:26pt;color:990000;font-weight:bold;}
			.break								{margin-top:20px;}

			.kiosk_key_message			{font-family:comic sans ms;font-size:36pt;padding-left:15px;padding-right:15px;font-weight:bold;}
			.kiosk_blue_message			{background-color:0000FF;color:FFFFFF;}


			.container							{text-align:center;}
			
			.detail_photo_container			{padding:0px 0px 0px 0px;}
			.detail_photo_container img		{cursor:hand;}
			.detail_info_container				{padding:0px 0px 0px 20px;}
			.detail_other_container			{margin:13px 0px 0px 10px;padding:10px;border:1px solid 999999;background-color:EEFFEE;}
			.detail_system_container			{margin:13px 0px 0px 10px;padding:10px;border:1px solid 000000;background-color:FFFFFF;}
			.detail_location_container		{margin:0px 0px 13px 0px;padding:5px 0px 5px 0px;background-color:FEFEFE;position:relative;top:-1px;left:-2px;border-width:1px 1px 1px 0px;border-style:solid;border-color:CCCCCC;}
			.detail_description_container	{margin:10px 0px 0px 0px;padding:0px 0px 0px 0px;}
			.detail_status_available			{background-color:006600;padding:4px 0px 4px 0px;text-align:center;font-weight:normal;font-size:13pt;color:FFFFFF;}
			.detail_status_restricted			{background-color:996600;padding:4px 0px 4px 0px;text-align:center;font-weight:normal;font-size:13pt;color:FFFFFF;}
			.detail_status_checkedout		{background-color:CC0000;padding:4px 0px 4px 0px;text-align:center;font-weight:normal;font-size:13pt;color:FFFFFF;}
			.detail_status_lost					{background-color:000000;padding:4px 0px 4px 0px;text-align:center;font-weight:normal;font-size:13pt;color:FFFFFF;}
			.detail_series							{font-size:14pt;}
			.detail_title								{font-size:22pt;font-weight:normal;}
			.detail_parallel_title				{font-size:12pt;font-weight:bold;}
			.detail_edition						{font-size:14pt;font-style:italic;}
			.detail_copy							{font-size:12pt;font-style:italic;}
			.detail_authors						{font-size:12pt;font-weight:normal;padding-left:10px;}
			.detail_compilation_title			{font-size:12pt;font-weight:bold;padding-top:6px;}
			.detail_compilation_pages		{font-size:10pt;font-weight:normal;}
			.detail_compilation_author		{font-size:10pt;font-weight:normal;padding-left:10px;}
			.detail_summary					{font-size:10pt;font-weight:normal;padding-top:10px;}
			.detail_subject						{font-size:10pt;font-weight:normal;padding-bottom:4px;}
			.detail_call_number				{margin:0px 0px 0px 10px;font-size:16pt;padding:5px;border:1px solid CCCCCC;background-color:FEFEFE;}
			.detail_location						{font-size:10pt;font-weight:bold;}
			.detail_category						{font-size:10pt;font-weight:bold;}
			.detail_length							{font-size:12pt;font-weight:normal;text-align:center;}
			.detail_type							{font-size:14pt;font-weight:normal;text-align:center;}
			.detail_style							{font-size:10pt;font-weight:bold;text-align:center;}
			.detail_age	 							{font-size:10pt;font-weight:bold;text-align:center;}
			.detail_publisher						{font-size:10pt;color:666666;margin-top:30px;border-width:1px 0px 0px 0px;border-style:solid;border-color:999999;}

			.detail_caption					{text-align:right;padding:0px 10px 10px 10px;vertical-align:top;}
			.detail_value						{font-weight:bold;padding:0px 10px 10px 10px;}
			.detail_error						{font-weight:bold;padding:0px 10px 10px 10px;font-size:18pt;color:#F00}
			.large_input						{font-size:14pt;}

			.edit_table							{}
			.edit_caption						{padding:3px 5px 3px 5px;text-align:right;font-weight:bold;vertical-align:top;}
			.edit_caption span				{font-size:10pt;font-style:italic;}
			.edit_value							{padding:3px 5px 3px 5px;font-style:italic;font-size:8pt;}
			.edit_message						{padding:13px 5px 23px 5px;font-weight:bold;font-size:12pt;}
			.edit_static_data					{font-size:10pt;font-style:normal;}
			.edit_highlight					{background-color:FFFF00;}
			.edit_record_link				{background-color:EEFFEE;border:solid 1px 99CC99;}

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

			.iframe_lookup						{display:none;border:solid 1px #000;}

			.search_item_a					{padding:7px 5px 7px 5px;background-color:C0F0C0;}
			.search_item_b					{padding:7px 5px 7px 5px;}
			.search_item_h					{padding:7px 5px 7px 5px;background-color:66CC66;}
			.search_series						{font-size:10pt;font-weight:bold;}
			.search_author					{font-size:10pt;font-style:italic;}
			.search_results					{text-align:center;font-size:16pt;padding-bottom:5px;padding-top:5px;}
			.search_listing						{border-style:solid;border-width:1px;border-color:009900;margin-bottom:25px;}
			.search_call_number				{font-size:12pt;font-weight:bold;}
			.search_location					{font-size:8pt;font-weight:bold;}
			.search_category					{font-size:10pt;font-weight:bold;}
			.search_highlight					{background-color:FFFF00;}
			.search_parallel_title				{font-size:10pt;font-weight:bold;}
			.search_checkbox					{padding-right:6px;}
			.search_image					{padding-right:6px;}
			.search_image img					{height:70px;border:0px;}
			.search_icon						{padding-right:6px;}
			.search_icon img					{height:48px;width:48px;border:0px;}
			.search_actions						{padding:5px 5px 5px 5px;background-color:339933;display:none;}
			.search_compilation_title			{font-size:10pt;font-weight:bold;padding-top:4px;padding-left:10px;}
			.search_compilation_pages		{font-size:8pt;font-weight:normal;}
			.search_compilation_author		{font-size:8pt;font-weight:normal;padding-left:20px;}
			.search_status_available			{background-color:006600;padding:0px 3px 0px 3px;font-weight:normal;font-size:8pt;color:FFFFFF;display:inline;}
			.search_status_restricted		{background-color:996600;padding:0px 3px 0px 3px;font-weight:normal;font-size:8pt;color:FFFFFF;display:inline;}
			.search_status_checkedout		{background-color:CC0000;padding:0px 3px 0px 3px;font-weight:normal;font-size:8pt;color:FFFFFF;display:inline;}
			.search_status_lost					{background-color:000000;padding:0px 3px 0px 3px;font-weight:normal;font-size:8pt;color:FFFFFF;display:inline;}

			.hidden_search_input		{position:absolute;top:-50px;left:0px;}

			.search_input					{font-size:12pt;}
			.search_button				{font-size:12pt;}
		</style>
		<script language="javascript">
			function submit_search()
			{
				document.location.href = "<?= $db->url_root; ?>/search.php/query/" + document.getElementById('query_input').value;
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
					if($db->home_reset > 0)
					{
						?>
						if(signout_counter > <?= $db->home_reset; ?>)
						{
							document.location.href = "<?= $db->url_root; ?>/index.php";
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
	<body>
		<div class="container">
			<table class="page" cellspacing="0" cellpadding="0" border="0" align="center">
				<tr>
					<td valign="top">
					<?
						if(! $standalone_window)
						{
							$sql = "
								SELECT
									*
								FROM
									`" . $db->table_library_checkout . "`
								WHERE
									`" . $db->field_account_ID . "`='" . $accounts->account_record->ID . "'
									AND `in_datetime`='" . $db->blank_datetime . "'
							";
							$result = mysql_query($sql, $mysql->link);
							$items_checked_out = mysql_num_rows($result);

							if(! $db->lookup_iframe)
							{
								include_once($db->common_dir . "/nav_top.php");
								include_once($db->common_dir . "/nav_bottom.php");
 
								?>
								<div class="toolbar">
									<table cellspacing="0" cellpadding="0" border="0" width="100%">
										<tr>
											<td width="0%"><div class="application_logo"><nobr><a href="<?= $db->url_root; ?>/index.php"><img src="<?= $db->url_root; ?>/../common/images/imlogo.jpg" width="65px" height="50px" align="absmiddle">library</a></nobr></div></td>
											<td width="100%" style="padding-left:12px;">
												<nobr>
												<?
													if($db->show_search)
													{
														$_SESSION['last_searched_query'] = $search_requery;
														?>
														<input id="query_input" type="text" value="<?= htmlspecialchars($search_requery); ?>" onkeypress="if(this.value.length==0){query_entry_start=new Date();};if(event.keyCode==13){submit_search();};"> <button onclick="submit_search();">Search</button>
														<script language="javascript">
															document.getElementById('query_input').focus();
															document.getElementById('query_input').select();
														</script>
														<?
													}; // end if
												?>
												</nobr>
											</td>
										</tr>
									</table>
								</div>
								<div class="subbar">
									<table cellspacing="0" cellpadding="0" border="0" width="100%">
										<tr>
											<td width="0%" align="left" valign="top">
												<div class="subnavbar" style="text-align:left;">
													<nobr>
													<?
														if($db->current_sub_application == "index")
														{
															?>
															Home
															<?
														}
														else
														{
															?>
															<a href="<?= $db->url_root; ?>/index.php">Home</a>
															<?
														}; // end if

														?>
														&nbsp;
														<?

														if($db->current_sub_application == "stats")
														{
															?>
															Stats
															<?
														}
														else
														{
															?>
															<a href="<?= $db->url_root; ?>/stats.php">Stats</a>
															<?
														}; // end if

														if($accounts->account_record->librarian == "Y" || $accounts->account_record->admin == "Y")
														{
															?>
															&nbsp;
															<?
															if($db->current_sub_application == "kiosk")
															{
																?>
																Kiosk
																<?
															}
															else
															{
																?>
																<a href="<?= $db->url_root; ?>/kiosk.php">Kiosk</a>
																<?
															}; // end if
														}; // end if
													?>
													<?
														if($accounts->account_record->librarian == "Y" || $accounts->account_record->admin == "Y")
														{
															?>
															&nbsp;
															<?
															if($db->current_sub_application == "librarian")
															{
																?>
																Librarian
																<?
															}
															else
															{
																?>
																<a href="<?= $db->url_root; ?>/librarian.php">Librarian</a>
																<?
															}; // end if
														}; // end if
													?>
													</nobr>
												</div>
											</td>
											<td width="0%" align="right">
												<div class="subnavbar" style="text-align:right;">
													<nobr>
														<?
															if($accounts->account_record->librarian == "Y" || $accounts->account_record->admin == "Y")
															{
																if($db->current_sub_application == "settings")
																{
																	?>
																	Settings
																	<?
																}
																else
																{
																	?>
																	<a target="_blank" href="<?= $db->url_root; ?>/settings.php">Settings</a>
																	<?
																}; // end if
															}; // end if
															if($db->current_sub_application == "help")
															{
																?>
																&nbsp; Help
																<?
															}
															else
															{
																?>
																&nbsp; <a target="_blank" href="<?= $db->url_root; ?>/help.php">Help</a>
																<?
															}; // end if
														?>
													</nobr>
												</div>
											</td>
										</tr>
									</table>
								</div>
								<?
							}; // end if
						}; // end if
					?>
					<div class="content"><?

if($title != "")
{
	?><div class="page_title"><?= $title; ?><?
		if($subtitle != "")
		{
			?> <span class="page_subtitle">(<?= $subtitle; ?>)</span><?
		}; // end if
	?></div><?
}; // end if

?>