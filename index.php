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

<?php $nbMatch = 2; ?>
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
        $(ul).append('<li class="' + activeClass + '"><a data-toggle="tab" href="#' + index + '">' + match.name + '</a></li>');
        $(div).append('<div id="' + index + '" class="tab-pane fade ' + activeClass2 + '">' + match.name + '</div>');
    });
</script>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script>
    console.log(matchs);
</script>
</body>
</html>