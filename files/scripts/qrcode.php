<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
$data = file_get_contents( "php://input" );
$dataArray = array_filter(json_decode($data, true));

if(count($dataArray > 0)){
    include('../../../phpqrcode/qrlib.php');

    function tempdir($dir = null, $prefix = 'tmp_', $mode = 0777, $maxAttempts = 1000)
    {
        $attempts = 0;
        do
        {
            $path = $prefix.mt_rand(100000, mt_getrandmax()).'/';
        } while (
            !mkdir($path, $mode) &&
            $attempts++ < $maxAttempts
        );
        return $path;
    }

    $tmpDir = tempdir();
    $mode = 0777;
    foreach($dataArray as $itemType){
        $thisType = $itemType[0];
        $thisPath = $tmpDir.$thisType.'/';
        mkdir($thisPath, $mode);
        $itemArray = $itemType[1];
        $i = 0;
        foreach($itemArray as $item){
            $thisJstart = $item['jumpStart'];
            $title = $item['title'];
            $isn = '';
            if(($thisType == 'Books') && (isset($item['isbn']))){
                $isn = ' '.$item['isbn'].' ';
            }
            else if(($thisType == 'Journals') && (isset($item['issn']))){
                $isn = ' '.$item['issn'].' ';
            }
            $cleanTitle = str_replace(array('\\','/',':','*','?','"','<','>','|'),' ',$title);
            $thisFilename = $thisPath.$cleanTitle.$isn.'.png';
            QRcode::png($thisJstart, $thisFilename);
            $i++;
        }
    }

    class FlxZipArchive extends ZipArchive 
    {
     public function addDir($location, $name) 
     {
           $this->addEmptyDir($name);
           $this->addDirDo($location, $name);
     } 
     private function addDirDo($location, $name) 
     {
        $name .= '/';
        $location .= '/';
        $dir = opendir ($location);
        while ($file = readdir($dir))
        {
            if ($file == '.' || $file == '..') continue;
            $do = (filetype( $location . $file) == 'dir') ? 'addDir' : 'addFile';
            $this->$do($location . $file, $name . $file);
        }
     } 
    }

    $zip_file_name = 'archive_'.mt_rand(100000, mt_getrandmax()).'.zip';
    $za = new FlxZipArchive;
    $res = $za->open($zip_file_name, ZipArchive::CREATE);
    if($res === TRUE) 
    {
        $za->addDir($tmpDir, basename('QR Codes'));
        $za->close();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"".$zip_file_name."\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".filesize($zip_file_name));
        ob_end_flush();
        readfile($zip_file_name);
        unlink($zip_file_name);
    }
    else{
    echo 'zip_error';
    }

    function rrmdir($dir) { 
        if (is_dir($dir)) { 
          $objects = scandir($dir);
          foreach ($objects as $object) { 
            if ($object != "." && $object != "..") { 
              if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                rrmdir($dir. DIRECTORY_SEPARATOR .$object);
              else
                unlink($dir. DIRECTORY_SEPARATOR .$object); 
            } 
          }
          $attempts = 0;
          do
          {
            $attempts++;
          } while (
              !rmdir($dir) &&
              $attempts++ < 100
          );
        } 
    }
    
    $tmpDir = str_replace('/', '', $tmpDir);
    rrmdir($tmpDir);
    clearstatcache(); 
    closedir($tmpDir);
    rmdir($tmpDir);
}

?>