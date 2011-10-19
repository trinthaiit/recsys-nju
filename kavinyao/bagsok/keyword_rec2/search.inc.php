<?php
require_once('aggregate_utility.inc.php');

interface iKeywordSearcher{
    /**
     * Returns array of product-weight pair via given $keywords array.
     */
    public function getResult($keywords);
}

class AggregatedKeywordSearcher implements iKeywordSearcher{
    private function get_product_list_via($keyword){
        $query = "SELECT id, keyword_set, product FROM keywordset_product WHERE keyword_set LIKE '%$keyword%'";
        $result = mysql_query($query);
        $products = array();
        while($row = mysql_fetch_array($result)){
            $temp = array();
            $temp['id'] = $row['id'];
            $temp['keyword'] = $row['keyword_set'];
            $temp['product'] = $row['product'];
            $products[] = $temp;
        }

        return $products;
    }

    private function remove_duplicate(){
        $id_cache = array();
        return function($item) use (&$id_cache){
            $is_in = in_array($item['id'], $id_cache);
            if(!$is_in)
                $id_cache[] = $item['id'];
            return !$is_in;
        };
    }

    //use keywords size as weight
    private function simple_weight($keyword_string){
        $keyword_array = explode(' ', $keyword_string);
        remove_empty2($keyword_array);
        $weight = count($keyword_array);
        return $weight;
    }


    public function getResult($keywords){
        $all_result = array();
        foreach($keywords as $keyword){
            $result = $this->get_product_list_via($keyword);
            $all_result = array_merge($result, $all_result);
        }
        $dup_remover = $this->remove_duplicate();
        $filtered_result = array_filter($all_result, $this->remove_duplicate());

        //combine weight
        $product_weight_raw = array();
        foreach($filtered_result as $item){
            $weight = $this->simple_weight($item['keyword']);

            if(array_key_exists($item['product'], $product_weight_raw))
                $product_weight_raw[$item['product']] += $weight;
            else
                $product_weight_raw[$item['product']] = $weight;
        }

        //reorganize for sort
        $product_weight = array();
        foreach($product_weight_raw as $product => $weight)
            $product_weight[] = array($product, $weight);

        //sort by weight descendingly
        usort($product_weight, function($item1, $item2){
            return $item2[1] - $item1[1];
        });

        return $product_weight;
    }
}

class DirectKeywordSearcher implements iKeywordSearcher{
    private $C = 1000;

    private function getRelatedProducts($keyword){
        $select_query = "SELECT product FROM m2_keyword_product WHERE keyword = '$keyword'";
        $result = mysql_query($select_query);
        $products = array();
        while($row = mysql_fetch_array($result))
            $products[] = $row['product'];

        return $products;
    }

    private function getKeywordWeight($keyword){
        $select_query = "SELECT occurrence FROM m2_keyword WHERE keyword = '$keyword'";
        $result = mysql_query($select_query);
        $row = mysql_fetch_array($result);
        $occur = $row['occurrence'];
        $weight = log($this->C / $occur);

        return $weight;
    }

    public function getResult($keywords){
        //get related products from db
        //TODO handle case when $keyword is not in database
        $keyword_product_arr = array();
        $keywrod_weight = array();
        foreach($keywords as $keyword){
            $keyword_product_arr[$keyword] = $this->getRelatedProducts($keyword);
            $keyword_weight[$keyword] = $this->getKeywordWeight($keyword);
        }

        $product_weight_raw = array();
        foreach($keyword_product_arr as $keyword => $product_arr){
            $weight = $keyword_weight[$keyword];
            foreach($product_arr as $product){
                if(array_key_exists($product, $product_weight_raw))
                    $product_weight_raw[$product] += $weight;
                else
                    $product_weight_raw[$product] = $weight;
            }
        }

        $product_weight = array();
        foreach($product_weight_raw as $product => $weight)
            $product_weight[] = array($product, $weight);

        //sort by weight descendingly
        usort($product_weight, function($item1, $item2){
            return $item2[1] - $item1[1];
        });

        return $product_weight;
    }
}
?>
