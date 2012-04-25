<?php
	include_once "recommender-system-tester.php";

	// all you should do is change the config array
	$config = array(
		'topN' => 10,
		'recommenders' => array(
			'key1'=>array(
				'name' => 'KeywordRecommender', 
				'weight' => 1,  // used in addRecommender()
				'config' => array(
					'name' => KEY_NO_EXPANSION,
				),
			),
			// 'key2' => array(
			// 	'name' => 'KeywordRecommender',
			// 	'weight' => 1,
			// 	'config' => array(
			// 		'name' => KEY_LINK_JACCARD,
			// 		'jaccard' => 0.001,
			// 		'expand_weight' => 0.1;
			// 	), 
			// ),
			// 'key3' => array(
			// 	'name' => 'KeywordRecommender',
			// 	'weight' => 1,
			// 	'config' => array(
			// 		'name' => KEY_LINK_COSINE,
			// 		'cosine' => 0.001,
			// 		'expand_weight' => 0.1;
			// 	),
			// ),
			// 'key4' => array(
			// 	'name' => 'RandomRecommender',
			// 	'weight' => 1,
			// 	'config' => array(), // random recommender doesn't need config params
			// ),
			// 'key5' => array(
			//  'name' => 'HottestRecommender',
			// 	'weight' => 1,
			// 	'config' => array(), // hottest recommender doesn't need config params
			// ),
			// 'key6' => array(
			// 	'name' => 'FPTreeRecommender',
			// 	'weight' => 1,
			// 	'config' => array(
			// 		'min_support' => 3,
			// 	),
			// ),
			// 'key7' => array(
			// 	'name' => 'PerfectRecommender',
			// 	'weight' => 1,
			// 	'config' => array(),
			// ),

			// you can add more recommenders
		),
		'splitters' => array( // you can use the same splitter with different config params or same config params to execute more times
			// 'key1' => array(
			// 	'name' => 'RandomSplitter',
			// 	'config' => array(
			// 		'train_percentage' => 0.7, // train_percentage + test_percentage should be 1
			// 		'test_percentage' => 0.3,
			// 	),
			// ),

			'key2' => array(
			 'name' => 'KFoldCrossSplitter',
				'config' => array(
					'k_fold' => 10,
				),
			),

			// you can add more splitters
		),
		'evaluators' => array(
			'key1' => array(
				'name' => 'HitEvaluator',
				'config' => array(
					'hit_threshold' => 1,
				),
			),
			'key2' => array(
				'name' => 'ConfusionMatrixEvaluator',
				'config' => array(),
			),
		),
	);

	$tester = new Tester($config);
	$tester->run();
?>