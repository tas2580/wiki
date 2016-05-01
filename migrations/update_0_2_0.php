<?php
/**
*
* @package phpBB Extension - Wiki
 * @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tas2580\wiki\migrations;

class update_0_2_0 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\tas2580\wiki\migrations\initial_module',
			'\tas2580\wiki\migrations\update_0_1_2',
		);
	}

	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'wiki_article' => array(
					'article_sources'		=> array('MTEXT_UNI', ''),
				),
			),
		);
	}
}
