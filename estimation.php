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

    function initRunnersBet(runnersName) {
        const runnerBets = [];
        runnersName.map((name) => {
            runnerBets.push({
                runnerName: name,
                bets: [],
            });
        });
        return runnerBets;
    }

    function getBorne(runner, avgByRunner, time) {
        const avg = avgByRunner.find(x => x.runnerName === runner.runnerName).avg;
        return {
            borneSup: (ratio * Math.sqrt(time)) + avg,
            borneInf: -(ratio * Math.sqrt(time)) + avg,
        };
    }

    function addMissingBet(runnerBets, lastObj) {
        runnerBets.map((myRunnerBet) => {
            if (myRunnerBet.bets.length % 2 !== 0) {
                const lastBetType = myRunnerBet.bets[myRunnerBet.bets.length - 1].type;
                const runner = lastObj.runners.find(x => x.runnerName === myRunnerBet.runnerName);
                if (lastBetType === "back") {
                    myRunnerBet.bets.push({
                        name: "test",
                        type: "lay",
                        odd: runner.layOdd,
                        time: 3600 + lastObj.time,
                    });
                } else if (lastBetType === "lay") {
                    myRunnerBet.bets.push({
                        name: "test",
                        type: "back",
                        odd: runner.backOdd,
                        time: 3600 + lastObj.time,
                    });
                } else {
                    alert("error !!!!");
                }
            }
        });
    }

    function bet(runner, runnerBets, bornes, time) {
        const myRunnerBet = runnerBets.find(x => x.runnerName === runner.runnerName);
        let lastBetType = null;
        if (myRunnerBet.bets.length > 0) lastBetType = myRunnerBet.bets[myRunnerBet.bets.length - 1].type;
        if (lastBetType === null || lastBetType === "lay" || myRunnerBet.bets.length % 2 === 0) {
            const type = "back";
            if (runner.backOdd > bornes.borneSup) {
                myRunnerBet.bets.push({
                    name: "test",
                    type: type,
                    odd: runner.backOdd,
                    time: 3600 + time,
                });
            }
        } else if (lastBetType === null || lastBetType === "back" || myRunnerBet.bets.length % 2 === 0) {
            const type = "lay";
            if (runner.backOdd < bornes.borneInf) {
                myRunnerBet.bets.push({
                    name: "test",
                    type: type,
                    odd: runner.backOdd,
                    time: 3600 + time,
                });
            }
        }
    }

    // c'est ici qu'on travaille
    $(document).ready(() => {
        matchs.map((match) => {
            const result = getFormatedMatchAndAvg(match);
            const runnersName = result.runnersName;
            const avgByRunner = result.avgByRunner;
            const runnerBets = initRunnersBet(runnersName);
            let lastObj = null;
            match.json.map((obj, index) => {
                if (3600 + obj.time > avgBeforeTime) {
                    obj.runners.map((runner) => {
                        if (runnersName.includes(runner.runnerName) && obj.volume > 0) {
                            const bornes = getBorne(runner, avgByRunner, 3600 + obj.time);
                            bet(runner, runnerBets, bornes, obj.time);
                            lastObj = obj;
                        }
                    });
                }
            });
            addMissingBet(runnerBets, lastObj);
            console.log(runnerBets);
        });
    });
</script>
</body>
</html>