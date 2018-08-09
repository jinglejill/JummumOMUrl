<?php
    include_once("dbConnect.php");
    setConnectionValue("");
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    ini_set("memory_limit","-1");
    

    
    
    
    if (isset ($_POST["branchID"]))
    {
        $branchID = $_POST["branchID"];
    }
    
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    //get current dbName and set connection
    $sql = "select * from $jummumOM.branch where branchID = '$branchID'";
    $selectedRow = getSelectedRow($sql);
    $dbName = $selectedRow[0]["DbName"];
    setConnectionValue($dbName);
    
    
    
    $sql = "select * from openingTime order by day,shiftNo";
    $selectedRow = getSelectedRow($sql);
    for($i=0; $i<sizeof($selectedRow); $i++)
    {
        if($dayOfWeek != intval($selectedRow[$i]["Day"]))
        {
            $dayOfWeek = $selectedRow[$i]["Day"];
            $startTime = $selectedRow[$i]["StartTime"];
            $endTime = $selectedRow[$i]["EndTime"];
            $text = $text == ""?"":$text."\n";
            $text .= getDayOfWeekText($dayOfWeek) . "\t" . $startTime . " - " . $endTime;
        }
        else
        {
            $startTime = $selectedRow[$i]["StartTime"];
            $endTime = $selectedRow[$i]["EndTime"];
            $text = $text == ""?"":$text."\n";
            $text .= "\t\t" . $startTime . " - " . $endTime;
        }
    }
    
    $sql = "select '$text' as Text;";
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;


    
    // Close connections
    mysqli_close($con);
    
?>
