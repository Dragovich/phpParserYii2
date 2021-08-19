<?php

/* @var $this yii\web\View */

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use yii\helpers\Html;
use Symfony\Component\DomCrawler\Crawler;

$this->title = 'About';
$this->params['breadcrumbs'][] = $this->title;

$page = 'https://www.princetonreview.com/college-search?ceid=cp-1022984&page=';
$highSchool = [];

$browser = new HttpBrowser(HttpClient::create());

for ($i = 1; $i <= 2; $i++) {
    $pageRequest = $page . $i;
    $crawler = $browser->request('GET', $pageRequest);
    $_COOKIE['page'] = $i;
    $highSchoolFromPage = $crawler->filter('.row .vertical-padding')->each(function (Crawler $node) {
        try {
            $name = $node->filter('.margin-top-none')->text();

            try {
                $location = explode(', ', $node->filter('.location')->text());
                $city = $location[0];
                $state = $location[1];
            } catch (Exception $e) {
                $city = '';
                $state = '';
            }

            $src = $node->filterXPath('//img')->extract(['src']);

            return [
                'name' => $name,
                'city' => $city,
                'state' => $state,
                'src' => $src
            ];
        } catch (Exception $e) {
            return [];
        }
    });
    $highSchool = array_merge($highSchool, $highSchoolFromPage);
//    $highSchool += $highSchoolFromPage;
}
echo '<pre>';
var_dump(count($highSchoolFromPage));
var_dump($highSchoolFromPage);
echo '</pre>';


?>

