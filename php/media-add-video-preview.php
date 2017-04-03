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

function base64_to_jpeg($base64_string, $output_file) {
 $ifp = fopen($output_file, "wb");

 $data = explode(',', $base64_string);

 fwrite($ifp, base64_decode($data[1]));
 fclose($ifp);

 return $output_file;
}


if (isset($_POST["previews"]) && isset($_POST["dir"])){
  $previews = json_decode($_POST["previews"], true);
  $projectDir = $_POST["dir"];
  $mediaDir = "media/"; 
  $thumbsDir = "media/thumbs/";
  $result = array();

  if (!is_dir("../" . $projectDir . $mediaDir)){
    mkdir("../" . $projectDir . $mediaDir);
  }

  if (!is_dir("../" . $projectDir . $thumbsDir)){
    mkdir("../" . $projectDir . $thumbsDir);  
  }

  foreach ($previews as $value){
    base64_to_jpeg($value["imageString"], "../" . $projectDir . $thumbsDir . $value["name"] . "-novi-video.jpg");

    $thumb = resizeImage("../" . $projectDir . $thumbsDir . $value["name"] . "-novi-video.jpg", 240, 70);
    imagejpeg($thumb, "../" . $projectDir . $thumbsDir . $value["name"] . "-novi-video.jpg");
    array_push($result, $value["name"] . "-novi-video.jpg");
    imagedestroy($thumb);
  }


 echo json_encode($result);
} 