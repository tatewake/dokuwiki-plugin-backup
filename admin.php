<?php

/**
 * Backup Tool for DokuWiki
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Terence J. Grant<tjgrant@tatewake.com>
 */
class admin_plugin_backup extends DokuWiki_Admin_Plugin
{
    var $state = 0;
    var $backup = '';

    protected $prefFile = DOKU_CONF . 'backup.json';

    /** @inheritdoc */
    public function handle()
    {
        global $INPUT;
        if ($INPUT->post->has('pref') && checkSecurityToken()) {
            $this->savePreferences($INPUT->post->arr('pref'));
        }
    }

    /**
     * output appropriate html
     */
    public function html()
    {
        global $conf;
        global $INPUT;

        if ($INPUT->post->bool('backup')) {
            echo $this->locale_xhtml('outro');
            tpl_flush();
            try {
                $this->createBackup($this->loadPreferences(), 'msg');
            } catch (\splitbrain\PHPArchive\ArchiveIOException $e) {
                msg('Backup failed. ' . $e->getMessage(), -1);
            }
        } else {
            echo $this->locale_xhtml('intro');
            echo $this->getForm();
        }


        {
            if ($this->state == 0 || $this->state == 2) {
            } elseif ($this->state == 1) {

                //Generate array of files
                $files = (array)NULL;

                if ($this->backup['config'] && is_readable(DOKU_INC . "inc/preload.php"))
                    $files[] = DOKU_INC . "inc/preload.php"; // the preload, if existant, is part of config.

                if (strcmp($this->backup['type'], 'lazy') == 0)    //Use fast lazy method
                {
                    if ($this->backup['pages']) $files = array_merge($files, array($conf['datadir']));
                    if ($this->backup['revisions']) $files = array_merge($files, array($conf['olddir']));
                    if ($this->backup['subscriptions']) $files = array_merge($files, array($conf['metadir']));
                    if ($this->backup['config']) $files = array_merge($files, array(DOKU_CONF));
                    if ($this->backup['templates']) $files = array_merge($files, array(DOKU_INC . "lib/tpl"));
                    if ($this->backup['plugins']) $files = array_merge($files, array(DOKU_INC . "lib/plugins"));
                    if ($this->backup['media']) $files = array_merge($files, array($conf['mediadir']));
                } else    //Use filtered files method
                {
                    if ($this->backup['pages']) $files = array_merge($files, directoryToArray($conf['datadir']));
                    if ($this->backup['revisions']) $files = array_merge($files, directoryToArray($conf['olddir']));
                    if ($this->backup['subscriptions']) $files = array_merge($files, directoryToArray($conf['metadir']));
                    if ($this->backup['config']) $files = array_merge($files, directoryToArray(DOKU_CONF));
                    if ($this->backup['templates']) $files = array_merge($files, directoryToArray(DOKU_INC . "lib/tpl"));
                    if ($this->backup['plugins']) $files = array_merge($files, directoryToArray(DOKU_INC . "lib/plugins"));
                    if ($this->backup['media']) $files = array_merge($files, directoryToArray($conf['mediadir']));
                }

                // convert all filenames to canonical ones.
                $files = array_map('realpath', $files);

                // construct list of filtered paths
                $filterpaths = array_map('trim', explode("\n", $this->getConf('filterdirs')));
                if ($this->getConf('filterbackups'))
                    $filterpaths[] = $tarpath;
                foreach (array_keys($filterpaths) as $key) {
                    if (!is_dir($filterpaths[$key]))
                        unset($filterpaths[$key]); // remove non-directories
                    else { // convert to realpath, check if path has trailing slash; if not, add one.
                        $dir = realpath($filterpaths[$key]);
                        if ($dir[strlen($dir) - 1] != DIRECTORY_SEPARATOR)
                            $dir .= DIRECTORY_SEPARATOR;
                        $filterpaths[$key] = $dir;
                    }
                }
                $this->filterdirs = array_combine($filterpaths, array_map('strlen', $filterpaths));
                // then filter away and sort.
                $this->filterresult = (array)NULL;
                $files = array_filter($files, array($this, 'filterFile'));
                sort($files, SORT_LOCALE_STRING);

                // Compute the common directory -- this will be subtracted from the filenames.
                $basedir = dirname(substr($files[0], 0, _commonPrefix($files)) . 'aaaaa');
                if ($basedir[strlen($basedir) - 1] != DIRECTORY_SEPARATOR)
                    $basedir .= DIRECTORY_SEPARATOR;

                //Run the backup method
                $this->_mkpath($tarpath, $conf['dmode']);
                if (strcmp($this->backup['type'], 'PEAR') == 0)
                    $finalfile = $this->runPearBackup($files, $tarpath . '/' . $finalfile, $tarfilename, $basedir, $compress_type);
                else    //exec and lazy both use the exec method
                {
                    $this->_commonlength = strlen($basedir);
                    $files = array_map(array($this, 'getRelativePath'), $files);
                    $finalfile = $this->runExecBackup($files, $tarpath . '/' . $tarfilename, $tarfilename, $basedir);
                }

                if ($finalfile == '') {
                    print $this->locale_xhtml('memory');
                } else {
                    print $this->locale_xhtml('download');
                    print '<div class="success">';
                    $filesize = round(filesize($tarpath . '/' . $finalfile) / 1024.0);
                    print $this->render_text('Download: {{:' . $this->getConf('backupnamespace') . ':' . $finalfile . '}} (' . $filesize . ' kiB)');
                    print '</div>';

                    if (count($this->filterresult) > 0) {
                        ptln("Files not backed up (blacklisted):<ul>");
                        foreach ($this->filterresult as $dir => $num)
                            ptln("<li>$num files under <tt>" . htmlspecialchars($dir) . "</tt></li>");
                        ptln("</ul>");
                    }
                }
                ob_flush();
                flush();
            }

            $extantbackups = glob($tarpath . '/dw-backup-*');
            if (count($extantbackups) > 0) {
                $buildrender = '';
                foreach ($extantbackups as $fname) {
                    $filesize = round(filesize($fname) / 1024.0);
                    $buildrender .= '{{:' . $this->getConf('backupnamespace') . ':' . basename($fname) . '}} (' . $filesize . " kiB)\\\\\n";
                }
                print $this->locale_xhtml('oldbackups');
                ptln('<form action="' . wl($ID) . '" method="post">');
                ptln('	<input type="hidden" name="do"   value="admin" />');
                ptln('	<input type="hidden" name="page" value="' . $this->getPluginName() . '" />');
                ptln('<input type="submit" class="button" name="delete[all]" value="Delete"/>');
                print $this->render_text($buildrender);
                ptln('</form>');
            }
        }


        print $this->locale_xhtml('donate');

    }

