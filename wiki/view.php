<?php
/**
*
* @package phpBB Extension - Wiki
 * @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace tas2580\wiki\wiki;

class view
{

	/* @var \phpbb\auth\auth */
	protected $auth;
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

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth			$auth			Auth object
	* @param \phpbb\controller\helper		$helper			Controller helper object
	* @param \phpbb\template\template	$template			Template object
	* @param \phpbb\user				$user
	* @param string					$phpbb_root_path
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, $article_table, $phpbb_root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->db = $db;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		// Extension tables
		$this->table_article = $article_table;
	}

	/**
	 * View an article
	 *
	 * @param	string	$article	URL of the article
	 * @return	object
	 */
	public function view_article($article, $id = 0)
	{

		$where = ($id === 0) ? 'article_url = "' . $this->db->sql_escape($article) . '"' : 'article_id = ' . (int) $id;

		$sql = 'SELECT *
			FROM ' . $this->table_article . '
			WHERE ' . $where . '
			ORDER BY article_last_edit DESC';
		$result = $this->db->sql_query_limit($sql, 1);
		$this->data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if(!empty($article))
		{
			$this->template->assign_block_vars('navlinks', array(
				'FORUM_NAME'	=> $this->data['article_title'],
				'U_VIEW_FORUM'	=> $this->helper->route('tas2580_wiki_article', array('article'	=> $article)),
			));
		}

		// If the article do not exist generate it
		if (!$this->data)
		{
			return $this->edit_article($article);
		}
		else
		{
			$this->template->assign_vars(array(
				'S_BBCODE_ALLOWED'	=> 1,
				'ARTICLE_TITLE'			=> $this->data['article_title'],
				'ARTICLE_TEXT'			=> generate_text_for_display($this->data['article_text'], $this->data['bbcode_uid'], $this->data['bbcode_bitfield'], 3, true),
				'LAST_EDIT'			=> $this->user->format_date($this->data['article_last_edit']),
				'S_EDIT'				=> $this->auth->acl_get('u_wiki_edit'),
				'U_EDIT'				=> $this->helper->route('tas2580_wiki_index', array('article' => $article, 'action'	=> 'edit')),
				'S_VERSIONS'			=> $this->auth->acl_get('u_wiki_versions'),
				'U_VERSIONS'			=> $this->helper->route('tas2580_wiki_index', array('article' => $article, 'action'	=> 'versions')),
				'VERSION'				=> $id,
				'EDIT_REASON'			=> ($id <> 0) ? $this->data['article_edit_reason'] : '',
				'U_TOPIC'				=> ($this->data['article_topic_id'] <> 0) ? append_sid($this->phpbb_root_path . 'viewtopic.' . $this->php_ext, 't=' . $this->data['article_topic_id']) : '',
			));
		}
		return $this->helper->render('article_body.html', $this->data['article_title']);
	}

}