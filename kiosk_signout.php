<?

include_once(dirname(__FILE__) . "/first.php");

$_SESSION['library_account_ID'] = 0;
?>
<script language="javascript">
	if(parent.kiosk)
	{
		parent.set_account(0, "");
		document.location.href = "<?= $db->url_root; ?>/kiosk_index.php";
	}; // end if
</script>
<?
exit();

?>