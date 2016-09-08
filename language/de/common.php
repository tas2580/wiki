<?php
/**
*
* @package phpBB Extension - Wiki
 * @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}
// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//
$lang = array_merge($lang, array(
	'ARTICLES'						=> 'Artikel',
	'DATE'							=> 'Datum',
	'NO_ARTICLE'					=> 'Der gewünschte Artikel existiert nicht.<br>Du bist nicht berechtigt den Artikel anzulegen.',
	'NO_ARTICLE_REDIRECT'			=> '<strong>Artikel wurde ersetzt!</strong><br><br> Der gewünschte Artikel existiert nicht mehr. <br> Der Artikel wurde mit einem neuen Artikel ersetzt: <a href="%1$s">%2$s</a>.<br><br><hr>',
	'LAST_EDIT'						=> 'Zuletzt bearbeitet',
	'EDIT_WIKI'						=> 'Artikel bearbeiten',
	'VERSIONS_WIKI'					=> 'Versionen anzeigen',
	'WIKI'							=> 'Wiki',
	'BACK_TO_ARTICLE'				=> 'Zurück zum Artikel',
	'BACK_TO_WIKI'					=> 'Zurück zum Wiki',
	'EDIT_ARTICLE_SUCCESS'			=> 'Der Artikel wurde erfolgreich bearbeitet.',
	'EDIT_ARTICLE_SUCCESS_INACTIVE'	=> 'Der Artikel wurde erfolgreich bearbeitet, er muss aber noch freigeschaltet werden bevor er öffentlich angezeit wird.',
	'VERSIONS_OF_ARTICLE'			=> 'Versionsgeschichte',
	'VERSION_COMPARE_HEADLINE'		=> 'Versionsunterschied von Version <a href="%3$s">%1$d</a> zu <a href="%4$s">%2$d</a>',
	'COMPARE'						=> 'Vergleichen',
	'COMPARE_EXPLAIN'				=> 'Hier werden alle Versionen des Artikels aufgelistet. Wähle zwei Versionen aus um sie zu vergleichen.',
	'VERSION'						=> 'Version',
	'TITLE'							=> 'Titel',
	'REASON_EDIT'					=> 'Änderungsgrund',
	'VIEW_DISCUSION'				=> 'Diskussion',
	'TOPIC_ID'						=> 'Diskusions Thema ID',
	'TOPIC_ID_EXPLAIN'				=> 'Gib die ID des Themas an das als Diskusion zu dem Artikel dient.',
	'CONFIRM_DELETE_VERSION'		=> 'Bist du sicher dass du die Version endgültig löschen möchtest?',
	'DELETE_VERSION_SUCCESS'		=> 'Die Version wurde erfolgreich gelöscht',
	'NO_DELETE_ACTIVE_VERSION'		=> 'Du kannst die aktive Version eines Artikels nicht löschen!',
	'WIKI_FOOTER'					=> 'Wiki by <a href="%1$s">%2$s</a>',
	'SOURCES'						=> 'Quellen',
	'SOURCES_EXPLAIN'				=> 'Gib URLs als Quellen für den Artikel an. Schreibe jede URL in eine eigene Zeile.',
	'INVALID_SOURCE_URL'			=> 'Eine der Quellen ist keine gültige URL.',
	'ACTIVATE_VERSION_SUCCESS'		=> 'Die Artikel Version wurde erfolgreich als aktiv gesetzt.',
	'CONFIRM_ACTIVATE_VERSION'		=> 'Bist du sicher das du die ausgewählte Version als aktive Version für den Artikel setzen willst?',
	'ARTICLE_HAS_NEW'				=> 'Zu dem Artikel ist eine neuere Version vorhanden.',
	'SET_ACTIVE'					=> 'Setze aktiv',
	'IS_ACTIVE'						=> 'Aktive Version',
	'DELETE_ARTICLE'				=> 'Artikel löschen',
	'DELETE_VERSION'				=> 'Version löschen',
	'ARTICLE_DESCRIPTION'			=> 'Artikel Beschreibung',
	'ARTICLE_DESCRIPTION_EXPLAIN'	=> 'Gib eine kurze Beschreibung (max. 255 Zeichen) für den Artikel ein.',
	'SET_REDIRECT'					=> 'Artikel weiterleiten',
	'SET_REDIRECT_EXPLAIN'			=> 'Gib hier einen anderen Artikel an um den Artikel dort hin weiter zu leiten.',
	'CONFIRM_DELETE_ARTICLE'		=> 'Bist du sicher das du den Artikel <strong>und alle seine Versionen</strong> unwiederruflich löschen möchtest?',
	'DELETE_ARTICLE_SUCCESS'		=> 'Der Artikel wurde erfolgreich gelöscht!',
	'NO_ARTICLE_DIFF'				=> 'Es gibt keine Veränderungen an dem Artikel.',
	'NO_SOURCE_DIFF'				=> 'Die Quellen wurden nicht geändert.',
	'SET_STICKY'					=> 'Artikel ist wichtig',
	'SET_INACTIV'					=> 'Artikel inaktiv setzen',
	'CONFIRM_DEACTIVATE_ARTICLE'	=> 'Bist du sicher das du den kompletten Artikel inaktiv setzen willst?',
	'DEACTIVATE_ARTICLE_SUCCESS'	=> 'Der Artikel wurde auf inaktiv gesetzt!',
	'TOTAL_ITEMS'		=>  array(
			1 => '1 Eintrag',
			2 => '%s Einträge',
		),
	'ARTICLE_VIEWS_TEXT'			=> 'Der Artikel wurde <strong>%d</strong> mal angeschaut.'
));
