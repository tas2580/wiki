<?php
/**
*
* @package phpBB Extension - Wiki
 * @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace tas2580\wiki\migrations;

class update_0_1_2 extends \phpbb\db\migration\migration
{

	public function update_data()
	{
		return array(
			array('permission.add', array('u_wiki_view', true, 'u_')),
		);
	}
}
