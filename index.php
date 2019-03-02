<?php
define('BASE_PATH', realpath(dirname(__FILE__)));

require BASE_PATH . '/vendor/autoload.php';

use App\Crawlers\SiteCrawler;
use App\Crawlers\LinkedInCrawler;

$siteClient = (new \App\Clients\SiteClient())->get();
$linkedInClient = (new \App\Clients\LinkedInClient())->get();

$sites = ['https://www.alternanet.it/', 'https://www.am-computer.com/', 'https://www.alphakor.com'];

$companies = [];

foreach ($sites as $site) {

    try {
        $crawler = $siteClient->request('GET', $site);
        $crawler = new SiteCrawler($crawler);
        $link = $crawler->get();
    } catch (\Exception $e) {
        echo $e->getMessage();
        continue;
    }

    $crawler = $linkedInClient->request('GET', $link . '/about');
    $crawler = new LinkedInCrawler($crawler);
    $companies[] = $crawler->get();

}

dd($companies);






