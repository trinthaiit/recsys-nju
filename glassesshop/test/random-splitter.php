<?php
	include_once "../database/glass-database-manager.php";

	class RandomSplitter{
		private $dm;

		public function __construct(){
			$this->dm = GlassDatabaseManager::getInstance();

			// you can change the following two params
			$this->trainPercentage = 0.8;
			$this->testPercentage = 0.2;
		}

		public function start_split(){

		}

		public function end_split(){

		}

		/**
		 * split query table into query_train(train set) and query_test(test set)
		 */
		public function split(){
			echo "RandomSplitter split start.....<br/>";
			flush();
			ob_flush();
			$time_start = microtime(true);

			assert('$this->trainPercentage + $this->testPercentage == 1');
			// prepare database tables
			$this->dm->query("drop table if exists query_train");
			$this->dm->query("create table query_train like query");
			$this->dm->query("drop table if exists query_test");
			$this->dm->query("create table query_test like query");

			// get size of test set
			$query_count_results = $this->dm->query("select count(id) query_count from query");
			$query_count_row = mysql_fetch_array($query_count_results);
			$query_count = $query_count_row['query_count'];
			$test_count = round($query_count * $this->testPercentage);

			// fill query_test and query_train
			$this->dm->query("insert into query_train select * from query");
			$test_query_results = $this->dm->query("select * from query_train order by rand() limit $test_count");

			while($test_query_row = mysql_fetch_array($test_query_results)){
				$this->dm->query("insert into query_test (id, userId, query) 
								 values ({$test_query_row['id']}, '{$test_query_row['userId']}', '{$test_query_row['query']}')");
				$this->dm->query("delete from query_train where id = {$test_query_row['id']}");
			}

			$time_end = microtime(true);
			$cost_time = $time_end - $time_start;
			echo 'RandomSplitter split end......<br/>';
			echo "RandomSplitter cost time: $cost_time <br/>";
			flush();
			ob_flush();

			// no more iterations
			return false;
		}
	}
?>