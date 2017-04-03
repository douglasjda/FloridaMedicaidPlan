<?php 
  
$result = array();

if (isset($_POST["type"]) && $_POST["type"] == "text/html" &&
  isset($_FILES["file"]) && !empty($_FILES["file"])){

  $result['type'] = "text/html"; 
  $result['sourceHTML'] = file_get_contents($_FILES["file"]['tmp_name']);
}

echo json_encode($result);