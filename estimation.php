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
<?php $nbMatch = 30; ?>
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
    const initMise = 10;

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
                const lastBet = myRunnerBet.bets[myRunnerBet.bets.length - 1];
                const runner = lastObj.runners.find(x => x.runnerName === myRunnerBet.runnerName);
                if (lastBet.type === "back" && runner.layOdd > 1) {
                    myRunnerBet.bets.push({
                        name: "test",
                        type: "lay",
                        odd: runner.layOdd,
                        otherOdd: runner.backOdd,
                        time: 3600 + lastObj.time,
                        mise: getMise(myRunnerBet, runner.layOdd),
                    });
                } else if (lastBet.type === "lay" && runner.backOdd > 1) {
                    myRunnerBet.bets.push({
                        name: "test",
                        type: "back",
                        odd: runner.backOdd,
                        otherOdd: runner.layOdd,
                        time: 3600 + lastObj.time,
                        mise: getMise(myRunnerBet, runner.backOdd),
                    });
                }
            }
        });
    }

    function getMise(myRunnerBet, odd) {
        let mise = initMise;
        if (myRunnerBet.bets.length % 2 !== 0) {
            const lastBet = myRunnerBet.bets[myRunnerBet.bets.length - 1];
            if (lastBet.type === "back" || lastBet.type === "lay") {
                mise = (lastBet.odd * lastBet.mise) / odd;
            } else {
                alert("error getMise !!!!!")
            }
        }
        mise = Math.round(mise * 100) / 100;
        return mise;
    }

    function report(runnerBets) {
        const report = [];
        runnerBets.map((myRunnerBet) => {
            const obj = {runnerName: myRunnerBet.runnerName, result: 0};
            myRunnerBet.bets.map((bets, i) => {
                if (i % 2 === 0) {
                    let value = 0;
                    if (typeof myRunnerBet.bets[i + 1] === "undefined") {
                        const odd = myRunnerBet.bets[i].odd;
                        const thisMise = myRunnerBet.bets[i].mise;
                        value = ((1 / thisMise) * (odd * (thisMise - 1))) - ((1 - (1 / thisMise)) * odd)
                    } else {
                        let miseLay = myRunnerBet.bets[i].mise;
                        let miseBack = myRunnerBet.bets[i + 1].mise;
                        if (myRunnerBet.bets[i].type === "back") {
                            miseLay = myRunnerBet.bets[i + 1].mise;
                            miseBack = myRunnerBet.bets[i].mise;
                            value = miseLay - miseBack;
                        }
                        obj.result += parseInt((value) * 100) / 100;
                    }
                }
            });
            report.push(obj);
        });
        return report;
    }

    function bet(runner, runnerBets, bornes, time) {
        const myRunnerBet = runnerBets.find(x => x.runnerName === runner.runnerName);
        let lastBetType = null;
        if (myRunnerBet.bets.length > 0) lastBetType = myRunnerBet.bets[myRunnerBet.bets.length - 1].type;
        if (runner.backOdd > 1 && (lastBetType === null || lastBetType === "lay" || myRunnerBet.bets.length % 2 === 0)) {
            const type = "back";
            const odd = runner.backOdd;
            if (runner.backOdd > bornes.borneSup) {
                myRunnerBet.bets.push({
                    name: "test",
                    type: type,
                    odd: odd,
                    otherOdd: runner.layOdd,
                    time: 3600 + time,
                    mise: getMise(myRunnerBet, odd),
                });
            }
        } else if (runner.layOdd > 1 && (lastBetType === null || lastBetType === "back" || myRunnerBet.bets.length % 2 === 0)) {
            const type = "lay";
            const odd = runner.layOdd;
            if (runner.backOdd < bornes.borneInf) {
                myRunnerBet.bets.push({
                    name: "test",
                    type: type,
                    odd: odd,
                    otherOdd: runner.backOdd,
                    time: 3600 + time,
                    mise: getMise(myRunnerBet, odd),
                });
            }
        }
    }

    // c'est ici qu'on travaille
    $(document).ready(() => {
        let totalWin = 0;
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
            const matchReport = report(runnerBets);
            matchReport.map((obj) => totalWin += obj.result);
            console.log(runnerBets, matchReport);
        });
        console.log("total win", totalWin);
        console.log("%", (totalWin / initMise) / matchs.length);
    });
</script>
</body>
</html>