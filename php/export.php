<?php
    if ( isset($_POST['dir'])) {
        $project_dir = $_POST['dir'];
        $sourceUrl = "../" . $project_dir;
        $destination = '../temp/' . basename($project_dir) . '.zip';

        if (!file_exists("../temp/")) {
          mkdir("../temp/");  
        }

        if (file_exists($destination)){
          unlink($destination);
        }

        zip($sourceUrl, $destination);
    }


    function zip($source, $destination)
    {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZipArchive::CREATE|ZipArchive::OVERWRITE)) {  
            return false;
        } 

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source))
        {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file)
            {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
                    continue;

                $file = str_replace('\\', '/', realpath($file));

                if (is_dir($file))
                {
                    $dirName = str_replace($source . '/', '', $file . '/');
                    if (!preg_match('/elements\//', $dirName) && !preg_match('/media\//', $dirName) && !preg_match('/preview\//', $dirName)){
                        $zip->addEmptyDir($dirName);
                    }

                }
                else if (is_file($file))
                {
                    $relativeFile = str_replace($source . '/', '', $file);
                    if (!preg_match('/^[^\/]*\..*/', $relativeFile)){
                        if (!preg_match('/elements\//', $relativeFile) && !preg_match('/media\//', $relativeFile) && !preg_match('/preview\//', $relativeFile)){
                            $zip->addFromString($relativeFile, file_get_contents($file));
                        }
                    }else if (preg_match('/^[^\/]*\.(html)$/', $relativeFile)){
                      $zip->addFromString($relativeFile, file_get_contents($file));
                    }
                }
            }
        }
        else if (is_file($source))
        {
            $zip->addFromString(basename($source), file_get_contents($source));
        } 

        $zip->close();

        echo json_encode(array( "download_file" => "temp/" . basename($destination)));
        exit;
    }

