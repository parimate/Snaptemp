<?php include 'connect.php'; ?>

<?php

date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$time = date("H:i:s");

function device_input(){
    $temperature = $_POST['temperature'];
    $humidity = $_POST['humidity'];
    $lpg = $_POST['lpg'];
    $co = $_POST['co'];
    $smoke = $_POST['smoke'];
    $company_code = $_POST['company_code'];
    $tag_id = $_POST['tag_id'];
    $macaddress = $_POST['macaddress'];
}

function post_input(){
    $_POST['temperature'];
    $_POST['humidity'];
    $_POST['lpg'];
    $_POST['co'];
    $_POST['smoke'];
    $_POST['company_code'];
    $_POST['tag_id'];
    $_POST['macaddress'];
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////

// ตรวจสอบการส่งข้อมูล
if((isset(post_input();)) {
        device_input();
        echo " 'post_OK' ";

        // ตรวจสอบว่ามีค่าว่างมั้ย
        if((empty(post_input();))){
            device_input();
            echo " 'empty' ";
        } else {
            echo " 'not empty' ";

        // ตรวจสอบว่าเป็นตัวเลขมั้ย
        if((is_numeric(post_input();))){
            device_input();
            echo " 'numeric_OK' " ;   

                // ตรวจสอบว่าเป็นตัวอักษรพิเศษมั้ย
                if ((htmlspecialchars(post_input();))){
                    device_input();
                    echo " 'No special characters' ";
               
                    sqlsrv_query($conn,"INSERT INTO [data] (record_date, record_time, temperature, humidity,lpg,co,smoke,company_code)
                    VALUES('$date','$time','$temperature','$humidity','$lpg','$co','$smoke','$company_code')");
                    echo " 'INSERT TO DATABASE' ";
                } 
                else { 
                    echo " 'With special characters' ";    
                }

        } else {
             echo " 'NO_numeric' ";
            }
         }
} else{
    echo " 'NO_Data' ";
} 

//////////////////////////////////////////////////////////////////////////////////////////////////////////

function sendlingmessaga(){
    define('LINE_API',"https://notify-api.line.me/api/notify");
    $token = "xG7D5ss6jTpsEDS9yqnUyDF51QXNc4vFinVSdWYv0GR"; //ใส่Token ที่copy เอาไว้
    $str = "อุณหภูมิสูงกว่ากำหนด!!! "; //ข้อความที่ต้องการส่ง สูงสุด 1000 ตัวอักษร
    $res = notify_message($str,$token);
    print_r($res);
    }
    
    function notify_message($message,$token){
     $queryData = array('message' => $message);
     $queryData = http_build_query($queryData,'','&');
     $headerOptions = array( 
             'http'=>array(
                'method'=>'POST',
                'header'=> "Content-Type: application/x-www-form-urlencoded\r\n"
                          ."Authorization: Bearer ".$token."\r\n"
                          ."Content-Length: ".strlen($queryData)."\r\n",
                'content' => $queryData
             ),
     );
     $context = stream_context_create($headerOptions);
     $result = file_get_contents(LINE_API,FALSE,$context);
     $res = json_decode($result);
     return $res;
    }

/////////////////////////////////////////////////////////////////////////////////////////////////////////


    if($temperature > 50){ // เงื่อนไขเมื่ออุณหภูมิสูงกว่ากำหนด
        $time = date("i");
        $time_mod = 0 % 60; 
        echo " 'time_mod=$time_mod' ";

        $status_query = sqlsrv_query($conn,"SELECT tag_id,status_notify FROM device");
        while($row = sqlsrv_fetch_array($status_query, SQLSRV_FETCH_ASSOC)) {
          
            if($row['tag_id'] == $tag_id){
                echo  $row["status_notify"]; 

                    if($row['status_notify'] == 1 && $time_mod != 0){ 
                        echo " '1_STOP_LINE_Notify' ";
                    }
                    elseif($row['status_notify'] == 1 && $time_mod == 0){
                        echo " '2_Send_LINE_Notify' ";
                        sendlingmessaga();
                        sqlsrv_query($conn,"UPDATE device SET status_notify = '0' WHERE tag_id = $tag_id"); 
                    }
                    elseif($row['status_notify'] == 0 && $time_mod != 0){
                        echo " '3_Send_LINE_Notify' ";
                        sendlingmessaga();
                        sqlsrv_query($conn,"UPDATE device SET status_notify = '1' WHERE tag_id = $tag_id");  
                    }
                    elseif($row['status_notify'] == 0 && $time_mod == 0){
                        echo " '4_Send_LINE_Notify' ";
                        sendlingmessaga();
                        sqlsrv_query($conn,"UPDATE device SET status_notify = '1' WHERE tag_id = $tag_id");  
                    }
            }
        }
        
    } else{
        sqlsrv_query($conn,"UPDATE device SET status_notify = '0' WHERE tag_id = $tag_id"); 
    }

?>