<html>
<head><title>laozibushiweisuonan</title></head>

<body>
<?php
	$searchterm = trim($_POST['searchterm']);
	if(!$searchterm){
		echo '老大，你总得输入点东西我才能推荐啊';
		exit;
	}
	
	if(!get_magic_quotes_gpc()){
		$searchterm = addslashes($searchterm);
	}
	
?>
</body>
</html>
