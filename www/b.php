<?php 
if (isset($_POST['code'])) {
	echo base64_encode($_POST['code']);
}
?>

<form action="" method="post">
	<input type="text" name="code"	>
	<input type="submit" value="encode">
</form>