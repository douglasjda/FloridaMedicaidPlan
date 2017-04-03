 <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && !empty($_POST['action'])) {

        switch ($_POST['action']){
            case 'loadProjectList':
                loadProjectList();
                break;
            case 'loadProjectByName':
                loadProjectByName($_POST['name']);
                break;
            case 'SaveProject':
                saveProject($_POST['project'], $_POST['mode']);
                break;
            case 'getIncludedFilesContents':
                getIncludedFilesContents($_POST['files'], $_POST['dir']);
                break;
        }
    }

    function loadProjectList(){
        $projects_dir = "../projects/";
        $projects = glob($projects_dir . "*");
        $projectList = array();

        for ($i = 0; $i < count($projects); $i++){
            $file = $projects[$i] . '/project.json';
            if (file_exists($file)){
                $project = file_get_contents($file);
                array_push($projectList, str_replace("../projects/", "", $projects[$i]));
            }
        }
        echo json_encode($projectList);
    }


    function loadProjectByName($name){
         $file = "../projects/" . $name . "/project.json";
         if (file_exists($file)){
              $project = json_decode(file_get_contents($file), true);
              if (isset($project["pages"])){
                for ($i = 0; $i < count($project["pages"]); $i++){
                    $htmlPath = "../projects/" . $name . "/" .$project["pages"][$i]["path"];
                    if (file_exists($htmlPath)){
                        $project["pages"][$i]["html"] = file_get_contents($htmlPath);
                    }
                }
              }
            echo json_encode($project);

         }else{
            echo "-1";
         }
    }

    function saveProject($project, $mode){
        $projectObj = json_decode($project, true);
        if (isset($projectObj) && $projectObj != "null"){
            $dir = "../" . $projectObj["dir"];
            if (isset($mode) && $mode != "null" && $mode == 0){
                $dir .= "preview/";
            }

            if (!file_exists($dir)){
                mkdir($dir, 0777);
            }

            $files = scandir($dir);
            $newFiles = array();

            if (isset($projectObj["pages"])){
                for ($i = 0; $i < count($projectObj["pages"]); $i++){
                    if (isset($projectObj["pages"][$i]["path"]) && $projectObj["pages"][$i]["path"] === "index.html"){
                        $htmlPath = $dir . "/" . $projectObj["pages"][$i]["path"];
                        $fileName = $projectObj["pages"][$i]["path"];
                    }else{
                        $title = preg_replace("/\s+/", "-", strtolower(preg_replace('/[\?|\||\\|\/|\:|\*|\>|\<|\.|\"]/', "", $projectObj["pages"][$i]["title"])));
                        $newFileName = $title . ".html";
                        if (in_array($newFileName, $newFiles)){
                            $j = 1;
                            while (in_array($title . "-" . $j . ".html", $newFiles)){
                                $j++;
                            }
                            $newFileName = $title . "-" . $j . ".html";
                        }

                        array_push($newFiles, $newFileName);
                        $projectObj["pages"][$i]["path"] = $newFileName;
                        $htmlPath = $dir . "/" . $newFileName;
                        $fileName = $newFileName;
                    }

                    if(($key = array_search($fileName, $files)) !== false) {
                        unset($files[$key]);
                    }


                    if (isset($projectObj["pages"][$i]["html"])){

                        $fp = fopen($htmlPath,"wb");
                        fwrite($fp,$projectObj["pages"][$i]["html"]);
                        fclose($fp);
                    }
                    unset($projectObj["pages"][$i]["html"]);
                }
            }

            foreach ($files as $key => $value){
                if (preg_match("/[^\.]\..*$/", $value) && $value != 'project.json'){
                    unlink($dir . $value);
                }
            }

            if (isset($projectObj["files"])){
                foreach ($projectObj["files"] as $key => $value){
                    if (file_exists($dir . "/" . $key)){
                        $fp = fopen($dir . "/" . $key,"wb");
                        fwrite($fp,$value);
                        fclose($fp);
                    }
                    unset($projectObj["files"][$key]);
                }
            }

            $file = $dir . "project.json";
            $projectStr = json_encode($projectObj);

            $fp = fopen($file,"wb");
            fwrite($fp, $projectStr);
            fclose($fp);
            echo $projectStr;
        } else {

        }
    }

    function getIncludedFilesContents($files, $dir){
    	$newObj["files"] = json_decode($files, true);

		// Get all files
    	foreach ($newObj["files"] as $key => $value){
			if (strpos($key, $dir) === false){  
				if (file_exists("../" . $dir . $key)){
					$newObj["files"][$key] = file_get_contents("../" . $dir . $key); 	  
				}else{
					$newObj["files"][$key] = "empty";
				}
    		}else{     
    			if (file_exists($key)){
					$newObj["files"][$key] = file_get_contents($key); 	  
				}else{
					$newObj["files"][$key] = "empty";
				}
    				      
    		}    		 
        } 
        echo json_encode($newObj);  
    }

 