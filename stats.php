<?

include_once(dirname(__FILE__) . "/first.php");

$db->current_sub_application = "stats";


include_once(dirname(__FILE__) . "/top.php");

?><div class="title">Library Stats</div><?

$sql = "
	SELECT
		`" . $db->table_library_item . "`.`ID` as 'ID'
	FROM
		`" . $db->table_library_checkout . "`,
		`" . $db->table_library_item . "`,
		`" . $db->table_library_type . "`
	WHERE
		`in_datetime`!='" . $db->blank_datetime . "'
		AND `out_datetime`!='" . $db->blank_datetime . "'
		AND `" . $db->table_library_type . "`.`checkout_type`='read'
		AND `" . $db->table_library_checkout . "`.`" . $db->field_library_item_ID . "`=`" . $db->table_library_item . "`.`ID`
		AND `" . $db->table_library_item . "`.`" . $db->field_library_type_ID . "`=`" . $db->table_library_type . "`.`ID`
";
$result = mysql_query($sql, $mysql->link);
$books_read = mysql_num_rows($result);

echo "<b>" . number_format($books_read, 0, "", ",") . "</b> books read. &nbsp;";

$stat_sql = "
	SELECT
		SUM(`" . $db->table_library_item . "`.`length`) AS 'length'
	FROM
		`" . $db->table_library_checkout . "`,
		`" . $db->table_library_item . "`,
		`" . $db->table_library_type . "`
	WHERE
		`in_datetime`!='" . $db->blank_datetime . "'
		AND `out_datetime`!='" . $db->blank_datetime . "'
		AND `" . $db->table_library_type . "`.`checkout_type`='read'
		AND `" . $db->table_library_checkout . "`.`" . $db->field_library_item_ID . "`=`" . $db->table_library_item . "`.`ID`
		AND `" . $db->table_library_item . "`.`" . $db->field_library_type_ID . "`=`" . $db->table_library_type . "`.`ID`
";
$stat_result = mysql_query($stat_sql, $mysql->link);
$stat_record = mysql_fetch_object($stat_result);
$pages_read = $stat_record->length;

echo "<b>" . number_format($pages_read, 0, "", ",") . "</b> pages read.<br>";





$sql = "
	SELECT
		`" . $db->table_library_item . "`.`ID` as 'ID'
	FROM
		`" . $db->table_library_checkout . "`,
		`" . $db->table_library_item . "`,
		`" . $db->table_library_type . "`
	WHERE
		`in_datetime`!='" . $db->blank_datetime . "'
		AND `out_datetime`!='" . $db->blank_datetime . "'
		AND `" . $db->table_library_type . "`.`checkout_type`='watch'
		AND `" . $db->table_library_checkout . "`.`" . $db->field_library_item_ID . "`=`" . $db->table_library_item . "`.`ID`
		AND `" . $db->table_library_item . "`.`" . $db->field_library_type_ID . "`=`" . $db->table_library_type . "`.`ID`
";
$result = mysql_query($sql, $mysql->link);
$books_read = mysql_num_rows($result);

echo "<b>" . number_format($books_read, 0, "", ",") . "</b> videos watched. &nbsp;";

$stat_sql = "
	SELECT
		SUM(`" . $db->table_library_item . "`.`length`) AS 'length'
	FROM
		`" . $db->table_library_checkout . "`,
		`" . $db->table_library_item . "`,
		`" . $db->table_library_type . "`
	WHERE
		`in_datetime`!='" . $db->blank_datetime . "'
		AND `out_datetime`!='" . $db->blank_datetime . "'
		AND `" . $db->table_library_type . "`.`checkout_type`='watch'
		AND `" . $db->table_library_checkout . "`.`" . $db->field_library_item_ID . "`=`" . $db->table_library_item . "`.`ID`
		AND `" . $db->table_library_item . "`.`" . $db->field_library_type_ID . "`=`" . $db->table_library_type . "`.`ID`
";
$stat_result = mysql_query($stat_sql, $mysql->link);
$stat_record = mysql_fetch_object($stat_result);
$hours_watched = floor($stat_record->length / 60);

echo "<b>" . number_format($hours_watched, 0, "", ",") . "</b> hours watched.<br>";

		
		
		
$sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_checkout . "`
	WHERE
		`in_datetime`='" . $db->blank_datetime . "'
";
$result = mysql_query($sql, $mysql->link);
$items_checked_out = mysql_num_rows($result);

echo "<b>" . ($items_checked_out > 0 ? $items_checked_out : "No") . "</b> item" . ($items_checked_out != 1 ? "s" : "") . " currently checked out.<br>";

		
		
		
		
$sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_item . "`
	WHERE
		`barcode`!='0'
		AND `enabled`='Y'
";
$result = mysql_query($sql, $mysql->link);
$items_processed = mysql_num_rows($result);

echo "<b>" . number_format($items_processed, 0, "", ",") . "</b> processed. &nbsp;";

			
			
			
$sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_item . "`
	WHERE
		`" . $db->field_location_ID . "`='5'
		AND `enabled`='Y'
";
$result = mysql_query($sql, $mysql->link);
$in_storage = mysql_num_rows($result);

echo "<b>" . number_format($in_storage, 0, "", ",") . '</b> in storage. &nbsp;';

		
		
		
		
$sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_item . "`
	WHERE
		`" . $db->field_image_ID . "`>0
		AND `enabled`='Y'
";
$result = mysql_query($sql, $mysql->link);
$items_scanned = mysql_num_rows($result);

echo "<b>" . number_format($items_scanned, 0, "", ",") . "</b> covers scanned.<br>";

		


			
$sql = "
	SELECT
		SUM(`library_award_points`) AS 'earned'
	FROM
		`" . $db->table_account . "`
";
$result = mysql_query($sql, $mysql->link);
$points_earned_record = mysql_fetch_object($result);

echo "<b>" . number_format($points_earned_record->earned, 0, "", ",") . "</b> reading award points earned. &nbsp;";

$sql = "
	SELECT
		SUM(`library_award_spent`) AS 'spent'
	FROM
		`" . $db->table_account . "`
";
$result = mysql_query($sql, $mysql->link);
$points_spent_record = mysql_fetch_object($result);

echo "<b>" . number_format($points_spent_record->spent, 0, "", ",") . "</b> spent. &nbsp;";


include_once(dirname(__FILE__) . "/bottom.php");

?>