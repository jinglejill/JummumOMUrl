<?php
    include_once("dbConnect.php");
    setConnectionValue("");
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if (isset ($_POST["branchID"]) && isset ($_POST["deviceID"]) && isset($_POST["deviceToken"]) && isset ($_POST["remark"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $deviceID = $_POST["deviceID"];
        $deviceToken = $_POST["deviceToken"];
        $remark = $_POST["remark"];
        $modifiedUser = $_POST["modifiedUser"];
        $modifiedDate = $_POST["modifiedDate"];
        
        
        $branchID = $_POST["branchID"];
    }
    else
    {
        $deviceToken = $_GET["deviceToken"];
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
    
    
    
    //get last DbName
    $sql = "select * from $jummumOM.`device` where DeviceToken = '$deviceToken'";
    $selectedRow = getSelectedRow($sql);
    if(sizeof($selectedRow)>0)
    {
        $lastDb = $selectedRow[0]["DbName"];
        if($dbName != $lastDb)
        {
            $sql = "delete from " . $lastDb . ".`device` where DeviceToken = '$deviceToken'";
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
//                putAlertToDevice();
                echo json_encode($ret);
                exit();
            }
            
            
            $sql = "delete from $jummumOM.`device` where DeviceToken = '$deviceToken'";
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
//                putAlertToDevice();
                echo json_encode($ret);
                exit();
            }
            
            
            
            
            //OM query statement
            $sql = "insert into $jummumOM.`device` (`DbName`,`DeviceToken`) values('$dbName','$deviceToken')";
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
//                putAlertToDevice();
                echo json_encode($ret);
                exit();
            }
            
            
            
            
            //query statement
            $sql = "insert into `Device` (`DeviceToken`, `Remark`) values('$deviceToken','$remark')";
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
//                putAlertToDevice();
                echo json_encode($ret);
                exit();
            }
        }
    }
    else
    {
        //OM query statement
        $sql = "insert into $jummumOM.`device` (`DbName`,`DeviceToken`) values('$dbName','$deviceToken')";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
//            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
        
        
        
        
        //query statement
        $sql = "insert into `Device` (`DeviceToken`, `Remark`) values('$deviceToken','$remark')";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
//            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    
    
    
    
    
    
    //do script successful
    mysqli_commit($con);
    mysqli_close($con);
    writeToLog("query commit, file: " . basename(__FILE__));
    $response = array('status' => '1', 'sql' => $sql);
    
    
    echo json_encode($response);
    exit();
?>
