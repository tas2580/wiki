<?php
/**
*
* @package phpBB Extension - Wiki
 * @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace tas2580\wiki\wiki;

class delete extends \tas2580\wiki\wiki\functions
{

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \parse_message */
	protected $message_parser;

	/** @var \phpbb\user */
	protected $user;

	/** @var string article_table */
	protected $article_table;


	/**
	* Constructor
	*
	* @param \phpbb\auth\auth						$auth					Auth object
	* @param \phpbb\config\config					$config
	* @param \phpbb\db\driver\driver_interface		$db						Database object
	* @param \phpbb\controller\helper				$helper					Controller helper object
	* @param \phpbb\notification\manager			$notification_manager
	* @param \phpbb\request\request					$request				Request object
	* @param \phpbb\template\template				$template				Template object
	* @param \phpbb\user							$user
	* @param string									$article_table
	* @param string									$phpbb_root_path
	* @param string									$php_ext

	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\user $user, $article_table)
	{
		$this->auth = $auth;
		$this->db = $db;
		$this->helper = $helper;
		$this->user = $user;
		$this->article_table = $article_table;
	}

	/**
	 * Delete a version of an article
	 *
	 * @param	int		$id	Id of the version to delete
	 * @return	object
	 */
	public function version($id)
	{
		if (!$this->auth->acl_get('u_wiki_delete'))
		{
			trigger_error('NOT_AUTHORISED');
		}

		if (confirm_box(true))
		{
			$sql = 'DELETE FROM ' . $this->article_table . '
				WHERE article_id = ' . (int) $id;
			$this->db->sql_query($sql);
			//return $helper->message('DELETE_VERSION_SUCCESS', array());
			trigger_error($this->user->lang['DELETE_VERSION_SUCCESS'] . '<br /><br /><a href="' . $this->helper->route('tas2580_wiki_index', array())  . '">' . $this->user->lang['BACK_TO_WIKI'] . '</a>');
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				'id'    => $id,
			));
			confirm_box(false, $this->user->lang['CONFIRM_DELETE_VERSION'], $s_hidden_fields);
		}
		redirect($this->helper->route('tas2580_wiki_index', array('id' => $id)));
	}

	/**
	 * Delete an complete article
	 *
	 * @param	string	$article	URL of the article to delete
	 * @return	object
	 */
	public function article($article)
	{
		if (!$this->auth->acl_get('u_wiki_delete_article'))
		{
			trigger_error('NOT_AUTHORISED');
		}

		if (confirm_box(true))
		{
			$sql = 'DELETE FROM ' . $this->article_table . "
				WHERE article_url = '" . $this->db->sql_escape($article) . "'";
			$this->db->sql_query($sql);
			trigger_error($this->user->lang['DELETE_ARTICLE_SUCCESS'] . '<br /><br /><a href="' . $this->helper->route('tas2580_wiki_index', array())  . '">' . $this->user->lang['BACK_TO_WIKI'] . '</a>');
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				'article'    => $article,
			));
			confirm_box(false, $this->user->lang['CONFIRM_DELETE_ARTICLE'], $s_hidden_fields);
		}
		redirect($this->helper->route('tas2580_wiki_index', array('article' => $article)));
	}
}
