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
	mysql_select_db('thesexylingerie_test');
	
	function is_stopword($str){
		@$fp = fopen("stopwords.txt",'rb');
		while(!feof($fp)){
			$stopword = trim(fgets($fp,999));
			if($str == $stopword)
				return false;
		}
		return true;
	}
	
	/**
	 * 
	 * 对于拆出的以及扩展的单个keyword 从权重表中获取原始权重值
	 * 除以关键字频数后得到相对权重值 返回商品相对权重数组
	 * @param string $str
	 * @param int $key_count
	 */
	function fetch_product_weight($str,$key_count){
		//echo $str."<br />";
		$product = array();
		$result = mysql_query("select * from keyword_product_weight_train where keyword = '".$str."'");
		while ($row = mysql_fetch_array($result)){
			//echo $row['product']." ".$row['weight']."<br />";
			if($key_count != 0){
				if(isset($product[$row['product']]))
					$product[$row['product']] += $row['weight']/$key_count;
				else
					$product[$row['product']] = $row['weight']/$key_count;
			}
		}
		return $product;
	}
	
	/**
	 * 
	 * 对于任意输入字符串 返回排序后的商品最终权重列表
	 * @param string $searchterm
	 */
	function recommendation_list($searchterm){
		$product = array();
		$product_temp = array();
		$key_temp = array();
			
		if(!get_magic_quotes_gpc()){
			$searchterm = addslashes($searchterm);
			$keyword_str = preg_replace('/\s/',' ',preg_replace("/[[:punct:]]/",' ',strip_tags(html_entity_decode(str_replace(array('？','！','￥','（','）','：','‘','’','“','”','《','》','，','…','。','、','nbsp','拢','-'),'',$searchterm),ENT_QUOTES,'UTF-8'))));
			$keyword_str = preg_replace("/[0-9]/", "", $keyword_str);
			
			$keywords = explode(' ', $keyword_str);
			$keywords = array_filter($keywords, "is_stopword");
			foreach ($keywords as $key){
				if(!isset($key_temp[$key])){
					$key_temp[$key] = true;
					$count_row = mysql_fetch_array(mysql_query("select count from keyword_link where keyword = '".$key."'"));
					$key_count = $count_row[0];
					$product_temp = fetch_product_weight($key,$key_count);
					foreach($product_temp as $p_name => $p_weight){
						if(isset($product[$p_name]))
							$product[$p_name] += $p_weight*0.5;
						else
							$product[$p_name] = $p_weight*0.5;
					}
					$result = mysql_query("select distinct * from keyword_link where keyword = '".$key."'");
					while ($row = mysql_fetch_array($result)){
						if(!isset($key_temp[$row[3]])){
							$product_temp = fetch_product_weight($row[3],$row[4]);
							foreach($product_temp as $p_name => $p_weight){
								if(isset($product[$p_name]))
									$product[$p_name] += $p_weight*0.5;
								else
									$product[$p_name] = $p_weight*0.5;
							}
							$key_temp[$row[3]] = true;
						}
					}
				}
			}
		}	
		arsort($product);
	    return $product;
	}
	
?>
</body>
</html>
