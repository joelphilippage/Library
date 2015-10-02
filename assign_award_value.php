<?

include_once(dirname(__FILE__) . "/first.php");


$item_value = urldecode($query[0]);
$barcode = urldecode($query[1]);


if(! $kiosk_account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if($kiosk_account_record->librarian != "Y")
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if

$db->show_search = FALSE;

if($barcode != "")
{
	$award_sql = "
		SELECT
			*
		FROM
			`" . $db->table_library_award . "`
		WHERE
			`barcode`='" . mysql_escape_string($barcode) . "'
			AND `enabled`='Y'
	";
	$award_result = mysql_query($award_sql, $mysql->link);

	if(mysql_num_rows($award_result) > 0)
	{
		$award_sql = "
			UPDATE
				`" . $db->table_library_award . "`
			SET
				`value`='" . mysql_escape_string($item_value) . "',
				`" . $db->field_account_ID . "`='0'
			WHERE
				`barcode`='" . mysql_escape_string($barcode) . "'
		";
		mysql_query($award_sql, $mysql->link);
	}
	else
	{
		$award_sql = "
			INSERT INTO
				`" . $db->table_library_award . "`
			SET
				`barcode`='" . mysql_escape_string($barcode) . "',
				`value`='" . mysql_escape_string($item_value) . "',
				`" . $db->field_account_ID . "`='0'
		";
		mysql_query($award_sql, $mysql->link);
	}; // end if

	$db->settings->library_last_award_value = $value;
	save_db_settings();
	
	$db->kiosk_sound = $db->common_sound_url . "/kling.mp3";

}; // end if

if($item_value != "")
{
	include_once(dirname(__FILE__) . "/kiosk_top.php");
	?>
	<div class="dialog_instructions"><?= ucwords($db->entry_mode_caption_present_tense); ?> award items to assign <?= $item_value; ?> points to.</div>
	<?
	$kiosk_mode = $db->barcode_assign_award_value;
	$kiosk_value = $item_value;
	include_once(dirname(__FILE__) . "/kiosk_bottom.php");
	exit();
}
else
{
	$db->activity_timeout = 15;

	include_once(dirname(__FILE__) . "/kiosk_top.php");

	?>
	<script language="javascript">
		function push_key(key)
		{
			key.style.borderStyle = "inset";
			setTimeout("release_key(\"" + key.id + "\")", 300);
		}; // end function
		function release_key(key_id)
		{
			document.getElementById(key_id).style.borderStyle = "outset";
		}; // end function
	</script>
	<div class="dialog_instructions">Enter value to assign:</div>
	<div class="dialog_input break"><input id="item_value_input" name="item_value_input" type="text" value="<?= $db->settings->library_last_award_value; ?>" size="3" onkeypress="if(event.keyCode==13){submit_value();};" autocomplete="off"></div>
	<div style="text-align:center;margin-top:10px;">
		<table class="kiosk_pin_keypad" cellspacing="0" cellpadding="5" border="0" align="center">
			<tr>
				<td valign="middle"><div id="key_7" class="kiosk_pin_key" onclick="push_key(this);parent.key_press(103);">7</div></td>
				<td valign="middle"><div id="key_8" class="kiosk_pin_key" onclick="push_key(this);parent.key_press(104);">8</div></td>
				<td valign="middle"><div id="key_9" class="kiosk_pin_key" onclick="push_key(this);parent.key_press(105);">9</div></td>
			</tr>
			<tr>
				<td valign="middle"><div id="key_4" class="kiosk_pin_key" onclick="push_key(this);parent.key_press(100);">4</div></td>
				<td valign="middle"><div id="key_5" class="kiosk_pin_key" onclick="push_key(this);parent.key_press(101);">5</div></td>
				<td valign="middle"><div id="key_6" class="kiosk_pin_key" onclick="push_key(this);parent.key_press(102);">6</div></td>
			</tr>
			<tr>
				<td valign="middle"><div id="key_1" class="kiosk_pin_key" onclick="push_key(this);parent.key_press(97);">1</div></td>
				<td valign="middle"><div id="key_2" class="kiosk_pin_key" onclick="push_key(this);parent.key_press(98);">2</div></td>
				<td valign="middle"><div id="key_3" class="kiosk_pin_key" onclick="push_key(this);parent.key_press(99);">3</div></td>
			</tr>
			<tr>
				<td valign="middle"><div id="key_0" class="kiosk_pin_key" onclick="push_key(this);parent.key_press(96);">0</div></td>
				<td valign="middle" colspan="2"><div id="key_enter" class="kiosk_pin_key_enter" onclick="push_key(this);parent.key_press(13);">ENTER</div></td>
			</tr>
		</table>
	</div>
	<script language="javascript">
		function key_watch()
		{
			document.getElementById('item_value_input').value = parent.sub_input;
			if(parent.sub_submit)
			{
				parent.sub_submit = false;
				document.location.href = "<?= $db->url_root; ?>/assign_award_value.php/" + document.getElementById('item_value_input').value;
			}; // end if
		}; // end function
		setInterval("key_watch()", 100);
		parent.reset_activity();
		parent.pending_account_barcode = "<?= $barcode; ?>";
	</script>
	<?

	$kiosk_mode = $db->barcode_assign_award_value;
	$kiosk_value = "";

	include_once(dirname(__FILE__) . "/kiosk_bottom.php");

	exit();
}; // end if


?>