<?php
/**
*
* @package phpBB Extension - Wiki
 * @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace tas2580\wiki\wiki;

class view extends \tas2580\wiki\wiki\functions
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \parse_message */
	protected $message_parser;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \tas2580\wiki\wiki\compare */
	protected $compare;

	/** @var \tas2580\wiki\wiki\edit */
	protected $edit;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var string php_ext */
	protected $php_ext;

	/** @var string article_table */
	protected $article_table;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth						$auth				Auth object
	* @param \phpbb\db\driver\driver_interface		$db					Database object
	* @param \phpbb\controller\helper				$helper				Controller helper object
	* @param \phpbb\template\template				$template			Template object
	* @param \phpbb\user							$user				User object
	* @param \tas2580\wiki\wiki\edit				$edit				Wiki edit object
	* @param string									$article_table
	* @param string									$phpbb_root_path
	* @param string									$php_ext
	*/

	public function __construct(\phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, \tas2580\wiki\wiki\compare $compare, \tas2580\wiki\wiki\edit $edit, $article_table, $phpbb_root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->db = $db;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->compare = $compare;
		$this->edit = $edit;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->article_table = $article_table;
	}

	/**
	 * View an article
	 *
	 * @param	string	$article	URL of the article
	 * @param	int		$id		ID of the article
	 * @return	object
	 */
	public function view_article($article, $id = 0)
	{
		// Setup message parser
		$this->message_parser = $this->setup_parser();

		$where = ($id === 0) ? "article_url = '" . $this->db->sql_escape($article) . "' AND article_approved = 1" : 'article_id = ' . (int) $id;
		$sql_array = array(
			'SELECT'		=> 'a.*, u.user_id, u.username, u.user_colour',
			'FROM'			=> array($this->article_table => 'a'),
			'LEFT_JOIN'		=> array(
				array(
					'FROM'		=> array(USERS_TABLE => 'u'),
					'ON'		=> 'u.user_id = a.article_user_id'
				)
			),
			'WHERE'			=> $where,
			'ORDER_BY'		=> 'a.article_last_edit DESC',
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, 1);
		$this->data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		// Do we have a newer version?
		if (($id === 0) && $this->auth->acl_get('u_wiki_set_active'))
		{
			$sql = 'SELECT article_id
				FROM ' . $this->article_table . "
				WHERE article_url = '" . $this->db->sql_escape($this->data['article_url']) . "'
					AND article_id <> " . (int) $this->data['article_id'] . '
					AND article_last_edit > ' . (int) $this->data['article_last_edit'] . '
				ORDER BY article_last_edit DESC';
			$result = $this->db->sql_query_limit($sql, 1);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
			if (!empty($row['article_id']))
			{
				$this->template->assign_vars(array(
					'S_NEW_VERSION'		=> true,
					'U_NEW_VERSION'		=> $this->helper->route('tas2580_wiki_article', array('article'	=> $article, 'id' => $row['article_id'])),
				));
			}
		}
		if (($id <> 0) && ($this->data['article_approved'] <> 1) && $this->auth->acl_get('u_wiki_set_active'))
		{
			$this->template->assign_vars(array(
				'U_SET_ACTIVE'		=> $this->helper->route('tas2580_wiki_article', array('article'	=> $article, 'action' => 'active', 'id' => $id)),
			));
		}

		if (!empty($this->data['article_title']) && !empty($article))
		{
			$this->template->assign_block_vars('navlinks', array(
				'FORUM_NAME'		=> $this->data['article_title'],
				'U_VIEW_FORUM'		=> $this->helper->route('tas2580_wiki_article', array('article'	=> $article)),
			));
		}

		// If the article do not exist generate it
		if (!$this->data)
		{
			// Do we have a inactive article?
			if ($this->auth->acl_get('m_wiki_view_inactive'))
			{
				$sql = 'SELECT article_id
					FROM ' . $this->article_table . "
					WHERE article_url = '" . $this->db->sql_escape($article) . "'";
				$result = $this->db->sql_query_limit($sql, 1);
				$row = $this->db->sql_fetchrow($result);

				if (!empty($row['article_id']))
				{
					return $this->compare->view_versions($article);
				}
			}
			return $this->edit->edit_article($article);
		}
		else
		{
			$sources = explode("\n", $this->data['article_sources']);
			foreach ($sources as $source)
			{
				if (!empty($source))
				{
					$this->template->assign_block_vars('article_sources', array(
						'SOURCE'		=> $source,
					));
				}
			}

			$this->message_parser->message = $this->data['article_text'];
			$this->message_parser->bbcode_bitfield = $this->data['bbcode_bitfield'];
			$this->message_parser->bbcode_uid = $this->data['bbcode_uid'];
			$allow_bbcode = $allow_magic_url = $allow_smilies = true;
			$this->message_parser->format_display($allow_bbcode, $allow_magic_url, $allow_smilies);


			if (!empty($this->data['article_redirect']))
			{
				$redirect_note = $this->user->lang('NO_ARTICLE_REDIRECT', $this->helper->route('tas2580_wiki_article', array('article' => $this->data['article_redirect'])), $this->data['article_redirect']);

				if ($this->auth->acl_get('u_wiki_set_redirect'))
				{
					$redirect_note = $redirect_note . $this->message_parser->message;
				}
			}

			// article views
			if (isset($this->user->data['session_page']) && !$this->user->data['is_bot'] && (strpos($this->user->data['session_page'], 'wiki/' . $this->data['article_url']) === false || isset($this->user->data['session_created'])))
			{
				$article_id = $this->data['article_id'];
				$sql = 'UPDATE ' . $this->article_table . "
						SET article_views = article_views + 1
						WHERE article_id = $article_id";
				$this->db->sql_query($sql);
			}

			$s_edit_redirect = ((!empty($this->data['article_redirect']) && $this->auth->acl_get('u_wiki_set_redirect')) || empty($this->data['article_redirect'])) ? true : false;
			$this->template->assign_vars(array(
				'ARTICLE_TITLE'			=> $this->data['article_title'],
				'ARTICLE_TEXT'			=> ($this->data['article_redirect'])? $redirect_note : $this->message_parser->message,
				'LAST_EDIT'				=> $this->user->format_date($this->data['article_last_edit']),
				'LAST_EDIT_ISO'			=> date('Y-m-d', $this->data['article_last_edit']),
				'ARTICLE_USER'			=> get_username_string('full', $this->data['user_id'], $this->data['username'], $this->data['user_colour']),
				'S_EDIT'				=> ($this->auth->acl_get('u_wiki_edit') && $s_edit_redirect),
				'U_EDIT'				=> $this->helper->route('tas2580_wiki_article', array('article' => $article, 'action'	=> 'edit')),
				'S_VERSIONS'			=> $this->auth->acl_get('u_wiki_versions'),
				'U_VERSIONS'			=> $this->helper->route('tas2580_wiki_article', array('article' => $article, 'action'	=> 'versions')),
				'S_DELETE'				=> ($this->auth->acl_get('m_wiki_delete') && !$this->data['article_approved']),
				'U_DELETE'				=> $this->helper->route('tas2580_wiki_index', array('article' => $article, 'action'	=> 'delete', 'id'	=> $this->data['article_id'])),
				'ARTICLE_VERSION'		=> $id,
				'ARTICLE_VIEWS_TEXT'	=> $this->user->lang('ARTICLE_VIEWS_TEXT', $this->data['article_views']),
				'EDIT_REASON'			=> ($id <> 0) ? $this->data['article_edit_reason'] : '',
				'U_TOPIC'				=> ($this->data['article_topic_id'] <> 0) ? append_sid($this->phpbb_root_path . 'viewtopic.' . $this->php_ext, 't=' . $this->data['article_topic_id']) : '',
			));
		}
		return $this->helper->render('article_body.html', $this->data['article_title']);
	}
}
