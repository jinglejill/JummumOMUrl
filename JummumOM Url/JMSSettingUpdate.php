<?php
    include_once("dbConnect.php");
    setConnectionValue("");
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if(isset ($_POST["branchID"]) && isset($_POST["settingID"]) && isset($_POST["keyName"]) && isset($_POST["value"]) && isset($_POST["type"]) && isset($_POST["remark"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $settingID = $_POST["settingID"];
        $keyName = $_POST["keyName"];
        $value = $_POST["value"];
        $type = $_POST["type"];
        $remark = $_POST["remark"];
        $modifiedUser = $_POST["modifiedUser"];
        $modifiedDate = $_POST["modifiedDate"];
        
        
        $branchID = $_POST["branchID"];
        $modifiedDeviceToken = $_POST["modifiedDeviceToken"];
    }
    
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    // Set autocommit to on
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to on");
    
    
    
    //get current dbName and set connection
    $sql = "select * from $jummumOM.branch where branchID = '$branchID'";
    $selectedRow = getSelectedRow($sql);
    $dbName = $selectedRow[0]["DbName"];
    setConnectionValue($dbName);
    
    
    
    //-----
    
    $sql = "select * from setting where keyName = '$keyName';";
    $selectedRow = getSelectedRow($sql);
    $settingID = $selectedRow[0]["SettingID"];
    
    
    
    //query statement
    $sql = "update `Setting` set value = '$value', modifiedUser = '$modifiedUser', modifiedDate = '$modifiedDate' where keyName = '$keyName';";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
        //            putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    mysqli_commit($con);
    
    

    
    //push sync to other device
    $pushSyncDeviceTokenReceiveOrder = array();
    $sql = "select * from $jummumOM.device where dbName = '$dbName';";
    $selectedRow = getSelectedRow($sql);
    for($i=0; $i<sizeof($selectedRow); $i++)
    {
        $deviceToken = $selectedRow[$i]["DeviceToken"];
        $modifiedDeviceToken = $_POST["modifiedDeviceToken"];
        if($deviceToken != $modifiedDeviceToken)
        {
            array_push($pushSyncDeviceTokenReceiveOrder,$deviceToken);
        }
    }
    
    $msg = "";
    $category = "openingTime";
    $contentAvailable = 1;
    $data = array("settingID" => $settingID);
    sendPushNotificationJummumOM($pushSyncDeviceTokenReceiveOrder,$title,$msg,$category,$contentAvailable,$data);
    
    
    
    /* execute multi query */
    $sql = "select * from Setting where settingID = '$settingID';";
    $dataJson = executeMultiQueryArray($sql);
    
    
    
    //do script successful
    mysqli_commit($con);
    mysqli_close($con);
    writeToLog("query commit, file: " . basename(__FILE__));
    $response = array('status' => '1', 'sql' => $sql, 'tableName' => 'Setting', 'dataJson' => $dataJson);
    
    
    echo json_encode($response);
    exit();
?>
