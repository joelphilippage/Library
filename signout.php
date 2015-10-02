<?

include_once(dirname(__FILE__) . "/first.php");

$_SESSION['library_account_ID'] = 0;

header("location:" . $db->url_root . "/index.php");
exit();

?>