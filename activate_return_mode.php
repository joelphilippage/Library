<?

include_once(dirname(__FILE__) . "/first.php");


$account_ID = urldecode($query[0]);
$barcode = urldecode($query[1]);


if(! $_SESSION['library_account_ID'])
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if(! $db->is_librarian_account)
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if

if($_SESSION['return_mode'])
{
	$_SESSION['return_mode'] = FALSE;
}
else
{
	$_SESSION['return_mode'] = TRUE;
}; // end if

header("location:" . $db->url_root . "/index.php");
exit();

?>