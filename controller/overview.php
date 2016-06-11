<?php
/**
 *
 * @package phpBB Extension - Wiki
 * @copyright (c) 2015 tas2580 (https://tas2580.net)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */
namespace tas2580\wiki\controller;

class overview
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \tas2580\wiki\wiki\edit */
	protected $edit;

	/** @var \tas2580\wiki\wiki\compare */
	protected $compare;

	/** @var \tas2580\wiki\wiki\view */
	protected $view;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var string php_ext */
	protected $php_ext;

	/** @var string article_table */
	protected $article_table;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth				$auth					Auth object
	 * @param \phpbb\controller\helper		$helper					Controller helper object
	 * @param \phpbb\db\driver\driver_inferface $db                 DB object
	 * @param \phpbb\request\request			$request			Request object
	 * @param \phpbb\template\template		$template				Template object
	 * @param \phpbb\user					$user					User object
	 * @param \tas2580\wiki\wiki\edit		$edit					Edit Wiki object
	 * @param \tas2580\wiki\wiki\compare		$compare					Diff Wiki object
	 * @param \tas2580\wiki\wiki\view		$view					View Wiki object
	 * @param string							$phpbb_root_path
	 * @param string							$php_ext
	 * @param string												article_table
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\controller\helper $helper, \phpbb\db\driver\driver_interface $db, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \tas2580\wiki\wiki\edit $edit, \tas2580\wiki\wiki\compare $compare, \tas2580\wiki\wiki\view $view, $phpbb_root_path, $php_ext, $article_table)
	{
		$this->auth = $auth;
		$this->helper = $helper;
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->edit = $edit;
		$this->compare = $compare;
		$this->view = $view;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		$this->article_table = $article_table;
	}

	public function base()
	{

		$this->user->add_lang_ext('tas2580/wiki', 'common');
		$this->user->add_lang_ext('tas2580/wiki', 'overview');

		// get all article

		$sql = 'SELECT article_title, article_url, article_description, article_views, article_last_edit
				FROM ' . $this->article_table . '
				WHERE article_approved=1
				ORDER BY article_id ASC';
		$result = $this->db->sql_query($sql);

		while ($all_wiki_article = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('all_wiki_article', array(
					'U_ARTICLE'				=> $this->helper->route('tas2580_wiki_article', array('article' => $all_wiki_article['article_url'])),
					'ARTICLE_NAME'			=> $all_wiki_article['article_title'],
					'ARTICLE_DESCRIPTION'	=> $all_wiki_article['article_description'],
					'ARTICLE_VIEWS'			=> $all_wiki_article['article_views'],
					'ARTICLE_LASTEDIT'		=> $this->user->format_date($all_wiki_article['article_last_edit']),
				)
			);
		}
		$this->db->sql_freeresult($result);

		// get latest article

		$sql = 'SELECT article_title, article_url, article_description, article_views, article_time_created
				FROM ' . $this->article_table . '
				WHERE article_approved=1
				ORDER BY article_time_created DESC';
		$result = $this->db->sql_query_limit($sql, 5);

		while ($all_wiki_article = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('latest_wiki_article', array(
					'U_ARTICLE'				=> $this->helper->route('tas2580_wiki_article', array('article' => $all_wiki_article['article_url'])),
					'ARTICLE_NAME'			=> $all_wiki_article['article_title'],
					'ARTICLE_DESCRIPTION'	=> $all_wiki_article['article_description'],
					'ARTICLE_VIEWS'			=> $all_wiki_article['article_views'],
					'ARTICLE_TIME_CREATED'	=> $this->user->format_date($all_wiki_article['article_time_created']),
				)
			);
		}
		$this->db->sql_freeresult($result);

		// get hot article

		$sql = 'SELECT article_title, article_url, article_description, article_views, article_last_edit
				FROM ' . $this->article_table . '
				WHERE article_approved=1
				ORDER BY article_views DESC';
		$result = $this->db->sql_query_limit($sql, 5);

		while ($all_wiki_article = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('hot_wiki_article', array(
					'U_ARTICLE'				=> $this->helper->route('tas2580_wiki_article', array('article' => $all_wiki_article['article_url'])),
					'ARTICLE_NAME'			=> $all_wiki_article['article_title'],
					'ARTICLE_DESCRIPTION'	=> $all_wiki_article['article_description'],
					'ARTICLE_VIEWS'			=> $all_wiki_article['article_views'],
					'ARTICLE_LASTEDIT'		=> $this->user->format_date($all_wiki_article['article_last_edit']),
				)
			);
		}
		$this->db->sql_freeresult($result);

		// get sticky article

		$sql = 'SELECT article_title, article_url, article_description, article_views, article_last_edit
				FROM ' . $this->article_table . '
				WHERE article_approved=1 
				AND article_sticky=1
				ORDER BY article_last_edit DESC';
		$result = $this->db->sql_query($sql);

		while ($all_wiki_article = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('sticky_wiki_article', array(
					'U_ARTICLE'				=> $this->helper->route('tas2580_wiki_article', array('article' => $all_wiki_article['article_url'])),
					'ARTICLE_NAME'			=> $all_wiki_article['article_title'],
					'ARTICLE_DESCRIPTION'	=> $all_wiki_article['article_description'],
					'ARTICLE_VIEWS'			=> $all_wiki_article['article_views'],
					'ARTICLE_LASTEDIT'		=> $this->user->format_date($all_wiki_article['article_last_edit']),
				)
			);
		}
		$this->db->sql_freeresult($result);

		return $this->helper->render('overview.html', $this->user->lang['OVERVIEW']);
	}
}
