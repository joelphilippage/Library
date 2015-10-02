<?
		?>
		<div id="wait_message" class="wait_message">One Moment...</div>
		<script language="javascript">
			if(parent.kiosk)
			{
				parent.ready_for_next_command();
				parent.kiosk_mode = "<?= $kiosk_mode; ?>";
				parent.kiosk_value = "<?= $kiosk_value; ?>";
			}; // end if
		</script>
	</body>
</html>
<?

?>