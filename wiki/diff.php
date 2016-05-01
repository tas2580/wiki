<?php
/**
*
* @package phpBB Extension - Wiki
 * @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace tas2580\wiki\wiki;

class diff
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
	/** @var string $article_table */
	protected $article_table;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth			$auth				Auth object
	* @param  \phpbb\db\driver\driver		$db					Database object
	* @param \phpbb\controller\helper		$helper				Controller helper object
	* @param \phpbb\template\template	$template				Template object
	* @param \phpbb\user				$user				User object
	* @param string					$article_table
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, $article_table)
	{
		$this->auth = $auth;
		$this->db = $db;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->table_article = $article_table;
	}


	public function compare_versions($article, $from, $to)
	{
		if ($from == 0 || $to == 0)
		{
			trigger_error('NO_VERSIONS_SELECTED');
		}
		$sql = 'SELECT article_text, bbcode_uid, bbcode_bitfield, article_sources
			FROM ' . $this->table_article . '
			WHERE article_id = ' . (int) $from;
		$result = $this->db->sql_query($sql);
		$from_row = $this->db->sql_fetchrow($result);

		$sql = 'SELECT article_text, bbcode_uid, bbcode_bitfield, article_sources
			FROM ' . $this->table_article . '
			WHERE article_id = ' . (int) $to;
		$result = $this->db->sql_query($sql);
		$to_row = $this->db->sql_fetchrow($result);

		$from_article = generate_text_for_display($from_row['article_text'], $from_row['bbcode_uid'], $from_row['bbcode_bitfield'], 3, true);
		$to_article = generate_text_for_display($to_row['article_text'], $to_row['bbcode_uid'], $to_row['bbcode_bitfield'], 3, true);
		$u_from = $this->helper->route('tas2580_wiki_index', array('id' => $from));
		$u_to = $this->helper->route('tas2580_wiki_index', array('id' => $to));

		$this->template->assign_vars(array(
			'HEADLINE'			=> sprintf($this->user->lang['VERSION_COMPARE_HEADLINE'], $from, $to, $u_from, $u_to),
			'DIFF'				=> $this->diffline($to_article, $from_article),
			'DIFF_SOURCE'			=> $this->diffline($to_row['article_sources'], $from_row['article_sources']),
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
			FROM ' . $this->table_article . '
			WHERE article_url = "' . $this->db->sql_escape($article) . '"
			ORDER BY article_last_edit DESC';
		$result = $this->db->sql_query_limit($sql, 1);
		$this->data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		$this->template->assign_vars(array(
			'ARTICLE_TITLE'			=> $this->data['article_title'],
			'S_SET_ACTIVE'			=> $this->auth->acl_get('u_wiki_set_active'),
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
			'FROM'		=> array($this->table_article => 'a'),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'		=> 'u.user_id = a.article_user_id'
				)
			),
			'WHERE'		=> 'article_url = "' . $this->db->sql_escape($article) . '"',
			'ORDER_BY'	=> 'a.article_last_edit DESC',
		);

		$sql = $this->db->sql_build_query('SELECT', array(
			'SELECT'		=> $sql_array['SELECT'],
			'FROM'		=> $sql_array['FROM'],
			'LEFT_JOIN'	=> $sql_array['LEFT_JOIN'],
			'WHERE'		=> $sql_array['WHERE'],
			'ORDER_BY'	=> $sql_array['ORDER_BY'],
		));
		$result = $this->db->sql_query($sql);
		while ($this->data = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('version_list', array(
				'ID'				=> $this->data['article_id'],
				'ARTICLE_TITLE'		=> $this->data['article_title'],
				'S_ACTIVE'			=> ($this->data['article_approved'] == 1) ? true : false,
				'USER'			=> get_username_string('full', $this->data['user_id'], $this->data['username'], $this->data['user_colour']),
				'EDIT_TIME'		=> $this->user->format_date($this->data['article_last_edit']),
				'U_VERSION'		=> $this->helper->route('tas2580_wiki_index', array('id' => $this->data['article_id'])),
				'U_DELETE'		=> $this->helper->route('tas2580_wiki_index', array('action' => 'delete', 'id' => $this->data['article_id'])),
				'U_SET_ACTIVE'		=> $this->helper->route('tas2580_wiki_index', array('action' => 'active', 'id' => $this->data['article_id'])),
			));
		}
		$this->db->sql_freeresult($result);

		return $this->helper->render('article_versions.html', $this->user->lang['VERSIONS_WIKI']);
	}


	private function diffline($line1, $line2)
	{
		$diff = $this->compute_diff(str_split($line1), str_split($line2));
		$diffval = $diff['values'];
		$diff_mask = $diff['mask'];

		$n = count($diffval);
		$pmc = 0;
		$result = '';
		for ($i = 0; $i < $n; $i++)
		{
			$mc = $diff_mask[$i];
			if ($mc != $pmc)
			{
				switch ($pmc)
				{
					case -1:
						$result .= '</del>';
						break;
					case 1:
						$result .= '</ins>';
						break;
				}
				switch ($mc)
				{
					case -1:
						$result .= '<del>';
						break;
					case 1:
						$result .= '<ins>';
						break;
				}
			}
			$result .= $diffval[$i];

			$pmc = $mc;
		}
		switch ($pmc)
		{
			case -1:
				$result .= '</del>';
				break;
			case 1:
				$result .= '</ins>';
				break;
		}

		return $result;
	}


	private function compute_diff($from, $to)
	{
		$diff_values = $diff_mask = $dm = array();
		$n1 = count($from);
		$n2 = count($to);

		for ($j = -1; $j < $n2; $j++)
		{
			$dm[-1][$j] = 0;
		}
		for ($i = -1; $i < $n1; $i++)
		{
			$dm[$i][-1] = 0;
		}
		for ($i = 0; $i < $n1; $i++)
		{
			for ($j = 0; $j < $n2; $j++)
			{
				if ($from[$i] == $to[$j])
				{
					$ad = $dm[$i - 1][$j - 1];
					$dm[$i][$j] = $ad + 1;
				}
				else
				{
					$a1 = $dm[$i - 1][$j];
					$a2 = $dm[$i][$j - 1];
					$dm[$i][$j] = max($a1, $a2);
				}
			}
		}

		$i = $n1 - 1;
		$j = $n2 - 1;
		while (($i > -1) || ($j > -1))
		{
			if ($j > -1)
			{
				if ($dm[$i][$j - 1] == $dm[$i][$j])
				{
					$diff_values[] = $to[$j];
					$diff_mask[] = 1;
					$j--;
					continue;
				}
			}
			if ($i > -1)
			{
				if ($dm[$i - 1][$j] == $dm[$i][$j])
				{
					$diff_values[] = $from[$i];
					$diff_mask[] = -1;
					$i--;
					continue;
				}
			}
			$diff_values[] = $from[$i];
			$diff_mask[] = 0;
			$i--;
			$j--;

		}

		$diff_values = array_reverse($diff_values);
		$diff_mask = array_reverse($diff_mask);

		return array('values' => $diff_values, 'mask' => $diff_mask);
	}
}
