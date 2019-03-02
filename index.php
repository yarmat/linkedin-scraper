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


$siteClient = (new \App\Clients\SiteClient())->get();
$linkedInClient = (new \App\Clients\LinkedInClient())->get();

$sites = (Company::select(['url'])->get())->toArray();

$companies = [];

foreach ($sites as $site) {
    try {
        $crawler = $siteClient->request('GET', $site['url']);
        $crawler = new SiteCrawler($crawler);
        $link = $crawler->get();
    } catch (\Exception $e) {
        echo $e->getMessage() . "\n";
        continue;
    }

    $crawler = $linkedInClient->request('GET', $link . '/about');
    $crawler = new LinkedInCrawler($crawler);

    $company = $crawler->get();
    $company['url'] = $site['url'];

    $companies[] = $company;

}

foreach ($companies as $company) {

    $location = null;
    if (isset($company['confirmedLocations'][0])) {
        $location = $company['confirmedLocations'][0];
        $location = $location['line1'] . ', ' . $location['city'] . ', ' . $location['geographicArea'] . ' ' . $location['postalCode'] . ', ' . $location['country'];
    }

    Company::where('url', $company['url'])
        ->update([
            'name'           => $company['name'],
            'category'       => isset($company['localizedName']) ? $company['localizedName'] : null,
            'location'       => $location,
            'followers'      => isset($company['followerCount']) ? $company['followerCount'] . ' followers' : null,
            'employee_count' => isset($company['staffCount']) ? $company['staffCount'] . ' staff on linked in' : null,
            'overview'       => isset($company['description']) ? $company['description'] : null,
            'company_size'   => isset($company['staffCountRange']) ? $company['staffCountRange']['start'] . ' - ' . $company['staffCountRange']['end'] . ' staff' : null,
            'type'           => isset($company['companyType']) ? $company['companyType']['localizedName'] : null,
            'founded'        => isset($company['foundedOn']) ? $company['foundedOn']['year'] : null,
            'specialties'    => isset($company['specialities']) ? implode(', ', $company['specialities']) : null,
        ]);

}

echo "Script is finished \n";







