<?php
include_once "../interface/recsys-interface.php";

class SiteBuiltinSearch implements iKeywordRecommender {
    private static $_cache = array();
    private static $hit_count = 0;
    private static $miss_count = 0;
    private static $hottest_keywords = array('glasses', 'cheap', 'eyeglasses', 'prescription', 'frames', 'sunglasses', 'online', 'eye', 'discount', 'eyeglass');


    private static function create_html_dom($url) {
        $html = file_get_contents($url);
        $dom = new DOMDocument();
        // use @ to suppress warnings
        @$dom->loadHTML($html);
        return $dom;
    }

    private static function get_sku($url) {
        $dom = SiteBuiltinSearch::create_html_dom($url);
        $elem = $dom->getElementById('pSKU');

        if(! is_object($elem))
            return 'UNKNOWN';

        $text = $elem->textContent;
        $pattern = '/^SKU: (.*)$/';
        $matches = array();
        $times = preg_match($pattern, $text, $matches);
        if(isset($matches[1])) {
            return $matches[1];
        } else {
            return 'UNKNOWN';
        }
    }

    /**
     * given an array of keywords
     * return an array of product sku
     */
    private static function search($keywords) {
        set_time_limit(0);

        $search_param = implode('+', $keywords);
        $url = 'http://www.glassesshop.com/search/?ser_color=&ser_material=&ser_circle=&ser_price=&keywords=' . $search_param . '&search=';
        $dom = SiteBuiltinSearch::create_html_dom($url);
        $xpath = new DOMXPath($dom);

        $xpath_query = '/html/body/div/div[4]/div[2]/div[3]/div/dl/dt/a';
        $nodes = $xpath->query($xpath_query);

        $results = array();
        foreach($nodes as $node) {
            // the node is of type XML_ELEMENT_NODE
            // so it's ok to use getAttribute method
            $prod_url = $node->getAttribute('href');

            echo '<p>processing URL:' . $prod_url . '</p>';
            if(! array_key_exists($prod_url, SiteBuiltinSearch::$_cache)) {
                echo '<p>URL exists in cache</p>';
                SiteBuiltinSearch::$miss_count++;
                SiteBuiltinSearch::$_cache[$prod_url] = SiteBuiltinSearch::get_sku($prod_url);
            }
            else {
                SiteBuiltinSearch::$hit_count++;
            }

            $sku = SiteBuiltinSearch::$_cache[$prod_url];
            if($sku != 'UNKNOWN')
                $results[] = $sku;
        }

        // appending weight
        $result_count = count($results);
        $results_with_weight = array();
        for($i = 0; $i < $result_count; $i++) {
            $results_with_weight[$results[$i]] = $result_count - $i;
        }

        return $results_with_weight;
    }

    public function __construct($argArray = '') {
        // pass
    }

    public function preprocess($tables, $startTime=null) {
        // this restricts concurrency
        SiteBuiltinSearch::$hit_count = 0;
        SiteBuiltinSearch::$miss_count = 0;
    }

    public function recommend($keywords) {
        echo '<pre>--------------------------------------------------------------';
        echo '<p>processing keywords "' . $keywords . '"</p>';
        $actual_keywords = explode(' ', $keywords);
        echo 'Actual keywords are:';
        print_r($actual_keywords);
        
        // filter out hottest keywords
        $effective_keywords = array();
        foreach($actual_keywords as $keyword) {
            if(! in_array($keyword, SiteBuiltinSearch::$hottest_keywords))
                $effective_keywords[] = $keyword;
        }
        echo 'Effective keywords are:';
        print_r($effective_keywords);
        $result = SiteBuiltinSearch::search($effective_keywords);
        echo 'Recommendations are:';
        print_r($result);
        echo '--------------------------------------------------------------</pre>';
    }

    public function cleanup() {
        echo '<p>hit ratio: ';
        echo SiteBuiltinSearch::$hit_count / (SiteBuiltinSearch::$hit_count + SiteBuiltinSearch::$miss_count);
        echo '</p>';
    }

}

function test() {
    $time_start = microtime(true);
    echo '<pre>';
    $ss = new SiteBuiltinSearch();
    print_r($ss->recommend('red eyeglasses frame'));
    echo '</pre>';
    $ss->cleanup();
    $time_end = microtime(true);
    print 'cost time: '. ($time_end - $time_start);
}
?>

