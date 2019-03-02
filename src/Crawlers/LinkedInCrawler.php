<?php

namespace App\Crawlers;

use Symfony\Component\DomCrawler\Crawler;

class LinkedInCrawler
{
    private $data;

    const KEYS = [
        'name',
        'followerCount',
        'staffCount',
        'specialities',
        'confirmedLocations',
        'description',
        'staffCountRange',
        'companyType',
        'localizedName'
    ];

    public function __construct(Crawler $data)
    {
        $this->data = $data;
    }

    public function get()
    {
        $codes = $this->data->filter('code')->each(function (Crawler $node, $i) {
            if (strpos($node->text(), '"description"')) {
                return $node->text();
            }
            return null;
        });

        $codes = $this->clearArray($codes);

        return $this->prepareData($codes);
    }

    private function prepareData($data)
    {

        $included = json_decode($data[1], true)['included'];

        $companyInfo = [];

        foreach ($included as $array) {
            if (is_array($array)) {
                foreach ($array as $key => $item) {
                    if (in_array($key, self::KEYS)) {
                        $companyInfo[$key] = $item;
                    }
                }
            }
        }

        return $companyInfo;
    }

    private function clearArray(array $array)
    {
        return array_values(array_diff($array, array('', NULL, false)));
    }
}