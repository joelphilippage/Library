<?

include_once(dirname(__FILE__) . "/first.php");


$barcode = urldecode($query[0]);


if(! $_SESSION['library_account_ID'])
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if(! $db->is_librarian_account)
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if

if($barcode != "")
{
	$sql = "
		UPDATE
			`" . $db->table_account . "`
		SET
			`library_barcode`=''
		WHERE
			`library_barcode`='" . mysql_escape_string($barcode) . "'
	";
	mysql_query($sql, $mysql->link);

	$db->home_reset = 4;

	include_once(dirname(__FILE__) . "/top.php");

	?><div class="dialog_title">Card Deactivated.</div><?

	include_once(dirname(__FILE__) . "/bottom.php");
	exit();
}
else
{
	include_once(dirname(__FILE__) . "/top.php");

	?>
	<script language="javascript">
		function submit_barcode()
		{
			if(document.getElementById('barcode_input').value.length == <?= $db->barcode_length; ?> && document.getElementById('barcode_input').value.substring(0, 1) == "<?= $db->barcode_account_prefix; ?>")
			{
				document.location.href = "<?= $db->url_root; ?>/deactivate_card.php/" + document.getElementById('barcode_input').value;
			}
			else
			{
				document.location.href = "<?= $db->url_root; ?>/search.php/query/" + document.getElementById('barcode_input').value;
			}; // end if
		}; // end function
	</script>
	<div class="dialog_instructions"><?= ucwords($db->entry_mode_caption_present_tense); ?> library card to deactivate.</div>
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