<?php
    include_once("dbConnect.php");    
?>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1" charset="UTF-8"/>
<link href="https://fonts.googleapis.com/css?family=Prompt" rel="stylesheet">
<style>
body {
    font-family: 'Prompt', serif;
    font-size: 22px;
}
</style>

<script type="text/javascript">
function showSuccessMessage()
{
    var resetPasswordSuccess = document.getElementById('resetPasswordSuccess');
    resetPasswordSuccess.style.display = "inline";
}

function validateForm()
{
    var validate = 1;
    var password = document.forms["resetPasswordForm"]["password"].value;
    var re = new RegExp("(?=^.{8,}$)((?=.*\\d)|(?=.*\\W+))(?![.\\n])(?=.*[A-Z])(?=.*[a-z]).*$");
    if(!re.test(password))
    {
        var errorstrongPassword = document.getElementById('errorStrongPassword');
        errorstrongPassword.style.display = "inline";
        validate = 0;
    }
    
    
    var reenterPassword = document.forms["resetPasswordForm"]["reenterPassword"].value;
    if(password != reenterPassword)
    {
        var errorReenterPassword = document.getElementById('errorReenterPassword');
        errorReenterPassword.style.display = "inline";
        validate = 0;
    }
    if(!validate)
    {
        var resetPasswordSuccess = document.getElementById('resetPasswordSuccess');
        resetPasswordSuccess.style.display = "none";
        return false;
    }
    
    return true;
}
</script>
</head>
<body text="#FFFFFF" style="background-color:#FF3C4B">
<p>&nbsp;<img class="" src="./Image/jummumOMLogo.png" alt="" width="120" /></p>
<form name="resetPasswordForm" action="./resetpassword.php?codereset=<?=$_GET['codereset']?>" onsubmit="return validateForm()" method="post">
<p>&nbsp;</p>
<p>รหัสผ่านใหม่:<br /><input name="password" type="password" /></p>
<p><br /> ใส่รหัสผ่านใหม่อีกครั้งหนึ่ง:<br /> <input name="reenterPassword" type="password" /></p>
<p>&nbsp;</p>
<p><input type="submit" value="Submit" name ="submit"/></p>
<p style="font-size:16px;">**หากคุณต้องการความช่วยเหลือสามารถติดต่อผ่านทางอีเมลล์&nbsp;<a href="mailto:help@jummum.co">help@jummum.co</a>&nbsp;หรือโทร 081-307-2993</p>
</form>
<table width="100%">
<tbody>
<tr>
<td id="errorStrongPassword" style="display:none" bgcolor="#FFC3C8"><font color='#464646'>พาสเวิร์ดต้องประกอบไปด้วย<br />1.อักษรตัวเล็กอย่างน้อย 1 ตัว<br />2.อักษรตัวใหญ่อย่างน้อย 1 ตัว<br />3.ตัวเลขหรืออักษรพิเศษอย่างน้อย 1 ตัว<br />4.ความยาวขั้นต่ำ 8 ตัวอักษร</font></td>
</tr>
<tr>
<td id="errorReenterPassword" style="display:none" bgcolor="#FFC3C8"><font color='#464646'>
<p>รหัสผ่านทั้ง 2 อันไม่เหมือนกัน กรุณาตรวจสอบอีกครั้งหนึ่ง</p></font>
</td>
</tr>
<?php
    include_once("dbConnect.php");
    setConnectionValue($jummumOM);
    writeToLog("file: " . basename(__FILE__) . ", user: " . $_POST["modifiedUser"]);
    printAllPost();
    
    
    
    if(isset($_GET["codereset"]))
    {
        $codereset = $_GET["codereset"];
    }
    
    
    
    
    
    if (mysqli_connect_errno())
    {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    
    
    
    // Set autocommit to off
    mysqli_autocommit($con,FALSE);
    writeToLog("set auto commit to off");
    
    writeToLog("submit: " . $_POST["submit"]);
    writeToLog("isset submit: " . isset($_POST["submit"]));
    if($_POST["submit"])
    {
        if(isset($_POST["password"]))
        {
            $password = $_POST["password"];
        }
        
        $sql = "select * from forgotPassword where codeReset = '$codereset'";
        $selectedRow = getSelectedRow($sql);
        if(sizeof($selectedRow) == 0)
        {
            exit();
        }
        else
        {
            $requestDate = $selectedRow[0]["RequestDate"];
            $email = $selectedRow[0]["Email"];
            $dbName = $selectedRow[0]["DbName"];
            
            
            if(time()-StrToTime($requestDate) > 2*60*60)//2 hours
            {
                header("Location: ./ResetPasswordTimeOut.php");
                //                echo "ไม่สามารถรีเซ็ตรหัสผ่านได้ กรุณาส่งคำขอเปลี่ยนรหัสผ่านอีกครั้งหนึ่ง";
            }
            else
            {
                $hashPassword = hash('SHA256',$password.'FvTivqTqZXsgLLx1v3P8TGRyVHaSOB1pvfm02wvGadj7RLHV8GrfxaZ84oGA8RsKdNRpxdAojXYg9iAj');
                $sql = "update $dbName.useraccount set password = '$hashPassword', modifiedDate = now() where username = '$email'";
                $ret = doQueryTask($sql);
                if($ret != "")
                {
                    mysqli_rollback($con);
//                    putAlertToDevice();
                    echo json_encode($ret);
                    exit();
                }
                echo "<tr><td id='resetPasswordSuccess' style='display:inline' bgcolor='#cff3ed'><font color='#464646'>ตั้งค่ารหัสผ่านสำเร็จ</font></td></tr>";
                
                
                //do script successful
                mysqli_commit($con);
                mysqli_close($con);
                
                
                writeToLog("query commit, file: " . basename(__FILE__) . ", user: " . $_POST['modifiedUser']);
                //                exit();
            }
        }
    }
    ?>

</tbody>

</table>
</body>
</html>

