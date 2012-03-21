<?php
interface iDatabaseManager {
    public function connect($host, $username, $password);

    public function useDatabase($database);

    public function dropTable($table);

    public function query($query);
}

/**
 * The QueryExtractor interface for extracting query string from raw url.
 * The implementation should be flexible enough to:
 * - deal with different search engines
 * - determine how to deal with punctuations
 * - remove stop words
 * - so on
 */
interface iQueryExtractor {
    /*
     * @param $url : a string of url
     * @return : a $delimiter-separated keyword string or empty string when no query
     *   could be extracted
     */
    public function extractQuery($url, $delimiter=" ");
}

/**
 * The RawDataProcessor interface for raw data preprocessing.
 * After the processing, the data should be populated to the following three tables:
 * - Query(session, query) in which session is a time period, query is a string of keywords
 * - Item(...) in which item informatin is kept
 * - Query-Item(QID, IID, bought) which is a Many-to-Many relation between Query
 *   and Item, is_bought indicates whether the user bought the item finally
 * TODO: the schema should be further discussed.
 */
interface iRawDataProcessor {
    /**
     * Append raw data of $source to the tables specified above.
     */
    public function processRawData($source);
}

/**
 * The WordSegmenter interface for word segmentation.
 * Given $sentence, a meaningful chunk of segments is returned.
 * @param $sentence : a space-separated string of English? words
 */
interface iWordSegmenter {
    public function segmentWords($sentence);
}

/**
 * The KeywordRecommender interface for recommending items with given $keywords
 * Generally there are three phases:
 * 1. preprocess: the recommender generates specific database tables to utilize later
 * 2. recommend: recommend items with given $keywords
 * 3. cleanup: erase the database tables specific to this recommender
 */
interface iKeywordRecommender {
    /**
     * @param $tables : an associative array contains at least three table names:
     *   $tables['queryTable'] : the name of the global Query table
     *   $tables['itemTable'] : the name of the global Item table
     *   $tables['queryItemTable'] : the name of the global Query-Item relation table
     * @param $startTime : an object indicates the start time of data preprocessing, 
     *   this is necessary for incremental data preprocessing
     * TODO: does Query table need a time field?
     * @return : void
     */
    public function preprocess($tables, $startTime=null);

    /**
     * @param $keywords : an array of keywords of type string
     * @return : an array of Items
     * TODO: determine the specific format of Item to return
     */
    public function recommend($keywords);

    public function cleanup();
}

/**
 * The ultimate interface of overall system.
 * Different recommenders can be added and removed, and thus various configurations can used.
 * Each recommender is associated with a $name and $weight, with $name the $weight can be adjusted.
 * The recommend function is similar to that of iKeywordRecommender.
 * TODO: is explicitly associate recommender with a weight reasonable?
 */
interface iKeywordRecommenderSystem {
    /**
     * Add $recommender with specified $name and $weight.
     * If the $name already exists, the existing recommender will be replaced with weight updated.
     * @return : True indicates no error; False else
     * TODO: is the replacement semantic reasonable?
     */
    public function addRecommender($name, $weight, $recommender);

    /**
     * Adjust the weight of recommender of given $name to $weight.
     * @return : True indicates no error; False else
     */
    public function adjustWeight($name, $newWeight);

    /**
     * Remove the recommender of given $name.
     * @return : True indicates no error; False else
     */
    public function removeRecommender($name);

    /**
     * Similar to that of iRecommender interface.
     */
    public function recommend($keywords);
}
?>
