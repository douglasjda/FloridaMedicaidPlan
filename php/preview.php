<?php

function xcopy($source, $dest, $permissions = 0755)
{
    if (is_dir($source) && (strpos($source, 'preview') !== false || strpos($source, 'media') !== false || strpos($source, 'elements') !== false) ){
        return;
    }
    // Check for symlinks
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }

    // Simple copy for a file
    if (is_file($source)) {
        return copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($dest)) {
        mkdir($dest, $permissions);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        xcopy("$source/$entry", "$dest/$entry", $permissions);
    }

    // Clean up
    $dir->close();
    return true;
}

if ( isset($_POST['dir'])) {
    $project_dir = $_POST['dir'];
    $sourceUrl = "../" . $project_dir;
    $destinationUrl = $sourceUrl . "/preview";

    if (!file_exists($destinationUrl)) {
      mkdir($destinationUrl);
    }

    if (xcopy($sourceUrl, $destinationUrl)){
        echo "success";
    }else{
        echo "error";
    }
}









