<?

include_once(dirname(__FILE__) . "/first.php");


$group_ID = urldecode($query[0]);


if(! $accounts->account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if


$checkout_entries = array();

switch($group_ID)
{
	case "overdue":
		$checkout_sql = "
			SELECT
				*
			FROM
				`" . $db->table_library_checkout . "`
			WHERE
				`in_datetime`='" . $db->blank_datetime . "'
				OR (`lost_datetime`!='" . $db->blank_datetime . "' AND `in_datetime`!='" . $db->blank_datetime . "' AND `paid_datetime`='" . $db->blank_datetime . "')
			ORDER BY
				`" . $db->field_account_ID . "`
		";
		$checkout_result = mysql_query($checkout_sql, $mysql->link);

		if(mysql_num_rows($checkout_result) > 0)
		{
			while($checkout_record = mysql_fetch_object($checkout_result))
			{

				if($checkout_record->due_datetime < date("Y-m-d H:i:s"))
				{
					$account_sql = "
						SELECT
							*
						FROM
							`" . $db->table_account . "`
						WHERE
							`ID`='" . $checkout_record->{$db->field_account_ID} . "'
					";
					$account_result = mysql_query($account_sql, $mysql->link);
					$account_record = mysql_fetch_object($account_result);

					$item_sql = "
						SELECT
							*
						FROM
							`" . $db->table_library_item . "`
						WHERE
							`ID`='" . $checkout_record->{$db->field_library_item_ID} . "'
					";
					$item_result = mysql_query($item_sql, $mysql->link);
					$item_record = mysql_fetch_object($item_result);

					$checkout_entry = (object) NULL;

					$checkout_entry->account_record = $account_record;
					$checkout_entry->item_record = $item_record;
					$checkout_entry->checkout_record = $checkout_record;

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
						$checkout_entry->series_record = $series_record;
					}; // end if

					$checkout_entries[] = $checkout_entry;
				}; // end if
			}; // end while
		}; // end if


		break;
	default:
		$group_sql = "
			SELECT
				*
			FROM
				`" . $db->table_group . "`
			WHERE
				`ID`='" . $group_ID . "'
				AND `enabled`='Y'
		";
		$group_result = mysql_query($group_sql, $mysql->link);
		if(mysql_num_rows($group_result) > 0)
		{
			$group_record = mysql_fetch_object($group_result);
		}
		else
		{
			exit_error("Unknown Group", "The group you specified does not exist.");
		}; // end if

		$group_members_sql = "
			SELECT
				`" . $db->table_group_member . "`.`account_ID` AS 'account_ID',
				`" . $db->table_account . "`.`name` AS 'name'
			FROM
				`" . $db->table_group_member . "`,
				`" . $db->table_account . "`
			WHERE
				`" . $db->table_group_member . "`.`" . $db->field_account_ID . "`=`" . $db->table_account . "`.`ID`
				AND `" . $db->table_group_member . "`.`" . $db->field_group_ID . "`='" . $group_ID . "'
				AND `" . $db->table_group_member . "`.`enabled`='Y'
		";
		$group_members_result = mysql_query($group_members_sql, $mysql->link);
		if(mysql_num_rows($group_members_result) > 0)
		{
			while($group_members_record = mysql_fetch_object($group_members_result))
			{
				$account_sql = "
					SELECT
						*
					FROM
						`" . $db->table_account . "`
					WHERE
						`ID`='" . $group_members_record->{$db->field_account_ID} . "'
				";
				$account_result = mysql_query($account_sql, $mysql->link);
				$account_record = mysql_fetch_object($account_result);

				$checkout_sql = "
					SELECT
						*
					FROM
						`" . $db->table_library_checkout . "`
					WHERE
						`" . $db->field_account_ID . "`='" . $group_members_record->{$db->field_account_ID} . "'
						AND
						(
							`in_datetime`='" . $db->blank_datetime . "'
							OR (`lost_datetime`!='" . $db->blank_datetime . "' AND `in_datetime`='" . $db->blank_datetime . "')
						)
						AND `paid_datetime`='" . $db->blank_datetime . "'
				";
				$checkout_result = mysql_query($checkout_sql, $mysql->link);

				if(mysql_num_rows($checkout_result) > 0)
				{
					while($checkout_record = mysql_fetch_object($checkout_result))
					{
						$item_sql = "
							SELECT
								*
							FROM
								`" . $db->table_library_item . "`
							WHERE
								`ID`='" . $checkout_record->item_ID . "'
						";
						$item_result = mysql_query($item_sql, $mysql->link);
						$item_record = mysql_fetch_object($item_result);

						$checkout_entry = (object) NULL;

						$checkout_entry->account_record = $account_record;
						$checkout_entry->item_record = $item_record;


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
							$checkout_entry->series_record = $series_record;
						}; // end if

						$checkout_entry->checkout_record = $checkout_record;

						$checkout_entries[] = $checkout_entry;

					}; // end while
				}; // end if

			}; // end while
		}
		else
		{
			exit_error("Group Empty", "The group you specified has nobody in it.");
		}; // end if
		break;
}; // end switch


