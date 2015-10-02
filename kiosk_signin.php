<?

include_once(dirname(__FILE__) . "/first.php");

$_SESSION['library_account_ID'] = 0;

$barcode = urldecode($query[0]);

$invalid_pin = FALSE;

if(isset($_POST['pin']))
{
	if(strlen($_POST['pin']) == $db->barcode_length)
	{
		header("location:" . $db->url_root . "/search.php/query/" . $_POST['pin']);
		exit();
	}; // end if

	$bypass_pin = FALSE;
	if(strlen($_POST['pin']) == $db->barcode_length + $db->pin_length)
	{
		$admin_pin = substr($_POST['pin'], 0, $db->pin_length);
		$admin_barcode = substr($_POST['pin'], $db->pin_length, $db->barcode_length);
		$sql = "
			SELECT
				*
			FROM
				`" . $db->table_account . "`
			WHERE
				`library_barcode`='" . $admin_barcode . "'
				AND `pin`='" . $admin_pin . "'
				AND (`admin`='Y' || `librarian`='Y')
				AND `enabled`='Y'
		";
		$result = mysql_query($sql, $mysql->link);
		if(mysql_num_rows($result) > 0)
		{
			$bypass_pin = TRUE;
		}; // end if
	}; // end if

	$sql = "
		SELECT
			*
		FROM
			`" . $db->table_account . "`
		WHERE
			`library_barcode`='" . $barcode . "'
			" . ($bypass_pin ? "" : "AND `pin`='" . $_POST['pin'] . "'") . "
			AND `enabled`='Y'
	";
	$result = mysql_query($sql, $mysql->link);
	if(mysql_num_rows($result) > 0)
	{
		$record = mysql_fetch_object($result);
		$_SESSION['library_account_ID'] = $record->ID;
		?>
		<script language="javascript">
			if(parent.kiosk)
			{
				parent.set_account(<?= $record->ID; ?>, <?= $record->library_barcode; ?>);
				document.location.href = "<?= $db->url_root; ?>/kiosk_index.php";
				parent.reset_activity();
			}; // end if
		</script>
		<?
		exit();
	}
	else
	{
		$invalid_pin = TRUE;
	}; // end if
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
if(mysql_num_rows($result) > 0)
{
	$record = mysql_fetch_object($result);

	//$db->home_reset = 17;

	$db->show_search = FALSE;

	$db->kiosk_message = "Hello, " . preg_replace("/\s.*/", "", $record->name) . "! &nbsp;";

	if($invalid_pin)
	{
		$db->kiosk_sound = $db->common_sound_url . "/failure.mp3";
	}
	else
	{
		$db->kiosk_sound = $db->common_sound_url . "/diamond.mp3";
	}; // end if

	$db->show_search = FALSE;

	include_once(dirname(__FILE__) . "/kiosk_top.php");

	if($invalid_pin)
	{
		?><div class="action_bar"><span class="action_status_failure">Invalid.  Try again.</span></div><?
	}; // end if

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
	<div class="action_bar"><span class="action_instructions">Enter PIN</span></div>
	<form id="signin_form" action="" method="post" onsubmit="show_wait_message();">
	<div style="text-align:center;"><input id="pin_input" class="kiosk_pin_input" name="pin" type="password" value="" size="<?= $db->pin_length; ?>" maxlength="<?= $db->barcode_length + $db->pin_length; ?>" onkeyup="key_pin_filter('up');" onkeydown="key_pin_filter('down');"></div>
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
	</form>
	<script language="javascript">
		function key_watch()
		{
			document.getElementById('pin_input').value = parent.sub_input;
			if(parent.sub_submit)
			{
				parent.sub_submit = false;
				document.getElementById('signin_form').submit();
			}; // end if
		}; // end function
		setInterval("key_watch()", 100);
		parent.reset_activity();
		parent.pending_account_barcode = "<?= $barcode; ?>";
	</script>
	<?

	include_once(dirname(__FILE__) . "/kiosk_bottom.php");
}
else
{
	exit_error("INVALID LIBRARY CARD!", "The library card you are using is not activated.");
}; // end if


?>