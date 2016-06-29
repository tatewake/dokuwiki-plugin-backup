<?php
/**
 * Backup Tool for DokuWiki
 * 
 * @license	GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author	 Terence J. Grant<tjgrant@tatewake.com>
 */
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('DOKU_INCLUDE')) define('DOKU_INCLUDE',DOKU_INC.'inc/');
require_once(DOKU_PLUGIN . 'admin.php');

include_once(DOKU_PLUGIN.'backup/pref_code.php');

@include_once("Archive/Tar.php");	//PEAR Archive/Tar

/**
 * All DokuWiki plugins to extend the admin function
 * need to inherit from this class
 */
class admin_plugin_backup extends DokuWiki_Admin_Plugin
{
var $state = 0;
var $backup = '';

	/**
	 * Constructor
	 */
	function admin_plugin_backup()
	{
		$this->setupLocale();
	}

	/**
	 * return some info
	 */
	function getInfo()
	{
		if(method_exists(DokuWiki_Admin_Plugin,"getInfo")) {
			 return parent::getInfo(); /// this will grab the data from the plugin.info.txt
		} else
			// Otherwise return some hardcoded data for old dokuwikis
			return array(
				'author' => 'Terence J. Grant, Andreas Wagner',
				'email'  => 'tjgrant@tatewake.com, andreas.wagner@em.uni-frankfurt.de',
				'date'   => '??',
				'name'   => 'BackupTool for DokuWiki',
				'desc'   => 'A tool to backup your data and configuration.',
				'url'	=> 'http://www.dokuwiki.org/plugin:backup',
			);
	}

	/**
	 * return sort order for position in admin menu
	 */
	function getMenuSort()
	{
		return 999;
	}
	

	/**
	 * handle user request
	 */
	function handle()
	{
		$this->backup = $_REQUEST['backup'];
		if (is_array($this->backup))
		{
			$this->state = 1;
		} elseif (is_array($_POST['delete'])) {
			$this->state = 2;
		} else {
			$this->state = 0;
		}
	}

	function runPearBackup($files, $finalfile, $tarfilename, $basedir, $compress_type)
	{
		//Create archive object, add files, compile and compress.
		$tar = new Archive_Tar($finalfile,$compress_type);
		$result = $tar->createModify($files,'',$basedir);
		$tar->_Archive_Tar();
		
		return ($result) ? $tarfilename.'.'.$compress_type : '';	//return filename on success...
	}

	function runExecBackup($files, $tarfilename, $basename, $basedir)
	{
		$result = false;
		$i = 0;	//mark for first file
		$rval = 0;
	  //dbg("runExecBackup(".print_r($files,true).", '$tarfilename', '$basename', '$basedir')");
		
		// Put all to-be-tarred filenames into a manifest.
		$manifile = $tarfilename.'.manifest.txt';
		$manihandle = fopen($manifile, 'w');
		foreach($files as $item) fwrite($manihandle,$item."\n");
		fclose($manihandle);
		
		$tarfilename = escapeshellarg($tarfilename);
		$res = bt_exec("tar -cf $tarfilename -C ".escapeshellarg($basedir)." --files-from ".escapeshellarg($manifile));
		unlink($manifile);
		if (!$res)
			return ''; //tar failed (possibly out of memory)

		if (bt_exec('bzip2 --version'))
			if (bt_exec('bzip2 -9 '.$tarfilename)) return $basename.'.bz2';	//Bzip2 compression available.
		if (bt_exec('gzip --version'))
			if (bt_exec('gzip -9 '.$tarfilename)) return $basename.'.gz';	//Gzip compression available.
		return $basename;					//No compression available, but tar succeeded
	}

