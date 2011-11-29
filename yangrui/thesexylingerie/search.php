<html>
<head><title>laozibushiweisuonan</title></head>

<body>
<?php
	require 'extract_keywords.php';
	require 'dbconfig.php';
	
	$con = mysql_connect($db_host , $db_user, $db_pass);
	if(!$con){
	    die(mysql_error());
	}

	mysql_select_db('thesexylingerie');
	
	function is_stopword($str){
		@$fp = fopen("stopwords.txt",'rb');
		while(!feof($fp)){
			$stopword = trim(fgets($fp,999));
			if($str == $stopword)
				return false;
		}
		return true;
	}
	
	function fetch_product_weight($str){
		echo $str."<br />";
		$product = array();
		$result = mysql_query("select * from keyword_product_weight where keyword = '".$str."'");
		while ($row = mysql_fetch_array($result)){
			echo $row['product']." ".$row['weight']."<br />";
			if(isset($product[$row['product']]))
				$product[$row['product']] += $row['weight'];
			else
				$product[$row['product']] = 0+$row['weight'];
		}
		return $product;
	}
	
	$product = array();
	$product_temp = array();
	$searchterm = trim($_POST['searchterm']);
	if(!$searchterm){
		echo '老大，你总得输入点东西我才能推荐啊';
		exit;
	}
	
	if(!get_magic_quotes_gpc()){
		$searchterm = addslashes($searchterm);
		$keyword_str = preg_replace('/\s/',' ',preg_replace("/[[:punct:]]/",' ',strip_tags(html_entity_decode(str_replace(array('？','！','￥','（','）','：','‘','’','“','”','《','》','，','…','。','、','nbsp','拢','-'),'',$searchterm),ENT_QUOTES,'UTF-8'))));
		$keyword_str = preg_replace("/[0-9]/", "", $keyword_str);
		
		$keywords = explode(' ', $keyword_str);
		$keywords = array_filter($keywords, "is_stopword");
		foreach ($keywords as $key){
			$product = fetch_product_weight($key,$product);
			$result = mysql_query("select distinct keyword_expand from keyword_link where keyword = '".$key."'");
			while ($row = mysql_fetch_array($result)){
				$product_temp = fetch_product_weight($row[0],$product);
				foreach($product_temp as $p_name => $p_weight){
					if(isset($product[$p_name]))
						$product[$p_name] += $p_weight*0.5;
					else
						$product[$p_name] = $p_weight*0.5;
				}
			}
		}
	}
	
	echo "<br />recommand list:<br />";
	arsort($product);
	
	foreach($product as $p_name => $p_weight){
        echo $p_name." ".$p_weight."<br />";
        //mysql_query("insert into keyword(keyword,occur) values('".$key."',".$count.")");
    }
?>
</body>
</html>
