<?php
header('Access-Control-Allow-Origin: *');
    $target_path ="uploads/";
    $target_path = $target_path.basename($_FILES['file']['name']);

    if(move_uploaded_file($_FILES['file']['tmp_name'],$target_path)){
        header('Content-type: application/json');
        $data = ['success'=>true,'message'=>'uploaded'];
        echo json_encode($data);
    }else{
        $data = ['success'=>false,'message'=>'not uploaded'];
        echo json_encode($data); 
    }
?>