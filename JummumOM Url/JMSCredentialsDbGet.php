<?php
    include_once("dbConnect.php");
    setConnectionValue($jummumOM);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if (isset ($_POST["username"]))
    {
        $username = $_POST["username"];
        $deviceToken = $_POST["modifiedDeviceToken"];
    }
    else
    {
        $username = "MAMARIN7";
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
    
    
    
    $sql = "select * from credentials where username = '" . $username . "'";
    $selectedRow = getSelectedRow($sql);
    $credentialsID = $selectedRow[0]["CredentialsID"];
    
    
    $sql = "select credentialsdb.*,branch.BranchID,branch.Name from credentialsdb left join branch on credentialsdb.dbName = branch.dbName  where credentialsdb.credentialsID = $credentialsID and (branch.status = 1 or branch.status = 2)";
    $jsonEncode = executeMultiQuery($sql);
    echo $jsonEncode;
    
    
    
    // Close connections
    mysqli_close($con);
    
    ?>
