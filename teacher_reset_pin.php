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
	$valid_pin = FALSE;
	if(isset($_POST['pin']) && strlen($_POST['pin']) == $db->pin_length)
	{
		if(preg_replace("/\d/", "", $_POST['pin']) == "")
		{
			$valid_pin = TRUE;
		}; // end if
	}; // end if

	if(isset($_POST['pin']) && $valid_pin)
	{
		if(strlen($_POST['pin']) == $db->barcode_length)
		{
			header("location:" . $db->url_root . "/search.php/query/" . $_POST['pin']);
			exit();
		}; // end if

		$sql = "
			UPDATE
				`" . $db->table_account . "`
			SET
				 `pin`='" . $_POST['pin'] . "'
			WHERE
				`library_barcode`='" . $barcode . "'
				AND `enabled`='Y'
		";
		mysql_query($sql, $mysql->link);
		header("location:" . $db->url_root . "/index.php");
		exit();
	}; // end if

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


	$db->show_search = FALSE;

	include_once(dirname(__FILE__) . "/top.php");

	?>
	<form id="signin_form" action="" method="post" onsubmit="show_wait_message();">
	<div class="dialog_instructions"><?= ($record->pin == "" ? "New" : "Reset"); ?> PIN for <?= $record->name; ?>.</div>
	<div class="dialog_title">Enter new PIN</div>
	<?
		if(isset($_POST['pin']) && ! $valid_pin)
		{
			?>
			<div class="dialog_instructions error">MUST BE 4 DIGITS</div>
			<?
		}; // end if
	?>
	<div class="dialog_input"><input id="pin_input" name="pin" type="password" value="" size="<?= $db->pin_length; ?>" maxlength="<?= $db->barcode_length; ?>"></div>
	<script language="javascript">
		document.getElementById('pin_input').focus();
		document.getElementById('pin_input').select();
	</script>
	<div class="dialog_instructions">and press Enter</div>
	</form>
	<?


	include_once(dirname(__FILE__) . "/bottom.php");
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
				document.location.href = "<?= $db->url_root; ?>/teacher_reset_pin.php/" + document.getElementById('barcode_input').value;
			}
			else
			{
				document.location.href = "<?= $db->url_root; ?>/search.php/query/" + document.getElementById('barcode_input').value;
			}; // end if
		}; // end function
	</script>
	<div class="dialog_instructions">To reset PIN</div>
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