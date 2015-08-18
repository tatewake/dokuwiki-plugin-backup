<?php
/**
 * german language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Florian Straub <flominator@gmx.net>
 */
 
// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';
 
// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = 'Backup Tool';

$lang['bt_item_type'] = 'Objekttyp';
$lang['bt_add_to_archive'] = 'Zum Archiv hinzufügen?';
$lang['bt_pages'] = 'Seiten';
$lang['bt_revisions'] = 'Ältere Versionen von Seiten';
$lang['bt_subscriptions'] = 'Metadaten (Subscriptions)';
$lang['bt_media'] = 'Mediendateien';
$lang['bt_config'] = 'Wiki/<acronym title="Access Control List">ACL</acronym>/User Config';
$lang['bt_templates'] = 'Vorlagen';
$lang['bt_plugins'] = 'Plugins';
$lang['bt_create_backup'] = 'Backup erstellen';
$lang['bt_archiving'] = 'Archiv wird erstellt';
$lang['bt_compressing_archive'] = 'Archiv wird komprimiert';
$lang['bt_blacklisted'] = 'Nicht gesicherte Dateien (ausgeschlossene Verzeichnisse)';
$lang['backupnamespace'] = 'Media namespace (e.g. <code>wiki:backup</code>) in which to store backup files.';
$lang['filterdirs'] = 'Ausgeschlossene Verzeichnisse, durch Zeilenumbruch getrennt. Diese Verzeichnisse werden nicht in Ihren Backups enthalten sein. Wenn die folgende Checkbox aktiviert ist, so wird der Backup-Namensraum implizit in diese Liste aufgenommen.';
$lang['filterbackups'] = 'Backup-Namensraum zu ausgeschlossenen Verzeichnissen hinzufügen? Es wird <em>wärmstens</em> empfohlen, diese Option zu aktivieren, damit Ihre neuen Sicherungen nicht die alten enthalten.';
