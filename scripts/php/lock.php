<?php
require_once('Zend/Session.php');
Zend_Session::start();

if (isset($_SESSION) && isset($_SESSION['user'])) {
	//user is logged in
	$fullpath = urldecode(str_replace('/static/files/innri/', '', $_SERVER['REQUEST_URI']));
	$filename = basename($fullpath);
	$path = dirname($fullpath);

	if (!$filename || $filename == '.lock.php') {
		//no filename given
		header("HTTP/1.0 404 Not Found");
		exit;
	}

	if (is_dir($fullpath)) {
		//dont download directories
		header("HTTP/1.0 404 Not Found");
		exit;
	}

	if (!file_exists($fullpath)) {
		//file not found
		header("HTTP/1.0 404 Not Found");
		exit;
	}

	//send the file
	$size = filesize($fullpath);

	//fileinfo module is missing on server
	//$mime = mime_content_type($fullpath);
	//header('Content-Type: '. $mime);
	header('Content-Type: application/force-download', true);
	header('Content-Length: ' . $size);
	header('Content-Disposition: attachment; filename="' . $filename .'"');
                        
	$fp = fopen($fullpath, "r");
	while (!feof($fp))
	{
		echo fread($fp, 65536);
		flush(); // this is essential for large downloads
	}
	fclose($fp);
}
else {
	//not logged in
	header("HTTP/1.1 403 Forbidden");
}
