<?php
use Github\Client;
use Github\ResultPager;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/mysql.php';
$mysql = new PDOWrapper();

$client = new Client();
$client->authenticate(GITHUB_TOKEN, null, Github\Client::AUTH_ACCESS_TOKEN);
$paginator = new ResultPager($client);
echo "----- New insert at ".date('Y-m-d H:i:s').PHP_EOL;

//insert entry
$sql = 'INSERT INTO `entry` (`datetime`) VALUES (CURRENT_TIMESTAMP);';
$mysql->query($sql);
$entry_id = $mysql->lastInsertId();

foreach (BRANCHES as $branch) {
    //insert entry in results
    echo "Checking $branch... ";
    $prs = $client->api('search')->issues('repo:PrestaShop/PrestaShop is:pr is:open label:'.$branch.' label:"waiting for QA" -label:"waiting for author"');
    $sql = 'INSERT INTO `data` (`entry_id`, `branch`, `value`) VALUES (:entry_id, :branch, :value);';
    $data = [
        'entry_id' => $entry_id,
        'branch' => $branch,
        'value' => $prs['total_count'],
    ];
    $sth = $mysql->query($sql, $data);

    echo "(".$prs['total_count']." PRs)".PHP_EOL;
}
echo "-----".PHP_EOL.PHP_EOL;
