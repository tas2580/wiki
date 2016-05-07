<?php
/**
*
* @package phpBB Extension - Wiki
 * @copyright (c) 2015 tas2580 (https://tas2580.net)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace tas2580\wiki\notification;

class articke_edit extends \phpbb\notification\type\base
{
	// Overwrite base in some cases:
	protected $language_key = 'WIKI_NEW_ARTICLE_UPDATE';
	protected $notify_icon = 'notify_phpbb';
	protected $permission = 'u_wiki_set_active';

	public static $notification_option = array(
		'lang' 	=> 'WIKI_ARTICLE_UPDATE',
		'group'	=> 'NOTIFICATION_GROUP_MISCELLANEOUS',
	);

	/** @var \phpbb\controller\helper */
	protected $helper;

	/**
	* Notification Type Boardrules Constructor
	*
	* @param \phpbb\user_loader						$user_loader
	* @param \phpbb\db\driver\driver_interface		$db
	* @param \phpbb\cache\driver\driver_interface	$cache
	* @param \phpbb\user							$user
	* @param \phpbb\auth\auth						$auth
	* @param \phpbb\config\config					$config
	* @param \phpbb\controller\helper				$helper
	* @param string									$phpbb_root_path
	* @param string									$php_ext
	* @param string									$notification_types_table
	* @param string									$notifications_table
	* @param string									$user_notifications_table
	*/
	public function __construct(\phpbb\user_loader $user_loader, \phpbb\db\driver\driver_interface $db, \phpbb\cache\driver\driver_interface $cache, $user, \phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\controller\helper $helper, $phpbb_root_path, $php_ext, $notification_types_table, $notifications_table, $user_notifications_table)
	{
		$this->helper = $helper;
		parent::__construct($user_loader, $db, $cache, $user, $auth, $config, $phpbb_root_path, $php_ext, $notification_types_table, $notifications_table, $user_notifications_table);
	}

	public function is_available()
	{
		return $this->auth->acl_get($this->permission);
	}

	/**
	* Get the id of the parent
	*
	* @param array $notification_data The data from the topic
	*/
	public static function get_item_parent_id($notification_data)
	{
		return 0;
	}

	/**
	* Get the id of the item
	*
	* @param array $notification_data The data from the post
	*/
	public static function get_item_id($notification_data)
	{
		return (int) $notification_data['article_id'];
	}

	/**
	 * Find the users who want to receive notifications
	 *
	 * @param array $notification_data The data from the post
	 * @param array $options Options for finding users for notification
	 *
	 * @return array
	 */
	public function find_users_for_notification($notification_data, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'      => array(),
		), $options);

		$users = $this->auth->acl_get_list(false, $this->permission);
		$users = (!empty($users[0][$this->permission])? $users[0][$this->permission] : array());
		$users = array_unique($users);

		return $this->check_user_notification_options($users, $options);
	}

	public function users_to_query()
	{
		return array();
	}

	/**
	* Get email template variables
	*
	* @return array
	*/
	public function get_email_template_variables()
	{
		return array(
			'NOTIFICATION_SUBJECT'	=> htmlspecialchars_decode($this->get_title()),
			'USERNAME'				=> htmlspecialchars_decode($this->user->data['username']),
			'U_LINK'				=> generate_board_url() . $this->helper->route('tas2580_wiki_index', array('id' =>$this->get_data('article_id'))),
		);
	}
	/**
	* Get the user's avatar
	*/
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('user_id'), true, true);
	}
	/**
	* Get the url to this item
	*
	* @return string URL
	*/
	public function get_url()
	{
		return $this->helper->route('tas2580_wiki_index', array('id' =>$this->get_data('article_id')));
	}

	/**
	* Get the HTML formatted title of this notification
	*
	* @return string
	*/
	public function get_title()
	{
		return $this->user->lang($this->language_key, $this->get_data('article_title'));
	}

	public function get_type()
	{
		return 'tas2580.wiki.notification.type.articke_edit';
	}

	public function get_email_template()
	{
		return '@tas2580_wiki/mail_articke_edit';
	}

	public function create_insert_array($notification_data, $pre_create_data = array())
	{
		$this->set_data('article_id', $notification_data['article_id']);
		$this->set_data('user_id', $notification_data['user_id']);
		$this->set_data('article_title', $notification_data['article_title']);
		$this->set_data('article_url', $notification_data['article_url']);
		return parent::create_insert_array($notification_data, $pre_create_data);
	}
}
