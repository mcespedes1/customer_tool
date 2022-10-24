<?php
error_reporting(0);

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

$query = $_GET['params'];
//$query = 'group=csublibr&lang=en';
$paramArray = explode('&', $query);

foreach ($paramArray as $param){
    $thisArray = explode('=', $param);
    if($thisArray[0] == 'group'){
        $ovidGroup = $thisArray[1];
    }
    elseif ($thisArray[0] == 'orders'){
        $orderString = $thisArray[1];
    }
    elseif($thisArray[0] == 'athens'){
        $athensString = 'y';
    }
    elseif($thisArray[0] == 'shib'){
        $shibString = trim($thisArray[1]);
    }
    elseif($thisArray[0] == 'da'){
        $daVals = $thisArray[1];
        $daValsArray = explode(',', $daVals);
        $daWhereString = "'".implode("','", $daValsArray)."'";
        include('connect_lpcms.php');
        $sql = 'SELECT `url`,`pissn`,`oissn` FROM `ejp` WHERE `platform` IN ('.$daWhereString.');';
        if($result = mysqli_query($conn, $sql)){
            $ejpArray = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $ejpArray[] = $row;
            }
        }
        mysqli_close($conn);
        $ejpString = 'y';
    }
    elseif($thisArray[0] == 'ejp'){
        $ejpString = 'y';
        include('connect_lpcms.php');
        $sql = 'SELECT `url`,`pissn`,`oissn` FROM `ejp`';
        if($result = mysqli_query($conn, $sql)){
            $ejpArray = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $ejpArray[] = $row;
            }
        }
        mysqli_close($conn);
    }
    elseif($thisArray[0] == 'vb'){
        $vbString = trim($thisArray[1]);
    }
    elseif($thisArray[0] == 'jbi'){
        $jbiString = urldecode(trim($thisArray[1]));
    }
    elseif($thisArray[0] == 'lang'){
        $lang = trim($thisArray[1]);
    }
    elseif($thisArray[0] == 'neurhwire'){
        $neurhwire = 'y';
    }
    elseif($thisArray[0] == 'neurhwireorderno'){
        $neurhwireorderno = trim($thisArray[1]);
    }
}

if(isset($orderString)){
    $orderArray = explode(',', $orderString);
    $type = 'limitedOrders';
}
else {
    $type = 'allOrders';
}

$url = 'https://portal.ovid.com/AuthService/api/ServiceUser';
//$url = 'https://appdevdemo.ovid.com:8443/AuthService/api/ServiceUser';
//  Initiate curl
$ch = curl_init();
// Disable SSL verification
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// Will return the response, if false it print the response
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Set the url
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_POSTFIELDS,     '{ "username": "CMSLandingPages", "password": "d6787b9c88d9fcbd3f14268348ef78b2" }' );
curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
// Execute
$result=curl_exec($ch);
// Closing
curl_close($ch);

$response = json_decode($result, true);


if(array_key_exists('error', $response)){
    if(isset($response['error']['message'])){
        $message = $response['error']['message'];
    }
    else {
        $message = 'Undefined error';
    }
    exit('There was a problem connecting to the entitlements API: '.$message);
}

$tok = $response['access_token'];
$authorization = "Authorization: Bearer ".$tok;

$url = 'https://portal.ovid.com/Entitlements/api/Group/'.$ovidGroup;
//$url = 'https://appdevdemo.ovid.com:8443/Entitlements/api/Group/'.$ovidGroup.'?AdditionalProductClasses=telecom,services';
//  Initiate curl
$ch = curl_init();
// Disable SSL verification
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// Will return the response, if false it print the response
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Set the url
curl_setopt($ch, CURLOPT_URL,$url);

curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
// Execute
$result=curl_exec($ch);
// Closing
curl_close($ch);

$response = json_decode($result, true);
if(array_key_exists('error', $response)){
    if(isset($response['error']['message'])){
        $message = $response['error']['message'];
    }
    else {
        $message = 'Undefined error';
    }
    exit('There was a problem connecting to the entitlements API: '.$message);
}

function unique_multidim_array($array, $key) { 
    $temp_array = array(); 
    $i = 0; 
    $key_array = array(); 
    
    foreach($array as $val) { 
        if ((!in_array($val[$key], $key_array)) && ($val[$key] !== '')) { 
            $key_array[$i] = $val[$key]; 
            $temp_array[$i] = $val; 
        } 
        $i++; 
    } 
    return $temp_array; 
} 

$classes = array_unique(array_column($response, 'productClass'));

$orderedClasses = array();
foreach($classes as $class){
    if($class = 'journal'){
        $orderedClasses[0] = $class;
    }
    elseif($class = 'book'){
        $orderedClasses[1] = $class;
    }
    elseif($class = 'database'){
        $orderedClasses[2] = $class;
    }
}

if(isset($vbString)){
    $orderedClasses[3] = 'vb';
}

if(isset($jbiString)){
    $orderedClasses[4] = 'jbi';
}

foreach($orderedClasses as $class){
    if($class == 'vb'){
        $wksheets[$class] = $vbString;
    }
    else if($class == 'jbi'){
        $wksheets[$class] = $jbiString;
    }
    else {
        $wksheets[$class] = array();
    }
}

foreach($response as $item){
    if(isset($orderArray)){
        $thisOrder = trim($item['orderNumber']);
        if(in_array($thisOrder, $orderArray) && ($thisOrder !== '')){
            $thisClass = $item['productClass'];
            $wksheets[$thisClass][] = $item;
        }
    }
    else {
        $thisClass = $item['productClass'];
        $wksheets[$thisClass][] = $item;
    }
}

unset($response);

$finalClasses = array();
foreach($wksheets as $key=>$value){
    if(($key !== 'vb') && ($key !== 'jbi')){
        $thisArray = array_filter($wksheets[$key]);
        if(empty($thisArray)){
            unset($wksheets[$key]);
        }
        else {
            $finalClasses[] = $key;
            $wksheets[$key] = $thisArray;
        }
    }
    else {
        $finalClasses[] = $key; 
    }
}

$ecArray = array();

