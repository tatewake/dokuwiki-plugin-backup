<?php

use splitbrain\PHPArchive\Tar;

/**
 * Backup Tool for DokuWiki
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Terence J. Grant<tjgrant@tatewake.com>
 * @author     Andreas Wagner <andreas.wagner@em.uni-frankfurt.de>
 * @author     Andreas Gohr <gohr@cosmocode.de>
 */
class admin_plugin_backup extends DokuWiki_Admin_Plugin
{
    protected $prefFile = DOKU_CONF . 'backup.json';
    protected $filters = null;

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
        global $INPUT;

        echo '<div class="plugin_backup">';

        if ($INPUT->post->bool('backup')) {
            $this->runBackup();
        } else {
            echo $this->locale_xhtml('intro');
            echo $this->getForm();
            $this->listBackups();
        }

        echo $this->locale_xhtml('donate');
        echo '</div>';
    }

    /**
     * Lists the 5 most recent backups if any.
     */
    protected function listBackups()
    {
        global $ID;
        $ns = $this->getConf('backupnamespace');
        $link = wl($ID, ['do' => 'media', 'ns' => $ns]);

        echo '<div class="recent">';

        $backups = glob(dirname(mediaFN("$ns:foo")) . '/*.tar*');
        rsort($backups);
        $backups = array_slice($backups, 0, 5);
        if ($backups) {
            echo '<h2>' . $this->getLang('recent') . '</h2>';
            echo '<ul>';
            foreach ($backups as $full) {
                $backup = basename($full);
                $url = ml("$ns:$backup");
                echo '<li><div class="li">';
                echo '<a href="' . $url . '">' . $backup . '</a> ';
                echo filesize_h(filesize($full));
                echo ' ';
                echo dformat(filemtime($full), '%f');
                echo '</div></li>';
            }
            echo '</ul>';
        }

        echo '<p>' . sprintf($this->getLang('medians'), $ns, $link) . '</p>';
        echo '</div>';
    }

    /**
     * Runs the backup process with XHTML output
     */
    protected function runBackup()
    {
        echo '<h1>' . $this->getLang('menu') . '</h1>';
        echo '<p class="running">';
        echo hsc($this->getLang('running'));
        echo '&nbsp;';
        echo '<img src="' . DOKU_BASE . 'lib/plugins/backup/spinner.gif" alt="…" />';
        echo '</p>';

        $id = $this->createBackupID();
        $fn = mediaFN($id);
        try {
            echo '<div class="log">';
            echo '<script>plugin_backup.start();</script>';
            tpl_flush();
            $this->createBackup($fn, $this->loadPreferences(), [$this, 'logXHTML']);
            echo '</div>';
            msg(sprintf($this->getLang('success'), ml($id), $id), 1);
        } catch (\splitbrain\PHPArchive\ArchiveIOException $e) {
            echo '</div>'; // close the log wrapping
            msg('Backup failed. ' . $e->getMessage(), -1);
            @unlink($fn);
        }

        echo '<script>plugin_backup.stop();</script>';
    }

    /**
     * The logger to output the progress of the backup
     *
     * We want the filenames a little bit less prominent, so we handle those differently
     *
     * @param string $msg
     * @param int $level
     */
    protected function logXHTML($msg, $level = 0)
    {
        if ($level === -1 || $level === 1) {
            msg(hsc($msg), $level);
        } else {
            echo '<div>' . hsc($msg) . '</div>';
        }
        ob_flush();
        flush();
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
            $label = $this->getLang('bt_' . $pref);
            if (!$label) continue; // unknown pref, skip it

            $form->setHiddenField("pref[$pref]", '0');
            $cb = $form->addCheckbox("pref[$pref]", $label)->useInput(false)->addClass('block');
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
        $prefs = [
            'config' => 1,
            'pages' => 1,
            'revisions' => 1,
            'meta' => 1,
            'media' => 1,
            'mediarevs' => 0,
            'mediameta' => 1,
            'templates' => 0,
            'plugins' => 0
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

    /**
     * Generate a new unique backup name
     *
     * @return string
     */
    protected function createBackupID()
    {
        $tarfilename = 'dw-backup-' . date('Ymd-His') . '.tar';
        if (extension_loaded('bz2')) {
            $tarfilename .= '.bz2';
        } elseif (extension_loaded('gz')) {
            $tarfilename .= '.gz';
        }
        return cleanID($this->getConf('backupnamespace') . ':' . $tarfilename);
    }

    /**
     * Create the backup
     *
     * @param string $fn Filename of the backup archive
     * @param array $prefs
     * @param Callable $logger A method compatible to DokuWiki's msg()
     * @throws \splitbrain\PHPArchive\ArchiveIOException
     */
    protected function createBackup($fn, $prefs, $logger)
    {
        @set_time_limit(0);
        io_mkdir_p(dirname($fn));
        $tar = new Tar();
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
     * Adds the given directory recursively to the tar archive
     *
     * @param Tar $tar
     * @param string $dir The original directory
     * @param string $as The directory name to use in the archive
     * @param Callable|null $logger msg() compatible logger
     * @param Callable|null $filter a filter method, returns true for all files to add
     * @throws \splitbrain\PHPArchive\ArchiveCorruptedException
     * @throws \splitbrain\PHPArchive\ArchiveIOException
     */
    protected function addDirectoryToTar(Tar $tar, $dir, $as, $logger = null, $filter = null)
    {
        $dir = fullpath($dir);
        $ri = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS);
        $rii = new RecursiveIteratorIterator($ri, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($rii as $path => $info) {
            $file = $this->stripPrefix($path, $dir);
            $file = $as . '/' . $file;

            // custom filter:
            if ($filter !== null && !$filter($file)) continue;
            if (!$this->defaultFilter($file)) continue;

            if ($logger !== null) $logger($file);
            $tar->addFile($path, $file);
        }
    }

    /**
     * Checks the default filters against the given backup path
     *
     * We also filter .git directories
     *
     * @param string $path the backup path
     * @return bool true if the file should be backed up, false if not
     */
    protected function defaultFilter($path)
    {
        if ($this->filters === null) {
            $this->filters = explode("\n", $this->getConf('filterdirs'));
            $this->filters = array_map('trim', $this->filters);
            $this->filters = array_filter($this->filters);
        }

        if (strpos($path, '/.git') !== false) return false;

        foreach ($this->filters as $filter) {
            if (strpos($path, $filter) === 0) return false;
        }

        return true;
    }

    /**
     * Strip the given prefix from the directory
     *
     * @param string $dir
     * @param string $prefix
     * @return string
     */
    protected function stripPrefix($dir, $prefix)
    {
        if (strpos($dir, $prefix) === 0) {
            $dir = substr($dir, strlen($prefix));
        }
        return ltrim($dir, '/');
    }

    // region backup components

    /**
     * Backup the config files
     *
     * @param Tar $tar
     * @param Callable $logger
     * @throws \splitbrain\PHPArchive\ArchiveCorruptedException
     * @throws \splitbrain\PHPArchive\ArchiveIOException
     */
    protected function backupConfig(Tar $tar, $logger)
    {
        $this->addDirectoryToTar($tar, DOKU_CONF, 'conf', $logger, function ($path) {
            return !preg_match('/\.(dist|example|bak)/', $path);
        });
        // we consider the preload a config file
        if (file_exists(DOKU_INC . 'inc/preload.php')) {
            $tar->addFile(DOKU_INC . 'inc/preload.php', 'inc/preload.php');
        }
    }

    /**
     * Backup the pages
     *
     * @param Tar $tar
     * @param Callable $logger
     * @throws \splitbrain\PHPArchive\ArchiveCorruptedException
     * @throws \splitbrain\PHPArchive\ArchiveIOException
     */
    protected function backupPages(Tar $tar, $logger)
    {
        global $conf;
        $this->addDirectoryToTar($tar, $conf['datadir'], 'data/pages', $logger);
    }

    /**
     * Backup the page revisions
     *
     * @param Tar $tar
     * @param Callable $logger
     * @throws \splitbrain\PHPArchive\ArchiveCorruptedException
     * @throws \splitbrain\PHPArchive\ArchiveIOException
     */
    protected function backupRevisions(Tar $tar, $logger)
    {
        global $conf;
        $this->addDirectoryToTar($tar, $conf['olddir'], 'data/attic', $logger);
    }

    /**
     * Backup the meta files
     *
     * @param Tar $tar
     * @param Callable $logger
     * @throws \splitbrain\PHPArchive\ArchiveCorruptedException
     * @throws \splitbrain\PHPArchive\ArchiveIOException
     */
    protected function backupMeta(Tar $tar, $logger)
    {
        global $conf;
        $this->addDirectoryToTar($tar, $conf['metadir'], 'data/meta', $logger);
    }

    /**
     * Backup the media files
     *
     * @param Tar $tar
     * @param Callable $logger
     * @throws \splitbrain\PHPArchive\ArchiveCorruptedException
     * @throws \splitbrain\PHPArchive\ArchiveIOException
     */
    protected function backupMedia(Tar $tar, $logger)
    {
        global $conf;

        // figure out what our backup folder would be called within the backup
        $media = fullpath(dirname(mediaFN('foo')));
        $self = fullpath(dirname(mediaFN($this->getConf('backupnamespace') . ':foo')));
        $relself = 'data/media/' . $this->stripPrefix($self, $media);

        $this->addDirectoryToTar($tar, $conf['mediadir'], 'data/media', $logger, function ($path) use ($relself) {
            // skip our own backups
            return (strpos($path, $relself) !== 0);
        });
    }

    /**
     * Backup the media revisions
     *
     * @param Tar $tar
     * @param Callable $logger
     * @throws \splitbrain\PHPArchive\ArchiveCorruptedException
     * @throws \splitbrain\PHPArchive\ArchiveIOException
     */
    protected function backupMediarevs(Tar $tar, $logger)
    {
        global $conf;
        $this->addDirectoryToTar($tar, $conf['mediaolddir'], 'data/media_attic', $logger);
    }

    /**
     * Backup the media meta info
     *
     * @param Tar $tar
     * @param Callable $logger
     * @throws \splitbrain\PHPArchive\ArchiveCorruptedException
     * @throws \splitbrain\PHPArchive\ArchiveIOException
     */
    protected function backupMediameta(Tar $tar, $logger)
    {
        global $conf;
        $this->addDirectoryToTar($tar, $conf['mediametadir'], 'data/media_meta', $logger);
    }

    /**
     * Backup the templates
     *
     * @param Tar $tar
     * @param Callable $logger
     * @throws \splitbrain\PHPArchive\ArchiveCorruptedException
     * @throws \splitbrain\PHPArchive\ArchiveIOException
     */
    protected function backupTemplates(Tar $tar, $logger)
    {
        // FIXME skip builtin ones
        $this->addDirectoryToTar($tar, DOKU_INC . 'lib/tpl', 'lib/tpl', $logger);
    }

    /**
     * Backup the plugins
     *
     * @param Tar $tar
     * @param Callable $logger
     * @throws \splitbrain\PHPArchive\ArchiveCorruptedException
     * @throws \splitbrain\PHPArchive\ArchiveIOException
     */
    protected function backupPlugins(Tar $tar, $logger)
    {
        // FIXME skip builtin ones
        $this->addDirectoryToTar($tar, DOKU_INC . 'lib/plugins', 'lib/plugins', $logger);
    }

    // endregion

}
