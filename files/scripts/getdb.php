<?php
$dbcodes = file_get_contents('http://demo.ovid.com/jumpstarts/get_dblist.cgi');
$dbCodesArray = explode('</tr>', $dbcodes);
foreach($dbCodesArray as $row){
    $row = str_replace(array("\n","\r"), '', $row);
    $thisArray = explode('</td><td>',$row);
    if(strip_tags($thisArray[0]) == 'biop12'){
        $thisTitle = str_replace('</td>', '', $thisArray[1]);
        if (preg_match('/<(.*?)>/', $thisTitle, $match) == 1) {
            echo $match[1];
        }
    }
}
?>