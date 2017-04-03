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

if (isset($_POST["dir"]) && isset($_POST["fileName"])){
  $dir = $_POST["dir"];
  $fileName = $_POST["fileName"];
  $isVideo = strpos($fileName, '-novi-video.jpg') !== false;
  $i = 0;

  if ($isVideo){
    if (!is_dir("../" . $dir . "video/")){
      mkdir("../" . $dir . "video/");
    }
    $fileName = preg_replace("#-novi-video.jpg#", ".mp4", $fileName);
    $sourcePath = "../" . $dir . "media/" . $fileName;
    $targetPath = "../" . $dir . "video/" . $fileName;
    $relativePath = "video/" . $fileName;
    copy($sourcePath, $targetPath);

    echo json_encode(array('path' => $relativePath));
  }else{

      $ext = pathinfo($fileName, PATHINFO_EXTENSION );
      $sourcePath = "../" . $dir . "media/" . $fileName;
      $targetPath = "../" . $dir . "images/" . $fileName;
      $relativePath = "images/" . $fileName;
      if (!is_dir("../" . $dir . "images/")){
        mkdir("../" . $dir . "images/");
      }

      // Copy source image to images/ folder with specified size
      list($sourceWidth, $sourceHeight) = getimagesize($sourcePath);
      switch ($ext) {
        case 'jpeg':
        case 'jpg':
          $sourceImage = imagecreatefromjpeg($sourcePath);
          break;

        case 'png':
          $sourceImage = imagecreatefrompng($sourcePath);
          break;

        case 'gif':
          $sourceImage = imagecreatefromgif($sourcePath);
          break;
      }

      $targetImage = imagecreatetruecolor($sourceWidth, $sourceHeight);
      imagealphablending( $targetImage, false );
      imagesavealpha( $targetImage, true );

      imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $sourceWidth, $sourceHeight, $sourceWidth, $sourceHeight);

      if (file_exists($targetPath)){
        ob_start();
        switch ($ext) {
          case 'jpg':
          case 'jpeg':
            imagejpeg($targetImage);
            break;

          case 'png':
            imagepng($targetImage);
            break;

          case 'gif':
            imagegif($targetImage);
            break;
        }
        $baseImage = ob_get_clean();

        if (md5_file($targetPath) == md5($baseImage)){
          echo json_encode(array('path' => $relativePath, 'width' => $sourceWidth, 'height' => $sourceHeight));
          exit();
        } else {
          while(file_exists($targetPath)){
            $targetPath = "../" . $dir . "images/" . $i . "-" . $fileName;
            $relativePath = "images/" . $i++ . "-" . $fileName;
          }
        }
      }

      switch ($ext) {
        case 'jpg':
        case 'jpeg':
          imagejpeg($targetImage, $targetPath);
          break;

        case 'png':
          imagepng($targetImage, $targetPath);
          break;

        case 'gif':
          imagegif($targetImage, $targetPath);
          break;
      }

      echo json_encode(array('path' => $relativePath, 'width' => $sourceWidth, 'height' => $sourceHeight));
    }
}