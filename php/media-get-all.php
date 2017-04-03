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

if (isset($_POST["dir"])){
  $projectDir = $_POST["dir"];
  $mediaDir = "media/"; 
  $thumbsDir = "media/thumbs/"; 
  $extensions = array("jpeg", "jpg", "png", "gif");
  $video_extensions = array("mp4", "webm", "avi", "ogg");

  $video_previews = array();

  if (!is_dir("../" . $projectDir . $mediaDir)){
    mkdir("../" . $projectDir . $mediaDir);
  }

  if (!is_dir("../" . $projectDir . $thumbsDir)){
    mkdir("../" . $projectDir . $thumbsDir);  
  }

  $origMedia = scandir("../" . $projectDir . $mediaDir);
  $thumbMedia = scandir("../" . $projectDir . $thumbsDir);

  foreach ($origMedia as $key => $filename) {       
    $ext = pathinfo($filename, PATHINFO_EXTENSION );

    if (in_array($ext, $extensions)){
      if (!in_array($filename, $thumbMedia)){ 
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
      }
    }else if (in_array($ext, $video_extensions)){
        if (!in_array(preg_replace("#\.[^/.]+#", "-novi-video.jpg", $filename), $thumbMedia)){
            array_push($video_previews, $filename);
        }
    }
  }

  $thumbMedia = scandir("../" . $projectDir . $thumbsDir); 
  $files = array();

  foreach ($thumbMedia as $key => $filename) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION ); 
    if (in_array($ext, $extensions)){
      array_push($files, $filename);
    }
  }

  echo json_encode(array("files" => $files, "noPreviewVideos" => $video_previews));
}