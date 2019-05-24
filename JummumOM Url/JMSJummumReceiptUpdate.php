<?php
    include_once("dbConnect.php");
    setConnectionValue($jummum);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    ini_set("memory_limit","-1");
    

    if(isset($_POST["branchID"]) && isset($_POST["receiptID"]) && isset($_POST["status"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $branchID = $_POST["branchID"];
        $receiptID = $_POST["receiptID"];
        $status = $_POST["status"];
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
    
    //*****ลูกค้ากดยกเลิก หรือส่งคำร้อง
    //2->7
    //5,6->8
    //7->9
    //8->10
    //*****ร้านค้ากดให้ลูกค้า -> ยกเลิก หรือส่งคำร้อง
    //2->9
    //5,6->10
    if($status == 9)
    {
        $sql = "select * from receipt where receiptID = '$receiptID' and status in ('2','7')";
        $selectedRow = getSelectedRow($sql);
        if(sizeof($selectedRow) == 0)
        {
            $alreadyDone = 1;
        }
        
        $msg = "Order cancelled";
        $msgCust = $msg;
        $category = "clear";
    }
    else if($status == 10)
    {
        $sql = "select * from receipt where receiptID = '$receiptID' and status in ('5','6','8')";
        $selectedRow = getSelectedRow($sql);
        if(sizeof($selectedRow) == 0)
        {
            $alreadyDone = 1;
        }
        
        $msg = "Order dispute finished";
        $msgCust = $msg;
        $category = "clear";
    }
    else if($status == 11)
    {
        $sql = "select * from receipt where receiptID = '$receiptID' and status in ('8')";
        $selectedRow = getSelectedRow($sql);
        if(sizeof($selectedRow) == 0)
        {
            $alreadyDone = 1;
        }
        
        $msg = "Negotiate request";
        $msgCust = "";
        $category = "updateStatus";
    }
    else if($status == 14)
    {
        $sql = "select * from receipt where receiptID = '$receiptID' and status = '13'";
        $selectedRow = getSelectedRow($sql);
        if(sizeof($selectedRow) == 0)
        {
            $alreadyDone = 1;
        }
        
        $msg = "Order dispute finished";
        $msgCust = $msg;
        $category = "clear";
    }

    writeToLog("alreadyDone: " . $alreadyDone);
    if(!$alreadyDone)
    {
        $sql = "update receipt set status = '$status', statusRoute = concat(statusRoute,',','$status'), modifiedUser = '$modifiedUser', modifiedDate = '$modifiedDate' where receiptID = '$receiptID'";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
            //        putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
        
        
        if($status == 9)
        {
            //update promoCode
            $sql = "select * from receipt where receiptID = '$receiptID'";
            $selectedRow = getSelectedRow($sql);
            $promoCodeID = $selectedRow[0]["PromoCodeID"];
            
            
            if($promoCodeID != 0)
            {
                $sql = "update promoCode set status = 1,modifiedUser = '$modifiedUser',modifiedDate = '$modifiedDate' where PromoCodeID = '$promoCodeID'";
                $ret = doQueryTask($sql);
                if($ret != "")
                {
                    mysqli_rollback($con);
            //        putAlertToDevice();
                    echo json_encode($ret);
                    exit();
                }
            }
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
    
    
    //alarm admin
    if($status == 11)
    {
        $sql = "select * from setting where keyName = 'AlarmAdmin'";
        $selectedRow = getSelectedRow($sql);
        $alarmAdmin = $selectedRow[0]["Value"];
        if(intval($alarmAdmin) == 1)
        {
            //alarmAdmin
            //query statement
            $ledStatus = 1;
            $sql = "update Setting set Value = '$ledStatus', ModifiedUser = '$modifiedUser', ModifiedDate = '$modifiedDate' where KeyName = 'LedStatus';";
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
                //        putAlertToDevice();
                echo json_encode($ret);
                exit();
            }
        }
    }
    mysqli_commit($con);
    
    
    $sql = "select * from Receipt where receiptID = '$receiptID';";
    $selectedRow = getSelectedRow($sql);
    $memberID = $selectedRow[0]["MemberID"];
    $orderNo = $selectedRow[0]["ReceiptNoID"];
    
    if($status == 11)
    {
        //get pushSync Device in jummum
        $sql = "select * from setting where KeyName = 'DeviceTokenAdmin'";
        $selectedRow = getSelectedRow($sql);
        $arrPushSyncDeviceTokenAdmin = array();
        for($i=0; $i<sizeof($selectedRow); $i++)
        {
            $pushSyncDeviceTokenAdmin = $selectedRow[$i]["Value"];
            array_push($arrPushSyncDeviceTokenAdmin,$pushSyncDeviceTokenAdmin);
        }
        
        $msgAdmin = "Order no.$orderNo negotiation arrive!";
        $category = "admin";
        $contentAvailable = 1;
        $data = array("receiptID" => $receiptID);
        sendPushNotificationAdmin($arrPushSyncDeviceTokenAdmin,$title,$msgAdmin,$category,$contentAvailable,$data);
    }
    else
    {
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
            $msgCust = "Order no.$orderNo $msgCust";
            $category = "updateStatus";
            $contentAvailable = 1;
            $data = array("receiptID" => $receiptID);
            sendPushNotificationJummum($arrCustomerDeviceToken,$title,$msgCust,$category,$contentAvailable,$data);
        }
    }
    
    
    
    //push sync to other device
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
    
    
    
    
   
    
   
    
    
    //dataJson
    $sql = "select * from Receipt where receiptID = '$receiptID';";
    $dataJson = executeMultiQueryArray($sql);
    
    
    
    
    //do script successful
    
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql, 'tableName' => 'Receipt', 'dataJson' => $dataJson);
    echo json_encode($response);
    exit();
?>
