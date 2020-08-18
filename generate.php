<?php
use Github\Client;
use Github\ResultPager;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/mysql.php';

$client = new Client();
$client->authenticate(GITHUB_TOKEN, null, Github\Client::AUTH_ACCESS_TOKEN);
$paginator = new ResultPager($client);
echo "----- New insert at ".date('Y-m-d H:i:s').PHP_EOL;

foreach (BRANCHES as $branch) {
    //insert entry in results
    echo "Checking $branch... ";
    $prs = $client->api('search')->issues('repo:PrestaShop/PrestaShop is:pr is:open label:'.$branch.' label:"waiting for QA" -label:"waiting for author"');

    $sql = 'INSERT INTO `entry` (`branch`, `datetime`, `value`) VALUES (:branch, CURRENT_TIMESTAMP, :value);';
    $sth = $pdo->prepare($sql);
    $sth->execute([
        'branch' => $branch,
        'value' => $prs['total_count'],
    ]);
    echo "(".$prs['total_count']." PRs)".PHP_EOL;
}
echo "-----".PHP_EOL.PHP_EOL;