	/**
	 * output appropriate html
	 */
	function html()
	{
		global $conf;
		global $bt_loaded, $bt_settings;

		$bt_pearWorks = (class_exists("Archive_Tar")) ? true : false;
		$bt_execWorks = bt_exec("tar --version");

		// Where to put these files?
		$tarpath = $conf['mediadir'].'/'.strtr($this->getConf('backupnamespace'),':','/');
		
		if (!($bt_pearWorks || $bt_execWorks))	//if neither works, display the error message.
		{
			print $this->locale_xhtml('error');
		}
		else
		{
			//dbg(print_r($_REQUEST,true));
			if($this->state == 2) {
				$killsuccess = true;
				ob_flush(); flush();
				$extantbackups = glob($tarpath.'/dw-backup-*');
				foreach($extantbackups as $kill)
					if(unlink($kill)) {
						ptln('<div class="info">'.'Deleted file: '.htmlspecialchars($kill).'</div>');
					} else {
						$killsuccess = false;
						ptln('<div class="error">'.'Could not delete: '.htmlspecialchars($kill).'</div>');
					}
			}
			
			if ($this->state == 0 || $this->state == 2)
			{
				//Print Backup introduction page
				print $this->locale_xhtml('intro');
	
				ptln('<form action="'.wl($ID).'" method="post">');
				ptln('	<input type="hidden" name="do"   value="admin" />');
				ptln('	<input type="hidden" name="page" value="'.$this->getPluginName().'" />');
				print '<center>';
	
//				ptln('bt_settings[type] = '.$bt_settings['type'].'<br/>');
				ptln('	Backup method: <select name="backup[type]">');
				if ($bt_pearWorks == true) ptln('		<option value="PEAR" '.(strcmp($bt_settings['type'], 'PEAR') == 0 ? 'selected' : '').'>PEAR Archive Library</option>');
				if ($bt_execWorks == true) ptln('		<option value="exec" '.(strcmp($bt_settings['type'], 'exec') == 0 ? 'selected' : '').'>GNU Tar (filtered)</option>');
				if ($bt_execWorks == true) ptln('		<option value="lazy" '.(strcmp($bt_settings['type'], 'lazy') == 0 ? 'selected' : '').'>GNU Tar (fast;unfiltered)</option>');
				ptln('	</select><br/><br/>');

				print '<table class="inline">';
				print '	<tr><th> '.$this->getLang('bt_item_type').' </th><th> '.$this->getLang('bt_add_to_archive').' </th></tr>';
				print '	<tr><td> '.$this->getLang('bt_pages').' </td><td><input type="checkbox" name="backup[pages]" '.$bt_settings['pages'].'/></td></tr>';
				print '	<tr><td> '.$this->getLang('bt_revisions').' </td><td><input type="checkbox" name="backup[revisions]" '.$bt_settings['revisions'].'/></td></tr>';
				print '	<tr><td> '.$this->getLang('bt_subscriptions').'</td><td><input type="checkbox" name="backup[subscriptions]" '.$bt_settings['subscriptions'].'/></td></tr>';
				print '	<tr><td> '.$this->getLang('bt_media').' </td><td><input type="checkbox" name="backup[media]" '.$bt_settings['media'].'/></td></tr>';
				print '	<tr><td> '.$this->getLang('bt_config').' </td><td><input type="checkbox" name="backup[config]" '.$bt_settings['config'].'/></td></tr>';
				print '	<tr><td> '.$this->getLang('bt_templates').'</td><td><input type="checkbox" name="backup[templates]" '.$bt_settings['templates'].'/></td></tr>';
				print '	<tr><td> '.$this->getLang('bt_plugins').'</td><td><input type="checkbox" name="backup[plugins]" '.$bt_settings['plugins'].'/></td></tr>';
				print '</table>';

				print '<br />';
				print '<p><input type="submit" class="button" value="'.$this->getLang('bt_create_backup').'"></p></center>';
				print '</form>';
			}
			elseif ($this->state == 1)
			{
				//Save settings...
				$bt_settings['type']					= strcmp($this->backup['type'], 'PEAR') == 0 ? 'PEAR' :
																				 strcmp($this->backup['type'], 'exec') == 0 ? 'exec' : 'lazy';
				$bt_settings['pages']					= strcmp($this->backup['pages'], 'on') == 0 ? 'checked' : '';
				$bt_settings['revisions']			= strcmp($this->backup['revisions'], 'on') == 0 ? 'checked' : '';
				$bt_settings['subscriptions']	= strcmp($this->backup['subscriptions'], 'on') == 0 ? 'checked' : '';
				$bt_settings['media']					= strcmp($this->backup['media'], 'on') == 0 ? 'checked' : '';
				$bt_settings['config']				= strcmp($this->backup['config'], 'on') == 0 ? 'checked' : '';
				$bt_settings['templates']			= strcmp($this->backup['templates'], 'on') == 0 ? 'checked' : '';
				$bt_settings['plugins']				= strcmp($this->backup['plugins'], 'on') == 0 ? 'checked' : '';
				bt_save();
				
				//Print outgoing message...
				print $this->locale_xhtml('outro');
				
				ob_flush(); flush();

				//Generate file names
				$tarfilename = 'dw-backup-'.date('Ymd-His').".tar";
				$compress_type = (extension_loaded('bz2') ? 'bz2' : (extension_loaded('zlib') ? 'gz' : ''));
				$finalfile = $tarfilename.'.'.$compress_type;

				//Generate array of files
				$files = (array)NULL;
				
				if($this->backup['config'] && is_readable(DOKU_INC."inc/preload.php"))
					$files[] = DOKU_INC."inc/preload.php"; // the preload, if existant, is part of config.
					
				if (strcmp($this->backup['type'], 'lazy') == 0)	//Use fast lazy method
				{
					if ($this->backup['pages'])					$files = array_merge($files, array($conf['datadir']));
					if ($this->backup['revisions'])			$files = array_merge($files, array($conf['olddir']));
					if ($this->backup['subscriptions'])	$files = array_merge($files, array($conf['metadir']));
					if ($this->backup['config'])				$files = array_merge($files, array(DOKU_CONF));
					if ($this->backup['templates'])			$files = array_merge($files, array(DOKU_INC . "lib/tpl"));
					if ($this->backup['plugins'])				$files = array_merge($files, array(DOKU_INC . "lib/plugins"));
					if ($this->backup['media'])					$files = array_merge($files, array($conf['mediadir']));
				}
				else	//Use filtered files method
				{
					if ($this->backup['pages'])					$files = array_merge($files, directoryToArray($conf['datadir']));
					if ($this->backup['revisions'])			$files = array_merge($files, directoryToArray($conf['olddir']));
					if ($this->backup['subscriptions'])	$files = array_merge($files, directoryToArray($conf['metadir']));
					if ($this->backup['config'])				$files = array_merge($files, directoryToArray(DOKU_CONF));
					if ($this->backup['templates'])			$files = array_merge($files, directoryToArray(DOKU_INC . "lib/tpl"));
					if ($this->backup['plugins'])				$files = array_merge($files, directoryToArray(DOKU_INC . "lib/plugins"));
					if ($this->backup['media'])  				$files = array_merge($files, directoryToArray($conf['mediadir']));
				}

				// convert all filenames to canonical ones.
				$files = array_map('realpath',$files);
				
				// construct list of filtered paths
				$filterpaths = array_map('trim',explode("\n",$this->getConf('filterdirs')));
				if($this->getConf('filterbackups'))
					$filterpaths[] = $tarpath;
				foreach(array_keys($filterpaths) as $key) {
					if(!is_dir($filterpaths[$key]))
						unset($filterpaths[$key]); // remove non-directories
					else { // convert to realpath, check if path has trailing slash; if not, add one.
						$dir = realpath($filterpaths[$key]);
						if($dir[strlen($dir)-1] != DIRECTORY_SEPARATOR)
							$dir .= DIRECTORY_SEPARATOR;
						$filterpaths[$key] = $dir;
					}	
				}
				$this->filterdirs = array_combine($filterpaths,array_map('strlen',$filterpaths));
				// then filter away and sort.
				$this->filterresult = (array)NULL;
				$files = array_filter($files,array($this,'filterFile'));
				sort($files,SORT_LOCALE_STRING);
				
				// Compute the common directory -- this will be subtracted from the filenames.
				$basedir = dirname(substr($files[0],0,_commonPrefix($files)).'aaaaa');
				if($basedir[strlen($basedir)-1] != DIRECTORY_SEPARATOR)
					$basedir .= DIRECTORY_SEPARATOR;
				
				//Run the backup method
				$this->_mkpath($tarpath,$conf['dmode']);
				if (strcmp($this->backup['type'], 'PEAR') == 0)
					$finalfile = $this->runPearBackup($files, $tarpath.'/'.$finalfile, $tarfilename, $basedir, $compress_type);
				else	//exec and lazy both use the exec method
				{
					$this->_commonlength = strlen($basedir);
					$files = array_map(array($this,'getRelativePath'),$files);
					$finalfile = $this->runExecBackup($files, $tarpath.'/'.$tarfilename, $tarfilename, $basedir);
				}

				if ($finalfile == '')
				{
					print $this->locale_xhtml('memory');
				}
				else
				{
					print $this->locale_xhtml('download');
					print '<div class="success">';
					$filesize = round(filesize($tarpath.'/'.$finalfile)/1024.0);
					print $this->render_text('Download: {{:'.$this->getConf('backupnamespace').':'.$finalfile.'}} ('.$filesize.' kiB)');
					print '</div>';
					
					if(count($this->filterresult)>0) {
						ptln("Files not backed up (blacklisted):<ul>");
						foreach($this->filterresult as $dir => $num)
							ptln("<li>$num files under <tt>".htmlspecialchars($dir)."</tt></li>");
						ptln("</ul>");
					}
				}
				ob_flush(); flush();
			}
		}
		
		$extantbackups = glob($tarpath.'/dw-backup-*');
		if(count($extantbackups) > 0) {
			$buildrender = '';
			foreach ($extantbackups as $fname) {
				$filesize = round(filesize($fname)/1024.0);
				$buildrender .= '{{:'.$this->getConf('backupnamespace').':'.basename($fname).'}} ('.$filesize." kiB)\\\\\n";
			}
			print $this->locale_xhtml('oldbackups');
			ptln('<form action="'.wl($ID).'" method="post">');
			ptln('	<input type="hidden" name="do"   value="admin" />');
			ptln('	<input type="hidden" name="page" value="'.$this->getPluginName().'" />');
			ptln('<input type="submit" class="button" name="delete[all]" value="Delete"/>');
			print $this->render_text($buildrender);
			ptln('</form>');
		}
		
		print $this->locale_xhtml('donate');
	}
	
