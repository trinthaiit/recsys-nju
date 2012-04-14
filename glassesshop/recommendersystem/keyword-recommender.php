<?php
	include_once "../interface/recsys-interface.php";
	include_once "../database/glass-database-manager.php";
	include_once "word-segmenter.php";
	include_once "OpenSlopeOne.php";
	
	class KeywordRecommender implements iKeywordRecommender{
		private $dm;
		
		public function __construct(){
			$this->dm = GlassDatabaseManager::getInstance();
		}
		
		public function preprocess($tables, $startTime=null){
			
			$word_segmenter = new WordSegmenter();
			$this->dm->executeSqlFile( __DIR__ . "/rec_tables.sql");
					
			/* Construct the keyword and keyword_item_weight table */
			$query_results = $this->dm->query("select query from ".$tables['query']."");
			$keyword_count = array();
			while($query_row = mysql_fetch_array($query_results)){
				$keywords = $word_segmenter->segmentWords($query_row['query']);
				foreach ($keywords as $keyword) {
					if(isset($keyword_count[$keyword]))
						$keyword_count[$keyword] += 1;
					else{
						$keyword_count[$keyword] = 1;
					}
				}
			}
			foreach ($keyword_count as $key => $key_count) {
				$key = addslashes($key);
				$this->dm->query("insert into Keyword (keyword, count) values('". $key ."', ".$key_count." )");
				$this->dm->query("CREATE OR REPLACE VIEW queryids AS
								SELECT DISTINCT id FROM ".$tables['query']." WHERE query LIKE '%{$key}%'");
				$this->dm->query("CREATE OR REPLACE VIEW visit_count (item, count) AS
								SELECT itemId, count(itemId) FROM ".$tables['query_item']." WHERE queryId IN
								(SELECT DISTINCT id FROM queryids) GROUP BY itemId");
				
				$weight_results = $this->dm->query("SELECT visit_count.item item, visit_count.count visit_count FROM visit_count");
				if($weight_results)
				while ($weight_row = mysql_fetch_array($weight_results)){
					$weight = $weight_row['visit_count']/$key_count; 
					$this->dm->query("INSERT INTO keyword_item_weight(keyword, item, weight) VALUE('{$key}',
									'{$weight_row['item']}', '{$weight}')");
				}
			}
		}
		
		public function wordAssociationWithJaccardPreprocess($threshold,$tables){
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
				    		if($jaccard > $threshold){
				       	 		$this->dm->query("INSERT INTO keyword_link(keyword, keyword_expand, link) VALUE ('".$key."', '".$key1."','".$jaccard."')");
				    		}
			    		}
			    	}
			    }
			 }
			 $this->dm->query("COMMIT");
		}
		
		public function collaborativeFilteringWithSlopeOnePreprocess(){
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
		}
		
    	public function recommend($keywords){
    		$product = array();
			$product_temp = array();
				
			if(!get_magic_quotes_gpc()){
				$keywords = addslashes($keywords);		
				$keywords = array_unique(explode(' ', $keywords));
				
				foreach ($keywords as $key){
					$product_temp = KeywordRecommender::fetch_product_weight($key);
					foreach($product_temp as $p_name => $p_weight){
						if(isset($product[$p_name]))
							$product[$p_name] += $p_weight;
						else
							$product[$p_name] = $p_weight;
					}
				}
			}	
			arsort($product);
			// echo "<br />------------------------------------------------------------<br />";
			// print_r($product);
			// echo "<br />------------------------------------------------------------<br />";
		    return $product;
    	}

    	public function cleanup(){
    		$this->dm->query("delete from keyword_item_weight");
    		$this->dm->query("delete from keyword");
    		
    	}

    	public function fetch_product_weight($str){
    		
			$product = array();
			$result = $this->dm->query("select * from keyword_item_weight where keyword = '".$str."'");
			while ($row = mysql_fetch_array($result)){
				if(isset($product[$row['item']]))
					$product[$row['item']] += $row['weight'];
				else
					$product[$row['item']] = $row['weight'];
			}
			return $product;
		}
	}