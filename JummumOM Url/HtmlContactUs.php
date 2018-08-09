<?php
    include_once("dbConnect.php");
    setConnectionValue("");
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
?>
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css?family=Prompt" rel="stylesheet">
        <style>
            body {
                font-family: 'Prompt', serif;
                font-size: 16px;
            }
        </style>
</head>
<body text="#464646">
<div class="page" title="Page 1">
    <div class="layoutArea">
        <div class="column">
<p>&nbsp;<img class="" src="http://www.jummum.co/<?=$jummumOMPath?>Image/jummumOMLogo.png" alt="" width="120" /></p>
            <p>&nbsp;</p>
            <p style="color:FF3C4B;font-size: 28px"><strong>Contact no. : </strong><span style="color:464646">081-307-2993, 089-1212-882</span></p>
            <p style="color:FF3C4B;font-size: 28px"><strong>Email : </strong><span style="color:464646">help@jummum.co</span></p>
        </div>
    </div>
</div>
</body>
