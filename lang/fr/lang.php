<?php
/**
 * french language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Guillaume Turri <guillaume.turri@gmail.com>
 * @author     Olivier Humbert <trebmuh@tuxfamily.org>
 */
 
// settings must be present and set appropriately for the language
$lang['encoding']   = 'utf-8';
$lang['direction']  = 'ltr';
 
// for admin plugins, the menu prompt to be displayed in the admin menu
// if set here, the plugin doesn't need to override the getMenuText() method
$lang['menu'] = 'Outil de sauvegarde';

$lang['bt_item_type'] = 'Type de données';
$lang['bt_add_to_archive'] = 'Ajouter à l\'archive ?';
$lang['bt_pages'] = 'Pages';
$lang['bt_revisions'] = 'Anciennes Révisions des Pages';
$lang['bt_subscriptions'] = 'Méta données (Soumissions)';
$lang['bt_media'] = 'Fichiers Média';
$lang['bt_config'] = 'Wiki/<acronym title="Access Control List">ACL</acronym>/Configuration';
$lang['bt_templates'] = 'Templates';
$lang['bt_plugins'] = 'Plugins';
$lang['bt_create_backup'] = 'Créer la sauvegarde';
$lang['bt_archiving'] = 'Archivage';
$lang['bt_compressing_archive'] = 'Compression de l\'archive';

$lang['backupnamespace'] = 'Endroit (exemple <code>wiki:backup</code>) où placer les fichiers de sauvegarde.';
$lang['filterdirs'] = 'Liste des répertoires black-listés, une ligne par répertoire. Ces répertoires ne seront pas 
inclus dans vos sauvegardes. Si la case-à-cocher suivante est sélectionnée, alors le répertoire de sauvegarde sera
automatiquement inclu dans la liste.';
$lang['filterbackups'] = 'Ajouter le répertoire de sauvegarde dans la liste des répertoires ? Il est <em>très
chaudement</em> recommendé de cocher cette case. Ainsi, les nouvelles sauvegardes n\'incluent pas les anciens
fichiers de sauvegarde.';
