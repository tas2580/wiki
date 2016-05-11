<?php
/**
*
* @package phpBB Extension - Wiki
 * @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace tas2580\wiki\wiki;

class compare
{

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string $article_table */
	protected $article_table;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var string php_ext */
	protected $php_ext;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth						$auth				Auth object
	* @param \phpbb\db\driver\driver_interface		$db					Database object
	* @param \phpbb\controller\helper				$helper				Controller helper object
	* @param \phpbb\template\template				$template			Template object
	* @param \phpbb\user							$user				User object
	* @param string									$article_table
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, $article_table, $phpbb_root_path, $php_ext)
	{
		$this->auth = $auth;
		$this->db = $db;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->article_table = $article_table;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	public function compare_versions($article, $from, $to)
	{
		if ($from == 0 || $to == 0)
		{
			trigger_error('NO_VERSIONS_SELECTED');
		}

		require($this->phpbb_root_path . 'includes/diff/diff.' . $this->php_ext);
		require($this->phpbb_root_path . 'includes/diff/engine.' . $this->php_ext);
		require($this->phpbb_root_path . 'includes/diff/renderer.' . $this->php_ext);

		$sql = 'SELECT article_text, bbcode_uid, bbcode_bitfield, article_sources
			FROM ' . $this->article_table . '
			WHERE article_id = ' . (int) $from;
		$result = $this->db->sql_query($sql);
		$from_row = $this->db->sql_fetchrow($result);

		$sql = 'SELECT article_text, bbcode_uid, bbcode_bitfield, article_sources
			FROM ' . $this->article_table . '
			WHERE article_id = ' . (int) $to;
		$result = $this->db->sql_query($sql);
		$to_row = $this->db->sql_fetchrow($result);

		$from_article = generate_text_for_edit($from_row['article_text'], $from_row['bbcode_uid'], $from_row['bbcode_bitfield'], 3, true);
		$to_article = generate_text_for_edit($to_row['article_text'], $to_row['bbcode_uid'], $to_row['bbcode_bitfield'], 3, true);
		$u_from = $this->helper->route('tas2580_wiki_index', array('id' => $from));
		$u_to = $this->helper->route('tas2580_wiki_index', array('id' => $to));

		$article_diff = new \diff($from_article['text'], $to_article['text']);
		$article_diff_empty = $article_diff->is_empty();

		$sources_diff = new \diff($from_row['article_sources'], $to_row['article_sources']);
		$sources_diff_empty = $sources_diff->is_empty();

		$renderer = new \diff_renderer_inline();

		$this->template->assign_vars(array(
			'HEADLINE'			=> sprintf($this->user->lang['VERSION_COMPARE_HEADLINE'], $from, $to, $u_from, $u_to),
			'DIFF'				=> ($article_diff_empty) ? '' : $renderer->get_diff_content($article_diff),
			'DIFF_SOURCE'		=> ($sources_diff_empty) ? '' : $renderer->get_diff_content($sources_diff),
		));

		return $this->helper->render('article_compare.html', $this->user->lang['VERSIONS_OF_ARTICLE']);
	}


	public function view_versions($article)
	{
		if (!$this->auth->acl_get('u_wiki_versions'))
		{
			trigger_error('NOT_AUTHORISED');
		}

		$sql = 'SELECT *
			FROM ' . $this->article_table . '
			WHERE article_url = "' . $this->db->sql_escape($article) . '"
			ORDER BY article_last_edit DESC';
		$result = $this->db->sql_query_limit($sql, 1);
		$this->data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		$this->template->assign_vars(array(
			'ARTICLE_TITLE'		=> $this->data['article_title'],
			'S_SET_ACTIVE'		=> $this->auth->acl_get('u_wiki_set_active'),
			'U_ACTION'			=> $this->helper->route('tas2580_wiki_index', array('article' => $article, 'action' => 'compare')),
		));

		if (!empty($article))
		{
			$this->template->assign_block_vars('navlinks', array(
				'FORUM_NAME'	=> $this->data['article_title'],
				'U_VIEW_FORUM'	=> $this->helper->route('tas2580_wiki_article', array('article'	=> $article)),
			));
		}

		$sql_array = array(
			'SELECT'		=> 'a.article_id, a.article_title, a.article_last_edit,  a.article_approved, u.user_id, u.username, u.user_colour',
			'FROM'		=> array($this->article_table => 'a'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'u.user_id = a.article_user_id'
				)
			),
			'WHERE'		=> 'article_url = "' . $this->db->sql_escape($article) . '"',
			'ORDER_BY'	=> 'a.article_last_edit DESC',
		);

		$sql = $this->db->sql_build_query('SELECT', array(
			'SELECT'		=> $sql_array['SELECT'],
			'FROM'			=> $sql_array['FROM'],
			'LEFT_JOIN'		=> $sql_array['LEFT_JOIN'],
			'WHERE'			=> $sql_array['WHERE'],
			'ORDER_BY'		=> $sql_array['ORDER_BY'],
		));
		$result = $this->db->sql_query($sql);
		while ($this->data = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('version_list', array(
				'ID'				=> $this->data['article_id'],
				'ARTICLE_TITLE'		=> $this->data['article_title'],
				'S_ACTIVE'			=> ($this->data['article_approved'] == 1) ? true : false,
				'USER'				=> get_username_string('full', $this->data['user_id'], $this->data['username'], $this->data['user_colour']),
				'EDIT_TIME'			=> $this->user->format_date($this->data['article_last_edit']),
				'U_VERSION'			=> $this->helper->route('tas2580_wiki_index', array('id' => $this->data['article_id'])),
				'U_DELETE'			=> $this->helper->route('tas2580_wiki_index', array('action' => 'delete', 'id' => $this->data['article_id'])),
				'U_SET_ACTIVE'		=> $this->helper->route('tas2580_wiki_index', array('action' => 'active', 'id' => $this->data['article_id'])),
			));
		}
		$this->db->sql_freeresult($result);

		return $this->helper->render('article_versions.html', $this->user->lang['VERSIONS_WIKI']);
	}
}
