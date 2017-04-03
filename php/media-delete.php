<?php
if (isset($_POST["fileName"]) && isset($_POST["dir"])){
  $projectDir = $_POST["dir"];
  $fileName = $_POST["fileName"];
  $targetFile = "../" . $projectDir . "media/" . $fileName; 
  $thumbFile = "../" . $projectDir . "media/thumbs/" . $fileName;

  $targetFile = preg_replace("#-novi-video.jpg#", ".mp4", $targetFile);

  if (file_exists($targetFile)) {    
    unlink($targetFile); 
  }  

  if (file_exists($thumbFile)) { 
    unlink($thumbFile);  
  } 

  echo json_encode(array("result" => true)); 
} 