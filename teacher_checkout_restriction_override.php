<?

include_once(dirname(__FILE__) . "/first.php");


$barcode = urldecode($query[0]);


if(! $kiosk_account_record->ID && ! $accounts->account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if($accounts->account_record->teacher != "Y" && $accounts->account_record->librarian != "Y" && $accounts->account_record->admin != "Y")
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if

if($barcode != "")
{
	$sql = "
		SELECT
			*
		FROM
			`" . $db->table_account . "`
		WHERE
			`library_barcode`='" . $barcode . "'
			AND `enabled`='Y'
	";
	$result = mysql_query($sql, $mysql->link);
	if(mysql_num_rows($result) < 1)
	{
		exit_error("Card Not Active", "The card you have " . $db->entry_mode_caption_past_tense . " is not active.");
	}; // end if
	$record = mysql_fetch_object($result);

	$sql = "
		UPDATE
			`" . $db->table_account . "`
		SET
			 `library_allow_overdue_checkout`=1
		WHERE
			`ID`='" . $record->ID . "'
			AND `enabled`='Y'
	";
	mysql_query($sql, $mysql->link);
	?>
	<script language="javascript"><!--
		window.close();
	//--></script>
	<?
	exit();

}
else
{
	$db->show_search = FALSE;

	include_once(dirname(__FILE__) . "/top.php");

	?>
	<script language="javascript">
		function submit_barcode()
		{
			if(document.getElementById('barcode_input').value.length == <?= $db->barcode_length; ?> && document.getElementById('barcode_input').value.substring(0, 1) == "<?= $db->barcode_account_prefix; ?>")
			{
				document.location.href = "<?= $db->url_root; ?>/teacher_checkout_restriction_override.php/" + document.getElementById('barcode_input').value;
			}
			else
			{
				document.location.href = "<?= $db->url_root; ?>/search.php/query/" + document.getElementById('barcode_input').value;
			}; // end if
		}; // end function
	</script>
	<div class="dialog_instructions">To override checkout restriction</div>
	<div class="dialog_title"><?= ucwords($db->entry_mode_caption_present_tense); ?> library card...</div>
	<div class="dialog_input"><input id="barcode_input" name="barcode" type="text" value="" size="<?= $db->barcode_length; ?>" maxlength="<?= $db->barcode_length; ?>" onkeypress="if(event.keyCode==13){submit_barcode();};"></div>
	<script language="javascript">
		document.getElementById('barcode_input').focus();
		document.getElementById('barcode_input').select();
	</script>
	<?
	if($accounts->computer_record->barcode_reader != "Y")
	{
		?>
		<div class="dialog_instructions">and press Enter</div>
		<?
	}; // end if

	include_once(dirname(__FILE__) . "/bottom.php");
}; // end if


?>