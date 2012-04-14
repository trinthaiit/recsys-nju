<?php
	include_once "../database/glass-database-manager.php";
	include_once "../preprocess/glass-raw-data-processor.php";
	include_once "../recommendersystem/keyword-recommender-system.php";

	/* ---------  recommender -------------------- */
	include_once "../recommendersystem/keyword-recommender.php";

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
		private $splitter;
		private $evaluator;

		public function __construct(){
			$this->dm = GlassDatabaseManager::getInstance();
			$this->rawDataProcessor = new GlassRawDataProcessor();
			$this->system = new KeywordRecommenderSystem();

			$this->recommenders = array();
			$this->recommenders['keyword_recommender'] = new KeywordRecommender();

			$this->splitter = new RandomSplitter();
			$this->evaluator = new HitEvaluator();
		}

		public function run(){
			// you can change this params
			$tables = array();
			$tables['query'] = 'query_train';
			$tables['query_item'] = 'query_item';
			$topN = 10;

			$this->rawDataProcessor->processRawData();

			$this->system->addRecommender('keyword_recommender', 1, $this->recommenders['keyword_recommender']);

			$this->splitter->start_split();
			$continue = true;
			while($continue){ // split query into query train set and query test set
				$continue = $this->splitter->split();
				
				// train part
				foreach($this->recommenders as $recommender){
					$recommender->preprocess($tables);
				}

				// test part
				$this->evaluator->start_evaluate();
				$query_result = $this->dm->query("select * from query_test");
				while($query_row = mysql_fetch_array($query_result)){
					$items = $this->system->recommend($query_row['query']);
					$recommendItems = array_slice($items, 0, $topN);
					$this->evaluator->evaluate($query_row, $recommendItems);
				}
				$this->evaluator->end_evaluate();
			}
			$this->splitter->end_split();
		}
	}
?>
