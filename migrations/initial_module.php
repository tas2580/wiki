<?php
/**
*
* @package phpBB Extension - Wiki
 * @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tas2580\wiki\migrations;

class initial_module extends \phpbb\db\migration\migration
{
	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'wiki_article'	=> array(
					'COLUMNS'	=> array(
						'article_id'				=> array('UINT', null, 'auto_increment'),
						'article_title'			=> array('VCHAR:255', ''),
						'article_url'				=> array('VCHAR:255', ''),
						'article_text'			=> array('MTEXT_UNI', ''),
						'bbcode_uid'			=> array('VCHAR:10', ''),
						'bbcode_bitfield'		=> array('VCHAR:32', ''),
						'article_approved'		=> array('BOOL', 0),
						'article_user_id'			=> array('UINT', 0),
						'article_last_edit'		=> array('TIMESTAMP', 0),
						'article_edit_reason'		=> array('VCHAR:255', ''),
						'article_topic_id'			=> array('UINT', 0),
					),
					'PRIMARY_KEY'	=> 'article_id',
				),
			),
		);
	}
	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'wiki_article',
			),
		);
	}
	public function update_data()
	{
		return array(
			array('permission.add', array('u_wiki_edit', true, 'a_board')),
			array('permission.add', array('u_wiki_versions', true, 'a_board')),
			array('permission.add', array('u_wiki_edit_topic', true, 'a_board')),
		);
	}
}
