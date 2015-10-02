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
		header("location:" . $db->url_root . "/index.php");
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

	$db->home_reset = 17;

	$db->show_search = FALSE;

	if($invalid_pin)
	{
		$db->kiosk_sound = $db->common_sound_url . "/failure.mp3";
	}
	else
	{
		//$db->kiosk_sound = $db->common_sound_url . "/success.mp3";
	}; // end if

	$db->show_search = FALSE;

	include_once(dirname(__FILE__) . "/top.php");

	if($invalid_pin)
	{
		?><div class="action_bar"><span class="action_status_failure">Invalid.  Try again.</span></div><?
	}; // end if

	?>
	<script language="javascript">
		function key_pin_filter(state)
		{
			num = '';
			switch(event.keyCode)
			{
				case 96:
					num = "0";
					break;
				case 97:
					num = "1";
					break;
				case 98:
					num = "2";
					break;
				case 99:
					num = "3";
					break;
				case 100:
					num = "4";
					break;
				case 101:
					num = "5";
					break;
				case 102:
					num = "6";
					break;
				case 103:
					num = "7";
					break;
				case 104:
					num = "8";
					break;
				case 105:
					num = "9";
					break;
				case 45:
					num = "0";
					break;
				case 35:
					num = "1";
					break;
				case 40:
					num = "2";
					break;
				case 34:
					num = "3";
					break;
				case 37:
					num = "4";
					break;
				case 12:
					num = "5";
					break;
				case 39:
					num = "6";
					break;
				case 36:
					num = "7";
					break;
				case 38:
					num = "8";
					break;
				case 33:
					num = "9";
					break;
				case 13:
					return;
			}; // end switch
			if(num != '' && state == 'up')
			{
				document.getElementById('pin_input').value += num;
			}; // end if
			event.cancelBubble = true;
			event.returnValue = false;
			return true;
		}; // end function
	</script>
	<div class="action_bar"><span class="action_instructions">Hello, <?= preg_replace("/\s.*/", "", $record->name); ?>!</span></div>
	<div class="action_bar"><span class="action_instructions">Enter PIN</span></div>
	<form id="signin_form" action="" method="post" onsubmit="show_wait_message();">
	<div class="dialog_input"><input id="pin_input" name="pin" type="password" value="" size="<?= $db->pin_length; ?>" maxlength="<?= $db->barcode_length + $db->pin_length; ?>" onkeyup="key_pin_filter('up');" onkeydown="key_pin_filter('down');"></div>
	</form>
	<?

	include_once(dirname(__FILE__) . "/bottom.php");
}
else
{
	exit_error("INVALID LIBRARY CARD!", "The library card you are using is not activated.");
}; // end if


?>