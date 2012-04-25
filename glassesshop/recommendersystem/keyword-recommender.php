<?php
	include_once "../interface/recsys-interface.php";
	include_once "../database/glass-database-manager.php";
	include_once "word-segmenter.php";
	include_once "OpenSlopeOne.php";
		
	define("KEY_NO_EXPANSION", 0);
	define("KEY_LINK_JACCARD", 1);
	define("KEY_COL_SLOPEONE", 2);
	define("KEY_LINK_COSINE",  3);

	class KeywordRecommender implements iKeywordRecommender{
		private $dm;
		private $name;
		private $user;
		private $item;
		private $lock;
		private $jaccard;
		private $cosine;
		private $expand_weight;
		private $hottestItems;

		public function __construct($config){
			$this->dm = GlassDatabaseManager::getInstance();
			$this->name = $config['name'];
			if($this->name == KEY_LINK_JACCARD){
				if(isset($config['jaccard'])){
					$this->jaccard = $config['jaccard'];
				}
				else{
					echo "warning: jaccard is not set <br/>";
					$this->jaccard = 0.2;
				}
			}
			else if($this->name == KEY_LINK_COSINE){
				if(isset($config['cosine'])){
					$this->cosine = $config['cosine'];
				}
				else{
					echo "warning: cosine is not set <br/>";
					$this->cosine = 0.2;
				}
			}

			if($this->name != KEY_NO_EXPANSION){
				if(isset($config['expand_weight'])){
					$this->expand_weight = $config['expand_weight'];
					assert('$this->expand_weight > 0 && $this->expand_weight <= 1');
				}
				else{
					echo "warning: expand_weight is not set <br/>";
					$this->expand_weight = 0.1;
				}
			}

			$this->lock = false;
			$this->hottestItems = array();
		}

		public function preprocess($tables, $startTime=null){
			echo "KeywordRecommender_{$this->name} preprocess start.....<br/>";
			flush();
			ob_flush();
			$time_start = microtime(true);

			$word_segmenter = new WordSegmenter();
			$this->dm->executeSqlFile( __DIR__ . "/rec_tables.sql");
			
			/* Construct the keyword and keyword_item_weight table */
			$keyword_item_count = array();
			$query_results = $this->dm->query("select id, query from ".$tables['query']."");
			$keyword_count = array();
			while($query_row = mysql_fetch_array($query_results)){
				$items = array();
				$item_results = $this->dm->query("SELECT itemId FROM {$tables['query_item']} WHERE queryId = {$query_row['id']}");
				while($item_row = mysql_fetch_array($item_results)){
					$items[] = $item_row['itemId'];
				}

				$keywords = $word_segmenter->segmentWords($query_row['query']);
				foreach ($keywords as $keyword) {
					if(isset($keyword_count[$keyword])){
						$keyword_count[$keyword] += 1;
					}
					else{
						$keyword_count[$keyword] = 1;
						$keyword_item_count[$keyword] = array();
					}
					foreach($items as $item){
						if(!array_key_exists($item, $keyword_item_count[$keyword])){
							$keyword_item_count[$keyword][$item] = 0;
						}
						$keyword_item_count[$keyword][$item] += 1;
					}
				}	
			}

			$this->dm->query("BEGIN");
			foreach ($keyword_count as $key => $key_count) {
				foreach($keyword_item_count[$key] as $item => $count){
					$weight = $count / $key_count; 
					$this->dm->query("INSERT INTO keyword_item_weight(keyword, item, weight) VALUE('{$key}',
									'{$item}', '{$weight}')");
				}
				$this->dm->query("INSERT INTO keyword(keyword, count) VALUE ('{$key}', {$key_count})");
			}
			$this->dm->query("COMMIT");

			if($this->name == KEY_COL_SLOPEONE)
				$this->collaborativeFilteringWithSlopeOnePreprocess();
			if($this->name == KEY_LINK_JACCARD)
				$this->wordAssociationWithJaccardPreprocess($tables);
			if($this->name == KEY_LINK_COSINE)
				$this->wordAssociationWithCosinePreprocess($tables);
		
			$time_end = microtime(true);
			$cost_time = $time_end - $time_start;
			echo "KeywordRecommender_{$this->name} preprocess end.....<br/>";
			echo "cost time: $cost_time <br/>";
			flush();
			ob_flush();
		}

		public function cleanup(){
    		$this->dm->query("delete from keyword_item_weight");
    		$this->dm->query("delete from keyword");
    	}

		public function recommend($keywords, $queryId){
			$recommend_items = array();
			if($this->name == KEY_NO_EXPANSION){
				$recommend_items = $this->__recommend(KEY_NO_EXPANSION, $keywords, $queryId);
			}
			else if($this->name == KEY_LINK_JACCARD || $this->name == KEY_LINK_COSINE || $this->name == KEY_COL_SLOPEONE){
				$recommend_items = $this->__recommend(KEY_NO_EXPANSION, $keywords, $queryId);
				foreach($recommend_items as $item => $weight){
					$recommend_items[$item] = $weight * (1 - $this->expand_weight);
				}
				$expand_recommend_items = $this->__recommend($this->name, $keywords, $queryId);
				foreach($expand_recommend_items as $item => $weight){
					if(isset($recommend_items[$item])){
						$recommend_items[$item] += $this->expand_weight * $weight;
					}
					else{
						$recommend_items[$item] = $this->expand_weight * $weight;
					}
				}
			}
			else{
				echo "Dude, no such mode <br/>";
				exit(-1);
			}

			if(count($recommend_items) < 20){
				$recommend_items = $recommend_items + $this->addHotList();
			}

			arsort($recommend_items);
			return $recommend_items;
		}

		private function loadUserItem(){
			$user_results = $this->dm->query("select * from keyword");
			while($user_row = mysql_fetch_array($user_results)){
				$this->user[$user_row['keyword']] = $user_row['id'];
			}
			$item_results = $this->dm->query("select * from item");
			while($item_row = mysql_fetch_array($item_results)){
				$this->item[$item_row['id']] = $item_row['name'];
			}
		}

		private function wordAssociationWithCosinePreprocess($tables){
			$matrix = array();
			$keywords = array();
			$items = array();
			$keyword_items = array();

			$keyword_result = $this->dm->query("SELECT keyword FROM keyword");
			while($keyword_row = mysql_fetch_array($keyword_result)){
				$keywords[] = $keyword_row['keyword'];
			}

			$keyword_item_result = $this->dm->query("SELECT keyword, item FROM keyword_item_weight");
			while($keyword_item_row = mysql_fetch_array($keyword_item_result)){
				if(!array_key_exists($keyword_item_row['keyword'], $keyword_items)){
					$keyword_items[$keyword_item_row['keyword']] = array();
				}
				if(!in_array($keyword_item_row['item'], $keyword_items[$keyword_item_row['keyword']])){
					$keyword_items[$keyword_item_row['keyword']][] = $keyword_item_row['item'];
				}
			}

			// $item_result = $this->dm->query("SELECT name FROM item");
			// while($item_row = mysql_fetch_array($item_result)){
			// 	$items[] = $item_row['name'];
			// }

			// // build matrix
			// foreach($keywords as $keyword){
			// 	$matrix[$keyword] = array();
			// 	foreach($items as $item){			
			// 		$keyword_item_result = $this->dm->query("SELECT * FROM keyword_item_weight 
			// 										WHERE keyword = '{$keyword}' AND item = '{$item}'");
			// 		if(mysql_num_rows($keyword_item_result) > 0){
			// 			$matrix[$keyword][$item] = 1;
			// 		}
			// 		else{
			// 			$matrix[$keyword][$item] = 0;
			// 		}
			// 	}
			// }

			$this->dm->query("BEGIN");
			// compute cosine similarity and fill keyword_cosine_link table
			// foreach($matrix as $keyword => $items){
			// 	foreach($matrix as $keyword1 => $items1){
			foreach($keywords as $keyword){
				foreach($keywords as $keyword1){
					if($keyword != $keyword1 && $keyword != null && $keyword1 != null){
						$common_item_count = count(array_intersect($keyword_items[$keyword], $keyword_items[$keyword1]));
						$key1_item_count = count($keyword_items[$keyword]);
						$key2_item_count = count($keyword_items[$keyword1]);
						$cosine = $key1_item_count * $key2_item_count != 0 ? $common_item_count / sqrt($key1_item_count * $key2_item_count) : 0;
						if($cosine >= $this->cosine){
							$this->dm->query("INSERT INTO keyword_cosine_link (keyword, keyword_expand, link) 
											VALUES ('{$keyword}', '{$keyword1}', {$cosine})");
						}
					}
				}
			}
			$this->dm->query("COMMIT");
		}

		private function cosineSimilaritySimplified($key1, $key2, $keyword_item_count){
			// $common_item_count_result = $this->dm->query("SELECT count(*) FROM keyword_item_weight WHERE keyword = '{$key1}' AND
			// 					item IN (SELECT item FROM keyword_item_weight WHERE keyword = '{$key2}')");
			// $common_item_count_row = mysql_fetch_array($common_item_count_result);
			// $common_item_count = $common_item_count_row[0];

			// $key1_item_count_result = $this->dm->query("SELECT count(*) FROM keyword_item_weight WHERE keyword = '{$key1}'");
			// $key1_item_count_row = mysql_fetch_array($key1_item_count_result);
			// $key1_item_count = $key1_item_count_row[0];

			// $key2_item_count_result = $this->dm->query("SELECT count(*) FROM keyword_item_weight WHERE keyword = '{$key2}'");
			// $key2_item_count_row = mysql_fetch_array($key2_item_count_result);
			// $key2_item_count = $key2_item_count_row[0];

			// $common_item_count = array_keys($keyword_item_count[$key1])

			// return $key1_item_count * $key2_item_count != 0 ? $common_item_count / sqrt($key1_item_count * $key2_item_count) : 0;
		}

		private function cosineSimilarity($vector1, $vector2){
			assert('count($vector1) == count($vector2)');
			$a = $b = $c = 0;
			for($i = 0; $i < count($vector1); $i++){
				$a += ($vector1[$i] * $vector2[$i]);
				$b += ($vector1[$i] * $vector1[$i]);
				$c += ($vector2[$i] * $vector2[$i]);
			}

			return $b * $c != 0 ? $a / sqrt($b * $c) : 0;
		}

		private function wordAssociationWithJaccardPreprocess($tables){
			$this->dm->query("BEGIN");
			$this->dm->query("truncate keyword_link");
			$result = $this->dm->query("SELECT keyword,count FROM keyword where count > 1");
			$keyword_count = array();
			
			if(!$result){
			    die('no result available');
			}else{
				while($row = mysql_fetch_array($result)){
					$keyword_count[$row['keyword']] = $row['count']; 
				}
			    foreach($keyword_count as $key => $count){
			    	foreach($keyword_count as $key1 => $count1){
			    		if($key != $key1 && $key != null && $key1 != null){
			    			// $key = mysql_real_escape_string($key);
			    			// $key1 = mysql_real_escape_string($key1);
				    		$nAB = mysql_num_rows($this->dm->query("select id from ".$tables['query']." where query like '%".$key."%".$key1."%' or query like '%".$key1."%".$key."%'"));
				    		if($count + $count1 - $nAB != 0)
				    			$jaccard = $nAB/($count + $count1 - $nAB);
				    		else
				    			$jaccard = 1;
				    		if($jaccard > $this->jaccard){
				       	 		$this->dm->query("INSERT INTO keyword_link(keyword, keyword_expand, link) VALUE ('".$key."', '".$key1."','".$jaccard."')");
				    		}
			    		}
			    	}
			    }
			}
			$this->dm->query("COMMIT");
		}
		
		private function collaborativeFilteringWithSlopeOnePreprocess(){
			$this->dm->executeSqlFile(__DIR__ . "\col_table.sql");
			
			$item = array();
			$user = array();
			
			$item_results = $this->dm->query("select * from item");
			while($item_row = mysql_fetch_array($item_results)){
				$item[$item_row['name']] = $item_row['id'];
			}
			$user_results = $this->dm->query("select * from keyword");
			while($user_row = mysql_fetch_array($user_results)){
				$user[$user_row['keyword']] = $user_row['id'];
			}
			
			$pair_results = $this->dm->query("select * from keyword_item_weight");
			while($pair_row = mysql_fetch_array($pair_results)){
				$this->dm->query("insert into oso_user_ratings values(".$user[$pair_row['keyword']].",".$item[$pair_row['item']].",".$pair_row['weight'].")");
			}
			
			$openslopeone = new OpenSlopeOne();
			$openslopeone->initSlopeOneTable('MySQL');

			$this->lock = false;
		}

		private function __recommend($mode, $keywords, $queryId){
		    $weightArray = array();
		    if($mode == KEY_NO_EXPANSION){
		    	if(!get_magic_quotes_gpc()){
		    		$keywords = addslashes($keywords);		
		    		$keywords = array_unique(explode(' ', $keywords));
		    		
		    		foreach ($keywords as $key){
		    			$product_temp = KeywordRecommender::fetch_product_weight($key);
		    			foreach($product_temp as $p_name => $p_weight){
		    				if(isset($weightArray[$p_name]))
		    					$weightArray[$p_name] += $p_weight;
		    				else
		    					$weightArray[$p_name] = $p_weight;
		    			}
		    		}

		    		return $weightArray;
		    	}
		    }
		    else if($mode == KEY_LINK_JACCARD){
		    	$expand_keywords = KeywordRecommender::fetch_expand_key($keywords);
		    	foreach ($expand_keywords as $expand_key) {
		    		$expand_weight = KeywordRecommender::fetch_product_weight($expand_key);
		    		foreach($expand_weight as $p_name => $p_weight){
		    			if(isset($weightArray[$p_name]))
		    				$weightArray[$p_name] += $p_weight;
		    			else
		    				$weightArray[$p_name] = $p_weight;
		    		}
		    	}
		    	
		    	return $weightArray;
		    }
		    else if($mode == KEY_LINK_COSINE){
		    	$keywords = array_unique(explode(' ', $keywords));
		    	$expand_keywords = array();
		    	foreach ($keywords as $key){
		    		$expand_results = $this->dm->query("select keyword_expand from keyword_cosine_link where keyword = '{$key}'");
		    		while($expand_row = mysql_fetch_array($expand_results)){
		    			if(!in_array($expand_row[0], $keywords))
		    				$expand_keywords[] = $expand_row[0];
		    		}
		    	}
		    	$expand_keywords = array_unique($expand_keywords);
		    	foreach($expand_keywords as $expand_key){
		    		$expand_weight = $this->fetch_product_weight($expand_key);
		    		foreach($expand_weight as $p_name => $p_weight){
		    			if(isset($weightArray[$p_name])){
		    				$weightArray[$p_name] += $p_weight;
		    			}
		    			else{
		    				$weightArray[$p_name] = $p_weight;
		    			}
		    		}
		    	}

		    	return $weightArray;
		    }
		    else if($mode == KEY_COL_SLOPEONE){
		    	if($this->lock == false)
		    		$this->loadUserItem();
		    	$this->lock = true;
		    	$keywords = array_unique(explode(' ', $keywords));
		    	$openslopeone = new OpenSlopeOne();
		    	
		    	foreach ($keywords as $key){
		    		if(key_exists($key, $this->user)){
		    			$weightArrayTemp = $openslopeone->getRecommendedItemsByUser($this->user[$key]);
		    			if($weightArrayTemp != NULL){
		    				foreach($weightArrayTemp as $p_name => $p_weight){
		    					if(isset($weightArray[$this->item[$p_name]]))
		    						$weightArray[$this->item[$p_name]] += $p_weight;
		    					else
		    						$weightArray[$this->item[$p_name]] = $p_weight;
		    				}
		    			}
		    		}
		    	}

		    	return $weightArray;
		    }
    	}
    	
		private function fetch_expand_key($str){
	    	$this->dm->query("BEGIN");
	    	$keywords = array_unique(explode(' ', $str));
	    	$expand_keywords = array();
	    	foreach ($keywords as $key){
	    		$expand_results = $this->dm->query("select keyword_expand from keyword_link where keyword = '".$key."'");
	    		while($expand_row = mysql_fetch_array($expand_results)){
	    			if(!in_array($expand_row[0], $keywords))
	    				$expand_keywords[] = $expand_row[0];
	    		}
	    	}
	    	$expand_keywords = array_unique($expand_keywords);
	    	$this->dm->query("COMMIT");
	    	//print_r($expand_keywords);
	    	echo "<br />";
	    	return $expand_keywords;
	    }

    	private function fetch_product_weight($str){
			$product = array();
			$result = $this->dm->query("select * from keyword_item_weight where keyword = '".$str."'");
			while ($row = mysql_fetch_array($result)){
				$product[$row['item']] = $row['weight'];
			}
			return $product;
		}

		private function addHotList(){
	    	if(empty($this->hottestItems)){
		   		$item_result = $this->dm->query(" SELECT pageinfo item, count(id) item_count FROM visit WHERE pagetype = 'product' AND pageinfo <> '' AND userId NOT IN (SELECT userId FROM query_test) GROUP BY pageinfo ORDER BY count(id) DESC ");
				while($item_row = mysql_fetch_array($item_result)){
					$this->hottestItems[$item_row['item']] = 0;
				}
	    	}
			return $this->hottestItems;
	    }
	}