<?php

$uploadDir = 'images/';
$result = array();

if (isset($_POST["dir"]) && isset($_FILES["file"]) && !empty($_FILES["file"])){
  $projectName = $_POST["dir"];
  $baseName = $_FILES["file"]["name"];
  $tmpName = "../" . $projectName . $uploadDir . $baseName;
  $i = 0;   

  while (true) {
    if (file_exists($tmpName)){
      $tmpName = "../" . $projectName . $uploadDir . (++$i) . $baseName;    
    }else{
      break;    
    }
  }   

  if( move_uploaded_file($_FILES["file"]["tmp_name"], $tmpName)){
    if ($i > 0){
      $result['url'] = $uploadDir . $i . $baseName;  
    }else{
      $result['url'] = $uploadDir . $baseName;
    }
  };
}

echo json_encode($result); 