<?php
	include_once "../interface/recsys-interface.php";
	include_once "../database/glass-database-manager.php";
	include_once "word-segmenter.php";
	include_once "OpenSlopeOne.php";
		
	define("KEY_LINK_JACCARD",1);
	define("KEY_COL_SLOPEONE",2);
	
	class KeywordRecommender implements iKeywordRecommender{
		private $dm;
		private $name;
		private $user;
		private $item;
		private $lock;
		private $jaccard;
		
		public function __construct($argArray = ''){
			$this->dm = GlassDatabaseManager::getInstance();
			$this->name = $argArray['name'];
			if($this->name == KEY_LINK_JACCARD && key_exists('jaccard', $argArray))
				$this->jaccard = $argArray['jaccard'];
			else if($this->name == KEY_LINK_JACCARD && !key_exists('jaccard', $argArray))
				echo "warning: jaccard is not set<br />";
			else
				$this->jaccard = 0.2;
			$this->lock = false;
		}
		
		public function loadUserItem(){
			$user_results = $this->dm->query("select * from keyword");
			while($user_row = mysql_fetch_array($user_results)){
				$this->user[$user_row['keyword']] = $user_row['id'];
			}
			$item_results = $this->dm->query("select * from item");
			while($item_row = mysql_fetch_array($item_results)){
				$this->item[$item_row['id']] = $item_row['name'];
			}
		}
		
		public function preprocess($tables, $startTime=null){			
			$word_segmenter = new WordSegmenter();
			$this->dm->executeSqlFile( __DIR__ . "/rec_tables.sql");
			$this->dm->executeSqlFile( __DIR__ . "/rec_tables_additional.sql");
					
            /*
             * $weight_matrix is actually an array of arrays
             * $weight_matrix_example = array(
             *      'keyword1' => array(
             *          'product1' => 1,
             *          'product1' => 2,
             *      ),
             *      'keyword2' => array(
             *          'product1' => 3,
             *          'product3' => 5,
             *      ),
             *      ...
             *  )
             */
			$weight_matrix = array();
            // by the way, also accumulate keyword count
            $keyword_count = array();

			$query_results = $this->dm->query("select id, query from ".$tables['query']."");
			while($query_row = mysql_fetch_array($query_results)){
                /* 
                 * get keywords set K from each query and
                 * products set P browsed in this session
                 * for each (k, p) in KxP, increment count by 1 in $weight_matrix
                 */
				$keywords = $word_segmenter->segmentWords($query_row['query']);
				foreach ($keywords as $keyword) {
					if(isset($keyword_count[$keyword]))
						$keyword_count[$keyword] += 1;
					else{
						$keyword_count[$keyword] = 1;
					}
				}

                $item_results = $this->dm->query('select itemId from ' . $tables['query_item'] . ' where queryId = ' . $query_row['id'] . ';'); //itemId is actually item name!
                $items = array();
                while($item_row = mysql_fetch_array($item_results))
                    $items[] = $item_row['itemId'];

				foreach ($keywords as $keyword) {
                    if(! array_key_exists($keyword, $weight_matrix))
                        $weight_matrix[$keyword] = array();
                    foreach ($items as $item) {
                        if(! array_key_exists($item, $weight_matrix[$keyword]))
                            $weight_matrix[$keyword][$item] = 0;
                        $weight_matrix[$keyword][$item] += 1;
                    }
				}
			}

            /*
             * put keyword count to database for later use:
             * 1. setting up ratings between keywords and items
             * 2. keyword expansion
             */
			foreach ($keyword_count as $key => $key_count) {
				$escaped_keyword = addslashes($key); 
				$this->dm->query("insert into Keyword (keyword, count) values('". $escaped_keyword ."', ".$key_count." )");
            }

            /*
             * put the $weight_matrix to database
             * create table weight_matrix (
             *      id integer primary key,
             *      keyword varchar,
             *      item varchar,
             *      weight integer,
             *      unique (keyword, item)
             *  );
             */
            $this->dm->query('truncate weight_matrix;');
            foreach($weight_matrix as $keyword => $weight_array) {
                $escaped_keyword = addslashes($keyword);
                foreach($weight_array as $item => $weight) {
                    $this->dm->query("insert into weight_matrix (keyword, item, weight) values ('{$escaped_keyword}', '{$item}', {$weight});");
                }
            }

            /* 
             * Construct the keyword and keyword_item_weight table
             * with Keyword Frequency - Inverted Item Frequency
             */
            $temp_result = $this->dm->query('select count(distinct item) from weight_matrix;');
            $temp_row = mysql_fetch_array($temp_result);
            $items_count = $temp_row[0];

            $this->dm->query('truncate rating_matrix;');
            $keyword_results = $this->dm->query('select keyword from Keyword;');
            while($keyword_row = mysql_fetch_array($keyword_results)) {
                $keyword = $keyword_row['keyword'];
                $escaped_keyword = addslashes($keyword);

                $temp_result = $this->dm->query("select count(*) from weight_matrix where keyword = '{$escaped_keyword}';");
                $temp_row = mysql_fetch_array($temp_result);
                $related_items_count = $temp_row[0]; //this couldn't be zero

                $related_results = $this->dm->query("select item, weight from weight_matrix where keyword = '{$escaped_keyword}';");
                while($item_weight_row = mysql_fetch_array($related_results)) {
                    $item = $item_weight_row['item'];
                    $weight = $item_weight_row['weight'];

                    $temp_result = $this->dm->query("select sum(weight) from weight_matrix where item = '{$item}';");
                    $temp_row = mysql_fetch_array($temp_result);
                    $related_weight = $temp_row[0];

                    //$iif = log($items_count / $related_items_count);
                    $iif = 1 / $related_items_count;
                    $kf = $weight / $related_weight;

                    $rating = $kf * $iif;
                    // rating_matrix is similar to weight_matrix
                    $this->dm->query("insert into rating_matrix (keyword, item, rating) values ('{$escaped_keyword}', '{$item}', {$rating});");
                }
            }

			if($this->name == KEY_LINK_JACCARD)
                // to do this, the keyword table is needed
				$this->wordAssociationWithJaccardPreprocess($tables);
		}
		
		public function wordAssociationWithJaccardPreprocess($tables){
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
		
		public function makeCombineRecList($keywords){
			$weightArray = array();
			
	    	if($this->name == KEY_LINK_JACCARD){
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
				if(count($weightArray) < 20)
					$weightArray = $weightArray+$this->addHotList();
				arsort($weightArray);
				return $weightArray;				
			}
			else if($this->name == KEY_COL_SLOPEONE){
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
				arsort($weightArray);
				return $weightArray;
			}
			else{	
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
					if(count($weightArray) < 20)
						$weightArray = $weightArray+$this->addHotList();
					arsort($weightArray);
					return $weightArray;
				}
			}
	    }
	    
	    public function addHotList(){
	    	$weightArray = array();
	   		$item_result = $this->dm->query("SELECT pageinfo item, count(id) item_count FROM visit WHERE pagetype = 'product' AND pageinfo <> '' AND userId NOT IN (SELECT userId FROM query_test) GROUP BY pageinfo ORDER BY count(id) DESC ");
			while($item_row = mysql_fetch_array($item_result)){
				$weightArray[$item_row['item']] = 0; // use count as weight, sort is handled by DBMS
			}
			return $weightArray;
	    }
		
    	public function recommend($keywords){
    		
		    return KeywordRecommender::makeCombineRecList($keywords);
    	}

    	public function cleanup(){
    		$this->dm->query("delete from keyword_item_weight");
    		$this->dm->query("delete from keyword");
    		
    	}
    	
		public function fetch_expand_key($str){
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

    	public function fetch_product_weight($escaped_keyword){
    		
			$product = array();
			$result = $this->dm->query("select item, rating from rating_matrix where keyword = '{$escaped_keyword}';");
			while ($row = mysql_fetch_array($result)){
				if(isset($product[$row['item']]))
					$product[$row['item']] += $row['rating'];
				else
					$product[$row['item']] = $row['rating'];
			}
			return $product;
		}
	}
