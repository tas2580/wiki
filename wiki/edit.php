<?php
/**
*
* @package phpBB Extension - Wiki
 * @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace tas2580\wiki\wiki;

class edit
{

	/* @var \phpbb\auth\auth */
	protected $auth;
	/* @var \phpbb\config\config */
	protected $config;
	/* @var \phpbb\db\driver\driver */
	protected $db;
	/* @var \phpbb\controller\helper */
	protected $helper;
	/* @var \phpbb\template\template */
	protected $template;
	/* @var \phpbb\user */
	protected $user;
	/** @var string phpbb_root_path */
	protected $phpbb_root_path;
	/** @var string php_ext */
	protected $php_ext;
	/** @var string article_table */
	protected $article_table;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth			$auth			Auth object
	* @param \phpbb\config\config		$config
	* @param  \phpbb\db\driver\driver		$db				Database object
	* @param \phpbb\controller\helper		$helper			Controller helper object
	* @param \phpbb\template\template	$template			Template object
	* @param \phpbb\user				$user
	* @param string					$phpbb_root_path
	* @param string					$php_ext
	* @param string					$article_table
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $article_table, $phpbb_root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->table_article = $article_table;
	}

	/**
	 * Delete a version of an article
	 *
	 * @param	int		$id	Id of the version to delete
	 * @return	object
	 */
	public function delete($id)
	{
		if (!$this->auth->acl_get('u_wiki_delete'))
		{
			trigger_error('NOT_AUTHORISED');
		}

		if (confirm_box(true))
		{
			$sql = 'DELETE FROM ' . $this->table_article . '
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
	}

	/**
	 * Delete an complete article
	 *
	 * @param	string	$article	URL of the article to delete
	 * @return	object
	 */
	public function detele_article($article)
	{
		if (!$this->auth->acl_get('u_wiki_delete_article'))
		{
			trigger_error('NOT_AUTHORISED');
		}

		if (confirm_box(true))
		{
			$sql = 'DELETE FROM ' . $this->table_article . "
				WHERE article_url = '" . $this->db->sql_escape($article) . "'";
			$this->db->sql_query($sql);
			trigger_error($this->user->lang['DELETE_ARTICLE_SUCCESS'] . '<br /><br /><a href="' . $this->helper->route('tas2580_wiki_index', array())  . '">' . $this->user->lang['BACK_TO_WIKI'] . '</a>');
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				'id'    => $id,
			));
			confirm_box(false, $this->user->lang['CONFIRM_DELETE_ARTICLE'], $s_hidden_fields);
		}
	}



	/**
	 * Set a version of an article as active
	 *
	 * @param	string	$id	Id of the version to delete
	 * @return	object
	 */
	public function active($id)
	{
		if (!$this->auth->acl_get('u_wiki_set_active'))
		{
			trigger_error('NOT_AUTHORISED');
		}

		if (confirm_box(true))
		{
			$article_url = $this->set_active_version($id);
			$back_url = empty($article_url) ? $this->helper->route('tas2580_wiki_index', array()) : $this->helper->route('tas2580_wiki_article', array('article'	=> $article_url));
			trigger_error($this->user->lang['ACTIVATE_VERSION_SUCCESS'] . '<br /><br /><a href="' . $back_url . '">' . $this->user->lang['BACK_TO_ARTICLE'] . '</a>');
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				'id'    => $id,
			));
			confirm_box(false, $this->user->lang['CONFIRM_ACTIVATE_VERSION'], $s_hidden_fields);
		}
	}

	/**
	 * Edit an article
	 *
	 * @param	string	$article	URL of the article
	 * @return	object
	 */
	public function edit_article($article)
	{
		// If no auth to edit display error message
		if (!$this->auth->acl_get('u_wiki_edit'))
		{
			trigger_error('NO_ARTICLE');
		}
		$this->user->add_lang('posting');

		$preview = $this->request->is_set_post('preview');
		$submit = $this->request->is_set_post('submit');
		$error = array();

		if ($preview || $submit)
		{
			$title = $this->request->variable('title', '', true);
			$message = $this->request->variable('message', '', true);
			$edit_reason = $this->request->variable('edit_reason', '', true);
			$topic_id = $this->request->variable('topic_id', '', true);
			$sources = $this->request->variable('sources', '', true);
			$sources_array = explode("\n", $sources);

			$message_length = utf8_strlen($message);

			foreach($sources_array as $source)
			{
				if(!filter_var($source, FILTER_VALIDATE_URL))
				{
					$error[] = $this->user->lang['INVALID_SOURCE_URL'];
				}
			}

			if (utf8_clean_string($title) === '')
			{
				$error[] = $this->user->lang['EMPTY_SUBJECT'];
			}

			if (utf8_clean_string($message) === '')
			{
				$error[] = $this->user->lang['TOO_FEW_CHARS'];
			}

			// Maximum message length check. 0 disables this check completely.
			if ((int) $this->config['max_post_chars'] > 0 && $message_length > (int) $this->config['max_post_chars'])
			{
				$error[] = $this->user->lang('CHARS_POST_CONTAINS', $message_length) . '<br />' . $this->user->lang('TOO_MANY_CHARS_LIMIT', (int) $this->config['max_post_chars']);
			}

			// Minimum message length check
			if (!$message_length || $message_length < (int) $this->config['min_post_chars'])
			{
				$error[] = (!$message_length) ? $this->user->lang['TOO_FEW_CHARS'] : ($this->user->lang('CHARS_POST_CONTAINS', $message_length) . '<br />' . $this->user->lang('TOO_FEW_CHARS_LIMIT', (int) $this->config['min_post_chars']));
			}
		}

		if (sizeof($error))
		{
			$this->template->assign_vars(array(
				'ERROR'			=> implode('<br />', $error),
				'TITLE'			=> $title,
				'MESSAGE'		=> $message,
				'SOURCES'		=> $sources,
			));
		}
		// Display the preview
		else if ($preview)
		{
			$preview_text = $message;
			$uid = $bitfield = $options = '';
			generate_smilies('inline', 0);
			display_custom_bbcodes();
			add_form_key('article');
			$allowed_bbcode = $allowed_smilies = $allowed_urls = true;
			generate_text_for_storage($preview_text, $uid, $bitfield, $options, true, true, true);
			$preview_text = generate_text_for_display($preview_text, $uid, $bitfield, $options);

			foreach($sources_array as $source)
			{
				$this->template->assign_block_vars('article_sources', array(
					'SOURCE'		=> $source,
				));
			}

			$this->template->assign_vars(array(
				'S_PREVIEW'				=> true,
				'S_BBCODE_ALLOWED'		=> 1,
				'TITLE'					=> $title,
				'PREVIEW_MESSAGE'			=> $preview_text,
				'MESSAGE'				=> $message,
				'EDIT_REASON'				=> $edit_reason,
				'TOPIC_ID'					=> $topic_id,
				'SOURCES'				=> $sources,
			));
		}
		// Submit the article to database
		else if ($submit)
		{
			$set_active = $this->auth->acl_get('u_wiki_set_active') ? $this->request->variable('set_active', 0) : 0;
			generate_text_for_storage($message, $uid, $bitfield, $options, true, true, true);
			$sql_data = array(
				'article_title'			=> $title,
				'article_url'				=> $article,
				'article_text'			=> $message,
				'bbcode_uid'			=> $uid,
				'bbcode_bitfield'		=> $bitfield,
				'article_approved'		=> $set_active,
				'article_user_id'			=> $this->user->data['user_id'],
				'article_last_edit'		=> time(),
				'article_edit_reason'		=> $edit_reason,
				'article_topic_id'			=> (int) $topic_id,
				'article_sources'		=> $sources,
			);
			$sql = 'INSERT INTO ' . $this->table_article . '
				' . $this->db->sql_build_array('INSERT', $sql_data);
			$this->db->sql_query($sql);

			if($this->auth->acl_get('u_wiki_set_active') && ($set_active <> 0))
			{
				$this->set_active_version($this->db->sql_nextid());
			}

			$back_url = empty($article) ? $this->helper->route('tas2580_wiki_index', array()) : $this->helper->route('tas2580_wiki_article', array('article'	=> $article));
			trigger_error($this->user->lang['EDIT_ARTICLE_SUCCESS'] . '<br /><br /><a href="' . $back_url . '">' . $this->user->lang['BACK_TO_ARTICLE'] . '</a>');
		}
		// Get the last version of the article to edit
		else
		{
			$sql = 'SELECT *
				FROM ' . $this->table_article . '
					WHERE article_url = "' . $this->db->sql_escape($article) . '"
				ORDER BY article_last_edit DESC';
			$result = $this->db->sql_query_limit($sql, 1);
			$this->data = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
			generate_smilies('inline', 0);
			display_custom_bbcodes();
			add_form_key('article');
			$message = generate_text_for_edit($this->data['article_text'], $this->data['bbcode_uid'], 3);
			$this->template->assign_vars(array(
				'TITLE'					=> $this->data['article_title'],
				'MESSAGE'				=> $message['text'],
				'SOURCES'				=> $this->data['article_sources'],
				'S_BBCODE_ALLOWED'		=> 1,
				'TOPIC_ID'					=> $this->data['article_topic_id'],
				'S_AUTH_ACTIVATE'			=> $this->auth->acl_get('u_wiki_set_active'),
			));

			if (!empty($article))
			{
				$this->template->assign_block_vars('navlinks', array(
					'FORUM_NAME'		=> empty($this->data['article_title']) ? $this->user->lang('EDIT_WIKI') : $this->data['article_title'],
					'U_VIEW_FORUM'	=> $this->helper->route('tas2580_wiki_article', array('article'	=> $article)),
				));
			}
		}
		return $this->helper->render('article_edit.html', $this->user->lang['EDIT_WIKI']);
	}

	/**
	 *
	 * @param	int		$id		Version ID
	 * @return	string			URL of the article
	 */
	private function set_active_version($id)
	{
		if (!$this->auth->acl_get('u_wiki_set_active'))
		{
			trigger_error('NOT_AUTHORISED');
		}

		// Get the URL of the article
		$sql = 'SELECT article_url
			FROM ' . $this->table_article . '
				WHERE article_id = ' . (int) $id;
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		// Set all versions to not approved
		$sql = 'UPDATE ' . $this->table_article . "
			SET article_approved = 0
			WHERE article_url = '" . $this->db->sql_escape($row['article_url']) . "'
				AND article_id <> " . (int) $id;
		$this->db->sql_query($sql);

		// Set version to approved
		$sql = 'UPDATE ' . $this->table_article . '
			SET article_approved = 1
			WHERE article_id = ' . (int) $id;
		$this->db->sql_query($sql);
		return $row['article_url'];
	}
}
