<?php
	include_once "../database/glass-database-manager.php";

	class ConfusionMatrixEvaluator{
		private $dm;
		private $test_count;
		private $metrics;
		private $total_item_count;
		private $time_start;
		private $time_end;

		public function __construct($config){
			$this->dm = GlassDatabaseManager::getInstance();		
		}

		public function start_evaluate(){
			echo 'ConfusionMatrixEvaluator start_evaluate<br/>';
			flush();
			ob_flush();
			$this->time_start = microtime(true);

			$this->test_count = 0;
			$this->metrics['accuracy'] = 0;
			$this->metrics['MAE'] = 0;
			$this->metrics['precision'] = 0;
			$this->metrics['recall'] = 0;

			$total_item_result = $this->dm->query("SELECT count(id) item_count FROM item");
			$total_item_row = mysql_fetch_array($total_item_result);
			$this->total_item_count = $total_item_row['item_count'];
		}

		public function end_evaluate(){
			$this->metrics['accuracy'] /= $this->test_count;
			$this->metrics['MAE'] /= $this->test_count;
			$this->metrics['precision'] /= $this->test_count;
			$this->metrics['recall'] /= $this->test_count;
			print_r($this->metrics);
			echo "<br/>";
			
			$this->time_end = microtime(true);
			$cost_time = $this->time_end - $this->time_start;
			echo 'ConfusionMatrixEvaluator end_evaluate<br/>';
			echo "cost time: $cost_time <br/>";
			flush();
			ob_flush();
		}

		public function evaluate($query, $recommendItems){
			$this->test_count++;
			$a = $b = $c = $d = 0;
			foreach($recommendItems as $item => $weight){
				$query_item_result = $this->dm->query("SELECT * FROM query_item WHERE queryId = '{$query['id']}' AND itemId = '{$item}'");
				$query_item_result_count = mysql_num_rows($query_item_result);
				assert('$query_item_result_count == 0 || $query_item_result_count == 1');
				if($query_item_result_count == 1){
					$d++;
				}
				else{
					$b++;
				}
			}
			$actual_positive_item_result = $this->dm->query("SELECT count(*) positive_count FROM query_item WHERE queryId = '{$query['id']}'");
			$actual_positive_item_row = mysql_fetch_array($actual_positive_item_result);
			$actual_positive_item_count = $actual_positive_item_row['positive_count'];
			assert('$actual_positive_item_count >= $d');

			$c = $actual_positive_item_count - $d;
			$a = $this->total_item_count - $b - $c - $d;
			assert('$a >= 0');

			$this->metrics['accuracy'] += ($a + $d) / ($a + $b + $c + $d);
			$this->metrics['MAE'] += ($b + $c) / ($a + $b + $c + $d);
			$this->metrics['precision'] += ($d) / ($b + $d);
			$this->metrics['recall'] += ($d) / ($c + $d);
		}
	}	

?>