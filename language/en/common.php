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
	'NO_ARTICLE'					=> 'The selected article does not exist.',
	'NO_ARTICLE_REDIRECT'			=> '<strong>Artikel wurde ersetzt!</strong><br><br> Der gewünschte Artikel existiert nicht mehr. <br> Der Artikel wurde mit einem neuen Artikel ersetzt: <a href="%1$s">%1$s</a>.<br><br>',
	'LAST_EDIT'					=> 'Last modified',
	'EDIT_WIKI'					=> 'Edit article',
	'VERSIONS_WIKI'				=> 'View versions',
	'WIKI'							=> 'Wiki',
	'BACK_TO_ARTICLE'				=> 'Back to articke',
	'BACK_TO_WIKI'					=> 'Back to Wiki',
	'EDIT_ARTICLE_SUCCESS'			=> 'The article has been successfully edited',
	'VERSIONS_OF_ARTICLE'			=> 'Version history',
	'VERSION_COMPARE_HEADLINE'	=> 'Diff of version <a href="%3$s">%1$d</a> to <a href="%4$s">%2$d</a>',
	'COMPARE'					=> 'Compare',
	'COMPARE_EXPLAIN'				=> 'Here will be listed all versions of the article. Choose from two versions to compare them.',
	'VERSION'						=> 'Version',
	'TITLE'						=> 'Title',
	'REASON_EDIT'					=> 'Reason for change',
	'VIEW_DISCUSION'				=> 'Discussion',
	'TOPIC_ID'						=> 'Discussion topic ID',
	'CONFIRM_DELETE_VERSION'		=> 'Are you sure you want to delete the version?',
	'DELETE_VERSION_SUCCESS'		=> 'The version has been deleted successfully',
	'WIKI_FOOTER'					=> 'Wiki by <a href="%1$s">%2$s</a>'
));
