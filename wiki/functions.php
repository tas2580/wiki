<?php
/**
*
* @package phpBB Extension - Wiki
* @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace tas2580\wiki\wiki;

class functions
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \parse_message */
	protected $message_parser;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var array data */
	protected $data;

	/** @var array option */
	protected $option;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var string php_ext */
	protected $php_ext;

	/**
	 * Setup message parser
	 *
	 * @return \parse_message
	 */
	protected function setup_parser()
	{
		if (!is_object($this->message_parser))
		{
			if (!class_exists('\bbcode'))
			{
				require($this->phpbb_root_path . 'includes/bbcode.' . $this->php_ext);
			}
			if (!class_exists('\parse_message'))
			{
				require($this->phpbb_root_path . 'includes/message_parser.' . $this->php_ext);
			}
			return new \parse_message;
		}
	}

	/**
	 *
	 * @param	int		$id		Version ID
	 * @return	string			URL of the article
	 */
	protected function set_active_version($id)
	{
		if (!$this->auth->acl_get('u_wiki_set_active'))
		{
			trigger_error('NOT_AUTHORISED');
		}

		// Get the URL of the article
		$sql = 'SELECT article_url
			FROM ' . $this->article_table . '
				WHERE article_id = ' . (int) $id;
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		// Set all versions to not approved
		$sql = 'UPDATE ' . $this->article_table . "
			SET article_approved = 0
			WHERE article_url = '" . $this->db->sql_escape($row['article_url']) . "'
				AND article_id <> " . (int) $id;
		$this->db->sql_query($sql);

		// Set version to approved
		$sql = 'UPDATE ' . $this->article_table . '
			SET article_approved = 1
			WHERE article_id = ' . (int) $id;
		$this->db->sql_query($sql);
		return $row['article_url'];
	}

	/**
	 * Display the edit form
	 *
	 * @param bool $preview
	 */
	protected function display_edit_form($preview = false)
	{
		generate_smilies('inline', 0);
		display_custom_bbcodes();
		add_form_key('article');

		$this->template->assign_vars(array(
			'S_PREVIEW'				=> $preview,
			'TITLE'					=> $this->data['article_title'],
			'MESSAGE'				=> ($preview) ? $this->data['article_text'] : $this->message_parser->message,
			'PREVIEW_MESSAGE'		=> $this->message_parser->message,
			'SOURCES'				=> $this->data['article_sources'],
			'S_BBCODE_ALLOWED'		=> $this->option['bbcode'],
			'S_LINKS_ALLOWED'		=> $this->option['url'],
			'S_BBCODE_IMG'			=> $this->option['img'],
			'S_BBCODE_FLASH'		=> $this->option['flash'],
			'S_BBCODE_QUOTE'		=> $this->option['quote'],
			'BBCODE_STATUS'			=> ($this->option['bbcode']) ? sprintf($this->user->lang['BBCODE_IS_ON'], '<a href="' . append_sid("{$this->phpbb_root_path}faq.{$this->php_ext}", 'mode=bbcode') . '">', '</a>') : sprintf($this->user->lang['BBCODE_IS_OFF'], '<a href="' . append_sid("{$this->phpbb_root_path}faq.{$this->php_ext}", 'mode=bbcode') . '">', '</a>'),
			'IMG_STATUS'			=> ($this->option['img']) ? $this->user->lang['IMAGES_ARE_ON'] : $this->user->lang['IMAGES_ARE_OFF'],
			'FLASH_STATUS'			=> ($this->option['flash']) ? $this->user->lang['FLASH_IS_ON'] : $this->user->lang['FLASH_IS_OFF'],
			'SMILIES_STATUS'		=> ($this->option['smilies']) ? $this->user->lang['SMILIES_ARE_ON'] : $this->user->lang['SMILIES_ARE_OFF'],
			'URL_STATUS'			=> ($this->option['bbcode'] && $this->option['url']) ? $this->user->lang['URL_IS_ON'] : $this->user->lang['URL_IS_OFF'],
			'EDIT_REASON'			=> $this->data['article_edit_reason'],
			'TOPIC_ID'				=> (int) $this->data['article_topic_id'],
			'S_AUTH_ACTIVATE'		=> $this->auth->acl_get('u_wiki_set_active'),
			'S_AUTH_EDIT_TOPIC'		=> $this->auth->acl_get('u_wiki_edit_topic'),
			'S_AUTH_REDIRECT'		=> $this->auth->acl_get('u_wiki_set_redirect'),
			'S_AUTH_STICKY'			=> $this->auth->acl_get('u_wiki_set_sticky'),
			'S_ACTIVE'				=> ($preview) ? $this->data['article_approved'] : 1,
			'S_STICKY'				=> $this->data['article_sticky'],
			'ARTICLE_REDIRECT'		=> $this->data['article_redirect'],
			'ARTICLE_DESCRIPTION'	=> $this->data['article_description'],
		));
	}

	/**
	 * Get Data for an article from database
	 *
	 * @param	string	$article	URL of the article
	 * @return	array				Data for the article
	 */
	protected function get_article_data($article)
	{
		$sql = 'SELECT *
			FROM ' . $this->article_table . "
				WHERE article_url = '" . $this->db->sql_escape($article) . "'
					AND article_approved = 1
			ORDER BY article_last_edit DESC";
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (empty($row['article_id']))
		{
			$row = array(
				'article_text'			=> '',
				'bbcode_uid'			=> '',
				'article_title'			=> '',
				'article_sources'		=> '',
				'article_topic_id'		=> 0,
				'article_sticky'		=> 0,
				'article_redirect'		=> '',
				'article_description'	=> '',
				'article_views'			=> 0,
			);
		}

		// empty edit reason on every edit
		$row['article_edit_reason'] = '';

		return $row;
	}

}
