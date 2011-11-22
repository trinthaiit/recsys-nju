<html>
<head>
<title>去除字符串的特殊符号使用实例</title>
</head>
<body>
<?
$s="<font color=\"#ff0000\">我爱北京天安门！</font>";
$t=strip_tags($s);
$s2="<font size=\"16pt\">天安门上太阳升！</font>";
$t2=strip_tags($s2);
echo $s;
echo "<p>";
echo $t;
echo "<p>";
echo $s2;
echo "<p>";
echo $t2;
?>
</body>
</html>