<?php
	include_once "../database/glass-database-manager.php";
	include_once "../preprocess/glass-raw-data-processor.php";
	include_once "../recommendersystem/keyword-recommender.php";
	include_once "../recommendersystem/keyword-recommender-system.php";

	class Tester{
		private $dm;
		private $rawDataProcessor;
		private $recomender;
		private $system;

		public function __construct(){
			$this->dm = GlassDatabaseManager::getInstance();
			$this->rawDataProcessor = new GlassRawDataProcessor();
			$this->recommender = new KeywordRecommender();
			$this->system = new KeywordRecommenderSystem();
		}

		public function run(){
			//$this->rawDataProcessor->processRawData();
			//$this->build_train_and_test_set();
			//$this->train();
			$this->test(10);
		}

		public function build_train_and_test_set(){
			// split query table into query_train(train set) and query_test(train set)

			// method: 80% train and 20% test
			// copy query to query_train table with all datas
			// prepare database tables
			$this->dm->query("drop table if exists query_train");
			$this->dm->query("create table query_train like query");
			$this->dm->query("drop table if exists query_test");
			$this->dm->query("create table query_test like query");

			$this->dm->query("insert into query_train select * from query");
			$query_results = $this->dm->query("select count(id) query_count from query");
			$query_row = mysql_fetch_array($query_results);
			$query_count = $query_row['query_count'];
			$test_count = round($query_count * 0.2);
			$query_results = $this->dm->query("select * from query_train order by rand() limit $test_count");

			while($query_row = mysql_fetch_array($query_results)){
				$query_row['query'] = addslashes($query_row['query']);
				$this->dm->query("insert into query_test (id, userId, query) 
					values ({$query_row['id']}, '{$query_row['userId']}', '{$query_row['query']}')");
				$this->dm->query("delete from query_train where id = {$query_row['id']}");
			}

			// build end
		}

		public function train(){
			$tables = array();
			$tables['query'] = 'query_train';
			$tables['query_item'] = 'query_item';
			//$this->recommender->preprocess($tables);
			//$this->system->wordAssociationWithJaccardPreprocess(0.2, $tables);
			//$this->system->collaborativeFilteringWithSlopeOnePreprocess();
		}

		public function test($topN){
			$this->system->addRecommender(KEY_COL_SLOPEONE, 0.001);
			// compute hits rate
			$test_num = 0;
			$hits = 0;
			$query_result = $this->dm->query("select distinct(query) query from query_test");
			while($query_row = mysql_fetch_array($query_result)){
				$test_num++;
				$items = $this->system->recommend($query_row['query']);
				//print_r($items);
				$recommendedItems = array_slice($items, 0, $topN);
				foreach($recommendedItems as $item => $weight){
					$result = $this->dm->query("select * from query_test, query_item where query_test.id = query_item.queryId AND 
											query_test.query = '{$query_row['query']}' AND query_item.itemId = '{$item}'");
					if(mysql_num_rows($result) > 0){
						$hits++;
						break;
					}
				}
				echo $test_num."<br />";
				flush();
				ob_flush();
			}

			$percentage = $hits/$test_num;
			echo "test_num is $test_num, hits is $hits, percentage is $percentage";
		}
	}

?>
