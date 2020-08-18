<?php

require_once __DIR__ . '/mysql.php';

$start_date = date("Y-m-d", strtotime( date( "Y-m-d", strtotime( date("Y-m-d") ) ) . "-1 month" ) );
$end_date = date('Y-m-d');
$branch = 'All';

//get branches
$sql = "SELECT DISTINCT(branch) FROM entry;";
$sth = $pdo->prepare($sql);
$sth->execute();
$rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
$branches = [
    'develop'
];
$branch = 'develop';
foreach($rows as $row) {
    if ($row['branch'] != 'develop') {
        $branches[] = $row['branch'];
    }
}

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = date('Y-m-d', strtotime($_GET['start_date']));
    $end_date = date('Y-m-d', strtotime($_GET['end_date']));
}

if (isset($_GET['branch']) && in_array($_GET['branch'], $branches)) {
    $branch = $_GET['branch'];
}

//get data
$sql_values = [
    'start_date' => $start_date,
    'end_date' => date('Y-m-d', strtotime($end_date) + 3600*24),
    'branch' => $branch,
];
$sql = "SELECT datetime, value
FROM entry
WHERE 1=1
AND datetime > :start_date
AND datetime < :end_date
AND branch = :branch 
ORDER BY datetime ASC;";
$sth = $pdo->prepare($sql);
$sth->execute($sql_values);
$rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
$results = [];
$labels = [];
$max = 0;
foreach($rows as $row) {
    $labels[] = date('Y-m-d H:i', strtotime($row['datetime']));
    $results[] = (int) $row['value'];
    if ($row['value'] > $max) {
        $max = $row['value'];
    }
}

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
    </style>

    <title>PrestaShop PR Browsing</title>
</head>
<body>
<div class="container">
    <h1>PrestaShop PR Browsing</h1>
    <form>
        <div class="form-row">
            <div class="col">
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>" placeholder="Start Date">
            </div>
            <div class="col">
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>" placeholder="End Date">
            </div>
            <div class="col">
                <select class="form-control" id="branch" name="branch">
                    <?php
                        foreach($branches as $br) {
                            $s = '';
                            if ($br == $branch) {
                                $s = 'selected';
                            }
                            echo '<option value="'.$br.'" '.$s.'>'.$br.'</option>';
                        }
                    ?>
                </select>
            </div>
            <div class="col">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </form>
    <hr>
    <canvas id="data"></canvas>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>
<script>
  var config = {
    type: 'line',
    data: {
      labels: <?php echo json_encode(array_values($labels)); ?>,
      datasets: [
        {
          label: '<?php echo $branch; ?>',
          borderColor: '<?php echo $colors[rand(0, count($colors)-1)]; ?>',
          data: <?php echo json_encode($results, JSON_NUMERIC_CHECK); ?>
        }
      ]
    },
    options: {
      responsive: true,
      title: {
        display: true,
        text: 'Number of PR per branch'
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
            labelString: 'Number of PR'
          },
          ticks: {
            beginAtZero: true,
            stepSize: 1,
            max:<?php echo $max+2; ?>
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
