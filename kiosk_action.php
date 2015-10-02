<?

include_once(dirname(__FILE__) . "/first.php");


$action_value = urldecode($query[0]);

switch($action_value)
{
	case "goto_home":
		header("location:" . $db->url_root . "/kiosk_index.php");
		exit();
		break;
	default:
		if(strlen($action_value) == $db->barcode_length && preg_replace("/\d/", "", $action_value) == "")
		{
			switch(substr($action_value, 0, 1))
			{
				case $db->barcode_account_prefix:
					?>
					<script language="javascript">
						if(parent.kiosk)
						{
							parent.reset_activity();
							document.location.href = "<?= $db->url_root; ?>/kiosk_signin.php/<?= $action_value; ?>";
						}; // end if
					</script>
					<?
					exit();
					if($_SESSION['library_account_ID'] > 0)
					{
						if($accounts->library_account_record->library_barcode == $action_value)
						{
							header("location:" . $db->url_root . "/signout.php/" . $action_value);
							exit();
						}
						else
						{
							header("location:" . $db->url_root . "/signin.php/" . $action_value);
							exit();
						}; // end if
					}
					else
					{
						header("location:" . $db->url_root . "/signin.php/" . $action_value);
						exit();
					}; // end if
					break;
				case $db->barcode_action_prefix:
					switch($action_value)
					{
						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

						case $db->barcode_view_actions:
							?>
							<script language="javascript">
								window.open("<?= $db->url_root; ?>/generate_barcode_labels.php/action", "library_actions");
								document.location.replace("<?= $db->url_root; ?>/index.php");
							</script>
							<?
							exit();
							break;
//						case $db->barcode_activate_library_card: header("location:" . $db->url_root . "/activate_card.php"); exit(); break;
						case $db->barcode_reset_pin_number: header("location:" . $db->url_root . "/reset_pin.php"); exit();  break;
//						case $db->barcode_deactivate_library_card: header("location:" . $db->url_root . "/deactivate_card.php"); exit();  break;
//						case $db->barcode_set_shelving_category: header("location:" . $db->url_root . "/set_shelving_category.php"); exit();  break;
//						case $db->barcode_checked_out_items: header("location:" . $db->url_root . "/search.php/special/checkedout"); exit();  break;
//						case $db->barcode_textbook_checkout: header("location:" . $db->url_root . "/item_checkout.php/textbook"); exit();  break;
						case $db->barcode_queue_call_number: header("location:" . $db->url_root . "/queue_call_number.php"); exit();  break;
						case $db->barcode_assign_award_value: header("location:" . $db->url_root . "/assign_award_value.php"); exit();  break;

						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						default:
							exit_error("Unknown Action", "The action you have " . $db->entry_mode_caption_past_tense . " is not recognized.");
							break;
					}; // end switch
					break;
				case $db->barcode_award_prefix:
					?>
					<script language="javascript">
						switch(parent.kiosk_mode)
						{
							case "<?= $db->barcode_assign_award_value; ?>":
								document.location.href = "<?= $db->url_root; ?>/assign_award_value.php/" + parent.kiosk_value + "/<?= $action_value; ?>";
								break;
							case "general":
							default:
								document.location.href = "<?= $db->url_root; ?>/award_process.php/<?= $action_value; ?>";
								break;
						}; // end switch
					</script>
					<?
					exit();
					break;
				case $db->barcode_item_prefix:
					?>
					<script language="javascript">
						switch(parent.kiosk_mode)
						{
							case "<?= $db->barcode_queue_call_number; ?>":
								document.location.href = "<?= $db->url_root; ?>/queue_call_number.php/<?= $action_value; ?>";
								break;
							case "general":
							default:
								<?
									if(! $kiosk_account_record->ID)
									{
										?>
										document.location.href = "<?= $db->url_root; ?>/item_return.php/return/<?= $action_value; ?>";
										<?
									}
									else
									{
										?>
										document.location.href = "<?= $db->url_root; ?>/item_checkout.php/auto/<?= $action_value; ?>";
										<?
									}; // end if
								?>
								break;
						}; // end switch
					</script>
					<?
					exit();
					break;
				case $db->barcode_scan_prefix:
					header("location:" . $db->url_root . "/link_cover_scans.php/" . $action_value);
					exit();
					break;
			}; // end switch
		}; // end if

		exit_error("Invalid", "Try again.");
		break;
}; // end switch

?>