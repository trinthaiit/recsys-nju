<?php
	class AprioriAssociationKeywordRecommender{
		private $dm;
		private $apriori_keyword_count_table_name;
		private $query_array; // an array contains all querys, each query is an array of keyword id. For example [[1, 2, 3][3, 4][1, 2][0, 5]]
		private $min_support; // the minimum support for apriori algorithm
		private $frequent_itemsets; // an array contains L1, L2 and so on. Every Lk is also an array stands for k-itemsets

		public function __construct($dm){
			$this->dm = $dm;
			$this->apriori_keyword_count_table_name = "recsys_apriori_keyword_count";
			$this->min_support = 2; // we can adjust min_support
			$this->query_array = array();
			$this->frequent_itemsets = array();
		}

		public function preprocess(){
			// create necessary tables
			$this->dm->executeSqlFile('apriori-tables.sql');

			$sql_query = "SELECT query FROM recsys_query";
			$querys_result = $this->dm->query($sql_query);
			while($row = mysql_fetch_array($querys_result)){
				$query = $row[0];

				// every keyword in $keywords contains no white space at left and right side of this string
				$keywords = preg_split('@ +@', $query, NULL, PREG_SPLIT_NO_EMPTY);
				
				// remove duplicate keywords in a single query
				$keywords = array_unique($keywords);

				$keyword_array = array(); // an array of keyword id, stands for a query
				foreach($keywords as $keyword){
					// this sql query trick is from http://dev.mysql.com/doc/refman/5.0/en/insert-on-duplicate.html
					$sql_query = "INSERT INTO {$this->apriori_keyword_count_table_name} (keyword) VALUES ('{$keyword}') 
								ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id), count=count+1";
					$this->dm->query($sql_query);

					$sql_query = "SELECT LAST_INSERT_ID()";
					$keyword_id_result = $this->dm->query($sql_query);
					$keyword_id_row = mysql_fetch_array($keyword_id_result);
					$keyword_id = $keyword_id_row[0];


					$keyword_array[] = $keyword_id;
				}
				$this->query_array[] = $keyword_array;
			}

			// following is the apriori algorithm from Data Mining Concepts and Techniques 2e P239
			// find L1 from table {$this->apriori_keyword_count_table_name}, this table is C1
			$this->frequent_itemsets[1] = array();
			$sql_query = "SELECT id FROM {$this->apriori_keyword_count_table_name} WHERE count >= {$this->min_support}";
			$L1_result = $this->dm->query($sql_query);
			while($row = mysql_fetch_array($L1_result)){
				$keyword_id = $row[0];
				$this->frequent_itemsets[1][] = array(0 => $keyword_id);
			}

			for($k = 2; !empty($this->frequent_itemsets[$k-1]); $k++){
				$candidate_itemsets_array = $this->apriori_gen($this->frequent_itemsets[$k-1], $k-1);
				$candidate_count_array = array_fill(0, count($candidate_itemsets_array), 0);
				foreach($this->query_array as $query){  // $query is like [1, 2, 3]
					for($i = 0; $i < count($candidate_itemsets_array); $i++){
						if($this->has_subset($query, $candidate_itemsets_array[$i])){
							$candidate_count_array[$i]++;
						}
					}
				}
				$this->frequent_itemsets[$k] = array();
				for($i = 0; $i < count($candidate_itemsets_array); $i++){
					if($candidate_count_array[$i] >= $this->min_support){
						$this->frequent_itemsets[$k][] = $candidate_itemsets_array[$i];
					}
				}
			}

			array_pop($this->frequent_itemsets); // remove the last itemsets because it is empty
			// end of apriori algorithm

			print_r($this->frequent_itemsets); // just for test
		}

		// generate candidate itemsets Ck from Lk-1
		private function apriori_gen($itemsets, $k){
			$candidate_itemsets_array = array();
			foreach ($itemsets as $itemset1) {
				foreach($itemsets as $itemset2){
					$can_join = ture;
					for($i = 0; $i < $k - 1; $i++){
						if($itemset1[$i] != $itemset2[$i]){
							$can_join = false;
							break;
						}
					}
					if($can_join){
						if($itemset1[$k-1] >= $itemset2[$k-1]){
							$can_join = false;
						}
					}
					if($can_join){
						$candidate_itemsets_array[] = array_merge($itemset1, array($itemset2[$k-1]));
					}
				}
			}
			return $candidate_itemsets_array;
		}

		/**
		 * determine whether $array1 contains $array2 as a subset
		 * @return true or false
		 */
		private function has_subset($array1, $array2){
			$result = (count(array_intersect($array1, $array2)) == count($array2));
			return $result;
		}
	}
?>