<?php
define('BASE_PATH', realpath(dirname(__FILE__)));

require BASE_PATH . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use App\Crawlers\SiteCrawler;
use App\Crawlers\LinkedInCrawler;
use App\Config;
use App\Models\Company;

$capsule = new Capsule();
$capsule->addConnection([
    'driver'   => Config::get('driver'),
    'host'     => Config::get('host'),
    'database' => Config::get('database'),
    'username' => Config::get('username'),
    'password' => Config::get('db_password')
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

//www.alternanet.it
//www.siriuscom.com
//www.am-computer.com
$sites = (Company::select(['url'])->whereNull('name')->orWhere('name', '')->take(100)->get())->toArray();

if (count($sites) < 1) {
    echo "Script is finished \n";
    die;
}

$siteClient = (new \App\Clients\SiteClient())->get();
$linkedInClient = (new \App\Clients\LinkedInClient())->get();



$companies = [];

foreach ($sites as $site) {
    try {
        $crawler = $siteClient->request('GET', 'http://' . $site['url']);
        $crawler = new SiteCrawler($crawler);
        $link = $crawler->get();
    } catch (\Exception $e) {
        echo $e->getMessage() . "\n";
        $companies[] = [
            'url'  => $site['url'],
            'name' => 'none found'
        ];
        continue;
    }

    $crawler = $linkedInClient->request('GET', $link);
    $crawler = new LinkedInCrawler($crawler);

    $company = $crawler->get();
    $company['url'] = $site['url'];

    $companies[] = $company;

    sleep(3);
}

foreach ($companies as $company) {
    $location = null;
    if (isset($company['headquarter'])) {
        $location = $company['headquarter'];
        $location = $location['line1'] . ', ' . $location['city'] . ', ' . $location['geographicArea'] . ' ' . $location['postalCode'] . ', ' . $location['country'];
    }

    $postal_code = null;
    if (isset($company['headquarter'])) {
        $postal_code = $company['headquarter'];
        $postal_code = $postal_code['city'] . ', ' . $postal_code['geographicArea'];
    }

    $location2 = null;
    if (isset($company['confirmedLocations'])) {
        $location2 = $company['confirmedLocations'][0];
        $location2 = $location2['line1'] . ', ' . $location2['city'] . ', ' . $location2['geographicArea'] . ' ' . $location2['postalCode'] . ', ' . $location2['country'];
    }

    Company::where('url', $company['url'])
        ->update([
            'name'           => $company['name'],
            'category'       => isset($company['localizedName']) ? $company['localizedName'] : null,
            'location'       => $location,
            'location_2'     => $location2,
            'postal_code'    => $postal_code,
            'followers'      => isset($company['followerCount']) ? $company['followerCount'] . ' followers' : null,
            'employee_count' => isset($company['staffCount']) ? $company['staffCount'] . ' staff on linked in' : null,
            'overview'       => isset($company['description']) ? $company['description'] : null,
            'company_size'   => isset($company['staffCountRange']) ? $company['staffCountRange']['start'] . ' - ' . $company['staffCountRange']['end'] . ' staff' : null,
            'type'           => isset($company['companyType']) ? $company['companyType']['localizedName'] : null,
            'founded'        => isset($company['foundedOn']) ? $company['foundedOn']['year'] : null,
            'specialties'    => isset($company['specialities']) ? implode(', ', $company['specialities']) : null,
            'phone'          => isset($company['phone']) ? $company['phone']['number'] : null,
        ]);

}

echo "Script is finished \n";







