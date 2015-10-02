<?

include_once(dirname(__FILE__) . "/first.php");

$_SESSION['library_account_ID'] = 0;

?>
<html>
	<head>
		<title>IM Library Kiosk</title>
		<style type="text/css">
			body			{margin:0px;}
		</style>
	</head>
	<body>
		<script language="javascript">
			var kiosk = true;
			var kiosk_mode = "<?= $kiosk_general_mode; ?>";
			var kiosk_value = "";
			var waiting = false;
			var account_ID = 0;
			var account_library_barcode = 0;
			var pending_account_barcode = 0;
			var start_input_time = 0;
			var sub_submit = false;
			var sub_input = "";
			var scan_input = "";
			var command_queue = new Array();
			var current_command = -1;
			function reset_activity()
			{
				pending_account_barcode = "";
				document.getElementById("scanner_input").value = "";
				sub_input = "";
				document.getElementById("sub_input").value = "";
			}; // end function
			function focus_cursor()
			{
				document.getElementById("status").value = "";
				document.getElementById("status").focus();
			}; // end function
			function key_track()
			{
				if(document.getElementById("scanner_input").value == "")
				{
					start_input_time = new Date();
				}; // end if
			}; // end function
			function set_account(ID, library_barcode)
			{
				account_ID = ID;
				account_library_barcode = library_barcode;
				document.getElementById("account_ID").value = account_ID + " :: " + account_library_barcode;
			}; // end function
			function queue_command(command)
			{
				command_queue[command_queue.length] = command;
			}; // end function
			function key_press(keyCode)
			{
				var character = 0;
				var scanner = false;
				switch(keyCode)
				{
					case 48: case 49: case 50: case 51: case 52: case 53: case 54: case 55: case 56: case 57: // 0-9
						character = keyCode;
						scanner = true;
						break;
					case 96: case 97: case 98: case 99: case 100: case 101: case 102: case 103: case 104: case 105: // 0-9
						character = keyCode - 48;
						break;
					case 45: character = 48; break; // 0
					case 35: character = 49; break; // 1
					case 40: character = 50; break; // 2
					case 34: character = 51; break; // 3
					case 37: character = 52; break; // 4
					case 12: character = 53; break; // 5
					case 39: character = 54; break; // 6
					case 36: character = 55; break; // 7
					case 38: character = 56; break; // 8
					case 33: character = 57; break; // 9
					case 9:
						end_input_time = new Date();
						input_time_length = end_input_time.valueOf() - start_input_time.valueOf();
						input_value = document.getElementById("scanner_input").value;
						if(input_time_length < 1300 && input_value.length == 8)
						{
							queue_command(input_value);
						}; // end if
						scan_input = "";
						document.getElementById("scanner_input").value = scan_input;
						focus_cursor();
						break;
					case 13:
						if(document.getElementById("sub_input").value != "")
						{
							sub_submit = true;
						}; // end if
						break;
					default:
						//alert(keyCode);
						break;
				}; // end switch
				if(character > 0)
				{
					if(scanner)
					{
						scan_input = scan_input + String.fromCharCode(character);
						document.getElementById("scanner_input").value = scan_input;
					}
					else
					{
						sub_input = sub_input + String.fromCharCode(character);
						document.getElementById("sub_input").value = sub_input;
					}; // end if
				}; // end if
			}; // end function

			function key_trap()
			{
				event.returnValue = false;
				event.cancelBubble = true;
				key_press(event.keyCode);
			}; // end function
			var command_timeout;
			var last_command = "";
			function execute_next_command()
			{
				d = new Date();
				clearTimeout(command_timeout);
				if(command_queue.length > current_command + 1)
				{
					current_command++;
					this_command = command_queue[current_command];
					if(this_command == account_library_barcode && kiosk_mode != "<?= $kiosk_general_mode; ?>")
					{
						document.getElementById("kiosk_action").src = "<?= $db->url_root; ?>/kiosk_index.php";
					}; // end if
					if(account_library_barcode != this_command && pending_account_barcode != this_command)
					{
						if(this_command == "10000000")
						{
							if(account_library_barcode != "" || pending_account_barcode != "")
							{
								waiting = false;
								account_library_barcode = 0;
								pending_account_barcode = 0;
								document.getElementById("kiosk_action").src = "<?= $db->url_root; ?>/kiosk_signout.php";
							}
							else
							{
								setTimeout("execute_next_command()", 100);
							}; // end if
						}
						else
						{
							if((this_command == last_command || this_command == kiosk_mode) && ! waiting)
							{
								setTimeout("execute_next_command()", 100);
							}
							else
							{
								waiting = false;
								pending_account_barcode = 0;
								sub_input = "";
								document.getElementById("kiosk_action").src = "<?= $db->url_root; ?>/kiosk_action.php/" + this_command;
							}; // end if
						}; // end if
						//command_timeout = setTimeout("ready_for_next_command()", 3000);
					}
					else
					{
						setTimeout("execute_next_command()", 100);
					}; // end switch
				}
				else
				{
					setTimeout("execute_next_command()", 100);
				}; // end if
				last_command = this_command;
			}; // end function
			function ready_for_next_command()
			{
				setTimeout("execute_next_command()", 100);
			}; // end function
			setTimeout("execute_next_command()", 100);
		</script>
		<iframe id="kiosk_action" src="<?= $db->url_root; ?>/kiosk_index.php" frameborder="0" style="position:absolute;width:100%;height:100%;"></iframe>
		<div id="input_container" style="position:absolute;top:-200px;left:-200px;">
			<input id="scanner_input" type="text" value="" size="40"> scanner_input<br>
			<input id="sub_input" type="text" value="" size="40"> sub_input<br>
			<input id="status" type="text" value="" size="40"> status<br>
			<input id="account_ID" type="text" value="" size="40"> account_ID<br>
		</div>
		<script language="javascript">
			window.onkeydown = key_track;
			window.onkeyup = key_trap;
			focus_cursor();
		</script>
	</body>
</html>
<?

?>