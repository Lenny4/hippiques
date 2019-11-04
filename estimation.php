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
Ouvre ta console : Ctrl + Alt + i
<?php $nbMatch = 1; ?>
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
    const maxRunnerOdd = 10;
    const avgBeforeTime = 2000;
    const ratio = 0.02;

    function getFormatedMatchAndAvg(match) {
        const runnerNameToNotDisplay = [];
        const runnersName = [];
        const oddsByRunners = [];
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
                runnersName.push(runner.runnerName);
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
                    if (runnersName.includes(runner.runnerName)) {
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
        // retire la prop values de oddsByRunners
        oddsByRunners.map((runnerOdds) => delete runnerOdds.values);
        return {
            runnersName: runnersName,
            avgByRunner: oddsByRunners,
        };
    }

    // c'est ici qu'on travaille
    $(document).ready(() => {
        matchs.map((match) => {
            const result = getFormatedMatchAndAvg(match);
            const runnersName = result.runnersName;
            const avgByRunner = result.avgByRunner;
            console.log(runnersName);// les noms des runners qui nous intéresse
            console.log(avgByRunner);// les moyenne des cotes de ces runners sur 2000s (avgBeforeTime)
            console.log(match);// les données du match
        });
    });
</script>
</body>
</html>