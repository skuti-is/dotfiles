<?php

ini_set('display_errors', 1);
$con = @mysql_connect('moyadb1', 'm_visitak', 'gorilla884') or die(mysql_error());
@mysql_select_db('m_visitak') or die(mysql_error());


function D($var){var_dump($var);die;}

class Import
{
	const DRYRUN = false;

	const F_MEDIA = 'visitakureyri-media.xml';
	const F_MEDIALINKS = 'visitakureyri-linktomedia.xml';
	const F_ARTICLES = 'visitakureyri-articles.xml';

	public static function run()
	{
		$GLOBALS['axml'] = new SimpleXMLElement(file_get_contents(self::F_ARTICLES));
		$GLOBALS['mxml'] = new SimpleXMLElement(file_get_contents(self::F_MEDIA));
		$GLOBALS['lxml'] = new SimpleXMLElement(file_get_contents(self::F_MEDIALINKS));
		#self::sitemap();
		#self::articles();
		#self::getMedia();
		#self::employees();
		#self::findPlaylists();
	}

	public static function employees()
	{
  		$doc = new DOMDocument();
		$doc->strictErrorChecking = FALSE;
		$doc->loadHTML(file_get_contents('employees.html'));
		$xml = simplexml_import_dom($doc);

		$table = $xml->body->div->div[1]->div->div->div->div->table;
		foreach($table as $tr) {
			foreach ($tr as $td) {
				$count = 0;
				foreach ($td as $html) {
					$count++;

					$sql = "INSERT INTO employee values (null, 1, '{$data[1]}', '{$codename}', '', '$email', '', '', '{$data[0]}', '', '{$data[3]}', 1, 0)";
				}
			}
		}
	}

	public static function findPlaylists()
	{
		$urls = array();
		foreach ($GLOBALS['axml'] as $item) {
			preg_match('#\/media\/fundir\/[a-z]{2}\d+\.xml#', $item->Article, $matches);
			if (count($matches) > 0) {
				//echo "- Found playlist: $matches[0]\n";
				$urls[] = $matches[0];
			}
		}
		foreach ($urls as $url) {
			echo 'http://www.akureyri.is' . $url . "\n";
		}
	}

	public static function articles()
	{
		foreach ($GLOBALS['axml'] as $item) {
			echo "- Parsing articleid $item->articleid\n";
			self::saveArticle($item);
		}
	}

	public static function saveArticle($item)
	{
		$type = self::getArticleType($item);
		$webpath = (string)$item->webpath;
		$mediainfo = array();

		if (self::isItemIgnored($item)) {return;}

		echo "  . Getting ready to save article of type $type\n";
		echo "  . webpath: $item->webpath\n";

		$id = $item->articleid;

		// setja subtitle við headline ef það er
		$title = $item->Title;
		if (!empty($item->Subtitle)) {
			$title .= ' - '. $item->Subtitle;
		}
		$title = addslashes(trim(utf8_decode(strip_tags(html_entity_decode($title)))));

		// græja createdate og displaydate
		$timestamp = 0;
		if (!empty($item->ArticlePublishedDate)) {
			$date = explode('-', $item->ArticlePublishedDate);
			$time = array(0, 0, 0);
			if (!empty($item->ArticlePublishTime)) {
				$time = explode(':', $item->ArticlePublishTime);
			}
			$timestamp = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
		}

		// uppfæra slóðir
		$intro = self::updateUrls($item->Summary, $type);
		$content = self::updateUrls($item->Article, $type);

		if (strlen($title) < 1 && strlen($content) < 1) return; // don't save crap

		// deild
		if (strpos($webpath, '/IS/') === 0) { $division = 2; }
		else { $division = 3; }
	
		$mediainfo = self::getLinkedMedia($id);
		$mediahtml = '';
		var_dump($mediainfo);

		if ($mediainfo) {
			$mediahtml = <<<html
<div class="entryImage">
	<img src="{$mediainfo['path']}" alt="{$mediainfo['alt']}" />
</div>
html;

		}

		$content = $mediahtml . $content;

		if ($type == 'page') {
			$codename = checkCodename($title, 'page', 'page_codename');
			$title = addslashes($title);
			$content = addslashes($content);
			
			$sql = "INSERT INTO page values(null,1,'$title','$codename','$content',2, $division, 1, 1, '', '', '', $timestamp,0,0,0,0)";
		}
		elseif($type == 'news') {
			$codename = checkCodename($title, 'news', 'news_codename');
			$title = addslashes($title);
			$content = addslashes($content);
			$intro = addslashes($intro);

			$sql = "INSERT INTO news values(1,null,'$title','$codename','$intro','$content',$timestamp,0,0,0,$timestamp,0,0,1,0,'','',2,$division,1,'',0,0)";
		}
		elseif($type = 'calendar') {
			return false; //do later
		}

		if  (self::DRYRUN === false) {
			$rs = mysql_query($sql);
			if (!$rs) {
				echo "  . Article not saved ".mysql_error()."\n";
				echo "  . $sql\n";
				die();
			}
			else {
				echo "  . Article saved\n";
			}
		}
		else {
			//echo "$sql\n";
		}
	}

	public static function isItemIgnored($item)
	{
		$webpath = (string)$item->webpath;
		$ignored = array(
			'/whats-on', '/IS/naest-a-dagskra'
		);

		foreach ($ignored as $path) {
			if(strpos($webpath, $path) === 0) {
				echo "  . is $webpath in ignore?";
				echo " [YES]\n";
				return true;
			}
		}

		return false;
	}
	
