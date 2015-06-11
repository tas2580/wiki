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
    'NO_ARTICLE'						=> 'Der gewünschte Artikel existiert nicht.<br />Du bist nicht berechtigt den Artikel anzulegen.',
	'LAST_EDIT'					=> 'Zuletzt bearbeidet',
	'EDIT_WIKI'					=> 'Artikel bearbeiten',
	'VERSIONS_WIKI'				=> 'Versionen anzeigen',
	'WIKI'							=> 'Wiki',
	'BACK_TO_ARTICLE'				=> 'Zurück zum Artikel',
	'EDIT_ARTICLE_SUCCESS'			=> 'Der Artikel wurde erfolgreich bearbeidet',
	'VERSIONS_OF_ARTICLE'			=> 'Versionsgeschichte',
	'VERSION_COMPARE_HEADLINE'	=> 'Versionsunterschied von Version <a href="%3$s">%1$d</a> zu <a href="%4$s">%2$d</a>',
	'COMPARE'					=> 'Vergleichen',
	'COMPARE_EXPLAIN'				=> 'Hier werden alle Versionen des Artikels aufgelistet. Wähle zwei Versionen aus um sie zu vergleichen.',
	'VERSION'						=> 'Version',
	'TITLE'						=> 'Titel',
	'REASON_EDIT'					=> 'Änderungsgrund',
	'VIEW_DISCUSION'				=> 'Diskussion',
));