    /**
     * Generate a new unique backup name
     *
     * @return string
     */
    protected function createBackupName()
    {
        $tarfilename = 'dw-backup-' . date('Ymd-His') . '.tar';
        if (extension_loaded('bz2')) {
            $tarfilename .= '.bz2';
        } elseif (extension_loaded('gz')) {
            $tarfilename .= '.gz';
        }
        return mediaFN($this->getConf('backupnamespace') . ':' . $tarfilename);
    }

    /**
     * Create the backup
     *
     * @param array $prefs
     * @param Callable $logger A method compatible to DokuWiki's msg()
     * @throws \splitbrain\PHPArchive\ArchiveIOException
     */
    protected function createBackup($prefs, $logger)
    {
        @set_time_limit(0);
        $fn = $this->createBackupName();
        $logger("Creating $fn", 0);
        io_mkdir_p(dirname($fn));
        $tar = new \splitbrain\PHPArchive\Tar();
        $tar->create($fn);

        foreach ($prefs as $pref => $val) {
            if (!$val) continue;

            $cmd = [$this, 'backup' . ucfirst($pref)];
            if (is_callable($cmd)) {
                $cmd($tar, $logger);
            } else {
                $logger('Can\'t call ' . $cmd[1], -1);
            }
        }

        $tar->close();
    }

    /**
     * Create the preference form
     *
     * @return string
     */
    protected function getForm()
    {
        global $ID;
        $form = new \dokuwiki\Form\Form([
            'method' => 'POST',
            'action' => wl($ID, ['do' => 'admin', 'page' => 'backup'], false, '&')
        ]);
        $form->addFieldsetOpen($this->getLang('components'));

        $prefs = $this->loadPreferences();
        foreach ($prefs as $pref => $val) {
            $form->setHiddenField("pref[$pref]", '0');
            $cb = $form->addCheckbox("pref[$pref]", $this->getLang('bt_' . $pref))->useInput(false)->addClass('block');
            if ($val) $cb->attr('checked', 'checked');
        }

        $form->addButton('backup', $this->getLang('bt_create_backup'));
        return $form->toHTML();
    }

