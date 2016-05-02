<?php

ini_set('display_errors', 0);
$con = @mysql_connect('localhost', 'root', 'asdf') or die(mysql_error());
@mysql_select_db('m_akureyri') or die(mysql_error());


function D($var){var_dump($var);die;}

class Import
{
	const DRYRUN = false;

	const F_MEDIA = 'akureyri-media.xml';
	const F_MEDIALINKS = 'akureyri-linktomedia.xml';
	const F_ARTICLES = 'akureyri-articles2.xml';

	public static function run()
	{
		$GLOBALS['axml'] = new SimpleXMLElement(file_get_contents(self::F_ARTICLES));
		$GLOBALS['mxml'] = new SimpleXMLElement(file_get_contents(self::F_MEDIA));
		$GLOBALS['lxml'] = new SimpleXMLElement(file_get_contents(self::F_MEDIALINKS));
		#self::sitemap();
		#self::articles();
		#self::getMedia();
		#self::employees();
		self::findPlaylists();
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

		if (self::isItemIgnored($item)) {return;}

		echo "  . Getting ready to save article of type $type\n";

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
		$division = 2;
		if (strpos($webpath, '/amtsbokasafn') === 0) { $division = 3; }
		if (strpos($webpath, '/hak') === 0) { $division = 4; }
		if (strpos($webpath, '/oldrunarheimili') === 0) { $division = 5; }
		if (strpos($webpath, '/framkvaemdadeild') === 0) { $division = 6; }
		if (strpos($webpath, '/fasteignir') === 0) { $division = 7; }
		if (strpos($webpath, '/skipulagsdeild') === 0) { $division = 8; }
		if (strpos($webpath, '/rosenborg') === 0) { $division = 9; }
		if (strpos($webpath, '/english') === 0) { $division = 10; }
		if (strpos($webpath, '/grimsey') === 0) { $division = 11; }
		if (strpos($webpath, '/kosningar2010') === 0) { $division = 12; }
		if (strpos($webpath, '/radgjafatorg') === 0) { $division = 13; }
		if (strpos($webpath, '/starfsmannahandbok') === 0) { $division = 14; }
		if (strpos($webpath, '/stjornendahandbok') === 0) { $division = 15; }

		
		if ($type == 'page') {
			$codename = checkCodename($title, 'page', 'page_codename');
			$title = addslashes($title);
			$content = addslashes($content);
			
			$sql = "INSERT INTO page values(null,1,'$title','$codename','$content',2, $division, 1, 1, '', '', '', $timestamp,0,0,0,0)";
		}
		else {
			$codename = checkCodename($title, 'news', 'news_codename');
			$title = addslashes($title);
			$content = addslashes($content);
			$intro = addslashes($intro);

			$sql = "INSERT INTO news values(1,null,'$title','$codename','$intro','$content',$timestamp,0,0,0,$timestamp,0,0,1,0,'','',2,$division,1,'',0,0)";
		}

		//echo "$sql\n";
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
	}

	public static function isItemIgnored($item)
	{
		$webpath = (string)$item->webpath;
		$ignored = array(
			'/frettabref', '/stjornkerfid/hafdu-samband/netfong', '/stjornkerfid/baejarstjorn/fundir',

			'/stjornkerfid/fundargerdirnar', '/stjornkerfid/hverfisnefndir/lundaoggerdahverfi/fundargerdir',
			'/stjornkerfid/hverfisnefndir/holtaoghlidahverfi/fundargerdir', '/stjornkerfid/hverfisnefndir/Brekkuskolahverfi/fundargerdir',
			'/stjornkerfid/hverfisnefndir/Hrisey/fundargerdir','/stjornkerfid/hverfisnefndir/giljahverfi/fundargerdir',
			'/stjornkerfid/hverfisnefndir/grimsey/fundargerdir','/stjornkerfid/hverfisnefndir/oddeyri/fundargerdir',
			'/stjornkerfid/hverfisnefndir/siduhverfi/fundargerdir','/stjornkerfid/hverfisnefndir/Naustahverfi/fundargerdir',
			'/stjornkerfid/fundargerdir','/stornkerfid/fundargerdir','/stjornkerfid/nefndir-og-rad/adrar-nefndir/hafnasamlag-nordurlands',

			'/fjolskyldustefna','/fjolskyldukort','/fjallid','/ferdamenn','/en','/daglegt-lif','/auglysingar/laus-storf','/athugasemdir','/althjodlegarskidareglur',
			'/adventuaevintyri','/abendingar','/YmislegtNytsamlegt','/Rosenborg','/Paskar','/Listasumar','/Kosningar','/Kosningar2006','/Kosningar2007','/Fyrirtaeki',
			'/150-ara','/arsskyrsla','/arsskyrsla2003','/forsgreinar','/frettir-og-vidburdir','/frettir/forsidufrettir','/frumkvodlar','/fundaradstada','/gaedahandbok',
			'/gonguskidabrautir','/greinasafn','/hafdu-samband','/hlidarfjall','/hrisey','/hugtok','/i-beinni','/ibuavefur','/innkaupasida','/komdunordur','/kosningar',
			'/leiga','/leit-old','/lifsins-gaedi','/logreglusamthykkt','/lyftumidar','/menning','/menningarhatid','/menningarhus','/millisidugreinar','/myndir','/opnunartimar',
			'/paskar2007','/profanir','/radstefna','/rss','/skemmtiferdaskip','/skidaleigan','/skidaskolinn','/skolabaerinn/Skoladeild','/skoli','/snjoframleidsla','/spjallthradur',
			'/stadardagskra','/stakar-sidur','/stjornkerfid/hafdu-samband','/stjornkerfid/namskeid','/stjornkerfid/svid-og-deildir','/stjornlagathing','/takk','/tenglar',
			'/vaka','/vefmyndavel','/vefmyndavelar','/verd','/veterarkort','/vmi','/yfirlitskort','/ymislegt-nytsamlegt','/ymsar-fjarmalaupplysingar'
		);

		#echo "  . is $webpath in ignore?";

		foreach ($ignored as $path) {
			if(strpos($webpath, $path) === 0) {
				#echo " [YES]\n";
				return true;
			}
		}

		#echo " [NO]\n";
		return false;
	}
	
	public static function getArticleType($item)
	{
		$type = 'news';
		$webPath = (string) $item->webpath;
		if (!empty($webPath)) {
			if (strpos($webPath, '/frettir') !== 0 
				&& $webPath !== '/amtsbokasafn' 
				&& strpos($webPath, '/amtsbokasafn/20') !== 0 
				&& strpos($webPath, '/amtsbokasafn/frettasafn') !== 0 
				&& strpos($webPath, '/hak/efst-a-baugi') !== 0 
				&& strpos($webPath, '/oldrunarheimili/frettir') !== 0) {
				$type = 'page';
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

	public static function getMedia() 
	{
		foreach ($GLOBALS['mxml'] as $item) {
			$filename = (string)$item->FileName;
			$folder = (string)$item->FileDirectory;

			if (strpos($filename, 'multipartdatabean') !== 0) { //weird files
				$url = 'http://akureyri.is/media' . $folder . '/' . $filename;
				$destPath = '/home/thrstn/www/_webs/akureyri.is/1.15.1/static/files/'.$folder.'/';

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
							}
						}
						catch(Exception $e) {
							echo " !! $e->getMessage()";
						}
					}
				}
			}
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
