<?php
$data = file_get_contents( "php://input" );
$dataArray = array_filter(json_decode($data, true));

$zip = new ZipArchive;
$tmp_file = 'zip.zip';
if ($zip->open($tmp_file,  ZipArchive::CREATE)) {
    foreach($dataArray as $data){
        $thisFile = $data['url'];
        $zip->addFile('../../../ovidtools/images/toolkit/'.$thisFile, $thisFile);
    }
    $zip->close();
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"".$tmp_file."\"");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".filesize($tmp_file));
    ob_end_flush();
    readfile($tmp_file);
    unlink($tmp_file);
} else {
    echo 'Failed!';
}
?>