	public static function getArticleType($item)
	{
		$type = 'page';
		$webPath = (string) $item->webpath;
		if (!empty($webPath)) {
			if (strpos($webPath, '/IS/naest-a-dagskra/') == 0 
				&& strpos($webPath, '/whats-on/') === 0 ) {
				$type = 'calendar';
			}
		}

		return $type;
	}


	public static function updateUrls($text, $type)
	{
		$text = utf8_decode($text);
		$text = str_replace('"/media/', '"/static/files/', $text);

		return $text;
	}

	public static function getLinkedMedia($articleId)
	{
		foreach ($GLOBALS['lxml'] as $item) {
			if ((int)$item->ArticleID == $articleId) {
				foreach ($GLOBALS['mxml'] as $media) {
					if ((int)$item->MediaID == (int)$media->MediaID) {
						return self::downloadMedia($media);
					}
				}
			}
		}
	}

	public static function getMedia() 
	{
		foreach ($GLOBALS['mxml'] as $item) {
			self:downloadMedia($item);
		}
	}

	public static function downloadMedia($item) 
	{
		$return = array();
		$filename = (string)$item->FileName;
		$folder = (string)$item->FileDirectory;
		$return['alt'] = (string)$item->AltTag;

		if (strpos($filename, 'multipartdatabean') !== 0) { //weird files
			$url = 'http://visitakureyri.is/media' . $folder . '/' . $filename;
			$destPath = '/domains/visitakureyri.is/current/static/files/'.$folder.'/';
			$destUrl = '/static/files/'.$folder.'/';

			if (self::DRYRUN === false) {
				// búa til möppur
				if (!file_exists($destPath)) {
					echo "  . Creating folder $destPath\n";
					moyaMkdir($destPath);		
				}

				echo "  . Downloading $url to $destPath$filename\n";
				$cmd = "cd $destPath && wget -T 30 -nd -c -r --tries=10 '$url'";
				exec($cmd);
				if (!file_exists($destPath.$filename) || filesize($destPath.$filename) == 0) continue; //404
				$ext = strtolower(end(explode('.', $destPath.$filename)));
				if ($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif') {
       	                                require_once "lib/Moya/Exception.php";
               	                        require_once "lib/Moya/Image.php";
                      	                require_once "lib/Moya/Image/Adapter.php";
	                	        require_once "lib/Moya/Image/Adapter/Abstract.php";
					require_once "lib/Moya/Image/Adapter/Gd2.php";
					require_once "lib/Moya/Image/Adapter/Exception.php";
       	                                require_once "lib/Moya/Fs.php";

					try {
						$mi = new Moya_Image($destPath . $filename);
						if ($mi->getHeight() > 639 || $mi->getWidth() > 639) {
							moyaMkdir($destPath . 'stor/');		
		                        		$mi->resize('640', '640');
	       			                	$mi->save($destPath . 'stor/' . $filename);
						}
						if ($mi->getHeight() > 204 || $mi->getWidth() > 204) {
							moyaMkdir($destPath . 'mid/');		
       	       	                        		$mi->resize('205', '205');
			        	                $mi->save($destPath . 'mid/' . $filename);
							$return['path'] = $destUrl.'mid/'.$filename;
						}
						else {
							$return['path'] = $destUrl.'/'.$filename;
						}
					}
					catch(Exception $e) {
						echo " !! $e->getMessage()";
					}
				}
			}
			return $return;
		}

	}

	public static function sitemap()
	{
		$paths = array();
		foreach ($GLOBALS['axml'] as $item) {
			$path = (string)$item->webpath;
			$paths[] = $path;
		}
		sort($paths);
		echo "Webtree:\n";
		foreach ($paths as $path) {
			echo "$path\n";
		}
	}

	public static function media()
	{
		#self::mediaDirStruct();
	}

	public static function mediaDirStruct()
	{
		$dirs = array();
		$types = array();
		$exts = array();
		foreach ($GLOBALS['mxml'] as $item) {
			$folder = (string)$item->FileDirectory;
			if (!isset($dirs[$folder])) {
				$dirs[$folder] = 0;
			}
			$dirs[$folder]++;

			$type = (string)$item->{'Type'};
			if (!isset($types[$type])){
				$types[$type] = 0;
			}
			$types[$type]++;

			$ext = strtolower(preg_replace('/.*\./', '', (string)$item->FileName));
			if (!isset($exts[$ext])) {
				$exts[$ext] = 0;
			}
			$exts[$ext]++;
		}
		echo "Per folder:\n";
		asort($dirs);
		foreach ($dirs as $folder => $count) {
			echo "$count\t$folder\n";
		}
		echo "\nPer type (hefur greinilega enga raunverulega merkingu):\n";
		asort($types);
		foreach ($types as $type => $count) {
			echo "$count\t$type\n";
		}
		echo "\nPer extension\n";
		asort($exts);
		foreach ($exts as $ext => $count) {
			echo "$count\t$ext\n";
		}
	}
}

Import::run();

/***** EXTRAS *****/

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

function moyaMkdir($path, $mode = 0777)
{
        if (is_dir($path))
        {
                if (!(is_writable($path)))
                {
                        chmod($path, $mode);
                }

                $ret = true;
        }
        else
        {
                $oldmask = umask(0);
                $partialpath = dirname($path);
                if (!moyaMkdir($partialpath, $mode))
                {
                        $ret = false;
                }
                else
                {
                        $ret = mkdir($path, $mode);
                }
                umask($oldmask);
        }

        return $ret;
}
