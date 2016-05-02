<?php

$link = mysql_connect('localhost', 'root', 'asdf');
if (!$link) {
	die(mysql_error());
}

$db = mysql_select_db('ihi_old', $link);

$sql = 'SELECT pageID, date, titill, content FROM pages WHERE catID = 2';

$string = '';

$rs = mysql_query($sql);
while ($row = mysql_fetch_array($rs, MYSQL_ASSOC)) {
	$titill = utf8_encode($row['titill']);
	$safe = safeString($titill);
	$codename = $safe.'-'.$row['pageID'];
	if (empty($codename)) die('no codename');
	$date = strtotime($row['date']);
	$content = utf8_encode($row['content']);
	$string .= 'INSERT INTO news VALUES (1, null, "'.addslashes(utf8_decode($titill)).'", "'.$codename.'", "", "'.addslashes(utf8_decode($content)).'", '.$date.', 0, 0, 0, '.$date.', 0, 0, 1, 0, "", "", 1, 2, 1, "", 0, 0);';
}

echo $string."\n";

function safeString($string, $whitespace = '-')
{
	$srcArr = array(
		'/á/','/ð/','/é/','/í/','/ó/','/ú/','/ý/','/þ/','/æ/','/ö/',
		'/Á/','/Ð/','/É/','/Í/','/Ó/','/Ú/','/Ý/','/Þ/','/Æ/','/Ö/',
		"/[^a-zA-Z0-9\._]+/", '/"/', "/'/"
	);
	$dstArray = array(
		'a','d','e','i','o','u','y','th','ae','o',
		'a','d','e','i','o','u','y','th','ae','o',
		$whitespace, '/"/', "/'/"
	);
	return strtolower(preg_replace($srcArr, $dstArray, trim($string)));
}
