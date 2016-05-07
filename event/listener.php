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
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth				$auth					Auth object
	* @param \phpbb\controller\helper		$helper					Controller helper object
	* @param \phpbb\notification\manager	$notification_manager	Notification Manager
	* @param \phpbb\template				$template				Template object
	* @param \phpbb\user					$user					User object
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\controller\helper $helper, \phpbb\notification\manager $notification_manager, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->auth = $auth;
		$this->helper = $helper;
		$this->notification_manager = $notification_manager;
		$this->template = $template;
		$this->user = $user;
	}

	public static  function getSubscribedEvents()
	{
		return array(
			'core.page_header'			=> 'page_header',
			'core.permissions'			=> 'permissions',
			'core.user_setup'			=> 'user_setup',
		);
	}

	/**
	 * Add permissions
	 *
	 * @param	object	$event	The event object
	 * @return	null
	 * @access	public
	 */
	public function permissions($event)
	{
		$permissions = $event['permissions'];
		$permissions += array(
			'u_wiki_view'			=> array(
				'lang'		=> 'ACL_U_WIKI_VIEW',
				'cat'		=> 'wiki'
			),
			'u_wiki_edit'			=> array(
				'lang'		=> 'ACL_U_WIKI_EDIT',
				'cat'		=> 'wiki'
			),
			'u_wiki_versions'		=> array(
				'lang'		=> 'ACL_U_WIKI_VERSIONS',
				'cat'		=> 'wiki'
			),
			'u_wiki_delete'			=> array(
				'lang'		=> 'ACL_U_WIKI_DELETE',
				'cat'		=> 'wiki'
			),
			'u_wiki_delete_article'	=> array(
				'lang'		=> 'ACL_U_WIKI_DELETE_ARTICLE',
				'cat'		=> 'wiki'
			),
			'u_wiki_edit_topic'		=> array(
				'lang'		=> 'ACL_U_WIKI_EDIT_TOPIC',
				'cat'		=> 'wiki'
			),
			'u_wiki_set_active'		=> array(
				'lang'		=> 'ACL_U_WIKI_SET_ACTIVE',
				'cat'		=> 'wiki'
			),
		);
		$event['permissions'] = $permissions;
		$categories['wiki'] = 'ACL_CAT_WIKI';
		$event['categories'] = array_merge($event['categories'], $categories);
	}

	/**
	 * Add notification
	 *
	 * @param	object	$event	The event object
	 * @return	null
	 * @access	public
	 */
	public function notification_add($event)
	{
		if (!$this->config['email_enable'])
		{
			return;
		}
		$notifications_data = array(
			array(
				'item_type'		=> 'tas2580.wiki.notification.type.articke_edit',
				'method'		=> 'notification.method.email',
			),
		);
		foreach ($notifications_data as $subscription)
		{
			$this->notification_manager->add_subscription($subscription['item_type'], 0, $subscription['method'], $event['user_id']);
		}
	}

	/**
	 * Add link to header
	 *
	 * @param	object	$event	The event object
	 * @return	null
	 * @access	public
	 */
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

	/**
	 * Add language file
	 *
	 * @param	object	$event	The event object
	 * @return	null
	 * @access	public
	 */
	public function user_setup($event)
	{
		$lang_ary = $event['lang_set_ext'];
		$lang_ary[] = array(
			'ext_name'	=> 'tas2580/wiki',
			'lang_set'		=> 'link',
		);
		$event['lang_set_ext'] = $lang_ary;
	}

}
