<?

include_once(dirname(__FILE__) . "/first.php");



$checkout_sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_checkout . "`
	WHERE
		`awards_applied`='N'
	ORDER BY
		`out_datetime` ASC
";
$checkout_result = mysql_query($checkout_sql, $mysql->link);

$not_applied = 0;

while($checkout_record = mysql_fetch_object($checkout_result))
{
	$account_sql = "
		SELECT
			*
		FROM
			`" . $db->table_account . "`
		WHERE
			`ID`='" . mysql_escape_string($checkout_record->account_ID) . "'
	";
	$account_result = mysql_query($account_sql, $mysql->link);
	$account_record = mysql_fetch_object($account_result);

	
	$item_sql = "
		SELECT
			*
		FROM
			`" . $db->table_library_item . "`
		WHERE
			`ID`='" . mysql_escape_string($checkout_record->item_ID) . "'
	";
	$item_result = mysql_query($item_sql, $mysql->link);
	if(mysql_num_rows($result) < 1)
	{
		exit("item not exist");
	}; // end if
	$item_record = mysql_fetch_object($item_result);

	$awards_points_earned = 0;


	if($item_record->{$db->field_library_series_ID} > 0)
	{
		$series_sql = "
			SELECT
				*
			FROM
				`" . $db->table_library_series . "`
			WHERE
				`ID`='" . $item_record->{$db->field_library_series_ID} . "'
				AND `enabled`='Y'
		";
		$series_result = mysql_query($series_sql, $mysql->link);
		$series_record = mysql_fetch_object($series_result);

		echo $series_record->title;
		if($item_record->series_number != 0)
		{
			echo " - #" . $item_record->series_number;
		}; // end if
		echo ": ";
	}; // end if

	echo $item_record->title . " (" . $checkout_record->ID . ")<BR>";


	$apply_done = FALSE;

	if($checkout_record->in_datetime != $db->blank_datetime)
	{
		$apply_done = TRUE;

		if(strtotime($checkout_record->out_datetime) + (60 * 60 * $db->settings->library_reading_awards_minimum_hours) < strtotime($checkout_record->in_datetime))
		{
			$awards_points_earned += $db->settings->library_reading_awards_return;
			echo "[read] ";
		}; // end if

		if($checkout_record->due_datetime != $db->blank_datetime)
		{
			if(strtotime($checkout_record->due_datetime) < strtotime($checkout_record->in_datetime))
			{
				$awards_points_earned += $db->settings->library_reading_awards_overdue;
				echo "[overdue] ";
			}; // end if
		}
		else
		{
			
		}; // end if
	}; // end if

	if($checkout_record->lost_datetime != $db->blank_datetime)
	{
		$awards_points_earned += $db->settings->library_reading_awards_lost;
		echo "[lost] ";
	}; // end if

	if($checkout_record->paid_datetime != $db->blank_datetime)
	{
		$awards_points_earned += $db->settings->library_reading_awards_paid;
		echo "[paid] ";
	}; // end if


	if($apply_done)
	{
		$sql = "
			UPDATE
				`" . $db->table_library_checkout . "`
			SET
				`awards_applied`='Y'
			WHERE
				`ID`='" . $checkout_record->ID . "'
		";
		//echo $sql ."<br>";
		mysql_query($sql, $mysql->link);

		$new_awards_total = $account_record->library_award_points + $awards_points_earned;

		$sql = "
			UPDATE
				`" . $db->table_account . "`
			SET
				`library_award_points`='" . mysql_escape_string($new_awards_total) . "'
			WHERE
				`ID`='" . $account_record->ID . "'
		";
		mysql_query($sql, $mysql->link);

		echo "[done] ";
	}
	else
	{
		$not_applied++;
	}; // end if


	echo $awards_points_earned . "<br><br>";

	//echo $checkout_record->ID . "<BR>";
}; // end while
echo $not_applied;
exit();


?>