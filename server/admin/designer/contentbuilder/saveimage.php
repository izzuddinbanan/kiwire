<?php

header('Cache-Control: no-cache, must-revalidate');

session_start();

if (!isset($_SESSION['tenant_id']) || empty($_SESSION['tenant_id'])) die("<html><body onload=\"alert('Sorry, your session expired. Please relogin.')\"></body></html>");

//Specify url path
$path = dirname(__FILE__, 4) . "/custom/{$_SESSION['tenant_id']}/images/";

//Read image
$count = $_REQUEST['count'];
$b64str = $_REQUEST['hidimg-' . $count]; 
$imgname = $_REQUEST['hidname-' . $count]; 
$imgtype = $_REQUEST['hidtype-' . $count]; 

$customvalue = $_REQUEST['hidcustomval-' . $count]; //Get customvalue  

//Generate random file name here
if($imgtype == 'png'){

	$image = $imgname . '-' . base_convert(rand(),10,36) . '.png';

} else {

	$image = $imgname . '-' . base_convert(rand(),10,36) . '.jpg';

}



//Check folder. Create if not exist
$pagefolder = $path;

if (!file_exists($pagefolder)) {

	mkdir($pagefolder, 0755, true);

} 


//Optional: If using customvalue to specify upload folder
if ($customvalue!='') {
  $pagefolder = $path . $customvalue. '/';
  if (!file_exists($pagefolder)) {
	  mkdir($pagefolder, 0755, true);
  } 
}


//Save image

$success = file_put_contents($pagefolder . $image, base64_decode($b64str));

if ($success === FALSE) {

  if (!file_exists($path)) {

    echo "<html><body onload=\"alert('Saving image to folder failed. Folder ".$pagefolder." not exists.')\"></body></html>";

  } else {

    echo "<html><body onload=\"alert('Saving image to folder failed. Please check write permission on " .$pagefolder. "')\"></body></html>";

  }

} else {

    $pagefolder = substr($pagefolder, strpos($pagefolder, "/server/") + 7);

    echo "<html><body onload=\"parent.document.getElementById('img-" . $count . "').setAttribute('src','" . $pagefolder . $image . "');  parent.document.getElementById('img-" . $count . "').removeAttribute('id') \"></body></html>";

}

