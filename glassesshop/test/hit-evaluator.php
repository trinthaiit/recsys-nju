<?php
	include_once "../database/glass-database-manager.php";

	class HitEvaluator{
		private $dm;
		private $test_count;
		private $hit_count;
		private $hit_threshold;

		public function __construct($config){
			$this->dm = GlassDatabaseManager::getInstance();
			$this->hit_threshold = $config['hit_threshold'];
			if(!isset($this->hit_threshold)){
				$this->hit_threshold = 1;
				echo 'Dude, give me [hit_threshold] param';
				flush();
				ob_flush();
			}
		}

		public function start_evaluate(){
			echo 'HitEvaluator start_evaluate<br/>';
			flush();
			ob_flush();

			$this->test_count = 0;
			$this->hit_count = 0;
		}

		/**
		 * Get the final result
		 */
		public function end_evaluate(){
			$percentage = $this->hit_count / $this->test_count;
			echo "test_count is {$this->test_count}, hit_count is {$this->hit_count}, hit percentage is $percentage <br />";

			echo 'HitEvaluator end_evaluate<br/>';
			flush();
			ob_flush();
		}

		public function evaluate($query, $recommendItems){
			// echo "test start.....<br/>";
			// flush();
			// ob_flush();
			// $time_start = microtime(true);

			$this->test_count++;
			$localHit = 0;
			foreach($recommendItems as $item => $weight){			
				$query_item_result = $this->dm->query("SELECT * FROM query_item WHERE queryId = '{$query['id']}' AND itemId = '{$item}'");
				$query_item_result_count = mysql_num_rows($query_item_result);
				assert('$query_item_result_count == 0 || $query_item_result_count == 1');
				if($query_item_result_count == 1){
					$localHit++;
					if($localHit >= $this->hit_threshold){
						$this->hit_count++;
						break;
					}
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