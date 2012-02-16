<?php
	include_once 'database_manager.php';
	
	$db = DatabaseManager::connectDB();
	
	//empty table keyword
	DatabaseManager::query("TRUNCATE table keyword_product_weight");
	DatabaseManager::query("TRUNCATE table keyword_product_weight_train");
	
	$product_identifier = "http://www.thesexylingerie.co.uk/product/";
	$visit_weight = 1;
	$shopcart_weight = 2;
	$order_weight = 3;
	
	$keyword_set = DatabaseManager::query("SELECT keyword from keyword");
	while($keyword_row = mysql_fetch_array($keyword_set)){
		DatabaseManager::query("CREATE OR REPLACE VIEW userids AS
								SELECT DISTINCT userid FROM preprocessed_user WHERE keywords LIKE '%{$keyword_row['keyword']}%'");
		DatabaseManager::query("CREATE OR REPLACE VIEW visit_count (product, count) AS
								SELECT page, count(page) FROM visit WHERE page LIKE '{$product_identifier}%' AND userid IN
								(SELECT DISTINCT userid FROM userids) GROUP BY page");
		DatabaseManager::query("CREATE OR REPLACE VIEW shopcart_count (product, count) AS
								SELECT page, count(page) FROM shopcart WHERE page LIKE '{$product_identifier}%' AND userid IN
								(SELECT DISTINCT userid FROM userids) GROUP BY page");
		DatabaseManager::query("CREATE OR REPLACE VIEW order_count (product, count) AS
								SELECT orderedItem, count(orderedItem) FROM orderrecord WHERE orderedItem LIKE '{$product_identifier}%' AND userid IN
								(SELECT DISTINCT userid FROM userids) GROUP BY orderedItem");
		$results = DatabaseManager::query("SELECT visit_count.product product, visit_count.count visit_count, shopcart_count.count shopcart_count, 
										  order_count.count order_count FROM visit_count
										  LEFT JOIN shopcart_count ON visit_count.product=shopcart_count.product
										  LEFT JOIN order_count ON visit_count.product=order_count.product");
		while($product_row = mysql_fetch_array($results)){
			$weight = $product_row['visit_count'] * $visit_weight; 
			if($product_row['shopcart_count'] != NULL){
				$weight += $product_row['shopcart_count'] * $shopcart_weight;
			}
			if($product_row['order_count'] != NULL){
				$weight += $product_row['order_count'] * $order_weight;
			}
			
			DatabaseManager::query("INSERT INTO keyword_product_weight(keyword, product, weight) VALUE('{$keyword_row['keyword']}',
									'{$product_row['product']}', '{$weight}')");
		}
	}
	
	$keyword_set_train = DatabaseManager::query("SELECT keyword from keyword_train");
	while($keyword_row_train = mysql_fetch_array($keyword_set_train)){
		DatabaseManager::query("CREATE OR REPLACE VIEW userids AS
								SELECT DISTINCT userid FROM preprocessed_user_train WHERE keywords LIKE '%{$keyword_row_train['keyword']}%'");
		DatabaseManager::query("CREATE OR REPLACE VIEW visit_count (product, count) AS
								SELECT page, count(page) FROM visit WHERE page LIKE '{$product_identifier}%' AND userid IN
								(SELECT DISTINCT userid FROM userids) GROUP BY page");
		DatabaseManager::query("CREATE OR REPLACE VIEW shopcart_count (product, count) AS
								SELECT page, count(page) FROM shopcart WHERE page LIKE '{$product_identifier}%' AND userid IN
								(SELECT DISTINCT userid FROM userids) GROUP BY page");
		DatabaseManager::query("CREATE OR REPLACE VIEW order_count (product, count) AS
								SELECT orderedItem, count(orderedItem) FROM orderrecord WHERE orderedItem LIKE '{$product_identifier}%' AND userid IN
								(SELECT DISTINCT userid FROM userids) GROUP BY orderedItem");
		$results_train = DatabaseManager::query("SELECT visit_count.product product, visit_count.count visit_count, shopcart_count.count shopcart_count, 
										  order_count.count order_count FROM visit_count
										  LEFT JOIN shopcart_count ON visit_count.product=shopcart_count.product
										  LEFT JOIN order_count ON visit_count.product=order_count.product");
		while($product_row_train = mysql_fetch_array($results_train)){
			$weight_train = $product_row_train['visit_count'] * $visit_weight; 
			if($product_row_train['shopcart_count'] != NULL){
				$weight_train += $product_row_train['shopcart_count'] * $shopcart_weight;
			}
			if($product_row_train['order_count'] != NULL){
				$weight_train += $product_row_train['order_count'] * $order_weight;
			}
			
			DatabaseManager::query("INSERT INTO keyword_product_weight_train(keyword, product, weight) VALUE('{$keyword_row_train['keyword']}',
									'{$product_row_train['product']}', '{$weight_train}')");
		}
	}
	
	
	DatabaseManager::closeDB($db);

?>