<?php

require_once __DIR__ . '/mysql.php';
$mysql = new PDOWrapper();

$start_date = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 week" ) );
$end_date = date('Y-m-d');
$branch = 'All';

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = date('Y-m-d', strtotime($_GET['start_date']));
    $end_date = date('Y-m-d', strtotime($_GET['end_date']));
}

//get data
$sql = "SELECT e.id, e.datetime, DATE_FORMAT(e.datetime, '%Y-%m-%d %H:%i') date_formatted, d.branch, d.value
FROM entry e
INNER JOIN data d ON e.id = d.entry_id
WHERE 1=1
AND e.datetime > :start_date
AND e.datetime < :end_date
ORDER BY e.datetime ASC;";
$data = [
    'start_date' => $start_date,
    'end_date' => date('Y-m-d', strtotime($end_date) + 3600*24),
];
$rows = $mysql->query($sql, $data);
$results = [];
$branches = [];
$max = 0;
foreach($rows as $row) {

    $results[$row['date_formatted']][$row['branch']][] = (int) $row['value'];
    if ($row['value'] > $max) {
        $max = $row['value'];
    }
    if (!in_array($row['branch'], $branches)) {
        $branches[] = $row['branch'];
    }
}

$dates = array_keys($results);

$colors = [
    'rgb(55, 55, 150)',
    'rgb(50, 132, 184)',
    'rgb(41, 158, 72)',
    'rgb(158, 76, 41)',
];
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">

    <style>
        .container {
            background-color: #EEE;
            margin-top: 20px;
            padding-top: 10px;
            padding-bottom: 10px;
        }

        span.branch {
            font-family: 'Courier New', serif;
            font-weight: bold;
        }
    </style>

    <title>PrestaShop WFQA PR Stats</title>
</head>
<body>
<div class="container">
    <h1>PrestaShop WFQA PR Stats</h1>
    <br>
    <p>This graph shows how many Pull Requests are actually labelled with the <strong>Waiting for QA</strong> label for the branches
        <?php echo implode(', ', array_map(
                function ($branch) {
                    return sprintf("<span class='branch'>%s</span>", $branch);
                }
            , $branches)); ?>.</p>
    <p>It is updated every 6 hours.</p>
    <form>
        <div class="form-row">
            <div class="col">
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>" placeholder="Start Date">
            </div>
            <div class="col">
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>" placeholder="End Date">
            </div>
            <div class="col">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </form>
    <hr>
    <canvas id="data"></canvas>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<script>
  var config = {
    type: 'line',
    data: {
      labels: <?php echo json_encode(array_keys($results)); ?>,
      datasets:
        <?php
            $data = [];
            foreach ($branches as $branch) {
                foreach($dates as $date) {
                    $data[$branch][] = isset($results[$date][$branch]) ? $results[$date][$branch] : null;
                }
            }
            $json = [];
            $ic = 0;
            foreach($data as $branch => $value) {
                $json[] = [
                        'label' => $branch,
                        'borderColor' => $colors[$ic%count($colors)],
                        'data' => $value
                ];
                $ic++;
            }
            echo json_encode($json);
        ?>
    },
    options: {
      responsive: true,
      title: {
        display: true,
        text: 'Number of PRs in WFQA per branch'
      },
      tooltips: {
        mode: 'index',
        intersect: false,
      },
      hover: {
        mode: 'nearest',
        intersect: true
      },
      scales: {
        xAxes: [{
          display: true,
          scaleLabel: {
            display: true,
            labelString: 'Date'
          }
        }],
        yAxes: [{
          display: true,
          scaleLabel: {
            display: true,
            labelString: 'Number of PRs'
          },
          ticks: {
            beginAtZero: true
          }
        }]
      }
    }
  };
    var ctx = document.getElementById('data').getContext('2d');
    window.myLine = new Chart(ctx, config);
</script>
</body>
</html>
