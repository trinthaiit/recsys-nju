<?php
	include_once "../database/glass-database-manager.php";

	class HitEvaluator{
		private $dm;
		private $test_count;
		private $hit_count;

		public function __construct(){
			$this->dm = GlassDatabaseManager::getInstance();
		}

		public function start_evaluate(){
			$this->test_count = 0;
			$this->hit_count = 0;
		}

		/**
		 * Get the final result
		 */
		public function end_evaluate(){
			$percentage = $this->hit_count / $this->test_count;
			echo "test_count is {$this->test_count}, hit_count is {$this->hit_count}, hit percentage is $percentage <br />";
		}

		public function evaluate($query, $recommendItems){
			// echo "test start.....<br/>";
			// flush();
			// ob_flush();
			// $time_start = microtime(true);

			$this->test_count++;
			foreach($recommendItems as $item => $weight){
				$query_item_result = $this->dm->query("SELECT * FROM query_item WHERE queryId = '{$query['id']}' AND itemId = '{$item}'");
				$query_item_result_count = mysql_num_rows($query_item_result);
				assert('$query_item_result_count == 0 || $query_item_result_count == 1');
				if($query_item_result_count == 1){
					$this->hit_count++;
					break;
				}
			}

			// $time_end = microtime(true);
			// $cost_time = $time_end - $time_start;
			// echo 'test end......<br/>';
			// echo "cost time: $cost_time <br/>";
			// flush();
			// ob_flush();
		}
	}
?>