<?php
/**
*
* @package phpBB Extension - Wiki
 * @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace tas2580\wiki\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header'						=> 'page_header',
			'core.permissions'						=> 'permissions',
		);
	}

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\user */
	protected $user;

	/**
	* Constructor
	*
	* @param \phpbb\controller\helper		$helper		Controller helper object
	* @param \phpbb\template			$template		Template object
	* @param \phpbb\user				$user		User object
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->auth = $auth;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
	}

	public function permissions($event)
	{
		$permissions = $event['permissions'];
		$permissions += array(
			'u_wiki_view'		=> array(
				'lang'		=> 'ACL_U_WIKI_VIEW',
				'cat'		=> 'wiki'
			),
			'u_wiki_edit'		=> array(
				'lang'		=> 'ACL_U_WIKI_EDIT',
				'cat'		=> 'wiki'
			),
			'u_wiki_versions'	=> array(
				'lang'		=> 'ACL_U_WIKI_VERSIONS',
				'cat'		=> 'wiki'
			),
			'u_wiki_delete'	=> array(
				'lang'		=> 'ACL_U_WIKI_DELETE',
				'cat'		=> 'wiki'
			),
			'u_wiki_edit_topic'	=> array(
				'lang'		=> 'ACL_U_WIKI_EDIT_TOPIC',
				'cat'		=> 'wiki'
			),
		);
		$event['permissions'] = $permissions;
		$categories['wiki'] = 'ACL_CAT_WIKI';
		$event['categories'] = array_merge($event['categories'], $categories);
	}

	public function page_header($event)
	{
		if ($this->auth->acl_get('u_wiki_view'))
		{
			$this->user->add_lang_ext('tas2580/wiki', 'common');
			$this->template->assign_vars(array(
				'U_WIKI'	=> $this->helper->route('tas2580_wiki_index', array()),
			));
		}
	}
}
