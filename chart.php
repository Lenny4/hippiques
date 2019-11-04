<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
</head>
<body>

<div class="">
    <ul class="nav nav-tabs"></ul>
    <div class="tab-content"></div>
</div>

<?php $nbMatch = 30; ?>
<script>
    const maxRunnerOdd = 10;
    const avgBeforeTime = 2000;
    const ratio = 0.02;
</script>
<script>
    const matchs = [];
    let json = null;
    let match = null;
    <?php
    $files = scandir('json/');
    $index = 0;
    foreach ($files as $fileIndex => $file) {
    if ($file !== "." AND $file !== ".." AND $index < $nbMatch) {
    $fileName = str_replace(".json", "", $file);
    $json = file_get_contents('json/' . $file);?>
    json = (<?= $json; ?>);
    match = {
        name: "<?=$fileName;?>",
        json: json,
    };
    matchs.push(match);
    <?php
    $index++;
    }
    }
    ?>
</script>

<script>
    const ul = $("ul");
    const div = $("div.tab-content");
    matchs.map((match, index) => {
        let activeClass = "";
        let activeClass2 = "";
        if (index === 0) activeClass = "active";
        if (index === 0) activeClass2 = "in active";
        $(ul).append('<li class="' + activeClass + '"><a data-toggle="tab" href="#match_' + index + '">' + match.name + '</a></li>');
        $(div).append('<div id="match_' + index + '" class="tab-pane fade ' + activeClass2 + '"></div>');
    });
</script>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load('current', {'packages': ['corechart']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart(match, index) {
        const runnerNameToNotDisplay = [];
        const runnersIndex = [];
        const oddsByRunners = [];
        const chartDatas = [
            ['time', 'borneSup', 'borneInf']
        ];
        // selectionne les runners à afficher
        match.json.map((obj, index) => {
            const runners = obj.runners.filter(x => x.backOdd > maxRunnerOdd);
            if (Array.isArray(runners) && runners.length > 0) {
                runners.map((runner) => {
                    if (typeof runner === "object" && runner !== null && !runnerNameToNotDisplay.includes(runner.runnerName)) {
                        runnerNameToNotDisplay.push(runner.runnerName);
                    }
                });
            }
        });
        // initialise oddsByRunners
        match.json[0].runners.map((runner, indexRunner) => {
            if (runner.backOdd > 0 && !runnerNameToNotDisplay.includes(runner.runnerName)) {
                runnersIndex.push(indexRunner);
                chartDatas[0].push(runner.runnerName);
                oddsByRunners.push({
                    runnerName: runner.runnerName,
                    values: [],
                    avg: null,
                });
            }
        });
        // récupère les moyennes des runners sur les avgBeforeTime premières secondes
        match.json.map((obj, index) => {
            if (obj.time > -3600 + avgBeforeTime && obj.volume > 0) {
                obj.runners.map((runner, indexRunner) => {
                    if (runnersIndex.includes(indexRunner)) {
                        const oddRunner = oddsByRunners.find(x => x.runnerName === runner.runnerName);
                        oddRunner.values.push(runner.backOdd);
                    }
                });
            }
        });
        // calcule la moyenne pour tous les runners
        oddsByRunners.map((runnerOdds) => {
            runnerOdds.avg = runnerOdds.values.reduce(function (avg, value, _, {length}) {
                return avg + value / length;
            }, 0);
        });
        // fait le tableau pour dessiner le graphe
        match.json.map((obj, index) => {
            if (index <= 3700 && obj.volume > 0 && obj.time > -3600 + avgBeforeTime) {
                const array = [];
                const time = 3600 + obj.time;
                array.push(time);
                const borneSup = ratio * (Math.sqrt(time));
                array.push(borneSup);
                array.push(-borneSup);
                obj.runners.map((runner, indexRunner) => {
                    if (runnersIndex.includes(indexRunner)) {
                        const odd = runner.backOdd;
                        const oddRunner = oddsByRunners.find(x => x.runnerName === runner.runnerName);
                        array.push(odd - oddRunner.avg);
                    }
                });
                chartDatas.push(array);
            }
        });

        const data = google.visualization.arrayToDataTable(chartDatas);

        const options = {
            title: match.name,
            curveType: 'function',
            legend: {position: 'bottom'},
            height: 580,
            width: 1340,
            chartArea: {left: 50, top: 1, width: "95%", height: "90%"},
        };

        const chart = new google.visualization.LineChart(document.getElementById('match_' + index));

        chart.draw(data, options);
    }
</script>


<script>
    $(document).ready(() => {
        setTimeout(() => {
            matchs.map((match, index) => {
                drawChart(match, index);
            });
        }, 1000);
    });
</script>
</body>
</html>