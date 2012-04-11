<?php
	include_once "glass-raw-data-processor.php";
	$string = 'eyeglasses\\';
	echo str_replace(array('\\', '/'), '', $string);
	echo $string;
	//$processor = new GlassRawDataProcessor();
	//$processor->processRawData();
?>