    /**
     * Get the currently saved preferences
     *
     * @return array
     */
    protected function loadPreferences()
    {
        // FIXME set sensible defaults
        // FIXME these selections may not be the most sensible
        $prefs = [
            'pages' => 1,
            'revisions' => 1,
            'subscriptions' => 1,
            'media' => 1,
            'config' => 1,
            'templates' => 1,
            'plugins' => 1
        ];
        // load and merge saved preferences
        if (file_exists($this->prefFile)) {
            $more = json_decode(io_readFile($this->prefFile, false), true);
            $prefs = array_merge($prefs, $more);
        }

        return $prefs;
    }

    /**
     * Store the backup preferences
     *
     * @param array $prefs
     */
    protected function savePreferences($prefs)
    {
        $prefs = array_map('intval', $prefs);
        io_saveFile($this->prefFile, json_encode($prefs, JSON_PRETTY_PRINT));
    }


    protected function runPearBackup($files, $finalfile, $tarfilename, $basedir, $compress_type)
    {
        //Create archive object, add files, compile and compress.
        $tar = new Archive_Tar($finalfile, $compress_type);
        $result = $tar->createModify($files, '', $basedir);
        $tar->_Archive_Tar();

        return ($result) ? $tarfilename . '.' . $compress_type : '';    //return filename on success...
    }

    protected function runExecBackup($files, $tarfilename, $basename, $basedir)
    {
        $result = false;
        $i = 0;    //mark for first file
        $rval = 0;
        //dbg("runExecBackup(".print_r($files,true).", '$tarfilename', '$basename', '$basedir')");

        // Put all to-be-tarred filenames into a manifest.
        $manifile = $tarfilename . '.manifest.txt';
        $manihandle = fopen($manifile, 'w');
        foreach ($files as $item) fwrite($manihandle, $item . "\n");
        fclose($manihandle);

        $tarfilename = escapeshellarg($tarfilename);
        $res = bt_exec("tar -cf $tarfilename -C " . escapeshellarg($basedir) . " --files-from " . escapeshellarg($manifile));
        unlink($manifile);
        if (!$res)
            return ''; //tar failed (possibly out of memory)

        if (bt_exec('bzip2 --version'))
            if (bt_exec('bzip2 -9 ' . $tarfilename)) return $basename . '.bz2';    //Bzip2 compression available.
        if (bt_exec('gzip --version'))
            if (bt_exec('gzip -9 ' . $tarfilename)) return $basename . '.gz';    //Gzip compression available.
        return $basename;                    //No compression available, but tar succeeded
    }

    // returns true if $fname is not in the filter list
    protected function filterFile($fname)
    {
        foreach ($this->filterdirs as $dir => $len)
            if (!strncmp($dir, $fname, $len)) {
                // dbg("filterFile($fname) -- FILTERED OUT");
                $this->filterresult[$dir] = isset($this->filterresult[$dir]) ?
                    ($this->filterresult[$dir] + 1) : 1;
                return false; // $fname has $dir as prefix. filter it.
            }
        return true; // $fname does not match any prefix.
    }

    // subtract first few characters from $fname
    protected function getRelativePath($fname)
    {
        return substr($fname, $this->_commonlength);
    }

    protected function _mkpath($path, $dmask = 0777)
    {
        if (@mkdir($path, $dmask) or file_exists($path)) return true;
        return ($this->_mkpath(dirname($path), $dmask) and mkdir($path, $dmask));
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
function _commonPrefix($array)
{
    if (count($array) < 2) {
        if (count($array) == 0)
            return false; // empty array: undefined prefix
        else
            return strlen($array[0]); // 1 element: trivial case
    }
    $len = max(array_map('strlen', $array)); // initial upper limit: max length of all strings.
    $prevval = reset($array);
    while (($newval = next($array)) !== FALSE) {
        for ($j = 0; $j < $len; $j += 1)
            if ($newval[$j] != $prevval[$j])
                $len = $j;
        $prevval = $newval;
    }
    return $len;
}

// from http://snippets.dzone.com/posts/show/155 :
function directoryToArray($directory)
{
    $array_items = array();
    if ($handle = opendir($directory)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && $file != "_dummy" && $file != "disabled") {
                $file = $directory . "/" . $file;
                if (is_dir($file)) {
                    $array_items = array_merge($array_items, directoryToArray($file));
                } else {
                    if (filesize($file) !== 0) $array_items[] = preg_replace("/\/\//si", "/", $file);
                }
            }
        }
        closedir($handle);
    }
    return $array_items;
}
