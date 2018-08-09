<?php
    include_once("dbConnect.php");
    setConnectionValue("");
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if(isset ($_POST["branchID"]) && isset($_POST["username"]))
    {
        $username = $_POST["username"];
        $branchID = $_POST["branchID"];
    }
    $modifiedUser = $_POST["modifiedUser"];
    
    
    // Check connection
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    
    
    //get current dbName and set connection
    $sql = "select * from $jummumOM.branch where branchID = '$branchID'";
    $selectedRow = getSelectedRow($sql);
    $dbName = $selectedRow[0]["DbName"];
    setConnectionValue($dbName);
    
    
    
    
    //query statement
    $sql = "select * from userAccount where username = '$username'";
    /* execute multi query */
    $dataJson = executeMultiQueryArray($sql);
    
    
    $selectedRow = getSelectedRow($sql);
    if(sizeof($selectedRow) > 0)
    {
        $requestDate = date('Y-m-d H:i:s', time());
        $randomString = generateRandomString();
        $codeReset = password_hash($username . $requestDate . $randomString, PASSWORD_DEFAULT);//
        $emailBody = file_get_contents('./HtmlEmailTemplateForgotPassword.php');
        $emailBody = str_replace("#codereset#",$codeReset,$emailBody);
        
        
        
        
        $sql = "INSERT INTO $jummumOM.`forgotpassword`(`CodeReset`, `Email`, `RequestDate`, `Status`, `DbName`, `ModifiedUser`, `ModifiedDate`) VALUES ('$codeReset','$username','$requestDate','1','$dbName','$modifiedUser',now())";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
//            putAlertToDevice();
            echo json_encode($ret);
            exit();
        }
        
        
        sendEmail($username,"Reset password from JUMMUM OM",$emailBody);
    }
    
    
    
    
    //do script successful
    mysqli_commit($con);
    mysqli_close($con);
    
    
    
    writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
    $response = array('status' => '1', 'sql' => $sql, 'tableName' => 'UserAccountForgotPassword', 'dataJson' => $dataJson);
    echo json_encode($response);
    exit();
?>
