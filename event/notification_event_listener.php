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

			// Event after a user is deleted
			'core.delete_user_after' => 'notify_user_deleted',
		);
	}

	/**
	 * Sends a notification to Discord when a new post is created.
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function notify_post_created($event)
	{
		// Check config settings first to see if we need to send a notification for this event
		if ($this->notification_service->is_notification_type_enabled('discord_notification_type_post_create') == false) {
			return;
		}

		$this->notification_service->send_discord_notification('notify_post_created', 'create');
	}

		/**
	 * Sends a notification to Discord when a post is updated.
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function notify_post_updated($event)
	{
		// Check config settings first to see if we need to send a notification for this event
		if ($this->notification_service->is_notification_type_enabled('discord_notification_type_post_update') == false) {
			return;
		}

		$this->notification_service->send_discord_notification('notify_post_updated', 'update');
	}

	/**
	 * Sends a notification to Discord when a post is deleted.
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function notify_post_deleted($event)
	{
		// Check config settings first to see if we need to send a notification for this event
		if ($this->notification_service->is_notification_type_enabled('discord_notification_type_post_delete') == false) {
			return;
		}

		$this->notification_service->send_discord_notification('notify_post_deleted', 'delete');
	}

	/**
	 * Sends a notification to Discord when a post is locked or unlocked.
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function notify_post_lock_status($event)
	{
		// TODO: Determine if the post is being locked or unlocked

		// Check config settings first to see if we need to send a notification for this event
		if ($this->notification_service->is_notification_type_enabled('discord_notification_type_post_lock') == false) {
			return;
		}

		$this->notification_service->send_discord_notification('notify_post_lock_status', 'lock');
	}

	/**
	 * Sends a notification to Discord when a new topic is created.
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function notify_topic_created($event)
	{
		// Check config settings first to see if we need to send a notification for this event
		if ($this->notification_service->is_notification_type_enabled('discord_notification_type_topic_create') == false) {
			return;
		}

		$this->notification_service->send_discord_notification('notify_topic_created', 'create');
	}

	/**
	 * Sends a notification to Discord when a topic is updated.
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function notify_topic_updated($event)
	{
		// Check config settings first to see if we need to send a notification for this event
		if ($this->notification_service->is_notification_type_enabled('discord_notification_type_topic_update') == false) {
			return;
		}

		$this->notification_service->send_discord_notification('notify_topic_updated', 'update');
	}

	/**
	 * Sends a notification to Discord when a topic is created.
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function notify_topic_deleted($event)
	{
		// Check config settings first to see if we need to send a notification for this event
		if ($this->notification_service->is_notification_type_enabled('discord_notification_type_topic_delete') == false) {
			return;
		}

		$this->notification_service->send_discord_notification('notify_topic_deleted', 'delete');
	}

	/**
	 * Sends a notification to Discord when a topic is locked or unlocked.
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function notify_topic_lock_status($event)
	{
		// TODO: Determine if the topic is being locked or unlocked

		// Check config settings first to see if we need to send a notification for this event
		if ($this->notification_service->is_notification_type_enabled('discord_notification_type_topic_lock') == false) {
			return;
		}

		$this->notification_service->send_discord_notification('notify_topic_lock_status', 'lock');
	}

	/**
	 * Sends a notification to Discord when a new user account is created.
	 * @param \phpbb\event\data	$event	Event object -- arguments(cp_data, user_id, user_row)
	 *
	 * Notification details include the user name and a link to the user's profile page
	 */
	public function notify_user_created($event)
	{
		// Check config settings first to see if we need to send a notification for this event
		if ($this->notification_service->is_notification_type_enabled('discord_notification_type_user_create') == false) {
			return;
		}

		$user_id = $event['user_id'];

		$this->notification_service->send_discord_notification('notify_user_created', 'user');
	}

	/**
	 * Sends a notification to Discord when a user account is deleted.
	 * @param \phpbb\event\data	$event	Event object -- arguments(mode, retain_username, user_ids, user_rows )
	 */
	public function notify_user_deleted($event)
	{
		// Check config settings first to see if we need to send a notification for this event
		if ($this->notification_service->is_notification_type_enabled('discord_notification_type_user_create') == false) {
			return;
		}

		$this->notification_service->send_discord_notification('notify_user_deleted', 'delete');
	}
}
