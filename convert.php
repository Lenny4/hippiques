<pre>
<?php
function removeEmptyValue($array)
{
    $arrayLenght = count($array);
    for ($i = $arrayLenght - 1; $i >= 0; $i--) {
        if ($array[$i] === "") unset($array[$i]);
    }
    $array = array_values($array);
    return $array;
}

function getValuesForRunner($array, $runnersName)
{
    $result = [];
    $i = 4;
    foreach ($runnersName as $index => $runnerName) {
        $realIndex = ($i * ($index + 1)) + $index;
        $arrayRunner = [
            "runnerName" => $runnerName,
            "volumeRunner" => floatval($array[$realIndex]),
            "backOdd" => floatval($array[$realIndex + 1]),
            "backAvailableAmount" => floatval($array[$realIndex + 2]),
            "layOdd" => floatval($array[$realIndex + 3]),
            "layAvailableAmount" => floatval($array[$realIndex + 4]),
        ];
        array_push($result, $arrayRunner);
    }
    return $result;
}

$files = scandir('export/');
foreach ($files as $fileIndex => $file) {
    if ($file !== "." AND $file !== ".." AND $fileIndex >= 0) {
        $fileName = str_replace(".csv", "", $file);
        $result = [];
        $csv = array_map('str_getcsv', file('export/' . $fileName . '.csv'));
        $runnersName = null;
        foreach ($csv as $index => $line) {
            if ($index === 0) {

            } elseif ($index === 1) {
                $runnersName = explode(";", $line[0]);
                $runnersName = removeEmptyValue($runnersName);
            } elseif ($index === 2) {

            } elseif ($index >= 3) {
                $arrayLine = explode(";", $line[0]);
                $time = intval($arrayLine[0]);
                $volume = floatval($arrayLine[3]);
                $runners = getValuesForRunner($arrayLine, $runnersName);
                array_push($result, [
                    "time" => $time,
                    "volume" => $volume,
                    "runners" => $runners,
                ]);
//        var_dump($time);
//        var_dump($arrayLine);
            }
        }
//        file_put_contents('json/' . $fileName . '.json', json_encode($result));
    }
}
?>
</pre>
