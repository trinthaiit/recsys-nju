<?php
include_once "../interface/recsys-interface.php";

class SiteBuiltinSearch implements iKeywordRecommender {
    private static $_cache = array();

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

        $text = $elem->textContent;
        $pattern = '/^SKU: (.*)$/';
        $matches = array();
        $times = preg_match($pattern, $text, $matches);
        return $matches[1];
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

            if(! array_key_exists($prod_url, SiteBuiltinSearch::$_cache)) {
                SiteBuiltinSearch::$_cache[$prod_url] = SiteBuiltinSearch::get_sku($prod_url);
            }
            $results[] = SiteBuiltinSearch::$_cache[$prod_url];

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
        // pass
    }

    public function recommend($keywords) {
        $actual_keywords = explode(' ', $keywords);
        return SiteBuiltinSearch::search($actual_keywords);
    }

    public function cleanup() {
        // pass
    }

}

$ss = new SiteBuiltinSearch();
echo '<pre>';
print_r($ss->recommend('frame red'));
echo '</pre>';
?>

