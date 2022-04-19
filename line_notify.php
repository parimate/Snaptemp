<?php include 'connect.php'; ?>

<?php

date_default_timezone_set("Asia/Bangkok");
$date = date("Y-m-d");
$time = date("H:i:s");


/******************************************************* กรองข้อมูลเข้า *****************************************************************************************************/

// ตรวจสอบการส่งข้อมูล
if((isset($_POST['t'])) && (isset($_POST['h'])) && (isset($_POST['lpg'])) && (isset($_POST['co'])) 
    && (isset($_POST['s'])) && (isset($_POST['c_code'])) && (isset($_POST['id']))/*&& (isset($_POST['m']))*/){
    echo " 'post_OK' ";


        // ตรวจสอบว่ามีค่าว่างมั้ย
        if((empty($_POST['t'])) && (empty($_POST['h'])) && (empty($_POST['lpg'])) && (empty($_POST['co'])) 
            && (empty($_POST['s']))&& (empty($_POST['id'])) /*&& (empty($_POST['m']))*/){
            echo " 'empty' ";
        } else {
            echo " 'not empty' ";

        
        // ตรวจสอบว่าเป็นตัวเลขมั้ย
        if((is_numeric($_POST['t']))  && (is_numeric($_POST['h'])) && (is_numeric($_POST['lpg']))
        && (is_numeric($_POST['co'])) && (is_numeric($_POST['s']))/*&& (is_numeric($_POST['tag_id']))*/){
            echo " 'numeric_OK' " ;   

                // ตรวจสอบว่าเป็นตัวอักษรพิเศษมั้ย
                if ((htmlspecialchars($_POST['t'])) && (htmlspecialchars($_POST['h'])) && (htmlspecialchars($_POST['lpg'])) 
                    && (htmlspecialchars($_POST['co'])) && (htmlspecialchars($_POST['s']))){
                  
                    echo " 'No special characters' ";
                    echo $_POST['t'];
                    
                    $temperature = $_POST['t'];
                    $humidity = $_POST['h'];
                    $lpg = $_POST['lpg'];
                    $co = $_POST['co'];
                    $smoke = $_POST['s'];
                    $company_code = $_POST['c_code'];
                    $id = $_POST['id'];
                    $macaddress = $_POST['m'];
                    
                    // เพิ่มข้อมูลลง Database 
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

/******************************************************* ฟังค์ชันการส่ง LINE (อุณหภูมิ) *****************************************************************************************************/

function line_notify_temp($Token,$message,$company_code,$temperature)
{
    $Token = "xG7D5ss6jTpsEDS9yqnUyDF51QXNc4vFinVSdWYv0GR";  //กำหนด token ของ line
    $message = "อุณหภูมิสูงกว่ากำหนด!!!".PHP_EOL."Company: ".$company_code.PHP_EOL."Temperature: ".$temperature." °C";                       //กำหนดข้อความที่ใช้ส่ง 
    $lineapi = $Token; 
    $mms =  trim($message);  
    date_default_timezone_set("Asia/Bangkok");
    $chOne = curl_init(); 
    curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
    curl_setopt($chOne, CURLOPT_HEADER, 0);
    curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($chOne, CURLOPT_PROXYPORT, "8080");
    curl_setopt($chOne, CURLOPT_PROXYTYPE, 'HTTP');
    curl_setopt($chOne, CURLOPT_PROXY,"s-hq-pac");
    curl_setopt($chOne, CURLOPT_PROXYUSERPWD, "lineservice:Yke=6MN%");
    // SSL USE 
    curl_setopt( $chOne, CURLOPT_SSL_VERIFYHOST, 0); 
    curl_setopt( $chOne, CURLOPT_SSL_VERIFYPEER, 0); 
    curl_setopt( $chOne, CURLOPT_POST, 1); 
    curl_setopt( $chOne, CURLOPT_POSTFIELDS, "message=$mms");  
    curl_setopt( $chOne, CURLOPT_FOLLOWLOCATION, 1); 
    $headers = array( 'Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer '.$lineapi.'', );
    curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers); 
    curl_setopt( $chOne, CURLOPT_RETURNTRANSFER, 1); 
    $result = curl_exec( $chOne ); 

    if(curl_error($chOne)){ 
           echo 'error:' . curl_error($chOne); 
    } 
    else { 
        $result_ = json_decode($result, true); 
        echo $result;
    } 
    curl_close( $chOne );   
}


/******************************************************* ฟังค์ชันการส่ง LINE (ควัน) *****************************************************************************************************/

function line_notify_smoke($Token,$message,$company_code,$smoke)
{
    $Token = "xG7D5ss6jTpsEDS9yqnUyDF51QXNc4vFinVSdWYv0GR";  //กำหนด token ของ line
    $message = "ตรวจพบควัน!!".PHP_EOL."Company: ".$company_code.PHP_EOL."Smoke: ".$smoke." ppm";                       //กำหนดข้อความที่ใช้ส่ง 
    $lineapi = $Token; 
    $mms =  trim($message);  
    date_default_timezone_set("Asia/Bangkok");
    $chOne = curl_init(); 
    curl_setopt($chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
    curl_setopt($chOne, CURLOPT_HEADER, 0);
    curl_setopt($chOne, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($chOne, CURLOPT_PROXYPORT, "8080");
    curl_setopt($chOne, CURLOPT_PROXYTYPE, 'HTTP');
    curl_setopt($chOne, CURLOPT_PROXY,"s-hq-pac");
    curl_setopt($chOne, CURLOPT_PROXYUSERPWD, "lineservice:Yke=6MN%");
    // SSL USE 
    curl_setopt( $chOne, CURLOPT_SSL_VERIFYHOST, 0); 
    curl_setopt( $chOne, CURLOPT_SSL_VERIFYPEER, 0); 
    curl_setopt( $chOne, CURLOPT_POST, 1); 
    curl_setopt( $chOne, CURLOPT_POSTFIELDS, "message=$mms");  
    curl_setopt( $chOne, CURLOPT_FOLLOWLOCATION, 1); 
    $headers = array( 'Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer '.$lineapi.'', );
    curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers); 
    curl_setopt( $chOne, CURLOPT_RETURNTRANSFER, 1); 
    $result = curl_exec( $chOne ); 

    if(curl_error($chOne)){ 
           echo 'error:' . curl_error($chOne); 
    } 
    else { 
        $result_ = json_decode($result, true); 
        echo $result;
    } 
    curl_close( $chOne );   
}

/******************************************************* เงื่อนไขการส่ง LINE (อุณหภูมิ) *******************************************************************************/

    if($temperature > 35 ){ // เงื่อนไขเมื่ออุณหภูมิสูงกว่ากำหนด
        $time = date("i");
        $time_mod = $time % 60;  
        echo " 'time_mod=$time_mod' ";

        $status_query = sqlsrv_query($conn,"SELECT tag_id,temp_notify FROM device");
        while($row = sqlsrv_fetch_array($status_query, SQLSRV_FETCH_ASSOC)) {
          
            if($row['tag_id'] == $id){
                echo  $row["temp_notify"]; 
                    //เมื่อมีการแจ้งเตือนและยังไม่ครบ 1 ชั่วโมง
                    if($row['temp_notify'] == 1 && $time_mod != 0){       
                        echo " '1_STOP_LINE_Notify' ";
                    }
                    //เมื่อมีการแจ้งเตือนและครบ 1 ชั่วโมง
                    elseif($row['temp_notify'] == 1 && $time_mod == 0){   
                        echo " '2_Send_LINE_Notify' ";
                        line_notify_temp($token,$message,$company_code,$temperature);
                    }
                    //เมื่อมีการแจ้งเตือนและครับ 1 ชั่วโมง 1 นาที
                    elseif($row['temp_notify'] == 1 && $time_mod == 1){   
                        echo " '3_STOP_LINE_Notify' ";  
                        sqlsrv_query($conn,"UPDATE device SET temp_notify = '0' WHERE tag_id = $id"); 
                    }
                    //เมื่อไม่มีการแจ้งเตือนและยังไม่ครบ 1 ชั่วโมง
                    elseif($row['temp_notify'] == 0 && $time_mod != 0){   
                        echo " '4_Send_LINE_Notify' ";                      
                        line_notify_temp($token,$message,$company_code,$temperature);
                        sqlsrv_query($conn,"UPDATE device SET temp_notify = '1' WHERE tag_id = $id");  
                    }
                    //เมื่อไม่มีการแจ้งเตือนและครบ 1 ชั่วโมง
                    elseif($row['temp_notify'] == 0 && $time_mod == 0){   
                        echo " '5_Send_LINE_Notify' ";
                        line_notify_temp($token,$message,$company_code,$temperature);
                        sqlsrv_query($conn,"UPDATE device SET temp_notify = '1' WHERE tag_id = $id");  
                    }
               
                
            }
        }
        
    } else{
        sqlsrv_query($conn,"UPDATE device SET temp_notify = '0' WHERE tag_id = $id"); 
    }

/******************************************************* เงื่อนไขการส่ง LINE (ควัน) *******************************************************************************/

    if($smoke > 1000 ){ // เงื่อนไขเมื่อค่าความหนาแน่นของควันสูงกว่ากำหนด
        $time = date("i");
        $time_mod = $time % 60;  
        echo " 'time_mod=$time_mod' ";

        $status_query = sqlsrv_query($conn,"SELECT tag_id,smoke_notify FROM device");
        while($row = sqlsrv_fetch_array($status_query, SQLSRV_FETCH_ASSOC)) {
          
            if($row['tag_id'] == $id){
                echo  $row["smoke_notify"]; 
                    //เมื่อมีการแจ้งเตือนและยังไม่ครบ 1 ชั่วโมง
                    if($row['smoke_notify'] == 1 && $time_mod != 0){       
                        echo " '1_STOP_LINE_Notify' ";
                    }
                    //เมื่อมีการแจ้งเตือนและครบ 1 ชั่วโมง
                    elseif($row['smoke_notify'] == 1 && $time_mod == 0){   
                        echo " '2_Send_LINE_Notify' ";
                        line_notify_smoke($token,$message,$company_code,$smoke);
                    }
                    //เมื่อมีการแจ้งเตือนและครับ 1 ชั่วโมง 1 นาที
                    elseif($row['smoke_notify'] == 1 && $time_mod == 1){   
                        echo " '3_STOP_LINE_Notify' ";  
                        sqlsrv_query($conn,"UPDATE device SET smoke_notify = '0' WHERE tag_id = $id"); 
                    }
                    //เมื่อไม่มีการแจ้งเตือนและยังไม่ครบ 1 ชั่วโมง
                    elseif($row['smoke_notify'] == 0 && $time_mod != 0){   
                        echo " '4_Send_LINE_Notify' ";                      
                        line_notify_smoke($token,$message,$company_code,$smoke);
                        sqlsrv_query($conn,"UPDATE device SET smoke_notify = '1' WHERE tag_id = $id");  
                    }
                    //เมื่อไม่มีการแจ้งเตือนและครบ 1 ชั่วโมง
                    elseif($row['smoke_notify'] == 0 && $time_mod == 0){   
                        echo " '5_Send_LINE_Notify' ";
                        line_notify_smoke($token,$message,$company_code,$smoke);
                        sqlsrv_query($conn,"UPDATE device SET smoke_notify = '1' WHERE tag_id = $id");  
                    }
               
                
            }
        }
        
    } else{
        sqlsrv_query($conn,"UPDATE device SET smoke_notify = '0' WHERE tag_id = $id"); 
    }


    

?>