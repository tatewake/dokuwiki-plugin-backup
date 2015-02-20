<?php
/**
 * German language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Terence J. Grant<tjgrant@tatewake.com>
 * @translator Martin "Chaoticer" Betz<chaoticer@live.de>
 */
 
// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';
 
// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = 'Backup Tool';

$lang['bt_item_type'] = 'Inhaltstyp';
$lang['bt_add_to_archive'] = 'Zum Archiv hinzufügen?';
$lang['bt_pages'] = 'Seiten';
$lang['bt_revisions'] = 'Ältere Versionen der Seiten';
$lang['bt_subscriptions'] = 'Meta Daten (Abonnements)';
$lang['bt_media'] = 'Media Dateien';
$lang['bt_config'] = 'Wiki/<acronym title="Access Control List">ACL</acronym>/User Config';
$lang['bt_templates'] = 'Templates';
$lang['bt_plugins'] = 'Plugins';
$lang['bt_create_backup'] = 'Sicherung starten';
$lang['bt_archiving'] = 'Archivieren';
$lang['bt_compressing_archive'] = 'Archiv komprimieren';

$lang['backupnamespace'] = 'Media Namensraum (z.B. <code>wiki:backup</code>) in dem die Sicherungskopien gespeichert werden.';
$lang['filterdirs'] = 'Liste von Verzeichnissen auf der Blacklist, getrennt von Zeilenumbrüchen. Diese Verzeichnisse
werden nicht in Ihre Backups integriert. Wenn Sie die folgende Checkbox aktivieren, so wird der Backup Namensraum
implizit zu dieser Liste hinzugefügt.';
$lang['filterbackups'] = 'Backup Namensraum zur Verzeichnisblacklist hinzufügen? Es wird <em>stark</em> empfohlen,
diese Option aktiv zu lassen, damit neue Sicherungen keine alten Sicherungskopien enthalten.';
