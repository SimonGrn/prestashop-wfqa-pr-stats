<?php
use Github\Client;
use Github\ResultPager;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/mysql.php';

//Github token data
if (!file_exists(__DIR__ . '/token.txt')) {
    exit("Token file not found." . PHP_EOL);
}
$token = file_get_contents(__DIR__ . '/token.txt');

//Branches data
if (!file_exists(__DIR__ . '/branches.txt')) {
    exit("Branches file not found." . PHP_EOL);
}
$branches_data = file_get_contents(__DIR__ . '/branches.txt');
$branches = array_filter(explode(PHP_EOL, $branches_data));

$client = new Client();
$client->authenticate($token, null, Github\Client::AUTH_ACCESS_TOKEN);
$paginator = new ResultPager($client);

foreach ($branches as $branch) {
    //insert entry in results
    echo "Checking $branch...".PHP_EOL;
    $prs = $client->api('search')->issues('repo:PrestaShop/PrestaShop is:pr is:open label:'.$branch.' label:"waiting for QA" -label:"waiting for author"');

    $sql = 'INSERT INTO `entry` (`branch`, `datetime`, `value`) VALUES (:branch, CURRENT_TIMESTAMP, :value);';
    $sth = $pdo->prepare($sql);
    $sth->execute([
        'branch' => $branch,
        'value' => $prs['total_count'],
    ]);
}

