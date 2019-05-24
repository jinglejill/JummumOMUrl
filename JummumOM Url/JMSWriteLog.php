<?php
    include_once("dbConnect.php");
    setConnectionValueWithoutCheckUpdate("");
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    if (isset ($_POST["stackTrace"]))
    {
        $stackTrace = $_POST["stackTrace"];
    }
    else
    {
        $stackTrace = "-";
    }
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    //alarm admin
    $sql = "select * from $jummum.setting where keyName = 'AlarmAdmin'";
    $selectedRow = getSelectedRow($sql);
    $alarmAdmin = $selectedRow[0]["Value"];
    if(intval($alarmAdmin) == 1)
    {
        //alarmAdmin
        //query statement
        $ledStatus = 1;
        $sql = "update $jummum.Setting set Value = '$ledStatus', ModifiedUser = '$modifiedUser', ModifiedDate = '$modifiedDate' where KeyName = 'LedStatus';";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
            //        putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    mysqli_commit($con);
    
    
    
    
    
    
    
    //send push to jummum admin
    $sql = "select Value from $jummum.Setting where keyName = 'DeviceTokenAdmin'";
    $selectedRow = getSelectedRow($sql);
    $arrPushSyncDeviceTokenAdmin = array();
    for($i=0; $i<sizeof($selectedRow); $i++)
    {
        $pushSyncDeviceTokenAdmin = $selectedRow[$i]["Value"];
        array_push($arrPushSyncDeviceTokenAdmin,$pushSyncDeviceTokenAdmin);
    }
    
    
    
    $msg = "Error occur $jummumOM" . ', time:' . date("Y/m/d H:i:s");
    $category = "admin";
    $contentAvailable = 1;
    $data = null;
    sendPushNotificationAdmin($arrPushSyncDeviceTokenAdmin,$title,$msg,$category,$contentAvailable,$data);
//    sendPushNotificationToDeviceWithMsg($pushSyncDeviceTokenAdmin,$msg);
    
    
    
    mysqli_close($con);
    writeToLog("fail with exception: " . $stackTrace);
    writeToErrorLog("fail with exception: " . $stackTrace);
    $response = array('status' => '1', 'sql' => "");
    echo json_encode($response);
    exit();
?>
