<?php
    include_once("dbConnect.php");
    setConnectionValue($jummum);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    ini_set("memory_limit","-1");
    
    

    if(isset($_POST["receiptID"]) && isset($_POST["branchID"]))
    {
        $receiptID = $_POST["receiptID"];
        $branchID = $_POST["branchID"];
    }
    else
    {
        $receiptID = $_GET["receiptID"];
        $branchID = $_GET["branchID"];
    }
    
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    $sql = "select Receipt.* from Receipt where ReceiptID = '$receiptID';";
    $sql .= "select OrderTaking.* from OrderTaking where ReceiptID = '$receiptID';";
    $sql .= "select OrderNote.* from OrderNote where OrderTakingID in (select orderTakingID from OrderTaking where ReceiptID = '$receiptID');";
    $sql .= "select Dispute.* from Dispute where ReceiptID = '$receiptID';";
    writeToLog($sql);
    
    
    /* execute multi query */
    $jsonEncode = executeMultiQueryArray($sql);
    $response = array('success' => true, 'data' => $jsonEncode, 'error' => null, 'status' => 1);
    echo json_encode($response);
    
    
    
    // Close connections
    mysqli_close($con);
?>
