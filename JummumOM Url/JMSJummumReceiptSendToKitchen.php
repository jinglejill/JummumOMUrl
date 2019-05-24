<?php
    include_once("dbConnect.php");
    setConnectionValue($jummum);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    ini_set("memory_limit","-1");
    

    if(isset($_POST["branchID"]) && isset($_POST["receiptID"]) && isset($_POST["status"]) && isset($_POST["sendToKitchenDate"]) && isset($_POST["deliveredDate"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $branchID = $_POST["branchID"];
        $receiptID = $_POST["receiptID"];
        $status = $_POST["status"];
        $sendToKitchenDate = $_POST["sendToKitchenDate"];
        $deliveredDate = $_POST["deliveredDate"];
        $modifiedUser = $_POST["modifiedUser"];
        $modifiedDate = $_POST["modifiedDate"];
        
        
        $modifiedDeviceToken = $_POST["modifiedDeviceToken"];
        
    }

    
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    

    
    $alreadyDone = 0;
    if($status == 5)
    {
        $sql = "select * from receipt where receiptID = '$receiptID' and status = '2'";
        $selectedRow = getSelectedRow($sql);
        if(sizeof($selectedRow) == 0)
        {
            $alreadyDone = 1;
        }
        
        
        $msg = "Processing";
        $category = "processing";
    }
    else if($status == 6)
    {
        $sql = "select * from receipt where receiptID = '$receiptID' and status = '5'";
        $selectedRow = getSelectedRow($sql);
        if(sizeof($selectedRow) == 0)
        {
            $alreadyDone = 1;
        }
        
        $msg = "Delivered";
        $category = "delivered";
    }

    writeToLog("alreadyDone: " . $alreadyDone);
    if(!$alreadyDone)
    {
        if($status == 5)
        {
            $sql = "update receipt set status = '$status', statusRoute = concat(statusRoute,',','$status'),sendToKitchenDate='$sendToKitchenDate', modifiedUser = '$modifiedUser', modifiedDate = '$modifiedDate' where receiptID = '$receiptID'";
            
        }
        else if($status == 6)
        {
            $sql = "update receipt set status = '$status', statusRoute = concat(statusRoute,',','$status'),deliveredDate='$deliveredDate', modifiedUser = '$modifiedUser', modifiedDate = '$modifiedDate' where receiptID = '$receiptID'";
            
        }
        
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
            //        putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    
    
    
//    2,5,7,8,13
    $sql = "select receipt.* from receipt where receipt.branchID = '$branchID' and status in (2,5,7,8,13)";
    $selectedRow = getSelectedRow($sql);
    if(sizeof($selectedRow)==0)
    {
        //alarmShopOff
        //query statement
        $ledStatus = 0;
        $sql = "update $jummumOM.Branch set LedStatus = '$ledStatus', ModifiedUser = '$modifiedUser', ModifiedDate = '$modifiedDate' where branchID = '$branchID';";
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
    
    
    
    
    
    
    
    
    
    
    
    //push sync to other device
    $sql = "select * from Receipt where receiptID = '$receiptID';";
    $selectedRow = getSelectedRow($sql);
    $memberID = $selectedRow[0]["MemberID"];
    $orderNo = $selectedRow[0]["ReceiptNoID"];
    
    
    $pushSyncDeviceTokenReceiveOrder = array();
    $sql = "select * from $jummumOM.device left join $jummumOM.Branch on $jummumOM.device.DbName = $jummumOM.Branch.DbName where branchID = '$branchID';";
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
    
    $msg = "Order no.$orderNo $msg";
    $contentAvailable = 1;
    $data = array("receiptID" => $receiptID);
    sendPushNotificationJummumOM($pushSyncDeviceTokenReceiveOrder,$title,$msg,$category,$contentAvailable,$data);
    
    
    
    
    
    //send noti to customer
    
    
    
    $sql = "select login.DeviceToken,login.ModifiedDate,login.Username from useraccount left join login on useraccount.username = login.username where useraccount.UserAccountID = '$memberID' and login.status = '1' order by login.modifiedDate desc;";
    $selectedRow = getSelectedRow($sql);
    $customerDeviceToken = $selectedRow[0]["DeviceToken"];
    $logInModifiedDate = $selectedRow[0]["ModifiedDate"];
    $logInUsername = $selectedRow[0]["Username"];
    $sql = "select * from login where DeviceToken = '$customerDeviceToken' and Username != '$logInUsername' and status = 1 and modifiedDate > '$logInModifiedDate';";
    $selectedRow = getSelectedRow($sql);
    if(sizeof($selectedRow) == 0)
    {
        $arrCustomerDeviceToken = array();
        array_push($arrCustomerDeviceToken,$customerDeviceToken);
        $category = "updateStatus";
        $contentAvailable = 1;
        $data = array("receiptID" => $receiptID);
        sendPushNotificationJummum($arrCustomerDeviceToken,$title,$msg,$category,$contentAvailable,$data);
    }

    
    
    //dataJson
    $sql = "select '$alreadyDone' as Text;";
    $sql .= "select * from receipt where receiptID = '$receiptID';";
    $dataJson = executeMultiQueryArray($sql);
    
    
    
    
    
    //do script successful
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql, 'tableName' => 'ReceiptSendToKitchen', 'dataJson' => $dataJson);
    echo json_encode($response);
    exit();
?>
