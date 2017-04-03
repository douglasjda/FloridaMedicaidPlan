<?php

function deleteFolder($path)
{
    if (is_dir($path) === true)
    {
        $files = array_diff(scandir($path), array('.', '..'));

        foreach ($files as $file)
        {
            deleteFolder(realpath($path) . '/' . $file);
        }

        return rmdir($path);
    }

    else if (is_file($path) === true)
    {
        return unlink($path);
    }

    return false;
}

if ( isset($_POST['dir'])) {
    $project_dir = $_POST['dir'];
    $destinationUrl = "../" . $project_dir . "/preview";

    if (file_exists($destinationUrl)) {
      if (deleteFolder($destinationUrl)){
        echo "success";
      }else{
        echo "error";
      }
    }
}









