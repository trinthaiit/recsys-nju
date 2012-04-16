<?php
	include_once "../database/glass-database-manager.php";
	include_once "../preprocess/glass-raw-data-processor.php";
	include_once "../recommendersystem/keyword-recommender-system.php";

	/* ---------  recommender -------------------- */
	include_once "../recommendersystem/keyword-recommender.php";
	include_once "../recommendersystem/random-recommender.php";	
	include_once "../recommendersystem/hottest-recommender.php";

	/* ---------  splitter -------------------- */
	include_once "random-splitter.php";
	include_once "k-fold-cross-splitter.php";

	/* ---------  evaluator -------------------- */
	include_once "confusion-matrix-evaluator.php";
	include_once "hit-evaluator.php";
	
	class Tester{
		private $dm;
		private $rawDataProcessor;
		private $recommenders;
		private $system;
		private $splitters;
		private $evaluators;
		private $topN;

		public function __construct($config){
			$this->dm = GlassDatabaseManager::getInstance();
			$this->rawDataProcessor = new GlassRawDataProcessor();
			$this->system = new KeywordRecommenderSystem();
			$this->topN = $config['topN'];

			$this->recommenders = array();
			foreach($config['recommenders'] as $key => $recommender){
				$recommender_name = $recommender['name'];
				$this->recommenders[$key] = new $recommender_name($recommender['config']);
				$this->system->addRecommender($key, $recommender['weight'], $this->recommenders[$key]);
			}

			$this->splitters = array();
			foreach($config['splitters'] as $key => $splitter){
				$splitter_name = $splitter['name'];
				$this->splitters[$key] = new $splitter_name($splitter['config']);
			}

			$this->evaluators = array();
			foreach($config['evaluators'] as $key => $evaluator){
				$evaluator_name = $evaluator['name'];
				$this->evaluators[$key] = new $evaluator_name($evaluator['config']);
			}
		}

		public function run(){
			// you can change this params
			$tables = array();
			$tables['query'] = 'query_train';
			$tables['query_item'] = 'query_item';
			$tables['query_test'] = 'query_test';
			$topN = $this->topN;

			$this->rawDataProcessor->processRawData();

			foreach($this->splitters as $splitter){
				$splitter->start_split();
				$continue = true;
				while($continue){ // split query into query train set and query test set
					$continue = $splitter->split();

					// train part
					foreach($this->recommenders as $recommender){
						$recommender->preprocess($tables);
					}

					// test part
					foreach($this->evaluators as $evaluator){
						$evaluator->start_evaluate();
						$query_result = $this->dm->query("select * from query_test");
						while($query_row = mysql_fetch_array($query_result)){
							$items = $this->system->recommend($query_row['query']);
							$recommendItems = array_slice($items, 0, $topN);
							$evaluator->evaluate($query_row, $recommendItems);
						}
						$evaluator->end_evaluate();
					}
				}
				$splitter->end_split();
			}
		}
	}
?>
