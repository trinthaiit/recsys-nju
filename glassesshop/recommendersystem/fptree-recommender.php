<?php
	include_once "../interface/recsys-interface.php";
	include_once "../database/glass-database-manager.php";

	class FPTreeRecommender implements iKeywordRecommender{
		private $dm;
		private $query_array; // an array contains all querys, each query is an array of keyword id. For example [[1, 2, 3][3, 4][1, 2][0, 5]]
		private $min_support;
		private $fplists;

		public function __construct($config){
			$this->dm = GlassDatabaseManager::getInstance();
			$this->min_support = $config['min_support'];

			if(!isset($this->min_support)){
				echo 'Dude, give me [min_support] param';
				flush();
				ob_flush();
			}
		}

		public function preprocess($tables, $startTime = null){
			$tables['query'] = 'fp_query_train'; // TODO delete

			// create necessary tables
			$this->dm->executeSqlFile(__DIR__ . '\fptree-tables.sql');

			// construct $this->query_array
			$querys_result = $this->dm->query("SELECT id, query FROM {$tables['query']}");
			while($query_row = mysql_fetch_array($querys_result)){
				$query = $query_row['query'];

				// every keyword in $keywords contains no white space at left and right side of this string
				$keywords = preg_split('@ +@', $query, NULL, PREG_SPLIT_NO_EMPTY);
				
				// remove duplicate keywords in a single query
				$keywords = array_unique($keywords);

				$keyword_array = array(); // an array of keyword id, stands for a query
				foreach($keywords as $keyword){
					// this sql query trick is from http://dev.mysql.com/doc/refman/5.0/en/insert-on-duplicate.html
					$this->dm->query("INSERT INTO fptree_keyword_count (keyword) VALUES ('{$keyword}') 
								ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id), count=count+1");

					$keyword_id_result = $this->dm->query("SELECT LAST_INSERT_ID()");
					$keyword_id_row = mysql_fetch_array($keyword_id_result);
					$keyword_id = $keyword_id_row[0];

					$keyword_array[] = $keyword_id;
				}
				$this->query_array[$query_row['id']] = $keyword_array;
			}

			// sort $this->query_array and remove infrequent keyword
			foreach($this->query_array as $id => $query){
				$keywordId_count = array();
				$keyword_count = array();
				foreach($query as $keyword_id){
					$keyword_result = $this->dm->query("SELECT * FROM fptree_keyword_count WHERE id = $keyword_id");
					$keyword_row = mysql_fetch_array($keyword_result);
					assert('$keyword_row["id"] == $keyword_id');
					$keywordId_count[$keyword_id] = $keyword_row['count'];
					$keyword_count[$keyword_row['keyword']] = $keyword_row['count'];
				}
				
				// update query
				arsort($keyword_count);
				$resort_query = '';
				foreach($keyword_count as $keyword => $count){
					$resort_query = $resort_query . $keyword;
					$resort_query = $resort_query . ' ';
				}
				$resort_query = substr_replace($resort_query, "", -1);
				$this->dm->query("UPDATE {$tables['query']} SET query = '{$resort_query}' WHERE id = $id");

				// update query_array
				$keywordId_count = array_filter($keywordId_count, array($this, "over_threshold"));
				$array = array($keywordId_count, array_keys($keywordId_count));
				array_multisort($array[0], SORT_DESC, $array[1], SORT_DESC);
				$keywordId_count = array_combine($array[1], $array[0]);
				unset($array);
				$this->query_array[$id] = array_keys($keywordId_count); 
			}

			// build fp-tree
			$parent_link = array();
			foreach($this->query_array as $id => $query){
				$parent_link[$id] = null;
			}
			
			$this->fplists = array();
			$keyword_result = $this->dm->query("SELECT * FROM fptree_keyword_count WHERE count >= {$this->min_support} 
												ORDER BY count DESC, id DESC");
			while($keyword_row = mysql_fetch_array($keyword_result)){
				$fplist = new fplist();
				$fplist->keywordId = $keyword_row['id'];
				$fplist->support = $keyword_row['count'];
				$this->fplists[$fplist->keywordId] = $fplist;
			}

			$continue = true;
			for($i = 0; $continue; $i++){
				$continue = false;
				foreach($this->query_array as $id => $query){
					if(count($query) >= $i + 1){
						$continue = true;
						$fplist = $this->fplists[$query[$i]];
						if(is_null($fplist->head)){
							$fpnode = new fpnode();
							$fpnode->keywordId = $query[$i];
							$fpnode->support = 1;
							$fpnode->parent = $parent_link[$id];
							$fpnode->next = null;
							$parent_link[$id] = $fpnode;
							$fplist->head = $fpnode;
						}
						else{
							$current = $fplist->head;
							while(!is_null($current->next)){
								if($current->parent == $parent_link[$id]){
									break;
								}
								else{
									$current = $current->next;
								}
							}
							if($current->parent == $parent_link[$id]){
								$current->support++;
								$parent_link[$id] = $current;
							}
							else{
								$next = $current->next;
								assert('is_null($next)');
								
								$fpnode = new fpnode();
								$fpnode->keywordId = $query[$i];
								$fpnode->support = 1;
								$fpnode->parent = $parent_link[$id];
								$fpnode->next = null;
								$parent_link[$id] = $fpnode;
								$current->next = $fpnode;
							}
						}
					}
				}
			}
			
			// mining fptree
			$frequent_items = $this->mine($this->fplists);
			foreach($frequent_items as $frequent_item){
				$frequent_query = '';
				foreach($frequent_item as $keywordId){
					$keyword_result = $this->dm->query("SELECT keyword FROM fptree_keyword_count WHERE id = {$keywordId}");
					$keyword_row = mysql_fetch_array($keyword_result);
					$frequent_query = $frequent_query . $keyword_row['keyword'];
					$frequent_query = $frequent_query . ' ';
				}
				$frequent_query = substr_replace($frequent_query, "", -1);
				$this->dm->query("INSERT INTO fptree_frequent_query (query) VALUES ('{$frequent_query}')");
			}
			exit(-1);
		}

		private function mine($fplists){
			$frequent_items = array();
			for($i = count($fplists) - 1; $i >= 0; $i--){
				$current_fplist = array_slice($fplists, $i, 1, false);
				$current_fplist = $current_fplist[0];
				if($current_fplist->support >= $this->min_support){
					$result_fplists = array();
					for($k = 0; $k < $i; $k++){
						$result_fplist = new fplist();
						$fplist = array_slice($fplists, $k, 1, false);
						$fplist = $fplist[0];
						$result_fplist->keywordId = $fplist->keywordId;
						$result_fplist->support = 0;
						$result_fplist->head = null;
						$result_fplists[$result_fplist->keywordId] = $result_fplist;
					}
					$current_fpnode = $current_fplist->head;
					while(!is_null($current_fpnode)){
						$parent_fpnode = $current_fpnode->parent;
						$child_fpnode = null;
						while(!is_null($parent_fpnode)){
							$fplist = $result_fplists[$parent_fpnode->keywordId];
							if(is_null($fplist->head)){
								$result_fpnode = new fpnode();
								$result_fpnode->keywordId = $parent_fpnode->keywordId;
								$result_fpnode->support = $current_fpnode->support;
								$result_fpnode->next = null;
								$result_fpnode->origin = $parent_fpnode;
								$fplist->head = $result_fpnode;
								if(!is_null($child_fpnode)){
									$child_fpnode->parent = $result_fpnode;
								}
								$child_fpnode = $result_fpnode;
								$fplist->support += $result_fpnode->support;
							}
							else{
								$current = $fplist->head;
								while(!is_null($current->next)){
									if($current->origin == $parent_fpnode){
										break;
									}
									else{
										$current = $current->next;
									}
								}
								if($current->origin == $parent_fpnode){
									$current->support += $current_fpnode->support;
									$fplist->support += $current_fpnode->support;
									if(!is_null($child_fpnode)){
										$child_fpnode->parent = $current;
									}
									$child_fpnode = $current;
								}
								else{
									$result_fpnode = new fpnode();
									$result_fpnode->keywordId = $parent_fpnode->keywordId;
									$result_fpnode->support = $current_fpnode->support;
									$result_fpnode->next = null;
									$result_fpnode->origin = $parent_fpnode;
									$current->next = $result_fpnode;
									if(!is_null($child_fpnode)){
										$child_fpnode->parent = $result_fpnode;
									}
									$child_fpnode = $result_fpnode;
									$fplist->support += $result_fpnode->support;
								}
							}
							$parent_fpnode = $parent_fpnode->parent;
						}
						$current_fpnode = $current_fpnode->next;
					}
					$result_fplists = array_filter($result_fplists, array($this, "fplist_not_empty"));
					$result_mine = $this->mine($result_fplists);
					foreach($result_mine as $result_frequent_items){
						$result_frequent_items[] = $current_fplist->keywordId;
						$frequent_items[] = $result_frequent_items;
					}
					$frequent_items[] = array($current_fplist->keywordId);
				}
			}
			return $frequent_items;
		}

		public function cleanup(){

		}

		public function recommend($keywords){

		}

		private function over_threshold($count){
			if($count >= $this->min_support){
				return true;
			}
			else{
				return false;
			}
		}

		private function fplist_not_empty($fplist){
			if($fplist->head == null){
				return false;
			}
			else{
				return true;
			}
		}

		private function print_fplists($fplists){
			echo "=============<br>";
			foreach($fplists as $fplist){
				$current = $fplist->head;
				while(!is_null($current)){
					$parent = $current;
					while(!is_null($parent)){
						echo "{$parent->keywordId}({$parent->support}) =>";
						$parent = $parent->parent;
					}
					$current = $current->next;
					echo "<br>";
				}			
			}
			echo "==============<br>";
		}
	}

	class fpnode{
		public $keywordId; 
		public $support;
		public $parent;
		public $next;

		public function __construct(){
			$this->support = 0;
			$this->parent = null;
			$this->next = null;
		}
	}

	class fplist{
		public $keywordId;
		public $support;
		public $head;

		public function __construct(){
			$this->support = 0;
			$this->head = null;
		}
	}
?>