include_once(dirname(__FILE__) . "/top.php");

if($accounts->account_record->librarian == "Y" || $accounts->account_record->admin == "Y")
{
	?><a href="<?= $db->url_root; ?>/overdue_letter.php/<?= $group_ID; ?>">Overdue Letters</a><?
}; // end if

$last_account_ID = 0;


foreach($checkout_entries as $checkout_entry)
{
	$this_account_ID = $checkout_entry->account_record->ID;


	if($this_account_ID != $last_account_ID)
	{
		$account_sql = "
			SELECT
				*
			FROM
				`" . $db->table_account . "`
			WHERE
				`ID`='" . $this_account_ID . "'
		";
		$account_result = mysql_query($account_sql, $mysql->link);
		$account_record = mysql_fetch_object($account_result);


		?><div style="margin-top:15px;"><b><a href="<?= $db->root_url_root; ?>/settings/account_view.php/<?= $account_record->ID; ?>"><?= $checkout_entry->account_record->name; ?></a></b><?

			?> <span class="small">(<?= ($account_record->library_award_points - $account_record->library_award_spent); ?> award points)</span><?

		?><div><?
	}; // end if

	$last_account_ID = $this_account_ID;


	?><div class="small indent"><a href="<?= $db->url_root; ?>/item_details.php/ID/<?= $checkout_entry->item_record->ID; ?>"><?
	if($checkout_entry->item_record->{$db->field_library_series_ID} > 0)
	{
		?><?= $checkout_entry->series_record->title; ?><?
		if($checkout_entry->item_record->series_number)
		{
			?> - #<?= $checkout_entry->item_record->series_number; ?><?
		}; // end if
		if($checkout_entry->item_record->title != "")
		{
			?>: <?
		}; // end if
	}; // end if
	?><?= $checkout_entry->item_record->title; ?><?= ($checkout_entry->item_record->{$db->field_library_copy_item_ID} > 0 ? " (Copy " . $checkout_entry->item_record->copy_number . ")" : ""); ?><?
	?></a><?

	$days_out = ceil((time() - strtotime($checkout_entry->checkout_record->out_datetime)) / 60 / 60 / 24);

	//$new_due = date("Y-m-d H:i:s", strtotime($checkout_entry->checkout_record->out_datetime) + (60 * 60 * 24 * $checkout_entry->account_record->library_overdue));

	?> <span class="small"<?= ($checkout_entry->checkout_record->due_datetime < date("Y-m-d H:i:s") ? ' style="background-color:red;color:white;font-weight:bold;"' : ""); ?>>(<a target="_blank" href="<?= $db->url_root; ?>/checkout_edit.php/<?= $checkout_entry->checkout_record->ID; ?>">Due <?= sql_date_format("F jS, Y", $checkout_entry->checkout_record->due_datetime); ?></a>)</span><?

	?></div><?

}; // end foreach


include_once(dirname(__FILE__) . "/bottom.php");

?>