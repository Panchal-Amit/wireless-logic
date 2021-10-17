<?php

/**
 * Read html page from given url and get all require information in required formate
 * @author  Amit Panchal 
 */
class Scapper {

    private string $html = "";
    private $title = [];
    private $description = [];
    private $price = [];
    private $discount = [];
    private $finalPrice = [];

    public function __construct() {
        $this->html = file_get_contents("https://videx.comesconnected.com");
    }

    /**
     * To execute all require private function
     * @return  json      
     */
    public function execution() {
        $parentPackage = $this->getHTMLByClass('row-subscriptions', $this->html);
        $this->getTitle($parentPackage);
        $this->getDescrption($parentPackage);
        $this->getPrice($parentPackage);
        return $this->generateResult();
    }

    /**
     * To get specific portion from html using class
     * @return  string      
     */
    private function getHTMLByClass($class, $html) {
        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        @$doc->loadHTML($html);
        $dom = new DomXPath($doc);
        $innerHTML = "";
        foreach ($dom->query("//*[contains(@class, '$class')]") as $node) {
            if ($node) {
                $innerHTML .= $doc->saveHTML($node);
            }
        }
        return $innerHTML;
    }

    /**
     * To set package title from list of packages
     * @return  void      
     */
    private function getTitle($html) {
        $childPackage = $this->getHTMLByClass('header', $html);
        $titles = explode("\n", $childPackage);
        foreach ($titles as $val) {
            preg_match('#<h3[^>]*>(.*?)</h3>#i', $val, $match);
            if (count($match) > 0) {
                $this->title[] = $match[1];
            }
        }
    }

    /**
     * To set package description from list of packages
     * @return  void      
     */
    private function getDescrption($html) {
        $childPackage = $this->getHTMLByClass('package-name', $html);
        $descriptions = explode('<div class="package-name">', $childPackage);
        foreach ($descriptions as $individualDescription) {
            if (trim($individualDescription) != "")
                $this->description[] = strip_tags($individualDescription);
        }
    }

    /**
     * To set package price from list of packages
     * @return  void      
     */
    private function getPrice($html) {
        $childPackage = $this->getHTMLByClass('package-price', $html);
        $prices = explode('<div class="package-price">', $childPackage);
        $i = 0;
        foreach ($prices as $price) {
            if (trim($price) != "") {
                if (strpos(trim($price), 'red') !== false) {
                    preg_match('#<p[^>]*>(.*?)</p>#i', $price, $match);
                    if (count($match) > 0) {
                        $this->discount[$i] = $match[1];
                    }
                } else {
                    $this->discount[$i] = "";
                }
                $basePrice = explode("\n", strip_tags($price));
                $this->price[] = strip_tags($basePrice[0]);
                $i++;
            }
        }
    }

    /**
     * Generate final result from set values
     * @return  json
     */
    private function generateResult() {
        $totalPakage = count($this->title);
        for ($i = 0; $i < $totalPakage; $i++) {
            $this->finalPrice[$i] = array("title" => $this->title[$i], "description" => $this->description[$i], "price" => $this->price[$i], "discount" => $this->discount[$i]);
        }
        return utf8_decode(json_encode($this->finalPrice, JSON_UNESCAPED_UNICODE));
    }

}

$scrapper = new Scapper();
$json = $scrapper->execution();
echo $json;
?>