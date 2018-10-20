<?php
/**
 * Discord Notifications extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Tyler Olsen, https://github.com/rootslinux
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace roots\discordnotifications\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Discord Notifications Event listener. The subscribed events correspond to activity that we
 * may desire to generate and send a notification to Discord describing the event.
 */
class notification_event_listener implements EventSubscriberInterface
{
	/** @var \roots\discordnotifications\notification_service */
	protected $notification_service;

	/**
	 * Constructor
	 * @param \roots\discordnotifications\notification_service $notification_service
	 * @param \phpbb\controller\helper $controller_helper Controller helper object
	 * @param \phpbb\language\language $lang              Language object
	 * @param \phpbb\template\template $template          Template object
	 * @param string                   $php_ext           phpEx
	 * @access public
	 */
	public function __construct(\roots\discordnotifications\notification_service $notification_service)
	{
		$this->notification_service = $notification_service;
	}

	/**
	 * Assign functions defined in this class to event listeners
	 * @return array
	 * @static
	 * @access public
	 */
	static public function getSubscribedEvents()
	{
		return array(
			// This event is used for performing actions directly after a post or topic
			// has been submitted. When a new topic is posted, the topic ID is
			// available in the $data array.
			'core.submit_post_end' => 'notify_post_created',

			// This event allows you to define errors after the post action is performed
			'core.posting_modify_submit_post_after' => 'notify_post_updated',

			// This event is used for performing actions directly after a post or topic has been deleted.
			// TODO: also consider listening for delete_posts_after
			'core.delete_post_after' => 'notify_post_deleted',

			// Perform additional actions after locking/unlocking posts/topics
			'core.mcp_lock_unlock_after' => 'notify_post_lock_status',

			// Event to modify the post data for the MCP topic review before assigning the posts
			'core.mcp_topic_modify_post_data' => 'notify_topic_updated',

			// Perform additional actions after topic(s) deletion
			'core.delete_topics_after_query' => 'notify_topic_deleted',

			// Event that returns user id, user details and user CPF of newly registered user
			'core.user_add_after' => 'notify_user_created',
		);
	}

	/**
	 * Sends a notification to Discord when a new post is created.
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function notify_post_created($event)
	{
		// Check config settings first to see if we need to send a notification for this event
		// TODO
// 		echo 'notify_post_created';
// 		echo '<pre>';
// 		echo var_dump($event);
// 		echo '</pre>';
		$this->notification_service->send_discord_notification('notify_post_created');
	}

		/**
	 * Sends a notification to Discord when a post is updated.
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function notify_post_updated($event)
	{
		// Check config settings first to see if we need to send a notification for this event
		// TODO
// 		echo 'notify_post_updated';
// 		echo '<pre>';
// 		var_dump($event);
// 		echo '</pre>';
		$this->notification_service->send_discord_notification('notify_post_updated');
	}

	/**
	 * Sends a notification to Discord when a post is deleted.
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function notify_post_deleted($event)
	{
		// Check config settings first to see if we need to send a notification for this event
		// TODO
// 		echo 'notify_post_deleted';
// 		echo '<pre>';
// 		var_dump($event);
// 		echo '</pre>';
		$this->notification_service->send_discord_notification('notify_post_deleted');
	}

	/**
	 * Sends a notification to Discord when a post is locked or unlocked.
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function notify_post_lock_status($event)
	{
		// Check config settings first to see if we need to send a notification for this event
		// TODO
// 		echo 'notify_post_locked';
// 		echo '<pre>';
// 		var_dump($event);
// 		echo '</pre>';
		$this->notification_service->send_discord_notification('notify_post_lock_status');
	}

	/**
	 * Sends a notification to Discord when a new topic is created.
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function notify_topic_created($event)
	{
		// Check config settings first to see if we need to send a notification for this event
		// TODO
// 		var_dump($event);
		$this->notification_service->send_discord_notification('notify_topic_created');
	}

	/**
	 * Sends a notification to Discord when a topic is updated.
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function notify_topic_updated($event)
	{
		// Check config settings first to see if we need to send a notification for this event
		// TODO
// 		echo 'notify_topic_updated';
// 		echo '<pre>';
// 		var_dump($event);
// 		echo '</pre>';
		$this->notification_service->send_discord_notification('notify_topic_updated');
	}

	/**
	 * Sends a notification to Discord when a topic is created.
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function notify_topic_deleted($event)
	{
		// Check config settings first to see if we need to send a notification for this event
		// TODO
// 		echo 'notify_topic_deleted';
// 		echo '<pre>';
// 		var_dump($event);
// 		echo '</pre>';
		$this->notification_service->send_discord_notification('notify_topic_deleted');
	}

	/**
	 * Sends a notification to Discord when a topic is locked or unlocked.
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function notify_topic_lock_status($event)
	{
		// Check config settings first to see if we need to send a notification for this event
		// TODO
// 		var_dump($event);
		$this->notification_service->send_discord_notification('notify_topic_lock_status');
	}

	/**
	 * Sends a notification to Discord when a new user account is created.
	 * @param \phpbb\event\data	$event	Event object -- Arguments(cp_data, user_id, user_row)
	 *
	 * Notification details include the user name and a link to the user's profile page
	 */
	public function notify_user_created($event)
	{
		// Check config settings first to see if we need to send a notification for this event
		// TODO

		$user_id = $event['user_id'];
// 		echo 'notify_user_created';
// 		echo '<pre>';
// 		var_dump($event);
// 		echo '</pre>';
		$this->notification_service->send_discord_notification('notify_user_created');
	}
}
