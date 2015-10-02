<?

include_once(dirname(__FILE__) . "/first.php");


$show = urldecode($query[0]);

if(! $accounts->account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if($accounts->account_record->librarian != "Y" && $accounts->account_record->admin != "Y")
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if

$db->lookup_iframe = TRUE;


$add_action = "Add";

if($_POST['action'] == $add_action)
{
	$errors = array();

	if(trim($_POST['last_name']) == "")
	{
		$errors['last_name'] = "Enter Last Name";
	}; // end if

	if(count($errors) < 1)
	{
		$sql = "
			INSERT INTO
				`" . $db->table_author . "`
			SET
				`last_name`='" . mysql_escape_string(stripslashes($_POST['last_name'])) . "',
				`first_name`='" . mysql_escape_string(stripslashes($_POST['first_name'])) . "',
				`middle_name`='" . mysql_escape_string(stripslashes($_POST['middle_name'])) . "',
				`enabled`='Y'
		";
		if(mysql_query($sql, $mysql->link))
		{
			$new_author_id = mysql_insert_id();

			$author_full_name = $_POST['last_name'] . (strlen($_POST['last_name']) == 1 ? "." : "") . ($_POST['first_name'] != "" ? ", " . $_POST['first_name'] . (strlen($_POST['first_name']) == 1 ? "." : "") : "") . ($_POST['middle_name'] != "" ? " " . $_POST['middle_name'] . (strlen($_POST['middle_name']) == 1 ? "." : "")  : "");
			
			include_once(dirname(__FILE__) . "/top.php");
			?>
			<script language="javascript">
				parent.add_author("<?= $new_author_id; ?>", "<?= addslashes(htmlspecialchars(stripslashes($author_full_name))); ?>");
				parent.nofocus = false;
				parent.reset_author_frame();
			</script>
			<?
			include_once(dirname(__FILE__) . "/bottom.php");
			exit();
		}; // end if

		$errors['general'] = "Unknown Error Adding Author";
	}; // end if
}; // end if


include_once(dirname(__FILE__) . "/top.php");

$auto_query = FALSE;
if($_POST['query'] != "")
{
	$_POST['title'] = $_POST['query'];
	$db->settings->library_author_query = $_POST['query'];
	save_db_settings();
}
else
{
	if($db->settings->library_author_query != "")
	{
		$_POST['query'] = $db->settings->library_author_query;
		$auto_query = TRUE;
	}; // end if
}; // end if


?>
<form method="POST" action="">
	<input id="lookup_query_input" type="text" name="query" value="<?= htmlspecialchars(stripslashes($_POST['query'])); ?>"><input type="submit" value="Search"> <a href="author_lookup.php/*">Show All</a>
</form>
<?

if($_POST['query'] != "")
{
	$names = explode(" ", $_POST['query']);
	switch(count($names))
	{
		case 0:
			break;
		case 1:
			$_POST['last_name'] = $names[0];
			break;
		case 2:
			$_POST['last_name'] = $names[1];
			$_POST['first_name'] = $names[0];
			break;
		case 3:
			$_POST['last_name'] = $names[2];
			$_POST['first_name'] = $names[0];
			$_POST['middle_name'] = $names[1];
			break;
	}; // end switch
}; // end if

?>
<form method="POST" action="">
	<table cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td class="small"><nobr><b>New Author: &nbsp;</b></nobr></td>
			<td><nobr><input id="last_name" name="last_name" type="text" value="<?= htmlspecialchars(stripslashes($_POST['last_name'])); ?>" size="12">,&nbsp;</nobr></td>
			<td><nobr><input id="first_name" name="first_name" type="text" value="<?= htmlspecialchars(stripslashes($_POST['first_name'])); ?>" size="14"></nobr>&nbsp;</td>
			<td><nobr><input id="middle_name" name="middle_name" type="text" value="<?= htmlspecialchars(stripslashes($_POST['middle_name'])); ?>" size="10"></nobr></td>
			<td><input id="action" type="submit" name="action" value="<?= $add_action; ?>"></td>
			<?
				if($_POST['query'] != "" && ! $auto_query)
				{
					$names = explode(" ", $_POST['query']);
					?>
					<script language="javascript">
						if(! parent.nofocus)
						{
							<?
								switch(count($names))
								{
									case 0:
										break;
									case 1:
										?>
										document.getElementById('first_name').focus();
										<?
										break;
									case 2:
										?>
										document.getElementById('middle_name').focus();
										<?
										break;
									case 3:
										?>
										document.getElementById('action').focus();
										<?
										break;
								}; // end switch
							?>
						}; // end if
					</script>
					<?
				}; // end if
			?>
		</tr>
		<tr>
			<td></td>
			<td class="tiny"><nobr><i>Last Name</i></nobr></td>
			<td class="tiny"><nobr><i>First Name</i></nobr></td>
			<td class="tiny"><nobr><i>Middle Name</i></nobr></td>
			<td></td>
		</tr>
	</table>
</form>
<?

if($show == "*" || $_POST['query'] != "")
{
	$sections = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");

	?><div style="margin-bottom:10px;">Jump to: <?
	foreach($sections as $section)
	{
		?><a href="#<?= $section; ?>"><?= $section; ?></a> <?
	}; // end foreach
	?></div><?

	$sql = "
		SELECT
			`ID`,
			`last_name`,
			`first_name`,
			`middle_name`,
			CONCAT(`first_name`, ' ', `last_name`) AS 'name_first_last',
			CONCAT(`last_name`, ', ', `first_name`) AS 'name_last_first',
			CONCAT(`first_name`, ' ', `middle_name`, ' ', `last_name`) AS 'name_first_middle_last',
			CONCAT(`last_name`, ', ', `first_name`, ' ', `middle_name`) AS 'name_last_first_middle'
		FROM
			`" . $db->table_author . "`
		WHERE
			`enabled`='Y'
		" . ($_POST['query'] != "" ? "HAVING (`name_first_last` LIKE '%" . mysql_escape_string($_POST['query']) . "%' OR `name_last_first` LIKE '%" . mysql_escape_string($_POST['query']) . "%' OR `name_first_middle_last` LIKE '%" . mysql_escape_string($_POST['query']) . "%' OR `name_last_first_middle` LIKE '%" . mysql_escape_string($_POST['query']) . "%')" : "") . "
		ORDER BY
			`last_name`
	";
	$result = mysql_query($sql, $mysql->link);

	if(mysql_num_rows($result) > 0)
	{
		$last_letter = "";
		while($record = mysql_fetch_object($result))
		{
			$this_letter = strtoupper(substr($record->last_name, 0, 1));
			if($this_letter != $last_letter)
			{
				?>
				<h3 id="<?= $this_letter; ?>">- <?= $this_letter; ?> -</h3>
				<?
			}; // end if
			$author_full_name = $record->last_name . (strlen($record->last_name) == 1 ? "." : "") . ($record->first_name != "" ? ", " . $record->first_name . (strlen($record->first_name) == 1 ? "." : "") : "") . ($record->middle_name != "" ? " " . $record->middle_name . (strlen($record->middle_name) == 1 ? "." : "")  : "");
			?><div><nobr><a href="javascript:" onclick="parent.add_author('<?= $record->ID; ?>', '<?= preg_replace("/'/", "&amp;apos;", addslashes(htmlspecialchars($author_full_name))); ?>');"><?= $author_full_name; ?></a></nobr></div><?
			$last_letter = $this_letter;
		}; // end while
	}; // end if
}; // end if


?>
<script language="javascript">
	parent.iframes_loaded++;
</script>
<?

include_once(dirname(__FILE__) . "/bottom.php");


?>