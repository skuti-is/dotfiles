<?php

$con = @mysql_connect('localhost', 'root', 'AerinSun') or die(mysql_error());
@mysql_select_db('m_ma115') or die(mysql_error());

$chunk = 1* (1024 * 1024);
$file = fopen('data.csv', 'r');

while (!feof($file)) {
	$line = fgets($file, $chunk);
	$arr = explode(';', $line);

	$kt = str_replace('-', '', $arr[1]);
	$name = $arr[2];
	$codename = checkCodename($name, 'employee', 'employee_codename');
	$email = $arr[9];
	$mobile = $arr[7];
	$phone = $arr[6];
	$job = $arr[8];

	if (!empty($name)) {
		$sql = "insert into employee values (null, 1, '$name', '$codename', '$kt', '$email', '', '$mobile', '$phone', '', '', 1, 0)";
		mysql_query($sql);

		$id = mysql_insert_id();
		$sql = "insert into employee_ebLink values (1, 0, $id, '$job')";
		mysql_query($sql);
	}
}



function checkCodename($codename, $table, $fieldName=null, $i=0, $delimiter = '-', $extraWhere = '')
{
	if(null === $fieldName) $fieldName = 'codename';

	$codename = safeString($codename, $delimiter);
	$sql = "SELECT * FROM $table WHERE $fieldName = '$codename' $extraWhere ORDER BY $fieldName DESC";

	$rs = mysql_query($sql);

	while ($row = mysql_fetch_assoc($rs))
	{
		// split the codename at underscores
		$pattern = '#' . $delimiter . '(\d)$#';
		$arr = preg_split($pattern, $codename, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		$num = (isset($arr[1])) ? ++$arr[1] : 1;

		// combine the new codename
		$newname = $arr[0] . $delimiter . $num;

		// check if new codename exists
		$codename = checkCodename($newname, $table, $fieldName, $num, $delimiter, $extraWhere);
	}

	return $codename;
}

function safeString($string, $whitespace = '-')
{
	$srcArr = array(
		'/á/','/ð/','/é/','/í/','/ó/','/ú/','/ý/','/þ/','/æ/','/ö/',
		'/Á/','/Ð/','/É/','/Í/','/Ó/','/Ú/','/Ý/','/Þ/','/Æ/','/Ö/',
		"/[^a-zA-Z0-9\._]+/"
	);
	$dstArray = array(
		'a','d','e','i','o','u','y','th','ae','o',
		'a','d','e','i','o','u','y','th','ae','o',
		$whitespace
	);
	return strtolower(preg_replace($srcArr, $dstArray, trim($string)));
}
