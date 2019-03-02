<?php

namespace App\Crawlers;

use Symfony\Component\DomCrawler\Crawler;


class SiteCrawler
{
    private $data;

    public function __construct(Crawler $data)
    {
        $this->data = $data;
    }

    public function get()
    {
        $links = $this->data
            ->filter('a')
            ->each(function (Crawler $node, $i) {
                $link = $node->link()->getUri();
                $checkLinked = strpos($link, 'linkedin.com/company');

                if ($checkLinked) {
                    return $link;
                }

                return false;
            });
        $links = $this->clearArray($links);

        if (empty($links)) throw new \Exception('Link to LinkedIn is undefined');

        $link = parse_url($links[0]);

        $link = 'https://linkedin.com' . $link['path'];

        return $link;

    }

    private function clearArray(array $array)
    {
        return array_values(array_diff($array, array('', NULL, false)));
    }
}