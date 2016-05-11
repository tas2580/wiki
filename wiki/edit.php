<?php
/**
*
* @package phpBB Extension - Wiki
 * @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace tas2580\wiki\wiki;

class edit extends \tas2580\wiki\wiki\functions
{

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \parse_message */
	protected $message_parser;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string article_table */
	protected $article_table;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var string php_ext */
	protected $php_ext;

	/** @var array data */
	protected $data;

	/** @var array option */
	protected $option;


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
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\notification\manager $notification_manager, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $article_table, $phpbb_root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->notification_manager = $notification_manager;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->article_table = $article_table;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;

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
	public function detele_article($article)
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
			$article = $this->set_active_version($id);
			$back_url = empty($article) ? $this->helper->route('tas2580_wiki_index', array()) : $this->helper->route('tas2580_wiki_article', array('article'	=> $article));
			trigger_error($this->user->lang['ACTIVATE_VERSION_SUCCESS'] . '<br /><br /><a href="' . $back_url . '">' . $this->user->lang['BACK_TO_ARTICLE'] . '</a>');
		}
		else
		{
			$s_hidden_fields = build_hidden_fields(array(
				'id'    => $id,
			));
			confirm_box(false, $this->user->lang['CONFIRM_ACTIVATE_VERSION'], $s_hidden_fields);
		}
		redirect($this->helper->route('tas2580_wiki_index', array('article' => $article)));
	}

	/**
	 * Edit an article
	 *
	 * @param	string	$article	URL of the article
	 * @return	object
	 */
	public function edit_article($article)
	{
		// @TODO
		$this->option['bbcode'] = $this->option['url'] = $this->option['img'] = $this->option['flash'] = $this->option['quote'] = $this->option['smilies'] = true;

		// If no auth to edit display error message
		if (!$this->auth->acl_get('u_wiki_edit'))
		{
			trigger_error('NO_ARTICLE');
		}
		$this->user->add_lang('posting');

		// Setup message parser
		$this->message_parser = $this->setup_parser();

		$preview = $this->request->is_set_post('preview');
		$submit = $this->request->is_set_post('submit');
		$error = array();

		if ($preview || $submit)
		{
			$this->data['article_title']		= $this->request->variable('title', '', true);
			$this->data['text']					= $this->request->variable('message', '', true);
			$this->data['article_edit_reason']	= $this->request->variable('edit_reason', '', true);
			$this->data['article_topic_id']		= $this->request->variable('topic_id', '', true);
			$this->data['article_sources']		= $this->request->variable('sources', '', true);
			$this->data['set_active']			= $this->request->variable('set_active', 0);

			// Validate sources URL
			$sources_array = explode("\n", $this->data['article_sources']);
			foreach ($sources_array as $source)
			{
				if (!empty($source) && !filter_var($source, FILTER_VALIDATE_URL))
				{
					$error[] = $this->user->lang['INVALID_SOURCE_URL'];
				}
			}

			if (utf8_clean_string($this->data['article_title']) === '')
			{
				$error[] = $this->user->lang['EMPTY_SUBJECT'];
			}

			if (utf8_clean_string($this->data['text']) === '')
			{
				$error[] = $this->user->lang['TOO_FEW_CHARS'];
			}

			// Maximum message length check. 0 disables this check completely.
			$message_length = utf8_strlen($this->data['text']);
			if ((int) $this->config['max_post_chars'] > 0 && $message_length > (int) $this->config['max_post_chars'])
			{
				$error[] = $this->user->lang('CHARS_POST_CONTAINS', $message_length) . '<br />' . $this->user->lang('TOO_MANY_CHARS_LIMIT', (int) $this->config['max_post_chars']);
			}

			// Minimum message length check
			if (!$message_length || $message_length < (int) $this->config['min_post_chars'])
			{
				$error[] = (!$message_length) ? $this->user->lang['TOO_FEW_CHARS'] : ($this->user->lang('CHARS_POST_CONTAINS', $message_length) . '<br />' . $this->user->lang('TOO_FEW_CHARS_LIMIT', (int) $this->config['min_post_chars']));
			}

			$this->message_parser->message = $this->data['text'];
		}

		if (sizeof($error))
		{
			$this->template->assign_vars(array(
				'ERROR'			=> implode('<br />', $error),
			));
			$this->display_edit_form(false);
		}
		else if ($preview) // Display the preview
		{
			$this->message_parser->parse($this->option['bbcode'], $this->option['url'], $this->option['smilies'], $this->option['img'], $this->option['flash'], $this->option['quote']);
			$this->message_parser->format_display($this->option['bbcode'], $this->option['url'], $this->option['smilies']);

			foreach ($sources_array as $source)
			{
				if (!empty($source))
				{
					$this->template->assign_block_vars('article_sources', array(
						'SOURCE'		=> $source,
					));
				}
			}

			$this->display_edit_form(true);
		}
		else if ($submit) // Submit the article to database
		{
			$this->data['set_active'] = $this->auth->acl_get('u_wiki_set_active') ? $this->data['set_active'] : 0;
			$this->message_parser->parse($this->option['bbcode'], $this->option['url'], $this->option['smilies'], $this->option['img'], $this->option['flash'], $this->option['quote']);
			$sql_data = array(
				'article_title'			=> $this->data['article_title'],
				'article_url'			=> $article,
				'article_text'			=> $this->message_parser->message,
				'bbcode_uid'			=> $this->message_parser->bbcode_uid,
				'bbcode_bitfield'		=> $this->message_parser->bbcode_bitfield,
				'article_approved'		=> $this->data['set_active'],
				'article_user_id'		=> $this->user->data['user_id'],
				'article_last_edit'		=> time(),
				'article_edit_reason'	=> $this->data['article_edit_reason'],
				'article_topic_id'		=> $this->data['article_topic_id'],
				'article_sources'		=> $this->data['article_sources'],
			);
			$sql = 'INSERT INTO ' . $this->article_table . '
				' . $this->db->sql_build_array('INSERT', $sql_data);
			$this->db->sql_query($sql);
			$article_id = $this->db->sql_nextid();

			if ($this->auth->acl_get('u_wiki_set_active') && ($this->data['set_active'] <> 0))
			{
				$this->set_active_version($article_id);
			}
			else
			{
				$notify_data = array(
					'article_id'		=> $article_id,
					'article_title'		=> $this->data['article_title'],
					'article_url'		=> $article,
					'user_id'			=> $this->user->data['user_id'],
				);
				$this->notification_manager->add_notifications('tas2580.wiki.notification.type.articke_edit', $notify_data);
			}
			$msg = ($this->data['set_active'] <> 0) ? $this->user->lang['EDIT_ARTICLE_SUCCESS'] : $this->user->lang['EDIT_ARTICLE_SUCCESS_INACTIVE'];
			$back_url = empty($article) ? $this->helper->route('tas2580_wiki_index', array()) : $this->helper->route('tas2580_wiki_article', array('article'	=> $article));
			trigger_error($msg . '<br /><br /><a href="' . $back_url . '">' . $this->user->lang['BACK_TO_ARTICLE'] . '</a>');
		}
		// Get the last version of the article to edit
		else
		{
			$sql = 'SELECT *
				FROM ' . $this->article_table . '
					WHERE article_url = "' . $this->db->sql_escape($article) . '"
				ORDER BY article_last_edit DESC';
			$result = $this->db->sql_query_limit($sql, 1);
			$this->data = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$this->message_parser->message = $this->data['article_text'];
			$this->message_parser->decode_message($this->data['bbcode_uid']);

			$this->display_edit_form(false);

			if (!empty($article))
			{
				$this->template->assign_block_vars('navlinks', array(
					'FORUM_NAME'		=> empty($this->data['article_title']) ? $this->user->lang('EDIT_WIKI') : $this->data['article_title'],
					'U_VIEW_FORUM'		=> $this->helper->route('tas2580_wiki_article', array('article'	=> $article)),
				));
			}
		}
		return $this->helper->render('article_edit.html', $this->user->lang['EDIT_WIKI']);
	}
}
