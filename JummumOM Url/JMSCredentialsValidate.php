<?php
    include_once("dbConnect.php");//
    setConnectionValue($jummumOM);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
 
    
    if (isset ($_POST["username"]) && isset ($_POST["password"]))
    {
        $username = $_POST["username"];
        $password = $_POST["password"];
        $deviceToken = $_POST["modifiedDeviceToken"];
    }
    else
    {
        $username = "test";
        $password = "test";
        $deviceToken = "test";
    }
    
    
    writeToLog("device token: " . $deviceToken);
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    
    //เช็คว่ามี username นี้มั๊ย -> ไม่มี alert -> username is invalid
    //เช็คว่ามี password ถูกต้องมั๊ย -> ไม่มี alert -> password is invalid
    //เช็คว่า expire หรือยัง
    //ยังไม่ expire -> insert device with this credentials
    //ถ้า else ให้ status => 2 alert -> application is expire, please contact administrator
    //2 tables นี้ ไม่ได้ใช้ในฝั่ง app เลย ใช้เพียงเช็คตอน first setup เท่านั้น
    
    $sql = "select * from Credentials where `Username` = '$username'";
    $selectedRow = getSelectedRow($sql);
    if(sizeof($selectedRow) == 0)
    {
        writeToLog("query commit");
        mysqli_commit($con);
        mysqli_close($con);
        $response = array('status' => '2', 'sql' => $sql, 'msg' => 'Username is invalid');
        
        
        echo json_encode($response);
        exit();
    }
    
    
    $credentialsID = $selectedRow[0]["CredentialsID"];
    $strExpiredDate = $selectedRow[0]["ExpiredDate"];
    $credentialPassword = $selectedRow[0]["Password"];
    //----
    
    
    if($password != $credentialPassword)
    {
        writeToLog("query commit");
        mysqli_commit($con);
        mysqli_close($con);
        $response = array('status' => '2', 'sql' => $sql, 'msg' => 'Password is invalid');
        
        
        echo json_encode($response);
        exit();
    }
    
    
    
    $expiredDate = date('Y-m-d H:i:s',strtotime($strExpiredDate));
    $currentDate = date('Y-m-d H:i:s');
    writeToLog("expired date: " . $expiredDate);
    writeToLog("current date: " . $currentDate);
    if($currentDate >= $expiredDate)
    {
        writeToLog("query commit");
        mysqli_commit($con);
        mysqli_close($con);
        $response = array('status' => '2', 'sql' => $sql, 'msg' => 'Application is expired, please contact administrator');
        
        
        echo json_encode($response);
        exit();
    }
    //------
    
    
    
    $sql = "insert into `CredentialsDevice` (`CredentialsID`,`DeviceToken`) values ($credentialsID, '$deviceToken')";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        // Rollback transaction
        mysqli_rollback($con);
        mysqli_close($con);
//        putAlertToDevice($_POST["modifiedUser"]);
        echo json_encode($ret);
        exit();
    }
    
    
    
    
    
    
    
    
    //do script successful
    writeToLog("query commit, file: " . basename(__FILE__));
    mysqli_commit($con);
    mysqli_close($con);
    $response = array('status' => '1', 'sql' => $sql);
    
    
    echo json_encode($response);
    exit();
    ?>
