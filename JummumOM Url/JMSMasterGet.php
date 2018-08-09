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
    

    
    
    //build sql statement for table
    $sql = "select * from setting union select SettingID+1000, `KeyName`, `Value`,Type, Remark, `ModifiedUser`, `ModifiedDate` from $jummumOM.setting where type = 2;";
    $sql .= "select * from customerTable;";
    $sql .= "select * from menuType;";
    $sql .= "select * from menu;";
    $sql .= "select * from noteType;";
    $sql .= "select * from note;";
    
    
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
    
    
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;


    
    // Close connections
    mysqli_close($con);
    
?>
