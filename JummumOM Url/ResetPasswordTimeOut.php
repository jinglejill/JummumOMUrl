<?php
    include_once("dbConnect.php");
    setConnectionValue("");
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
?>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1" charset="UTF-8"/>
<link href="https://fonts.googleapis.com/css?family=Prompt" rel="stylesheet">
<style>
body {
    font-family: 'Prompt', serif;
    font-size: 22px;
}
</style>
</head>
<body text="#FFFFFF" style="background-color:#FF3C4B">
<p>&nbsp;<img class="" src="./Image/jummumOMLogo.png" alt="" width="120" /></p>
<p>&nbsp;</p>
ไม่สามารถรีเซ็ตรหัสผ่านได้ กรุณาส่งคำขอเปลี่ยนรหัสผ่านอีกครั้งหนึ่ง
<p>&nbsp;</p>
<table width="100%" style="border-top: 1px solid #FFFFFF">
<tbody>
</tbody>
</table>
<p style="font-size:16px;">**หากคุณต้องการความช่วยเหลือสามารถติดต่อผ่านทางอีเมลล์&nbsp;<a href="mailto:help@jummum.co">help@jummum.co</a>&nbsp;หรือโทร 081-307-2993</p>
</body>
</html>
