<?php
    include_once("dbConnect.php");
    setConnectionValue($jummum);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    ini_set("memory_limit","-1");
    

    
    
    
    if(isset($_POST["branchID"]))
    {
        $branchID = $_POST["branchID"];
    }
    if(isset($_POST["modifiedDate"]))
    {
        $modifiedDate = $_POST["modifiedDate"];
    }
    
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    //**** Receipt, OrderTaking, OrderNote, Dispute
    $sql2 = "select * from receipt where branchID = '$branchID' and modifiedDate >= '$modifiedDate';";
    $selectedRow = getSelectedRow($sql2);
    
    
    if(sizeof($selectedRow) > 0)
    {
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
        }
        
        
        $sql2 .= "select * from OrderTaking where receiptID in ($receiptIDListInText);";
        $sql2 .= "select * from OrderNote where orderTakingID in (select orderTakingID from OrderTaking where receiptID in ($receiptIDListInText));";
        $sql2 .= "select * from Dispute where receiptID in ($receiptIDListInText);";
    }
    else
    {
        $sql2 .= "select * from OrderTaking where 0;";
        $sql2 .= "select * from OrderNote where 0;";
        $sql2 .= "select * from Dispute where 0;";
    }
    
    
    
    
    $sql .= $sql2;
    //****-----
    
    
    
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;


    
    // Close connections
    mysqli_close($con);
    
?>
