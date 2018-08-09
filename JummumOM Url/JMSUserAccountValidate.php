<?php
    include_once("dbConnect.php");
    setConnectionValue($jummumOM);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if(isset($_POST["username"]) && isset($_POST["password"]))
    {
        $username = $_POST["username"];
        $password = $_POST["password"];
    }
    if(isset($_POST["logInID"]) && isset($_POST["username"]) && isset($_POST["status"]) && isset($_POST["deviceToken"]) && isset($_POST["modifiedUser"]) && isset($_POST["modifiedDate"]))
    {
        $logInID = $_POST["logInID"];
        $username = $_POST["username"];
        $status = $_POST["status"];
        $deviceToken = $_POST["deviceToken"];
        $modifiedUser = $_POST["modifiedUser"];
        $modifiedDate = $_POST["modifiedDate"];
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    
    

    $sql = "select * from $jummumOM.UserAccount where username = '$username' and password = '$password'";
    $selectedRow = getSelectedRow($sql);
    if(sizeof($selectedRow)==0)
    {
        //download UserAccount
        $sql = "select * from $jummumOM.UserAccount where 0";
        writeToLog("sql = " . $sql);
        
        
        /* execute multi query */
        $dataJson = executeMultiQueryArray($sql);
        
        
        
        writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
        $response = array('status' => '1', 'sql' => $sql , 'tableName' => 'UserAccountValidate', 'dataJson' => $dataJson);
        echo json_encode($response);
        exit();
    }
    else
    {
        $branchID = $selectedRow[0]["BranchID"];
        $sql = "select * from $jummumOM.Branch where BranchID = '$branchID';";
        $selectedRow = getSelectedRow($sql);
        $dbName = $selectedRow[0]["DbName"];
    }
    
    
    
    
    
    //login--------------------
    //query statement
    $sql = "INSERT INTO $dbName.LogIn(Username, Status, DeviceToken, ModifiedUser, ModifiedDate) VALUES ('$username', '$status', '$deviceToken', '$modifiedUser', '$modifiedDate')";
    $ret = doQueryTask($sql);
    if($ret != "")
    {
        mysqli_rollback($con);
//        putAlertToDevice();
        echo json_encode($ret);
        exit();
    }
    //-----
    
    //device-----***********************
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
            $sql = "insert into $dbName.`Device` (`DeviceToken`, `Remark`) values('$deviceToken','$remark')";
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
        $sql = "insert into $dbName.`Device` (`DeviceToken`, `Remark`) values('$deviceToken','$remark')";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
            //            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
    }
    //-----***********************
    
    

    //userAccount
    $sql = "select * from $jummumOM.UserAccount where username = '$username' and password = '$password';";
    $selectedRow = getSelectedRow($sql);
    $userAccountID = $selectedRow[0]["UserAccountID"];
    $sqlAll = $sql;
    
    
    
    
    //Master table-----**********
    //build sql statement for table
    $sql = "select * from $dbName.setting union select SettingID+1000, `KeyName`, `Value`,Type, Remark, `ModifiedUser`, `ModifiedDate` from $jummumOM.setting where type = 2;";
    $sql .= "select * from $dbName.customerTable;";
    $sql .= "select * from $dbName.menuType;";
    $sql .= "select * from $dbName.menu;";
    $sql .= "select * from $dbName.noteType;";
    $sql .= "select * from $dbName.note;";
    
    
    //****-----
    $sql2 = "(select $jummum.receipt.* from $jummum.receipt where $jummum.receipt.branchID = '$branchID' and status in (2,5,7,8,11,12,13)) UNION (select $jummum.receipt.* from $jummum.receipt where branchID = '$branchID' and status = '6' order by receipt.ReceiptDate DESC, receipt.ReceiptID DESC limit 20) UNION (select $jummum.receipt.* from $jummum.receipt where branchID = '$branchID' and status in (9,10,14) order by receipt.ReceiptDate DESC, receipt.ReceiptID DESC limit 20);";
    $selectedRow = getSelectedRow($sql2);
    
    
    $receiptIDList = array();
    for($i=0; $i<sizeof($selectedRow); $i++)
    {
        array_push($receiptIDList,$selectedRow[$i]["ReceiptID"]);
    }
    if(sizeof($receiptIDList) > 0)
    {
        $receiptIDListInText = $receiptIDList[0];
        for($i=1; $i<sizeof($receiptIDList); $i++)
        {
            $receiptIDListInText .= "," . $receiptIDList[$i];
        }
        
        
        $sql2 .= "select * from $jummum.OrderTaking where receiptID in ($receiptIDListInText);";
        $sql2 .= "select * from $jummum.OrderNote where orderTakingID in (select orderTakingID from $jummum.OrderTaking where receiptID in ($receiptIDListInText));";
        $sql2 .= "select * from $jummum.Dispute where receiptID in ($receiptIDListInText);";
    }
    else
    {
        $sql2 .= "select * from $jummum.OrderTaking where 0;";
        $sql2 .= "select * from $jummum.OrderNote where 0;";
        $sql2 .= "select * from $jummum.Dispute where 0;";
    }
    $sql .= $sql2;
    //****-----
    
    
    $sql .= "select * from $jummum.DisputeReason where status = 1;";
    
    
    $sqlAll .= $sql;
    //-----**********
    
    
    //branch-----**********
    $sql = "select * from $jummumOM.Branch where branchID = '$branchID'";
    $sqlAll .= $sql;
    //-----**********
    
    
    
    
    
    
    /* execute multi query */
    $dataJson = executeMultiQueryArray($sqlAll);
    
    
    
    //do script successful
    mysqli_commit($con);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql, 'tableName' => 'UserAccountValidate', dataJson => $dataJson);
    echo json_encode($response);
    exit();
?>
