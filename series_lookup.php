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

	if(trim($_POST['title']) == "")
	{
		$errors['title'] = "Enter Series Title";
	}; // end if

	if(count($errors) < 1)
	{
		$sql = "
			INSERT INTO
				`" . $db->table_library_series . "`
			SET
				`title`='" . mysql_escape_string(stripslashes($_POST['title'])) . "',
				`enabled`='Y'
		";
		if(mysql_query($sql, $mysql->link))
		{
			$new_series_id = mysql_insert_id();

			include_once(dirname(__FILE__) . "/top.php");
			?>
			<script language="javascript">
				parent.set_series('<?= $new_series_id; ?>', '<?= addslashes(htmlspecialchars(stripslashes($_POST['title']))); ?>');
				document.location.replace("series_lookup.php");
			</script>
			<?
			include_once(dirname(__FILE__) . "/bottom.php");
			exit();
		}; // end if

		$errors['general'] = "Unknown Error Adding Series";
	}; // end if
}; // end if


include_once(dirname(__FILE__) . "/top.php");

?>
<?

$sections = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");

$auto_query = FALSE;
if($_POST['query'] != "")
{
	$_POST['title'] = $_POST['query'];
	$db->settings->library_series_query = $_POST['query'];
	save_db_settings();
}
else
{
	if($db->settings->library_series_query != "")
	{
		$_POST['query'] = $db->settings->library_series_query;
		$auto_query = TRUE;
	}; // end if
}; // end if


?>
<form method="POST" action="">
	<input id="lookup_query_input" type="text" name="query" value="<?= htmlspecialchars(stripslashes($_POST['query'])); ?>"><input type="submit" value="Search"> <a href="series_lookup.php/*">Show All</a>
</form>
<?

?>
<form method="POST" action="">
	<table cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td class="small"><nobr><b>New Series: &nbsp;</b></nobr></td>
			<td><nobr><input id="title" name="title" type="text" value="<?= htmlspecialchars(stripslashes($_POST['title'])); ?>" size="42"></nobr></td>
			<td><input type="submit" name="action" value="<?= $add_action; ?>"></td>
			<?
				if($_POST['query'] != "" && ! $auto_query)
				{
					?>
					<script language="javascript">
						if(! parent.nofocus)
						{
							document.getElementById('title').focus();
						}; // end if
					</script>
					<?
				}; // end if
			?>
		</tr>
		<tr>
			<td></td>
			<td class="tiny"><nobr><i>Title</i></nobr></td>
			<td></td>
		</tr>
	</table>
</form>
<?

if($show == "*" || $_POST['query'] != "")
{
	?><div style="margin-bottom:10px;">Jump to: <?
	foreach($sections as $section)
	{
		?><a href="#<?= $section; ?>"><?= $section; ?></a> <?
	}; // end foreach
	?></div><?

	$sql = "
		SELECT
			*
		FROM
			`" . $db->table_library_series . "`
		WHERE
			`enabled`='Y'
			" . ($_POST['query'] != "" ? "AND `title` LIKE '%" . mysql_escape_string($_POST['query']) . "%'" : "") . "
		ORDER BY
			`title`
	";
	$result = mysql_query($sql, $mysql->link);
	if(mysql_num_rows($result) > 0)
	{
		$last_letter = "";
		while($record = mysql_fetch_object($result))
		{
			$this_letter = strtoupper(substr($record->title, 0, 1));
			if($this_letter != $last_letter)
			{
				?>
				<h3 id="<?= $this_letter; ?>">- <?= $this_letter; ?> -</h3>
				<?
			}; // end if
			?><div><nobr><a href="javascript:" onclick="parent.set_series('<?= $record->ID; ?>', '<?= addslashes(htmlspecialchars($record->title)); ?>');"><?= $record->title; ?></a></nobr></div><?
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