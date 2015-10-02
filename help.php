<?

include_once(dirname(__FILE__) . "/first.php");

$standalone_window = TRUE;

if($accounts->account_record->teacher != "Y")
{
	header("location:index.php");
}; // end if

$title = "About IM Library";

include_once(dirname(__FILE__) . "/top.php");

?>
This page has not been written yet.
<br>
<br>
<input type="button" value="Close" onclick="window.close();">
<?

include_once(dirname(__FILE__) . "/bottom.php");

?>