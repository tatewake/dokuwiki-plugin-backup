<?php
/**
 * english language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Terence J. Grant<tjgrant@tatewake.com>
 */
 
// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';
 
// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = 'Backup Tool';

$lang['bt_item_type'] = 'Item type';
$lang['bt_add_to_archive'] = 'Add to archive?';
$lang['bt_pages'] = 'Pages';
$lang['bt_revisions'] = 'Old Revisions of Pages';
$lang['bt_subscriptions'] = 'Meta data (Subscriptions)';
$lang['bt_media'] = 'Media files';
$lang['bt_config'] = 'Wiki/<acronym title="Access Control List">ACL</acronym>/User Config';
$lang['bt_templates'] = 'Templates';
$lang['bt_plugins'] = 'Plugins';
$lang['bt_create_backup'] = 'Create Backup';
$lang['bt_archiving'] = 'Archiving';
$lang['bt_compressing_archive'] = 'Compressing archive';

$lang['backupnamespace'] = 'Media namespace (e.g. <code>wiki:backup</code>) in which to store backup files.';
$lang['filterdirs'] = 'List of blacklisted directories, separated by newlines. These directories will not be
included in your backups. If the following checkbox is selected, then the backup namespace will be implicitly included in this list.';
$lang['filterbackups'] = 'Add backup namespace to blacklisted directory list? It is <em>highly</em> recommended
to keep this option checked, so your new backups don\'t include old backup files.';
