<?php
    include_once('dbConnect.php');
    setConnectionValue("");
    
    $contentAvailable = 1;
    $receiptID = 2;
    $data = null;
    $arrBody = array(
                     'alert' => 'test'//ข้อความ
                      ,'sound' => 'default'//,//เสียงแจ้งเตือน
//                      ,'badge' => 3 //ขึ้นข้อความตัวเลขที่ไม่ได้อ่าน
                     ,'category' => 'Print'
//                     ,'data' => $data
                     ,'content-available' => $contentAvailable
                     ,'receiptID' => $receiptID
                      );
    sendPushNotificationWithPath('d4ab5cd3e06c0b840795e5112a5bba4489550f0a3c5af4ab4194625048deb23c',$arrBody,'','jill');
    
//    sleep(5);
//
//
//    $arrBody = array(
//                     'alert' => 'เทสจิ๋ว 2
//                     เอ'//ข้อความ
//                     ,'sound' => 'default'//,//เสียงแจ้งเตือน
//                     //                      ,'badge' => 3 //ขึ้นข้อความตัวเลขที่ไม่ได้อ่าน
//                     );
//    sendTestApplePushNotification('1877301d04f677b7fcc415b7f0bcbd799bf679013b14f76ce746d778087a22f6',$arrBody);
?>

//<table><tr><td style="text-align: center;border: 1px solid black; padding-left: 10px;padding-right: 10px; border-radius: 15px;">x</td></tr></table>