	// returns true if $fname is not in the filter list
	function filterFile($fname) {
		foreach($this->filterdirs as $dir=>$len)
			if(!strncmp($dir,$fname,$len)) {
				// dbg("filterFile($fname) -- FILTERED OUT");
				$this->filterresult[$dir] = isset($this->filterresult[$dir])?
						($this->filterresult[$dir]+1):1;
				return false; // $fname has $dir as prefix. filter it.
			}
		return true; // $fname does not match any prefix.
	}
	
	// subtract first few characters from $fname
	function getRelativePath($fname) {
		return substr($fname,$this->_commonlength);
	}

	function _mkpath($path,$dmask=0777)
	{
		if(@mkdir($path,$dmask) or file_exists($path)) return true;
		return ($this->_mkpath(dirname($path),$dmask) and mkdir($path,$dmask));
	}
}

function bt_exec($cmd)
{
	$oval = array();
	$rval = 0;
	exec($cmd, $oval, $rval);

	return (($rval == 0) ? true : false);	
}

/// Return length of longest common prefix in an array of strings.
function _commonPrefix($array) {
	if(count($array) < 2) {
		if(count($array) == 0)
			return false; // empty array: undefined prefix
		else
			return strlen($array[0]); // 1 element: trivial case
	}
	$len = max(array_map('strlen',$array)); // initial upper limit: max length of all strings.
	$prevval = reset($array);
	while(($newval = next($array)) !== FALSE) {
		for($j = 0 ; $j < $len ; $j += 1)
			if($newval[$j] != $prevval[$j])
				$len = $j;
		$prevval = $newval;
	}
	return $len;
}

// from http://snippets.dzone.com/posts/show/155 :
function directoryToArray($directory) {
	$array_items = array();
	if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != ".." && $file != "_dummy" && $file != "disabled") {
				$file = $directory . "/" . $file;
				if (is_dir($file)) {
					$array_items = array_merge($array_items, directoryToArray($file));
				} else {
					if(filesize($file) !== 0) $array_items[] = preg_replace("/\/\//si", "/", $file);
				}
			}
		}
		closedir($handle);
	}
	return $array_items;
}