$c = 0;
foreach($wksheets as $key=>$value){
    $thisSheetName = $key;
      
    if($key == 'book'){
        $jstartColumnArray = array('E');
        if((isset($athensString)) && (isset($shibString))){
            $order = array('title', 'edition', 'isbn', 'orderNumber', 'jumpStart', 'athensJumpstart', 'shibJumpstart', 'publisher');
            $booksHeaderArray = array(array('headerName' => 'Title'), array('headerName' => 'Edition'), array('headerName' => 'ISBN'), array('headerName' => 'Order No.'), array('headerName' => 'Jumpstart'), array('headerName' => 'Athens Jumpstart'), array('headerName' => 'Shibboleth Jumpstart'), array('headerName' => 'Publisher'));
        }
        else if(isset($athensString)){
            $order = array('title', 'edition', 'isbn', 'orderNumber', 'jumpStart', 'athensJumpstart', 'publisher');
            $booksHeaderArray = array(array('headerName' => 'Title'), array('headerName' => 'Edition'), array('headerName' => 'ISBN'), array('headerName' => 'Order No.'), array('headerName' => 'Jumpstart'), array('headerName' => 'Athens Jumpstart'), array('headerName' => 'Publisher'));
        }
        else if(isset($shibString)){
            $order = array('title', 'edition', 'isbn', 'orderNumber', 'jumpStart', 'shibJumpstart', 'publisher');
            $booksHeaderArray = array(array('headerName' => 'Title'), array('headerName' => 'Edition'), array('headerName' => 'ISBN'), array('headerName' => 'Order No.'), array('headerName' => 'Jumpstart'), array('headerName' => 'Shibboleth Jumpstart'), array('headerName' => 'Publisher'));
        }
        else {
            $order = array('title', 'edition', 'isbn', 'orderNumber', 'jumpStart', 'publisher');
            $booksHeaderArray = array(array('headerName' => 'Title'), array('headerName' => 'Edition'), array('headerName' => 'ISBN'), array('headerName' => 'Order No.'), array('headerName' => 'Jumpstart'), array('headerName' => 'Publisher'));
        }
        $sheetTitle = 'Books';
        if(isset($athensString)){
            $highestColumn = 'G';
            $athensJstartColumn = 'F';
        }
        else {
            $highestColumn = 'F';
        }
        $centreColumns = array('B', 'C', 'D');
        $jstartColumn = 'E';
        $i = 0;
        foreach($wksheets[$key] as $item){
            if(isset($item['title'])){
                $tempArray = array();
                foreach ($item as $k=>$v){
                    if(in_array($k, $order)){
                        if($k == 'edition'){
                            $v = str_replace('_Edition', '', $v);
                        }                       
                        elseif($k == 'jumpStart'){
                            if(isset($athensString)){
                                $thisJstart = $v;
                                $thisAthensJstart = str_replace('http://ovidsp.ovid.com/ovidweb.cgi?', 'http://ovidsp.ovid.com/athens/ovidweb.cgi?', $thisJstart);
                                $tempArray['athensJumpstart'] = '<a href="'.$thisAthensJstart.'">'.$thisAthensJstart.'</a>';
                                $jstartColumn++;
                                $bookJstartColumnArray[] = $jstartColumn;
                            }
                            if(isset($shibString)){
                                $thisJstart = $v;
                                $thisParamsArray = explode('?', $thisJstart);
                                $thisParams = $thisParamsArray[1];
                                $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&'.$thisParams;
                                $tempArray['shibJumpstart'] = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
                                $jstartColumn++;
                                $bookJstartColumnArray[] = $jstartColumn;
                            }
                            $v = '<a href="'.$v.'">'.$v.'</a>';
                        }
                        $tempArray[$k] = $v;
                    }
                }
                $ordered_array = array_merge(array_flip($order), $tempArray);
                $wksheets[$key][$i] = $ordered_array;
            }
            else {
                unset($wksheets[$key][$i]); 
            }
        $i++;
        }
        $ecArray[] = 'books';
    }
    elseif ($key == 'journal'){
        $jstartColumnArray = array('D');
        $order = array('title', 'jumpStart', 'startCoverage', 'endCoverage', 'issn', 'orderNumber',   'publisher');
        $journalsHeaderArray = array(array('headerName' => 'Title'), array('headerName' => 'Jumpstart'), array('headerName' => 'Start Date'), array('headerName' => 'End Date'), array('headerName' => 'ISSN'), array('headerName' => 'Order No.'), array('headerName' => 'Publisher'));
        $pos = 2;
        $highestColumn = 'G';
        if((isset($athensString)) && (isset($shibString))){
            $endCoverageIndex = 5;
            $order = array('title', 'jumpStart', 'athensJumpstart', 'shibJumpstart', 'startCoverage', 'endCoverage', 'issn', 'orderNumber',  'publisher');
            $journalsHeaderArray = array(array('headerName' => 'Title'), array('headerName' => 'Jumpstart'), array('headerName' => 'Athens Jumpstart'), array('headerName' => 'Shibboleth Jumpstart'), array('headerName' => 'Start Date'), array('headerName' => 'End Date'), array('headerName' => 'ISSN'), array('headerName' => 'Order No.'), array('headerName' => 'Publisher'));
        }
        else if(isset($athensString)){
            $inserted = array('athensJumpstart');
            array_splice( $order, $pos, 0, $inserted);
            $inserted = array('Athens Jumpstart');
            array_splice( $headerArray, $pos, 0, $inserted);
            $athensJstartColumn = 'E';
            $highestColumn++;
            $pos++;
            $endCoverageIndex = 5;
            $order = array('title', 'jumpStart', 'athensJumpstart', 'startCoverage', 'endCoverage', 'issn', 'orderNumber',  'publisher');
            //$journalsHeaderArray = array('Title', 'Ovid Jumpstart', 'Athens Jumpstart', 'Start Date', 'End Date', 'ISSN', 'Order No.', 'Publisher');
            $journalsHeaderArray = array(array('headerName' => 'Title'), array('headerName' => 'Jumpstart'), array('headerName' => 'Athens Jumpstart'), array('headerName' => 'Start Date'), array('headerName' => 'End Date'), array('headerName' => 'ISSN'), array('headerName' => 'Order No.'), array('headerName' => 'Publisher'));
        }
        else if(isset($shibString)){
            $endCoverageIndex = 4;
            $order = array('title', 'jumpStart', 'shibJumpstart', 'startCoverage', 'endCoverage', 'issn', 'orderNumber',  'publisher');
            //$journalsHeaderArray = array('Title', 'Ovid Jumpstart', 'Shibboleth Jumpstart', 'Start Date', 'End Date', 'ISSN', 'Order No.', 'Publisher');
            $journalsHeaderArray = array(array('headerName' => 'Title'), array('headerName' => 'Jumpstart'), array('headerName' => 'Shibboleth Jumpstart'), array('headerName' => 'Start Date'), array('headerName' => 'End Date'), array('headerName' => 'ISSN'), array('headerName' => 'Order No.'), array('headerName' => 'Publisher'));
        }
        else {
            $endCoverageIndex = 3;
            $order = array('title', 'jumpStart', 'startCoverage', 'endCoverage', 'issn', 'orderNumber', 'publisher');
            //$journalsHeaderArray = array('Title', 'Ovid Jumpstart', 'Start Date', 'End Date', 'ISSN', 'Order No.', 'Publisher');
            $journalsHeaderArray = array(array('headerName' => 'Title'), array('headerName' => 'Jumpstart'), array('headerName' => 'Start Date'), array('headerName' => 'End Date'), array('headerName' => 'ISSN'), array('headerName' => 'Order No.'), array('headerName' => 'Publisher'));
        }

        if(isset($ejpString)){
            $inserted = array('ejpURL');
            array_splice( $order, $pos, 0, $inserted);
            $inserted = array(array('headerName' => 'Dual Access URL'));
            array_splice( $journalsHeaderArray, $pos, 0, $inserted);
            $highestColumn++;
            $endCoverageIndex++;
        }

        $sheetTitle = 'Journals';

        $centreColumns = array('B', 'C');
        $nextCentreColumn = 'E';
        $jstartColumn = 'D';
        $i = 0;
        foreach($wksheets[$key] as $item){
            if(isset($item['title'])){
                $tempArray = array();
                foreach ($item as $k=>$v){
                    if(in_array($k, $order)){
                        if($k == 'issn'){
                            $v = substr_replace($v, '-', 4, 0);
                            if(isset($ejpArray)){
                                $ejpKey = FALSE;
                            
                                $ejpKey = array_search($v, array_column($ejpArray, 'pissn'));
                                if($ejpKey === FALSE){
                                    $ejpKey = array_search($v, array_column($ejpArray, 'oissn'));
                                }
                                
                                if($ejpKey === FALSE){
                                    $thisTitle = $item['title'];
                                    if($thisTitle == 'Circulation: Arrhythmia and Electrophysiology'){
                                        $ejpKey = 42;
                                    }
                                    else if($thisTitle == 'Circulation: Arrhythmia and Electrophysiology'){
                                        $ejpKey = 44;
                                    }
                                    else if($thisTitle == 'Circulation: Cardiovascular Interventions'){
                                        $ejpKey = 45;
                                    }
                                    else if($thisTitle == 'Circulation: Cardiovascular Imaging'){
                                        $ejpKey = 44;
                                    }
                                    else if($thisTitle == 'JBJS Case Connector'){
                                        $ejpKey = 158;
                                    }
                                    else if($thisTitle == 'JBJS Essential Surgical Techniques'){
                                        $ejpKey = 159;
                                    }
                                    else if($thisTitle == 'JBJS Open Access'){
                                        $ejpKey = 161;
                                    }
                                    else if($thisTitle == 'JBJS Reviews'){
                                        $ejpKey = 162;
                                    }
                                    else if($thisTitle == 'JCO Clinical Cancer Informatics'){
                                        $ejpKey = 701;
                                    }
                                    else if($thisTitle == 'JCO Global Oncology'){
                                        $ejpKey = 703;
                                    }
                                    else if($thisTitle == 'JCO Precision Oncology'){
                                        $ejpKey = 702;
                                    }
                                }

                                if($ejpKey === FALSE){
                                    $url = '';
                                }
                                else {
                                    $url = trim($ejpArray[$ejpKey]['url']);
                                    $url = str_replace("na.","",$url);
                                }
                                $tempArray['ejpURL'] = '<a href="'.$url.'">'.$url.'</a>';
                            }
                        }
                        else if($k == 'startCoverage'){
                            $v = substr_replace($v, '-', 4, 0);
                        }
                        else if($k == 'endCoverage'){
                            if($v == '999999'){
                                $v = 'Current';
                            }
                            else {
                                $v = substr_replace($v, '-', 4, 0);
                            }
                        }
                        else if($k == 'jumpStart'){
                            if(isset($athensString)){
                                $thisJstart = $v;
                                $thisAthensJstart = str_replace('http://ovidsp.ovid.com/ovidweb.cgi?', 'http://ovidsp.ovid.com/athens/ovidweb.cgi?', $thisJstart);
                                $tempArray['athensJumpstart'] = '<a href="'.$thisAthensJstart.'">'.$thisAthensJstart.'</a>';
                                if($i == 0){
                                    $jstartColumn++;
                                    $jstartColumnArray[] = $jstartColumn;
                                    $nextCentreColumn++;
                                }
                            }
                            if(isset($shibString)){
                                $thisJstart = $v;
                                $thisParamsArray = explode('?', $thisJstart);
                                $thisParams = $thisParamsArray[1];
                                $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&'.$thisParams;
                                $tempArray['shibJumpstart'] = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
                                $jstartColumn++;
                                $jstartColumnArray[] = $jstartColumn;
                            }
                            if(isset($ejpString)){
                                if($i == 0){
                                    $jstartColumn++;
                                    $jstartColumnArray[] = $jstartColumn;
                                    $nextCentreColumn++;
                                }
                            }
                            $v = '<a href="'.$v.'">'.$v.'</a>';
                        }
                        else if($k == 'title'){
                            $v = str_replace('®', '&reg;', $v);
                        }
                    $tempArray[$k] = $v;
                    }
                }
                $centreColumns[] = $nextCentreColumn;
                $nextCentreColumn++;
                $centreColumns[] = $nextCentreColumn;
                $ordered_array = array_merge(array_flip($order), $tempArray);
                $wksheets[$key][$i] = $ordered_array;
                }
                else {
                    unset($wksheets[$key][$i]); 
                }
            $i++;
        }
        if(isset($neurhwire)){            
            if(!isset($neurhwireorderno)){
                $neurhwireorderno = '';
            }
            if((isset($athensString)) && (isset($shibString))){
                $neurHwireArray = array('title' => 'Neurology', 'jumpStart' => '<a href="https://n.neurology.org/">https://n.neurology.org/</a>', 'athensJumpstart' => '', 'shibJumpstart' => '','startCoverage' => '1995-01', 'endCoverage' => 'Current', 'issn' => '0028-3878', 'orderNumber' => $neurhwireorderno, 'publisher' => 'Wolters Kluwer Health _ Lippincott Williams & Wilkins');
                $neurCPHwireArray = array('title' => 'Neurology: Clinical Practice', 'jumpStart' => '<a href="https://cp.neurology.org/">https://cp.neurology.org/</a>', 'athensJumpstart' => '', 'shibJumpstart' => '','startCoverage' => '2011-12', 'endCoverage' => 'Current', 'issn' => '2163-0402', 'orderNumber' => $neurhwireorderno, 'publisher' => 'Wolters Kluwer Health _ Lippincott Williams & Wilkins');
            }
            else if(isset($athensString)){
                $neurHwireArray = array('title' => 'Neurology', 'jumpStart' => '<a href="https://n.neurology.org/">https://n.neurology.org/</a>', 'athensJumpstart' => '', 'startCoverage' => '1995-01', 'endCoverage' => 'Current', 'issn' => '0028-3878', 'orderNumber' => $neurhwireorderno, 'publisher' => 'Wolters Kluwer Health _ Lippincott Williams & Wilkins');
                $neurCPHwireArray = array('title' => 'Neurology: Clinical Practice', 'jumpStart' => '<a href="https://cp.neurology.org/">https://cp.neurology.org/</a>', 'athensJumpstart' => '', 'startCoverage' => '2011-12', 'endCoverage' => 'Current', 'issn' => '2163-0402', 'orderNumber' => $neurhwireorderno, 'publisher' => 'Wolters Kluwer Health _ Lippincott Williams & Wilkins');
            }
            else if(isset($shibString)){
                $neurHwireArray = array('title' => 'Neurology', 'jumpStart' => '<a href="https://n.neurology.org/">https://n.neurology.org/</a>', 'shibJumpstart' => '', 'startCoverage' => '1995-01', 'endCoverage' => 'Current', 'issn' => '0028-3878', 'orderNumber' => $neurhwireorderno, 'publisher' => 'Wolters Kluwer Health _ Lippincott Williams & Wilkins');
                $neurCPHwireArray = array('title' => 'Neurology: Clinical Practice', 'jumpStart' => '<a href="https://cp.neurology.org/">https://cp.neurology.org/</a>', 'shibJumpstart' => '','startCoverage' => '2011-12', 'endCoverage' => 'Current', 'issn' => '2163-0402', 'orderNumber' => $neurhwireorderno, 'publisher' => 'Wolters Kluwer Health _ Lippincott Williams & Wilkins');
            }
            else {
                $neurHwireArray = array('title' => 'Neurology', 'jumpStart' => '<a href="https://n.neurology.org/">https://n.neurology.org/</a>', 'startCoverage' => '1995-01', 'endCoverage' => 'Current', 'issn' => '0028-3878', 'orderNumber' => $neurhwireorderno, 'publisher' => 'Wolters Kluwer Health _ Lippincott Williams & Wilkins');
                $neurCPHwireArray = array('title' => 'Neurology: Clinical Practice', 'jumpStart' => '<a href="https://cp.neurology.org/">https://cp.neurology.org/</a>', 'startCoverage' => '2011-12', 'endCoverage' => 'Current', 'issn' => '2163-0402', 'orderNumber' => $neurhwireorderno, 'publisher' => 'Wolters Kluwer Health _ Lippincott Williams & Wilkins');
            }
            if(isset($ejpString)){
                $neurHwireArray['ejpURL'] = '';
                $neurCPHwireArray['ejpURL'] = '';
            }
            $ordered_array_Neur = array_merge(array_flip($order), $neurHwireArray);
            $ordered_array_NeurCP = array_merge(array_flip($order), $neurCPHwireArray);
            
            $wksheets[$key][$i] = $ordered_array_Neur;
            $i++;
            $wksheets[$key][$i] = $ordered_array_NeurCP;
        }
        $ecArray[] = 'journals';
    }
    elseif ($key == 'database'){
        $jstartColumnArray = array('C');
        if((isset($athensString)) && (isset($shibString))){
            $order = array('title', 'orderNumber', 'jumpStart', 'athensJumpstart', 'shibJumpstart', 'publisher');
            $databasesHeaderArray = array(array('headerName' => 'Title'), array('headerName' => 'Order No.'), array('headerName' => 'Jumpstart'), array('headerName' => 'Athens Jumpstart'), array('headerName' => 'Shibboleth Jumpstart'), array('headerName' => 'Publisher'));
        }
        else if(isset($shibString)){
            $order = array('title', 'orderNumber', 'jumpStart', 'shibJumpstart', 'publisher');
            $databasesHeaderArray = array(array('headerName' => 'Title'), array('headerName' => 'Order No.'), array('headerName' => 'Jumpstart'), array('headerName' => 'Shibboleth Jumpstart'), array('headerName' => 'Publisher'));
        }
        else if(isset($athensString)){
            $order = array('title', 'orderNumber', 'jumpStart', 'athensJumpstart', 'publisher');
            $databasesHeaderArray = array(array('headerName' => 'Title'), array('headerName' => 'Order No.'), array('headerName' => 'Jumpstart'), array('headerName' => 'Athens Jumpstart'), array('headerName' => 'Publisher'));
        }
        else {
            $order = array('title', 'orderNumber', 'jumpStart', 'publisher');
            $databasesHeaderArray = array(array('headerName' => 'Title'), array('headerName' => 'Order No.'), array('headerName' => 'Jumpstart'), array('headerName' => 'Publisher'));
        }
        $sheetTitle = 'Databases';
        if(isset($athensString)){
            $highestColumn = 'E';
            $athensJstartColumn = 'D';
        }
        else {
            $highestColumn = 'D';
        }
        $jstartColumn = 'C';
        $centreColumns = array('B');
        $makeupArray = array();
        $ebmrArray = array();
        $acrArray = array();
        $vdxArray = array();
        $i = 0;
        foreach($wksheets[$key] as $item){
            if($wksheets[$key][$i]['orderNumber'] !== 746896){
                if($wksheets[$key][$i]['shortName'] == 'jbi'){
                    $item['title'] = 'Joanna Briggs Institute EBP Database';
                    $item['publisher'] = 'JBI';
                }
                elseif($item["shortName"] == 'mwic'){
                    $item['title'] = 'Maternity & Infant Care Database (MIDIRS)';
                    $item['publisher'] = 'MIDIRS--Databases';
                }
                elseif($item["shortName"] == 'psye'){
                    $item['title'] = 'APA PsycExtra';
                    $item['publisher'] = 'American Psychological Association APA';
                }
                elseif($item["shortName"] == 'paovft'){
                    $item['title'] = 'APA PsycArticles - database display of journals';
                    $item['publisher'] = 'American Psychological Association APA';
                }
                elseif($item["shortName"] == 'ovrn'){
                    $item['title'] = 'Ovid Nursing Database';
                    $item['publisher'] = 'Wolters Kluwer';
                }
                if((isset($item['title'])) && (($item['title'] == 'Ovid MEDLINE(R)') || ($item['title'] == 'CAB Abstracts') || ($item['title'] == 'APA PsycINFO') || ($item['title'] == 'EMBASE') || (strpos($item['title'] , 'Food Science and Technology Abstracts') !== false) || ($item['title'] == 'INSPEC') || (strpos($item['title'] , 'Emcare') !== false) || ($item['formatShortName'] == 'dscvdb'))){
                    if($item['title'] == 'Ovid MEDLINE(R)'){
                        $singleTitle = 'Ovid MEDLINE(R)';
                        $singleScode = 'medall';
                        $singlePublisher = 'National Library of Medicine--Bethesda';
                    }
                    else if($item['title'] == 'CAB Abstracts'){
                        $singleTitle = 'CAB Abstracts';
                        $singleScode = 'caba';
                        $singlePublisher = 'CAB International';
                    }
                    else if($item['title'] == 'APA PsycINFO'){
                        $singleTitle = 'APA PsycINFO';
                        $singleScode = 'psyh';
                        $singlePublisher = 'American Psychological Association APA';
                    }
                    else if($item['title'] == 'EMBASE'){
                        $singleTitle = 'EMBASE';
                        $singleScode = 'oemezd';
                        $singlePublisher = 'Elsevier Inc';
                    }
                    else if((strpos($item['title'] , 'Food Science and Technology Abstracts') !== false)){
                        $singleTitle = $item['title'];
                        $singleScode = 'fsta';
                        $singlePublisher = 'IFIS--Reading';
                    }
                    else if($item['title'] == 'INSPEC'){
                        $singleTitle = 'INSPEC <1969 to present>';
                        $singleScode = 'insz';
                        $singlePublisher = 'Institution Engineering & Technology IET';
                    }
                    else if((strpos($item['title'] , 'Emcare') !== false)){
                        $singleTitle = $item['title'];
                        $singleScode = 'emcr';
                        $singlePublisher = 'Elsevier Inc';
                    }
                    else if($item['formatShortName'] == 'dscvdb'){
                        $singleTitle = 'Northern Light Life Sciences Conference Abstracts <2010 - present>';
                        $singleScode = 'dscv';
                        $singlePublisher = 'Northern Light SinglePoint LLC';
                    }
                    if(isset($singleScode)){
                        $ecArray[] = $singleScode;
                    }
                    $thisOrder = $wksheets[$key][$i]['orderNumber'];
                    unset($wksheets[$key][$i]);
                    if((isset($athensString)) && (isset($shibString))){
                        $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$singleScode;
                        $thisShibJstartHTML = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
                        $makeupArray[] = array('title' => $singleTitle, 'orderNumber' => $thisOrder, 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$singleScode.'">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$singleScode.'</a>', 'athensJumpstart' => '<a href="http://ovidsp.ovid.com/athens/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$singleScode.'">http://ovidsp.ovid.com/athens/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$singleScode.'</a>', 'shibJumpstart' => $thisShibJstartHTML, 'publisher' => $singlePublisher);
                    }
                    else if(isset($shibString)){
                        $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$singleScode;
                        $thisShibJstartHTML = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
                        $makeupArray[] = array('title' => $singleTitle, 'orderNumber' => $thisOrder, 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$singleScode.'">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$singleScode.'</a>', 'shibJumpstart' => $thisShibJstartHTML, 'publisher' => $singlePublisher);
                    }
                    else if(isset($athensString)){
                        $makeupArray[] = array('title' => $singleTitle, 'orderNumber' => $thisOrder, 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$singleScode.'">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$singleScode.'</a>', 'athensJumpstart' => '<a href="http://ovidsp.ovid.com/athens/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$singleScode.'">http://ovidsp.ovid.com/athens/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$singleScode.'</a>', 'publisher' =>  $singlePublisher);
                    }
                    else {
                        $makeupArray[] = array('title' => $singleTitle, 'orderNumber' => $thisOrder, 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$singleScode.'">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$singleScode.'</a>', 'publisher' => $singlePublisher);
                    }
                }
                else if((isset($item['title'])) && ($item['title'] == 'CAB Abstracts')){
                    $thisOrder = $wksheets[$key][$i]['orderNumber'];
                    unset($wksheets[$key][$i]);
                    if((isset($athensString)) && (isset($shibString))){
                        $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&T=JS&NEWS=n&CSC=Y&PAGE=main&D=caba';
                        $thisShibJstartHTML = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
                        $makeupArray[] = array('title' => 'CAB Abstracts', 'orderNumber' => $thisOrder, 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=caba">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=caba</a>', 'athensJumpstart' => '<a href="http://ovidsp.ovid.com/athens/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=caba">http://ovidsp.ovid.com/athens/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=caba</a>', 'shibJumpstart' => $thisShibJstartHTML, 'publisher' => 'CAB International');
                    }
                    else if(isset($shibString)){
                        $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&T=JS&NEWS=n&CSC=Y&PAGE=main&D=caba';
                        $thisShibJstartHTML = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
                        $makeupArray[] = array('title' => 'CAB Abstracts', 'orderNumber' => $thisOrder, 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=caba">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=caba</a>', 'shibJumpstart' => $thisShibJstartHTML, 'publisher' => 'CAB International');
                    }
                    else if(isset($athensString)){
                        $makeupArray[] = array('title' => 'CAB Abstracts', 'orderNumber' => $thisOrder, 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=caba">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=caba</a>', 'athensJumpstart' => '<a href="http://ovidsp.ovid.com/athens/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=caba">http://ovidsp.ovid.com/athens/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=caba</a>', 'publisher' => 'CAB International');
                    }
                    else {
                        $makeupArray[] = array('title' => 'CAB Abstracts', 'orderNumber' => $thisOrder, 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=caba">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=caba</a>', 'publisher' => 'CAB International');
                    }
                    $ecArray[] = 'caba';
                }
                elseif (($item['formatShortName'] == 'biosis') || ($item['formatShortName'] == 'biopdb') || ($item['formatShortName'] == 'zoordb') || ($item['formatShortName'] == 'biobadb')){
                    if(($item['formatShortName'] == 'biobadb') || ((strpos($item['title'] , 'Biological Abstracts') !== false))){
                        $thisPurchaseKey = 'bioba';
                    }
                    elseif(($item['formatShortName'] == 'biosis') || ($item['formatShortName'] == 'biopdb')){
                        $thisPurchaseKey = 'biosis';
                    }
                    elseif($item['formatShortName'] == 'zoordb'){
                        $thisPurchaseKey = 'zoor';
                    }
                    $thisSName = str_replace('%', '', $item['shortName']);
                    if(!isset($dbCodesArray)){
                        $dbcodes = file_get_contents('http://demo.ovid.com/jumpstarts/get_dblist.cgi');
                        $dbCodesArray = explode('</tr>', $dbcodes);
                    }
                    if(!isset($purchaseArray)){
                        $purchaseArray = array();
                    }
                    foreach($dbCodesArray as $row){
                        $row = str_replace(array("\n","\r"), '', $row);
                        $thisArray = explode('</td><td>',$row);
                        if(strip_tags($thisArray[0]) == $thisSName){
                            $thisTitle = str_replace('</td>', '', $thisArray[1]);
                            if(preg_match('/<(.*?)>/', $thisTitle, $match) == 1) {
                                if(($item['formatShortName'] == 'biosis') || ($item['formatShortName'] == 'biopdb')){
                                    $purchaseArray[$thisPurchaseKey][] = substr($match[1],0 ,4);
                                }
                                else {
                                    $purchaseArray[$thisPurchaseKey][] = substr($match[1], -4);
                                }
                            }
                        }
                    }
                    unset($wksheets[$key][$i]);
                }
                elseif($item['formatShortName'] == 'ebmr'){
                    $ebmrArray[] = $item;
                    unset($wksheets[$key][$i]);
                }
                elseif(($item['shortName'] == 'cpi') || ($item['shortName'] == 'cip')){
                    $acrArray[] = $item;
                    unset($wksheets[$key][$i]);
                }
                elseif(($item['shortName'] == 'vdxc') || ($item['shortName'] == 'vdxdxp') || ($item['shortName'] == 'vdxm')){
                    $vdxArray[] = $item;
                    unset($wksheets[$key][$i]);
                }
                elseif ((!isset($item['title'])) || ($item['title'] == 'Ovid Open Access Database') || ($item['title'] == 'Multi Media Linking') || ($item['title'] == '') || ($item['title'] == 'Journals@Ovid Full Text')){
                    unset($wksheets[$key][$i]);
                }
                else {
                    $ecArray[] = $wksheets[$key][$i]['shortName'];
                    $tempArray = array();
                    foreach ($item as $k=>$v){
                        if(in_array($k, $order)){
                            if($k == 'jumpStart'){
                                if(isset($athensString)){
                                    $thisJstart = $v;
                                    $thisAthensJstart = str_replace('http://ovidsp.ovid.com/ovidweb.cgi?', 'http://ovidsp.ovid.com/athens/ovidweb.cgi?', $thisJstart);
                                    $tempArray['athensJumpstart'] = '<a href="'.$thisAthensJstart.'">'.$thisAthensJstart.'</a>';
                                    $jstartColumn++;
                                    $jstartColumnArray[] = $jstartColumn;
                                }
                                if(isset($shibString)){
                                    $thisJstart = $v;
                                    $thisParamsArray = explode('?', $thisJstart);
                                    $thisParams = $thisParamsArray[1];
                                    $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&'.$thisParams;
                                    $tempArray['shibJumpstart'] = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
                                    $jstartColumn++;
                                    $jstartColumnArray[] = $jstartColumn;
                                }
                                $v = '<a href="'.$v.'">'.$v.'</a>';
                            }
                            $tempArray[$k] = $v;
                        }
                    }
                    $ordered_array = array_merge(array_flip($order), $tempArray);
                    $wksheets[$key][$i] = $ordered_array;
                }
            }
            else {
                unset($wksheets[$key][$i]);
            }
        $i++;
        }

        if(in_array('journal', $finalClasses)){
            
            if((isset($athensString)) && (isset($shibString))){
                $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&T=JS&NEWS=n&CSC=Y&PAGE=main&D=ovft';
                $thisShibJstartHTML = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
                $makeupArray[] = array('title' => 'Journals@Ovid Full Text', 'orderNumber' => '', 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=ovft">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=ovft</a>', 'athensJumpstart' => '<a href="http://ovidsp.ovid.com/athens/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=ovft">http://ovidsp.ovid.com/athens/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=ovft</a>', 'shibJumpstart' => $thisShibJstartHTML, 'publisher' => 'Wolters Kluwer');

                $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&T=JS&NEWS=n&CSC=Y&PAGE=main&D=yrovft';
                $thisShibJstartHTML = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
                $makeupArray[] = array('title' => 'Your Journals@Ovid', 'orderNumber' => '', 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=yrovft">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=yrovft</a>', 'athensJumpstart' => '<a href="http://ovidsp.ovid.com/athens/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=yrovft">http://ovidsp.ovid.com/athens/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=yrovft</a>', 'shibJumpstart' => $thisShibJstartHTML, 'publisher' => 'Wolters Kluwer');
            }
            else if(isset($shibString)){
                $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&T=JS&NEWS=n&CSC=Y&PAGE=main&D=ovft';
                $thisShibJstartHTML = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
                $makeupArray[] = array('title' => 'Journals@Ovid Full Text', 'orderNumber' => '', 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=ovft">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=ovft</a>', 'shibJumpstart' => $thisShibJstartHTML, 'publisher' => 'Wolters Kluwer');

                $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&T=JS&NEWS=n&CSC=Y&PAGE=main&D=yrovft';
                $thisShibJstartHTML = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
                $makeupArray[] = array('title' => 'Your Journals@Ovid', 'orderNumber' => '', 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=yrovft">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=yrovft</a>', 'shibJumpstart' => $thisShibJstartHTML, 'publisher' => 'Wolters Kluwer');
            }
            else if(isset($athensString)){
                $makeupArray[] = array('title' => 'Journals@Ovid Full Text', 'orderNumber' => '', 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=ovft">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=ovft</a>', 'athensJumpstart' => '', 'publisher' => 'Wolters Kluwer');
                $makeupArray[] = array('title' => 'Your Journals@Ovid', 'orderNumber' => '', 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=yrovft">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=yrovft</a>', 'athensJumpstart' => '', 'publisher' => 'Wolters Kluwer');
            }
            else {
                $makeupArray[] = array('title' => 'Journals@Ovid Full Text', 'orderNumber' =>  '', 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=ovft">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=ovft</a>', 'publisher' => 'Wolters Kluwer');
                $makeupArray[] = array('title' => 'Your Journals@Ovid', 'orderNumber' =>  '', 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=yrovft">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=yrovft</a>', 'publisher' => 'Wolters Kluwer');
            }
        }

        
        if(in_array('book', $finalClasses)){
            if((isset($athensString)) && (isset($shibString))){
                $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&T=JS&NEWS=n&CSC=Y&PAGE=main&D=books';
                $thisShibJstartHTML = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
                $makeupArray[] = array('title' => 'Books@Ovid', 'orderNumber' => '', 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=books">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=books</a>', 'athensJumpstart' => '<a href="http://ovidsp.ovid.com/athens/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=books">http://ovidsp.ovid.com/athens/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=books</a>', 'shibJumpstart' => $thisShibJstartHTML, 'publisher' => 'Wolters Kluwer');
            }
            else if(isset($shibString)){
                $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&T=JS&NEWS=n&CSC=Y&PAGE=main&D=books';
                $thisShibJstartHTML = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
                $makeupArray[] = array('title' => 'Books@Ovid', 'orderNumber' => '', 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=books">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=books</a>', 'shibJumpstart' => $thisShibJstartHTML, 'publisher' => 'Wolters Kluwer');
            }
            else if(isset($athensString)){
                $makeupArray[] = array('title' => 'Books@Ovid', 'orderNumber' => '', 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=books">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=books</a>', 'athensJumpstart' => '', 'publisher' => 'Wolters Kluwer');
            }
            else {
                $makeupArray[] = array('title' => 'Books@Ovid', 'orderNumber' =>  '', 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=books">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=books</a>', 'publisher' => 'Wolters Kluwer');
            }
        }

        $makeupArray = unique_multidim_array($makeupArray, 'title');

        foreach ($makeupArray as $item){
            if($item['title'] !== ''){
                $wksheets[$key][$i] = $item;
                $i++;
            }
        }
        $wksheets[$key] = array_values($wksheets[$key]);
        if(isset($purchaseArray)){
            foreach($purchaseArray as $k=>$v){
                $endYear = substr(max($v), -2);
                $startYear = substr(min($v), -2);
                if($k == 'biosis'){
                    $fLetter = 'b';
                    $sLetter = 'o';
                    $purchaseName = 'BIOSIS Previews';
                    $purchasePublisher = 'Clarivate Analytics (US) LLC, (Biosis)';
                }
                elseif($k == 'bioba'){
                    $fLetter = 'b';
                    $sLetter = 'a';
                    $purchaseName = 'Biological Abstracts';
                    $purchasePublisher = 'Clarivate Analytics (US) LLC, (Biosis)';
                }
                elseif($k == 'zoor'){
                    $fLetter = 'z';
                    $sLetter = 'r';
                    $purchaseName = 'Zoological Record';
                    $purchasePublisher = 'Clarivate Analytics (US) LLC, (Biosis)';
                }
                $ecArray[] = $k;
                $thisScode = $fLetter.$endYear.$sLetter.$startYear;
                $thisJstart = "http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=".$thisScode;

                if((isset($athensString)) && (isset($shibString))){
                    $thisAthensJstart = "http://ovidsp.ovid.com/athens/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=".$thisScode;
                    $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$thisScode;
                    $thisShibJstartHTML = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
                    $thisItem = array('title' => $purchaseName, 'orderNumber' => '', 'jumpStart' => '<a href="'.$thisJstart.'">'.$thisJstart.'</a>', 'athensJumpstart' => '<a href="'.$thisAthensJstart.'">'.$thisAthensJstart.'</a>', 'shibJumpstart' => $thisShibJstartHTML, 'publisher' => $purchasePublisher);
                }
                else if(isset($shibString)){
                    $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$thisScode;
                    $thisShibJstartHTML = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
                    $thisItem = array('title' => $purchaseName, 'orderNumber' => '', 'jumpStart' => '<a href="'.$thisJstart.'">'.$thisJstart.'</a>', 'shibJumpstart' => $thisShibJstartHTML, 'publisher' => $purchasePublisher);
                }
                else if(isset($athensString)){
                    $thisAthensJstart = "http://ovidsp.ovid.com/athens/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=".$thisScode;
                    $thisItem = array('title' => $purchaseName, 'orderNumber' => '', 'jumpStart' => '<a href="'.$thisJstart.'">'.$thisJstart.'</a>', 'athensJumpstart' => '<a href="'.$thisAthensJstart.'">'.$thisAthensJstart.'</a>', 'publisher' => $purchasePublisher);
                }
                else {
                    $thisItem = array('title' => $purchaseName, 'orderNumber' => '', 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$thisScode.'">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$thisScode.'</a>', 'publisher' => $purchasePublisher);
                }
                }
                $wksheets[$key][] = $thisItem;
            }
        if(!empty($ebmrArray)){
            $ecArray[] = 'ebmr';
            $extraEBMR = array('ebmz' => 'All EBM Reviews - Cochrane DSR, ACP Journal Club, DARE, CCA, CCTR, CMR, HTA, and NHSEED', 'ebmy' => 'EBM Reviews Full Text - Cochrane DSR, ACP Journal Club, CCA, and DARE');
            foreach($extraEBMR as $k => $v){
                $thisJstart = 'http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$k;
                if((isset($athensString)) && (isset($shibString))){
                    $thisAthensJstart = "http://ovidsp.ovid.com/athens/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=".$k;
                    $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$k;
                    $thisShibJstartHTML = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
                    $thisItem = array('title' => $v, 'orderNumber' => '', 'jumpStart' => '<a href="'.$thisJstart.'">'.$thisJstart.'</a>', 'athensJumpstart' => '<a href="'.$thisAthensJstart.'">'.$thisAthensJstart.'</a>', 'shibJumpstart' => $thisShibJstartHTML, 'publisher' => 'Multiple');
                }
                else if(isset($shibString)){
                    $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$k;
                    $thisShibJstartHTML = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
                    $thisItem = array('title' => $v, 'orderNumber' => '', 'jumpStart' => '<a href="'.$thisJstart.'">'.$thisJstart.'</a>', 'shibJumpstart' => $thisShibJstartHTML, 'publisher' => 'Multiple');
                }
                else if(isset($athensString)){
                    $thisAthensJstart = "http://ovidsp.ovid.com/athens/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D=".$k;
                    $thisItem = array('title' => $v, 'orderNumber' => '', 'jumpStart' => '<a href="'.$thisJstart.'">'.$thisJstart.'</a>', 'athensJumpstart' => '<a href="'.$thisAthensJstart.'">'.$thisAthensJstart.'</a>', 'publisher' => 'Multiple');
                }
                else {
                    $thisItem = array('title' => $v, 'orderNo' => '', 'jumpStart' => '<a href="http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$k.'">http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&CSC=Y&PAGE=main&D='.$k.'</a>', 'publisher' => 'Multiple');
                }
                $wksheets[$key][] = $thisItem;
            }

            foreach($ebmrArray as $ebmr){
                if($ebmr['shortName'] == 'acp'){
                    $ebmr['title'] = 'EBM Reviews - ACP Journal Club';
                }
                elseif($ebmr['shortName'] == 'cca'){
                    $ebmr['title'] = 'EBM Reviews - Cochrane Clinical Answers';
                }
                elseif($ebmr['shortName'] == 'cctr'){
                    $ebmr['title'] = 'EBM Reviews - Cochrane Central Register of Controlled Trials';
                }
                elseif($ebmr['shortName'] == 'clcmr'){
                    $ebmr['title'] = 'EBM Reviews - Cochrane Methodology Register';
                }
                elseif($ebmr['shortName'] == 'cleed'){
                    $ebmr['title'] = 'EBM Reviews - NHS Economic Evaluation Database';
                }
                elseif($ebmr['shortName'] == 'clhta'){
                    $ebmr['title'] = 'EBM Reviews - Health Technology Assessment';
                }
                elseif($ebmr['shortName'] == 'coch'){
                    $ebmr['title'] = 'EBM Reviews - Cochrane Database of Systematic Reviews';
                }
                elseif($ebmr['shortName'] == 'dare'){
                    $ebmr['title'] = 'EBM Reviews - Database of Abstracts of Reviews of Effects';
                }
                $ebmr['publisher'] = 'Multiple';
                $thisJstart = $ebmr['jumpStart'];
                if(isset($athensString)){
                    $thisAthensJstart = str_replace('http://ovidsp.ovid.com/ovidweb.cgi?', 'http://ovidsp.ovid.com/athens/ovidweb.cgi?', $thisJstart);
                    $ebmr['athensJumpstart'] = '<a href="'.$thisAthensJstart.'">'.$thisAthensJstart.'</a>';
                }
                if(isset($shibString)){
                    $thisParamsArray = explode('?', $thisJstart);
                    $thisParams = $thisParamsArray[1];
                    $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&'.$thisParams;
                    $ebmr['shibJumpstart'] = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
                }
                $ebmr['jumpStart'] = '<a href="'.$thisJstart.'">'.$thisJstart.'</a>';
                $unsetArray = array('shortName', 'vendor', 'formatShortName', 'productClass');
                foreach($unsetArray as $remove){
                    unset($ebmr[$remove]);
                }
                $ordered_array = array_merge(array_flip($order), $ebmr);
                $wksheets[$key][$i] = $ordered_array;
            }
        }

        if(!empty($acrArray)){
            $ecArray[] = 'acr';
            $finalAcrArray = array();
            if(count($acrArray > 1)){
                $thisPcodesArray = array();
                foreach($acrArray as $acr){
                    $thisPcodesArray[] = $acr['shortName'];
                }
                $thisPCodesString = implode(',', $thisPcodesArray);
                $thisPCodesString = '&pcodes='.$thisPCodesString;
                $thisDBCode = $thisPcodesArray[0];
                $thisJStart = 'http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&PAGE=main&D='.$thisDBCode.$thisPCodesString;
                $finalAcrArray['title'] = 'Radiology Learning from ACR';
                $finalAcrArray['jumpStart'] = '<a href="'.$thisJStart.'">'.$thisJStart.'</a>';
                $finalAcrArray['orderNumber'] = $acrArray[0]['orderNumber'];
                $finalAcrArray['publisher'] = 'American College of Radiology';
            }
            else {
                $finalAcrArray =  $acrArray[0];
            }
            if(isset($athensString)){
                $thisAthensJstart = str_replace('http://ovidsp.ovid.com/ovidweb.cgi?', 'http://ovidsp.ovid.com/athens/ovidweb.cgi?', $thisJstart);
                $finalAcrArray['athensJumpstart'] = '<a href="'.$thisAthensJstart.'">'.$thisAthensJstart.'</a>';
            }
            if(isset($shibString)){
                $thisParamsArray = explode('?', $thisJstart);
                $thisParams = $thisParamsArray[1];
                $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&'.$thisParams;
                $finalAcrArray['shibJumpstart'] = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
            }
            $ordered_array = array_merge(array_flip($order), $finalAcrArray);
            $wksheets[$key][$i] = $ordered_array;
        }

        if(!empty($vdxArray)){
            $ecArray[] = 'vdx';
            $finalVdxArray = array();
            if(count($vdxArray > 1)){
                $thisPcodesArray = array();
                foreach($vdxArray as $vdx){
                    $thisPcodesArray[] = $vdx['shortName'];
                }
                $thisPCodesString = implode(',', $thisPcodesArray);
                $thisPCodesString = '&pcodes='.$thisPCodesString;
                $thisDBCode = $thisPcodesArray[0];
                $thisJStart = 'http://ovidsp.ovid.com/ovidweb.cgi?T=JS&NEWS=n&PAGE=main&D='.$thisDBCode.$thisPCodesString;
                if((count($vdxArray == 3)) && (in_array('vdxdxp', $thisPcodesArray, TRUE)) && (in_array('vdxm', $thisPcodesArray, TRUE))){
                    $finalVdxArray['title'] = 'VisualDx Complete w/Mobile Apps & DermExpert';
                }
                else if((count($vdxArray == 2)) && (in_array('vdxm', $thisPcodesArray, TRUE))){
                    $finalVdxArray['title'] = 'VisualDx Complete w/Mobile Apps & DermExpert';
                }
                else {
                    $finalVdxArray['title'] = 'VisualDx Complete';
                }
                $finalVdxArray['jumpStart'] = '<a href="'.$thisJStart.'">'.$thisJStart.'</a>';
                $finalVdxArray['orderNumber'] = $vdxArray[0]['orderNumber'];
                $finalVdxArray['publisher'] = 'Logical Images, Inc.';

                $vdxAppArray = array();
                
            }
            else {
                $vdxArray[0]['title'] = 'VisualDx Complete';
                $finalVdxArray =  $vdxArray[0];
            }
            if(isset($athensString)){
                $thisAthensJstart = str_replace('http://ovidsp.ovid.com/ovidweb.cgi?', 'http://ovidsp.ovid.com/athens/ovidweb.cgi?', $thisJstart);
                $finalVdxArray['athensJumpstart'] = '<a href="'.$thisAthensJstart.'">'.$thisAthensJstart.'</a>';
            }
            if(isset($shibString)){
                $thisParamsArray = explode('?', $thisJstart);
                $thisParams = $thisParamsArray[1];
                $thisShibJstart = 'https://openathens.ovid.com/OAKeystone/deeplink?idpselect='.$shibString.'&entityID='.$shibString.'&'.$thisParams;
                $finalVdxArray['shibJumpstart'] = '<a href="'.$thisShibJstart.'">'.$thisShibJstart.'</a>';
            }
            $ordered_array = array_merge(array_flip($order), $finalVdxArray);
            $wksheets[$key][] = $ordered_array;
        }
    }
    else if($key == 'vb'){
        include('connect.php');
        $jstartColumnArray = array('C');
        $vbHeaderArray = array(array('headerName' => 'Title'), array('headerName' => 'Jumpstart'), array('headerName' => 'Publisher'));
        $sheetTitle = 'Visible Body';
        $vbDbCodeArray = array();
        $vbAppArray = array();
        $mobOnlyArray = array();
        $vbArray = array();
        $vbCodeArray = explode(',', $vbString);
        foreach($vbCodeArray as $vbProd){
            $thisVBArray = explode(':', $vbProd);
            $thisVBShortCode = $thisVBArray[0];
            if(isset($thisVBArray[1])){
                $thisAppStatus = $thisVBArray[1];
            }
            if(!isset($thisVBArray[2])){
                $vbDbCodeArray[] = $thisVBShortCode;
            }
            if($thisAppStatus == 'app'){
                $thisAppArray = array();
                if($lang == 'cn'){
                    $typeString = "(`type` = 'itunes' OR `type` = 'apk')";
                }
                else {
                    $typeString = "(`type` = 'itunes' OR `type` = 'googleplay')";
                }
                $sql = "SELECT `name`,`url` FROM `vb` WHERE (`code`='$thisVBShortCode' AND ".$typeString.");";
                $result = mysqli_query($conn, $sql);
                while($row = mysqli_fetch_assoc($result)){
                    $appName = trim($row['name']);
                    $appURL = "<a href=\"".$row['url']."\">".trim($row['url'])."</a>";
                    $vbAppArray[] = array('title' => $appName, 'jumpStart' => $appURL, 'publisher' => 'Argosy Publishing, Inc.');
                }
            }
        }
        if(count($vbDbCodeArray) > 0){
            $dbCode = $vbDbCodeArray[0];
            $pcString = '';
            if(count($vbDbCodeArray) > 1){
                $pcString = implode(',', $vbDbCodeArray);
                $pcString = "&pcodes=".$pcString;
            }
            $vbDbUrl = "http://ovidsp.ovid.com/ovidweb.cgi?T=JS&CSC=Y&NEWS=n&PAGE=main&D=".$dbCode.$pcString;
            $vbDbUrl = "<a href='".$vbDbUrl."'>".$vbDbUrl."</a>";
            $vbDbArray = array('title' => 'Desktop access', 'jumpStart' => $vbDbUrl, 'publisher' => 'Argosy Publishing, Inc.');
        }
        if(count($vbAppArray) > 0){
            usort($vbAppArray, function($a, $b) {
                return $a['title'] <=> $b['title'];
            });
            $placeholder = array('title' => '<b>Mobile Apps:', 'jumpStart' => '', 'publisher' => '');
            array_unshift($vbAppArray,$placeholder);
            foreach($vbAppArray as $app){
                $vbArray[] = $app;
            }
        }
        if(isset($vbDbArray)){
            array_unshift($vbArray,$vbDbArray);
        }
        $wksheets[$key] = $vbArray;
        mysqli_close($conn);
    }
    else if($key == 'jbi'){
        $jbiHeaderArray = array(array('headerName' => 'Tool'), array('headerName' => 'Jumpstart'), array('headerName' => 'Publisher'));
        $jbiToolArray = array();
        $sheetTitle = 'JBI Tools';
        include('connect.php');
        $sql = "SELECT `toolname`,`scode` FROM `jbitools` WHERE `prodcode` IN (".$jbiString.");";
        $result = mysqli_query($conn, $sql);
        while($row = mysqli_fetch_assoc($result)){
            $toolName = trim($row['toolname']);
            $toolURL = 'http://ovidsp.ovid.com/ovidweb.cgi?T=JS&PAGE=jbi&TOOL='.trim($row['scode']);
            $toolURL = "<a href=\"".$toolURL."\">".$toolURL."</a>";
            $jbiToolArray[] = array('title' => $toolName, 'jumpStart' => $toolURL, 'publisher' => 'JBI');
        }
        $jbiToolArray = array_map("unserialize", array_unique(array_map("serialize", $jbiToolArray)));;
        usort($jbiToolArray, function($a, $b) {
            return $a['title'] <=> $b['title'];
        });
        $wksheets[$key] = $jbiToolArray;
        mysqli_close($conn);
    }
    /*
    usort($wksheets[$key], function($a, $b) {
        return $a['title'] <=> $b['title'];
    });
    */
$c++;
}

function colDefs($headerArray){
    $outArray = array();
    $i = 0;
    foreach ($headerArray as $col){
        if(($col['headerName'] == 'Title') || ($col['headerName'] == 'Tool')){
            $headerArray[$i]['field'] = 'title';
            $headerArray[$i]['suppressSizeToFit'] = false;
        }
        else if($col['headerName'] == 'Edition'){
            $headerArray[$i]['field'] = 'edition';
            $headerArray[$i]['suppressSizeToFit'] = true;
            $headerArray[$i]['cellClass'] = 'grid-cell-centered';
        }
        else if($col['headerName'] == 'ISBN'){
            $headerArray[$i]['field'] = 'isbn';
            $headerArray[$i]['suppressSizeToFit'] = true;
            $headerArray[$i]['cellClass'] = 'grid-cell-centered';
            $headerArray[$i]['maxWidth'] = '150';
        }
        else if($col['headerName'] == 'ISSN'){
            $headerArray[$i]['field'] = 'issn';
            $headerArray[$i]['suppressSizeToFit'] = true;
            $headerArray[$i]['cellClass'] = 'grid-cell-centered';
            $headerArray[$i]['maxWidth'] = '150';
        }
        else if($col['headerName'] == 'Order No.'){
            $headerArray[$i]['field'] = 'orderNumber';
            $headerArray[$i]['suppressSizeToFit'] = true;
            $headerArray[$i]['cellClass'] = 'grid-cell-centered';
            $headerArray[$i]['maxWidth'] = '150';
        }
        else if($col['headerName'] == 'Jumpstart'){
            $headerArray[$i]['field'] = 'jumpStart';
            $headerArray[$i]['suppressSizeToFit'] = false;
        }
        else if($col['headerName'] == 'Athens Jumpstart'){
            $headerArray[$i]['field'] = 'athensJumpstart';
            $headerArray[$i]['suppressSizeToFit'] = false;
        }
        else if($col['headerName'] == 'Shibboleth Jumpstart'){
            $headerArray[$i]['field'] = 'shibJumpstart';
            $headerArray[$i]['suppressSizeToFit'] = false;
        }
        else if($col['headerName'] == 'Publisher'){
            $headerArray[$i]['field'] = 'publisher';
            $headerArray[$i]['cellClass'] = 'grid-cell-centered';
        }
        else if($col['headerName'] == 'Start Date'){
            $headerArray[$i]['field'] = 'startCoverage';
            $headerArray[$i]['suppressSizeToFit'] = true;
            $headerArray[$i]['cellClass'] = 'grid-cell-centered';
            $headerArray[$i]['maxWidth'] = '150';
        }
        else if($col['headerName'] == 'End Date'){
            $headerArray[$i]['field'] = 'endCoverage';
            $headerArray[$i]['suppressSizeToFit'] = true;
            $headerArray[$i]['cellClass'] = 'grid-cell-centered';
            $headerArray[$i]['maxWidth'] = '150';
        }
        else if($col['headerName'] == 'Dual Access URL'){
            $headerArray[$i]['field'] = 'ejpURL';
            $headerArray[$i]['suppressSizeToFit'] = false;
        }
        $i++;
    }
    return $headerArray;
}

foreach($wksheets as $key => $value){
    $data = array_map('array_filter', $wksheets[$key]);
    unset($wksheets[$key]);
    $wksheets[$key]['rows'] = array_values($data);
    if($key == 'journal'){
        $wksheets[$key]['cols'] = colDefs($journalsHeaderArray);
    }
    elseif($key == 'book'){
        $wksheets[$key]['cols'] = colDefs($booksHeaderArray);
    }
    elseif($key == 'database'){
        $wksheets[$key]['cols'] = colDefs($databasesHeaderArray);
    }
    elseif($key == 'vb'){
        $wksheets[$key]['cols'] = colDefs($vbHeaderArray);
    }
    elseif($key == 'jbi'){
        $wksheets[$key]['cols'] = colDefs($jbiHeaderArray);
    }
}

$suptArray = array();
$ecArray[] = 'all';
$ecString = implode("', '", $ecArray);

include('../../../ovidtools/assets/php/connect_toolkit.php');
$sql = "SELECT `title`,`url` FROM `icons` WHERE `ecID` IN ('".$ecString."');";
if($result = mysqli_query($conn, $sql)){
    $suptArray['icons'] = array('label' => 'Icons', 'data' => array());
    while ($row = mysqli_fetch_assoc($result)) {
        $thisLabel = $row['title'];
        $thisURL = $row['url'];
        $thisArray = array('label' => $thisLabel, 'url' => $thisURL);
        $suptArray['icons']['data'][] = $thisArray;
    }
}

$sql = "SELECT `label`,`img`,`url` FROM `qrc` WHERE `ecID` IN ('".$ecString."') ORDER BY `label`;";
if($result = mysqli_query($conn, $sql)){
    $suptArray['qrc'] = array('label' => 'Quick Reference Cards', 'data' => array());
    while ($row = mysqli_fetch_assoc($result)) {
        $thisLabel = $row['label'];
        $thisURL = $row['url'];
        $thisIMG = $row['img'];
        $thisArray = array('label' => $thisLabel, 'url' => $thisURL, 'img' => $thisIMG);
        $suptArray['qrc']['data'][] = $thisArray;
    }
}

mysqli_close($conn);

include('../../../ovidtools/assets/php/connect.php');

$sql = "SELECT `title`,`url` FROM `videos` WHERE `ecID` IN ('".$ecString."') ORDER BY `title`;";

if($result = mysqli_query($conn, $sql)){
    $suptArray['videos'] = array('label' => 'Videos', 'data' => array());
    while ($row = mysqli_fetch_assoc($result)) {
        $thisLabel = $row['title'];
        $thisURL = $row['url'];
        $thisArray = array('label' => $thisLabel, 'url' => $thisURL);
        $suptArray['videos']['data'][] = $thisArray;
    }
}

mysqli_close($conn);

$wksheets['support'] = $suptArray;

$orderArrray = array('journal', 'book', 'database', 'vb', 'jbi', 'support');
$response = array();
foreach($orderArrray as $tab){
    if(isset($wksheets[$tab])){
        $response[$tab] = $wksheets[$tab];
    }
}

$response['count'] = $c;
$response['type'] = $type;
$response['group'] = $ovidGroup;
if(isset($endCoverageIndex)){
    $response['endCoverageIndex'] = $endCoverageIndex;
}

function utf8ize($d) {
    if (is_array($d)) {
        foreach ($d as $k => $v) {
            $d[$k] = utf8ize($v);
        }
    } else if (is_string ($d)) {
        return utf8_encode($d);
    }
    return $d;
}
//var_dump($purchaseArray);
//echo json_encode(utf8ize($wksheets));
//ob_clean();
echo str_replace('\u00c2\u00a0', ' ', json_encode(utf8ize($response)));
?>