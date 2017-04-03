<?php
function resizeImage($filename, $max_width, $max_height)
{
  list($orig_width, $orig_height) = getimagesize($filename);

  $width = $orig_width;
  $height = $orig_height;

  # taller
  if ($height > $max_height) {
      $width = ($max_height / $height) * $width;
      $height = $max_height;
  }

  # wider
  if ($width > $max_width) {
      $height = ($max_width / $width) * $height;
      $width = $max_width;
  }

  $image_p = imagecreatetruecolor($width, $height);

  $ext = pathinfo($filename, PATHINFO_EXTENSION );

  switch ($ext) {
    case 'jpeg':
    case 'jpg':
      $image = imagecreatefromjpeg($filename);
      break;

    case 'png':
      imagealphablending( $image_p, false );
      imagesavealpha( $image_p, true );  
      $image = imagecreatefrompng($filename);
      break;  

    case 'gif':
      imagealphablending( $image_p, false );
      imagesavealpha( $image_p, true );   
      $image = imagecreatefromgif($filename);
      break;
  }

  

  imagecopyresampled($image_p, $image, 0, 0, 0, 0, 
                                   $width, $height, $orig_width, $orig_height);

  return $image_p;
} 


if (isset($_FILES) && !empty($_FILES) && isset($_POST["dir"])){
  $projectDir = $_POST["dir"];
  $mediaDir = "media/"; 
  $thumbsDir = "media/thumbs/"; 
  $extensions = array("jpeg", "jpg", "png", "gif");
  $video_extensions = array("mp4", "webm", "avi", "ogg");

  if (!is_dir("../" . $projectDir . $mediaDir)){
    mkdir("../" . $projectDir . $mediaDir);
  }

  if (!is_dir("../" . $projectDir . $thumbsDir)){
    mkdir("../" . $projectDir . $thumbsDir);  
  }

  foreach ($_FILES["files"]["error"] as $key => $error) {
    if ($error == UPLOAD_ERR_OK) {
      $name = $_FILES["files"]["name"][$key];      
      $ext = pathinfo($name, PATHINFO_EXTENSION ); 
      if (in_array($ext, $extensions) || in_array($ext, $video_extensions) ){
        move_uploaded_file( $_FILES["files"]["tmp_name"][$key], "../" . $projectDir . $mediaDir . $_FILES['files']['name'][$key]);      
      }       
    }
  }

  $origMedia = scandir("../" . $projectDir . $mediaDir);
  $thumbMedia = scandir("../" . $projectDir . $thumbsDir); 

  foreach ($origMedia as $key => $filename) {       
    $ext = pathinfo($filename, PATHINFO_EXTENSION );

    if (!in_array($filename, $thumbMedia)){
        if (in_array($ext, $extensions)){
            $thumb = resizeImage("../" . $projectDir . $mediaDir . $filename, 240, 70);

            switch ($ext) {
              case 'jpg':
              case 'jpeg':
                imagejpeg($thumb, "../" . $projectDir . $thumbsDir . $filename);
                break;

              case 'png':
                imagepng($thumb, "../" . $projectDir . $thumbsDir . $filename);
                break;

              case 'gif':
                imagegif($thumb, "../" . $projectDir . $thumbsDir . $filename);
                break;
            }
               
            imagedestroy($thumb);
        }else if (in_array($ext, $video_extensions)){

        }
    }
  }
  
  $files = array();   
  foreach ($_FILES["files"]["error"] as $key => $error) {
    if ($error == UPLOAD_ERR_OK) {
      $name = $_FILES["files"]["name"][$key];
      $ext = pathinfo($name, PATHINFO_EXTENSION ); 
      if (in_array($ext, $extensions) || in_array($ext, $video_extensions)){
        array_push($files, $name);   
      }
    }
  }

  echo json_encode($files);   
} 