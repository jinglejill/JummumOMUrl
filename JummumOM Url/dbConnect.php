<?php
    $salt = @"FvTivqTqZXsgLLx1v3P8TGRyVHaSOB1pvfm02wvGadj7RLHV8GrfxaZ84oGA8RsKdNRpxdAojXYg9iAj";
    $masterFolder = "MasterPrd";
    include_once("./../$masterFolder/JMSNeedUpdateVersion.php");
    
    
    //conection variable
    $con;
    $jummum = "JUMMUM";
    $jummumOM = "JUMMUM_OM";
    $encryptKey = "jmmom";
    $jummumCkPath = "./../$masterFolder/JUMMUM/";
    $jummumCkPass = "jill";
    $jummumOMCkPath = "./../$masterFolder/JUMMUM_OM/";
    $jummumOMCkPass = "jill";
    $adminCkPath = "./../AdminApp/";
    $adminCkPass = "jill";
    $bundleID = "com.JummumCo.JummumOM";
    $sandBox = 0;
    $firebaseKeyJummum = "AAAA5h2UtWI:APA91bG5-_F1ISKk1dAbAOiZxtoG75nbYc-0N-NmYsXaDaL7Pv8yOeP21wnWm7kRO8P6JvwNIRTiJCn45KdbeGoW6qZEHM4NPHrKgFJdymKgEE2ctDhTFc0jDZ4cOKnjqAW1rSgVeF03l9st8U4efhoviuhETQd1pA";
    $firebaseKeyJummumOM = "AAAAs8VRTGE:APA91bHeKIqzV7q7aQgoSqefRR7kyGwW7OCcpwsX5o_pu4eMbCTidNe3SU-8YB_2u-W1kD2yLlA4RTXGe4_HGXnFPri9OrFT-fCIjpXtIraxLMaMsQiGmJtVnSzRKaI9Tbh5UZSkjoqYFrymtdZGQYyxz-NNr4f4dQ";
    $firebaseKeyAdmin = "AAAAulrZL7w:APA91bFoAJOcDaZSmiPnT2X2MyC18b95x0j09CiuRqbeo4o0MXvzWWmdsVwKfL6ZyLaEHZ1drMHNG1OZBoWOKWtpDDHKzH0UU3lLy-kly52riEtZ1Az_HZIqCOnrnHGTICXi49Whi8_5EB99X6z81QiBT3j0YmnAIA";
    
    
    function isNeedUpdateVersion()
    {
        global $bundleID;
        global $needUpdateVersion;
        
        // create curl resource
        $ch = curl_init();
        
        // set url
//curl_setopt($ch, CURLOPT_URL, "http://www.jummum.co/MasterDemo/JUMMUM/JMMLatestVersion.php");
        curl_setopt($ch, CURLOPT_URL, "http://itunes.apple.com/lookup?bundleId=$bundleID");
        
        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        // $output contains the output string
        $output = curl_exec($ch);
        
        // close curl resource to free up system resources
        curl_close($ch);
        
        $result = json_decode($output,true);
        
        if($result["resultCount"]==1)
        {
            $appStoreVersion = $result[@"results"][0][@"version"];
            writeToLog("appStoreVersion: " . $appStoreVersion);
            writeToLog("needUpdateVersion: " . $needUpdateVersion);
            if($appStoreVersion == $needUpdateVersion)
            {
                writeToLog("new appStore version coming, user: " . $_POST['modifiedUser']);
                $response = array('status' => '3');
                echo json_encode($response);
                exit();
            }
        }
    }
    
    function generate_strings($number, $length) {
        
        
        mt_srand(10000000 * (double)microtime() * $length); // Generate a randome string
        
        $salt    = "ABCDEFGHJKMNPQRSTUVWXY3456789"; // the characters you want to allow
        
        $len    = strlen($salt);
        
        $strings = array();
        
        for($i = 0; $i < $number; $i++) {
            
            $string = null;
            
            for($j = 0; $j < $length; $j++) { //if you want to change the length of each string, do it here. You could randomise the string length by replacing $length, with mt_rand(6, 10); This would create random string lengths from 6-10 characters in length.
                
                $string .= $salt[mt_rand(0, $len - 1)];
                
            }
            
            if(in_array($string, $strings)) {
                
                $number++;
                
            } else {
                
                $strings[] = $string;
                
            }
            
        }
        
        return $strings;
        
    }
    
    function executeMultiQueryArray($sql)
    {
        writeToLog("multiQueryArray: " . $sql);
        global $con;
        if (mysqli_multi_query($con, $sql)) {
            $arrOfTableArray = array();
            $resultArray = array();
            do {
                /* store first result set */
                if ($result = mysqli_store_result($con)) {
                    while ($row = mysqli_fetch_object($result)) {
                        array_push($resultArray, $row);
                    }
                    array_push($arrOfTableArray,$resultArray);
                    $resultArray = [];
                    mysqli_free_result($result);
                }
                if(!mysqli_more_results($con))
                {
                    break;
                }
            } while (mysqli_next_result($con));
            
            return $arrOfTableArray;
        }
        return "";
    }
    
    function executeMultiQuery($sql)
    {
        writeToLog("executeMultiQuery: " . $sql);
        global $con;
        if (mysqli_multi_query($con, $sql)) {
            $arrOfTableArray = array();
            $resultArray = array();
            do {
                /* store first result set */
                if ($result = mysqli_store_result($con)) {
                    while ($row = mysqli_fetch_object($result)) {
                        array_push($resultArray, $row);
                    }
                    array_push($arrOfTableArray,$resultArray);
                    $resultArray = [];
                    mysqli_free_result($result);
                }
                if(!mysqli_more_results($con))
                {
                    break;
                }
            } while (mysqli_next_result($con));
            
            return json_encode($arrOfTableArray);
        }
        return "";
    }
    
    function printAllPost()
    {
        global $con;
        $paramAndValue;
        $i = 0;
        foreach ($_POST as $param_name => $param_val)
        {
            if($i == 0)
            {
                $paramAndValue = "Param=Value: ";
            }
            $paramAndValue .= "$param_name=$param_val&";
            $_POST[$param_name] = mysqli_real_escape_string($con,$param_val);
            $i++;
        }
        
        if(sizeof($_POST) > 0)
        {
            writeToLog($paramAndValue);
        }
        
        $_POST["modifiedDate"] = date("Y-m-d H:i:s");
    }
    
    function getToPost()
    {
        global $con;
        foreach ($_GET as $param_name => $param_val)
        {
            $_POST['$param_name'] = mysqli_real_escape_string($con,$param_val);
        }
    }

    function setConnectionValueWithoutCheckUpdate($dbName)
    {
        global $con;
        global $jummumOM;
        global $checkUpdate;
        
        
        if($_GET['dbName'])
        {
            $dbName = $_GET['dbName'];
        }
        
        
        if($dbName == "")
        {
            $dbName = $jummumOM;
        }
        
        
        // Create connection
        //        $con=mysqli_connect("localhost","FFD","123456",$dbName);
        $con=mysqli_connect("localhost","andAdmin","111111",$dbName);
        
        
        $timeZone = mysqli_query($con,"SET SESSION time_zone = '+07:00'");
        mysqli_set_charset($con, "utf8");
        
    }
    
    function setConnectionValue($dbName)
    {
        global $con;
        global $jummumOM;
        global $checkUpdate;
    
        
        if($_GET['dbName'])
        {
            $dbName = $_GET['dbName'];
        }
        
        
        if($dbName == "")
        {
            $dbName = $jummumOM;
        }
        
        
        // Create connection
//        $con=mysqli_connect("localhost","FFD","123456",$dbName);
        $con=mysqli_connect("localhost","andAdmin","111111",$dbName);
        
        
        $timeZone = mysqli_query($con,"SET SESSION time_zone = '+07:00'");
        mysqli_set_charset($con, "utf8");
        
        
        if($checkUpdate)
        {
            isNeedUpdateVersion();
        }
    }
    
    function getDeviceTokenFromUsername($user)
    {
        global $con;
        $sql = "select DeviceToken from useraccount where username = '$user'";
        $selectedRow = getSelectedRow($sql);
        $deviceToken = $selectedRow[0]['DeviceToken'];
        
        
        writeToLog('getDeviceTokenFromUsername deviceToken: ' . $deviceToken);
        return $deviceToken;
    }
    
    function doQueryTask($sql)
    {
        global $con;
        $user = $_POST["modifiedUser"];
        $res = mysqli_query($con,$sql);        
        if(!$res)
        {
            $error = "query fail: " .  mysqli_error($con). ", sql: $sql, modified user: $user";
            writeToLog($error);
            $response = array('status' => $error);
            return $response;
        }
        else
        {
            writeToLog("query success, sql: $sql, modified user: $user");
        }
        return "";
    }
    
    function doMultiQueryTask($sql)
    {
        global $con;
        $user = $_POST["modifiedUser"];
        $res = mysqli_multi_query($con,$sql);
        if(!$res)
        {
            $error = "query fail: " .  mysqli_error($con). ", sql: $sql, modified user: $user";
            writeToLog($error);
            $response = array('status' => $error);
            return $response;
        }
        else
        {
            writeToLog("query success, sql: $sql, modified user: $user");
        }
        return "";
    }

    function doPushNotificationTaskToDevice($deviceToken,$selectedRow,$type,$action)
    {
        global $con;
        $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$deviceToken','$type','$action','" . json_encode($selectedRow, JSON_UNESCAPED_UNICODE) . "',now())";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);            
            return $ret;
        }
        $pushSyncID = mysqli_insert_id($con);
        writeToLog('pushsyncid: '.$pushSyncID);
        
        return "";
    }
    
    function doPushNotificationTask($deviceToken,$selectedRow,$type,$action)
    {
        global $con;
        $pushDeviceTokenList = getOtherDeviceTokensList($deviceToken);
        
        foreach ($pushDeviceTokenList as $iDeviceToken)
        {
            //query statement
            if(strcmp($type,"sProductSales") == 0)
            {
                $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$iDeviceToken','$type','$action','" . $selectedRow . "',now())";
            }
            else if(strcmp($type,"sCompareInventory") == 0)
            {
                $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$iDeviceToken','$type','$action','" . $selectedRow . "',now())";
            }
            else
            {
                $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$iDeviceToken','$type','$action','" . json_encode($selectedRow, JSON_UNESCAPED_UNICODE) . "',now())";
            }
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
                return $ret;
            }
            $pushSyncID = mysqli_insert_id($con);
            writeToLog('pushsyncid: '.$pushSyncID);
        }
        return "";
    }
    
    function doPushNotificationTaskWithDbName($deviceToken,$selectedRow,$type,$action,$dbName)
    {
        global $con;
//        $pushDeviceTokenList = getOtherDeviceTokensList($deviceToken);
        
//        foreach ($pushDeviceTokenList as $iDeviceToken)
        {
            //query statement
            {
                $sql = "insert into $dbName.pushSync (DeviceToken, TableName, Action, Data, TimeSync) values ('$deviceToken','$type','$action','" . json_encode($selectedRow, JSON_UNESCAPED_UNICODE) . "',now())";
            }
            $ret = doQueryTask($sql);
            if($ret != "")
            {
                mysqli_rollback($con);
                return $ret;
            }
            $pushSyncID = mysqli_insert_id($con);
            writeToLog('pushsyncid: '.$pushSyncID);
        }
        return "";
    }
    
    function doPushNotificationTaskAsLog($con,$user,$deviceToken,$selectedRow,$type,$action)
    {
        //query statement
        $sql = "insert into pushSync (DeviceToken, TableName, Action, Data, TimeSync,TimeSynced) values ('$deviceToken','$type','delete log','" . json_encode($selectedRow, true) . "',now(),now())";
        $ret = doQueryTask($sql);
        if($ret != "")
        {
            mysqli_rollback($con);
            return $ret;
        }
        $pushSyncID = mysqli_insert_id($con);
        writeToLog('delete log pushsyncid: '.$pushSyncID);
        return "";
    }
    
    function sendPushNotificationToAllDevices()
    {
        $pushDeviceTokenList = getAllDeviceTokenList();
        
        foreach ($pushDeviceTokenList as $iDeviceToken)
        {
            sendPushNotificationToDevice($iDeviceToken);
        }
    }
    
    function sendPushNotificationToOtherDevices($deviceToken)
    {
        $pushDeviceTokenList = getOtherDeviceTokensList($deviceToken);
        foreach ($pushDeviceTokenList as $iDeviceToken)
        {
            sendPushNotificationToDevice($iDeviceToken);
        }
    }
    
    function sendPushNotificationToDevice($deviceToken)
    {
        $paramBody = array(
                           'badge' => 0
                           );
        sendPushNotification($deviceToken, $paramBody);
    }
    
    function sendPushNotificationAdmin($deviceToken,$title,$text,$category,$contentAvailable,$data)
    {
        writeToLog("send push to admin $jummum");
        global $adminCkPath;
        global $adminCkPass;
        foreach($deviceToken as $eachDeviceToken)
        {
            if(strlen($eachDeviceToken) == 64)
            {
                $paramBody = array(
                                   'content-available' => $contentAvailable
                                   ,'data' => $data
                                   );
                if($category != '')
                {
                    $paramBody["category"] = $category;
                }
                if($text != '')
                {
                    $paramBody["alert"] = $text;
                    $paramBody["sound"] = "default";
                }
                
                
                sendPushNotificationWithPath($eachDeviceToken, $paramBody, $adminCkPath, $adminCkPass);
            }
            else
            {
                $key = $firebaseKeyAdmin;
                sendFirebasePushNotification($eachDeviceToken,$text,"",$data,$key);
            }
        }
    }
    
    function sendPushNotificationJummum($deviceToken,$title,$text,$category,$contentAvailable,$data)
    {
        writeToLog("send push to $jummum");
        global $jummumCkPath;
        global $jummumCkPass;
        foreach($deviceToken as $eachDeviceToken)
        {
            if(strlen($eachDeviceToken) == 64)
            {
                $paramBody = array(
                                   'content-available' => $contentAvailable
                                   ,'data' => $data
                                   );
                if($category != '')
                {
                    $paramBody["category"] = $category;
                }
                if($text != '')
                {
                    $paramBody["alert"] = $text;
                    $paramBody["sound"] = "default";
                }
                
                
                sendPushNotificationWithPath($eachDeviceToken, $paramBody, $jummumCkPath, $jummumCkPass);
            }
            else
            {
                $key = $firebaseKeyJummum;
                sendFirebasePushNotification($eachDeviceToken,$text,"",$data,$key);
            }
        }
    }
    
    function sendPushNotificationJummumOM($deviceToken,$title,$text,$category,$contentAvailable,$data)
    {
        writeToLog("send push to $jummumOM");
        global $jummumOMCkPath;
        global $jummumOMCkPass;
        foreach($deviceToken as $eachDeviceToken)
        {
            if(strlen($eachDeviceToken) == 64)
            {
                $paramBody = array(
                                   'content-available' => $contentAvailable
                                   );
                if($category != '')
                {
                    $paramBody["category"] = $category;
                }
                if($text != '')
                {
                    $paramBody["alert"] = $text;
                    $paramBody["sound"] = "default";
                }
                if($data)
                {
                    $paramBody["data"] = $data;
                }
            
                
                sendPushNotificationWithPath($eachDeviceToken, $paramBody, $jummumOMCkPath, $jummumOMCkPass);
            }
            else
            {
                $key = $firebaseKeyJummumOM;
                sendFirebasePushNotification($eachDeviceToken,$text,"",$data,$key);
            }
        }
    }
    
    function sendPushNotificationToDeviceWithPath($deviceToken,$path,$passForCk,$msg,$receiptID,$category,$contentAvailable)
    {
        foreach($deviceToken as $eachDeviceToken)
        {
            if(strlen($eachDeviceToken) == 64)
            {
                $paramBody = array(
                                   'content-available' => $contentAvailable
                                   ,'receiptID' => $receiptID
                                   );
                if($category != '')
                {
                    $paramBody["category"] = $category;
                }
                if($msg != '')
                {
                    $paramBody["alert"] = $msg;
                    $paramBody["sound"] = "default";
                }
                
                sendPushNotificationWithPath($eachDeviceToken, $paramBody, $path, $passForCk);
            }
            else
            {
                $data = array("receiptID" => $receiptID);
                sendFirebasePushNotification($eachDeviceToken,"",$msg,$data,$key);
            }
        }
    }
    
    function sendFirebasePushNotification($token, $title, $text, $data, $key)
    {
        // create curl resource
        $ch = curl_init();
        
        // set url
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/fcm/send");
        
        
        
        //payload
        $noti = array("title" => $title, "text" => $text);
        $paramBody = array(
                           "to" => $token
                           ,"notification" => $noti
                           ,"data" => $data
                           );
        $payload = json_encode($paramBody);
        
        
        
        //header
        $header = array();
        $header[] = 'Content-Type:application/json';
        $header[] = 'Authorization: key=' . $key;
        
        
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $header);
        
        
        
        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        // $output contains the output string
        $output = curl_exec($ch);
        
        
        if ($output === false)
        {
            // throw new Exception('Curl error: ' . curl_error($crl));
            print_r('Curl error: ' . curl_error($ch));
        }
        // close curl resource to free up system resources
        curl_close($ch);
    }
    
    function getSelectedRow($sql)
    {
        global $con;
        if ($result = mysqli_query($con, $sql))
        {
            $resultArray = array();
            $tempArray = array();
            
            while($row = mysqli_fetch_array($result))
            {
                $tempArray = $row;
                array_push($resultArray, $tempArray);
            }
            mysqli_free_result($result);
        }
        if(sizeof($resultArray) == 0)
        {
            $error = "query: selected row count = 0, sql: " . $sql . ", modified user: " . $_POST["modifiedUser"];
            writeToLog($error);
        }
        else
        {
            writeToLog("query success, sql: " . $sql . ", modified user: " . $_POST["modifiedUser"]);
        }
        
        return $resultArray;
    }
    
    function getAllDeviceTokenList()
    {
        global $con;
        $sql = "select DeviceToken from Device where DeviceToken != ''";
        if ($result = mysqli_query($con, $sql))
        {
            $deviceTokenList = array();
            while($row = mysqli_fetch_array($result))
            {
                $strDeviceToken = $row["DeviceToken"];
                array_push($deviceTokenList, $strDeviceToken);
            }
            mysqli_free_result($result);
        }
        return $deviceTokenList;
    }
    
    function getOtherDeviceTokensList($modifiedDeviceToken)
    {
        global $con;
        $sql = "select DeviceToken from Device where DeviceToken != '' and DeviceToken != '" . $modifiedDeviceToken . "'";
        if ($result = mysqli_query($con, $sql))
        {
            $deviceTokenList = array();
            while($row = mysqli_fetch_array($result))
            {
                $strDeviceToken = $row["DeviceToken"];
                array_push($deviceTokenList, $strDeviceToken);
            }
            mysqli_free_result($result);
        }

        return $deviceTokenList;
    }
    
    function getDeviceTokenAndCountNotSeenList($modifiedUser,$modifiedDeviceToken)
    {
        global $con;
        $sql = "select Device.DeviceToken, UserAccount.CountNotSeen, UserAccount.Username from Device left join UserAccount on Device.DeviceToken = UserAccount.DeviceToken where Device.DeviceToken != '" . $modifiedDeviceToken . "' and Device.DeviceToken != '' and UserAccount.PushOnSale = 1";
        writeToLog("countNotSeenList: " . $sql);
        if ($result = mysqli_query($con, $sql))
        {
            $deviceTokenAndCountNotSeenList = array();
            while($row = mysqli_fetch_array($result))
            {
                $strDeviceToken = $row["DeviceToken"];
                $strCountNotSeen = $row["CountNotSeen"];
                $strUsername = $row["Username"];
                array_push($deviceTokenAndCountNotSeenList, array("DeviceToken" => $strDeviceToken,"CountNotSeen" => $strCountNotSeen,"Username"=>$strUsername));
            }
            mysqli_free_result($result);
        }
        return $deviceTokenAndCountNotSeenList;
    }
    
    function writeToLog($message)
    {
        $year = date("Y");
        $month = date("m");
        $day = date("d");
        
        $fileName = 'transactionLog' . $year . $month . $day . '.log';
        $filePath = './TransactionLog/';
        if (!file_exists($filePath))
        {        
            mkdir($filePath, 0777, true);
        }
        $filePath = $filePath . $fileName;
        
        
        
        if ($fp = fopen($filePath, 'at'))
        {
            $arrMessage = explode("\\n",$message);
            if(sizeof($arrMessage) > 1)
            {
                foreach($arrMessage as $eachLine)
                {
                    $newMessge .= PHP_EOL . $eachLine ;
                }
            }
            else
            {
                $newMessge = $message;
            }
            
            fwrite($fp, date('c') . ' ' . $newMessge . PHP_EOL);
            fclose($fp);
        }
    }
    
    function writeToErrorLog($message)
    {
        $year = date("Y");
        $month = date("m");
        $day = date("d");
        
        $fileName = 'errorLog' . $year . $month . $day . '.log';
        $filePath = './TransactionLog/';
        if (!file_exists($filePath))
        {
            mkdir($filePath, 0777, true);
        }
        $filePath = $filePath . $fileName;
        
        
        
        if ($fp = fopen($filePath, 'at'))
        {
            $arrMessage = explode("\\n",$message);
            if(sizeof($arrMessage) > 1)
            {
                foreach($arrMessage as $eachLine)
                {
                    $newMessge .= PHP_EOL . $eachLine ;
                }
            }
            else
            {
                $newMessge = $message;
            }
            
            fwrite($fp, date('c') . ' ' . $newMessge . PHP_EOL);
            fclose($fp);
        }
    }

    function sendPushNotification($strDeviceToken,$arrBody)
    {
        if($strDeviceToken == "simulator")
        {
            return;
        }
        writeToLog("send push to device: " . $strDeviceToken . ", body: " . json_encode($arrBody));
        global $pushFail;
        $token = $strDeviceToken;
        $pass = 'jill';
        $message = 'คุณพิสุทธิ์ กำลังไปเขาใหญ่กับฉัน แกอยากได้อะไรไหมกั๊ง (สายน้ำผึ้ง)pushnotification';
        
        
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $pass);
        

        if(!$pushFail)
        {
            global $sandBox;
            if($sandBox)
            {
                $fp = stream_socket_client(
                                       'ssl://gateway.sandbox.push.apple.com:2195', $err,
                                       $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
            }
            else
            {
                $fp = stream_socket_client(
                                           'ssl://gateway.push.apple.com:2195', $err,
                                           $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
            }
        }
        
        
        if (!$fp)
        {
            $pushFail = true;
            $error = "ติดต่อ Server ไม่ได้ ให้ลองย้อนกลับไป สร้าง pem ใหม่: $err $errstr" . PHP_EOL;
            writeToLog($error);
            
            return;
        }

        
        $body['aps'] = $arrBody;
        $json = json_encode($body);
        $msg = chr(0).pack('n', 32).pack('H*',$token).pack('n',strlen($json)).$json;
        $result = fwrite($fp, $msg, strlen($msg));
        if (!$result)
        {
            $status = "0";
            writeToLog("push notification: fail, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
        }
        else
        {
            $status = "1";
            writeToLog("push notification: success, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
        }
        
        fclose($fp);
        return $status;
    }
    
    function sendPushNotificationWithPath($strDeviceToken,$arrBody,$path,$passForCk)
    {
        writeToLog("send push to device: " . $strDeviceToken . ", body: " . json_encode($arrBody));
        global $pushFail;
        $token = $strDeviceToken;
        $pass = $passForCk;//'jill';
        $message = 'pushnotification';
        
        
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', "$path".'ck.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $pass);
        
        
        if(!$pushFail)
        {
            global $sandBox;
            if($sandBox)
            {
                $fp = stream_socket_client(
                                       'ssl://gateway.sandbox.push.apple.com:2195', $err,
                                       $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
            }
            else
            {
                $fp = stream_socket_client(
                                           'ssl://gateway.push.apple.com:2195', $err,
                                           $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
            }
        }
        
        
        if (!$fp)
        {
            $pushFail = true;
            $error = "ติดต่อ Server ไม่ได้ ให้ลองย้อนกลับไป สร้าง pem ใหม่: $err $errstr" . PHP_EOL;
            writeToLog($error);
            
            return;
        }
        
        $body['aps'] = $arrBody;
        $json = json_encode($body);
        $msg = chr(0).pack('n', 32).pack('H*',$token).pack('n',strlen($json)).$json;
        $result = fwrite($fp, $msg, strlen($msg));
        if (!$result)
        {
            $status = "0";
            writeToLog("push notification: fail, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
        }
        else
        {
            $status = "1";
            writeToLog("push notification: success, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
        }
        
        fclose($fp);
        return $status;
    }
    
    function sendTestApplePushNotification($strDeviceToken,$arrBody)
    {
        global $pushFail;
        $token = $strDeviceToken;
        $pass = 'jill';
        $message = 'คุณพิสุทธิ์ กำลังไปเขาใหญ่กับฉัน แกอยากได้อะไรไหมกั๊ง (สายน้ำผึ้ง)pushnotification';
        
        
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $pass);
        
        
        if(!$pushFail)
        {
            global $sandBox;
            if($sandBox)
            {
                $fp = stream_socket_client(
                                       'ssl://gateway.sandbox.push.apple.com:2195', $err,
                                       $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
            }
            else
            {
                $fp = stream_socket_client(
                                           'ssl://gateway.push.apple.com:2195', $err,
                                           $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
            }
        }
        
        
        if (!$fp)
        {
            $pushFail = true;
            $error = "apple push: ติดต่อ Server ไม่ได้ ให้ลองย้อนกลับไป สร้าง pem ใหม่: $err $errstr" . PHP_EOL;
            writeToLog($error);
            
            return;
        }
        
        
        $body['aps'] = $arrBody;
        $json = json_encode($body);
        $msg = chr(0).pack('n', 32).pack('H*',$token).pack('n',strlen($json)).$json;
        $result = fwrite($fp, $msg, strlen($msg));
        if (!$result)
        {
            $status = "0";
            writeToLog("apple push notification: fail, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
        }
        else
        {
            $status = "1";
            writeToLog("apple push notification: success, device token : " . $strDeviceToken . ", payload: " . json_encode($arrBody));
        }
        
        fclose($fp);
        return $status;
    }

    function sendEmail($toAddress,$subject,$body)
    {
        require './../phpmailermaster/PHPMailerAutoload.php';
        $mail = new PHPMailer;
//        writeToLog("phpmailer");
        //$mail->SMTPDebug = 3;                               // Enable verbose debug output
        
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'cpanel02mh.bkk1.cloud.z.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication // if not need put false
        $mail->Username = 'noreply@jummum.co';                 // SMTP username
        $mail->Password = 'Jin1210!88';                           // SMTP password
        
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted // if nedd
        $mail->Port = 465;                                    // TCP port to connect to // if nedd
        
        $mail->From = 'noreply@jummum.co'; // mail form user mail auth smtp
        $mail->FromName = 'JUMMUM';//$_POST['dbName'];
        $mail->addAddress($toAddress); // Add a recipient
        //$mail->addAddress('ellen@example.com'); // if nedd
        //$mail->addReplyTo('info@example.com', 'Information'); // if nedd
        //$mail->addCC('cc@example.com'); // if nedd
        //$mail->addBCC('bcc@example.com'); // if nedd
        
        $mail->WordWrap = 50;                                 // Set word wrap to 50 characters
        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments // if nedd
        //$mail->addAttachment('http://minimalist.co.th/imageupload/34664/minimalistLogoReceipt.gif', 'logo.gif');    // Optional name // if nedd
//        $mail->AddEmbeddedImage('minimalistLogoReceipt.jpg', 'logo', 'minimalistLogoReceipt.jpg');
        $mail->isHTML(true);                                  // Set email format to HTML // if format mail html // if no put false
        
        $mail->Subject = $subject; // text subject
        $mail->Body    = $body; // body
        
        //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients'; // if nedd
//        writeToLog("before send()");
        if(!$mail->send())
        { // check send mail true/false
            echo 'Message could not be sent.'; // message if send mail not complete
            echo 'Mailer Error: ' . $mail->ErrorInfo; // message error
            $response = array('status' => 'Mailer Error: ' . $mail->ErrorInfo);
            
            $error = "send email fail, Mailer Error: " . $mail->ErrorInfo . ", modified user: " . $user;
            writeToLog($error);
            
        }
        else
        {
            //    echo 'Message has been sent'; // message if send mail complete
            $response = array('status' => '1');
        }
    }
    
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    function getDayOfWeekText($dayOfWeek)
    {
        switch($dayOfWeek)
        {
            case 1:
                return "Mon";
            case 2:
                return "Tue\t";
            case 3:
                return "Wed";
            case 4:
                return "Thu\t";
            case 5:
                return "Fri\t";
            case 6:
                return "Sat\t";
            case 7:
                return "Sun\t";
        }
    }
?>
