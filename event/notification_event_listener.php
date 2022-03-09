<?php
/**
 * Discord Notifications extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Tyler Olsen, https://github.com/rootslinux
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace mober\discordnotifications\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Discord Notifications Event listener. The subscribed events correspond to activity that we
 * may desire to generate and send a notification to Discord that describes the event.
 */
class notification_event_listener implements EventSubscriberInterface
{
	// These constants get prepended to their corresponding notification types
	const EMOJI_CREATE	= '📄';
	const EMOJI_UPDATE	= '📝';
	const EMOJI_DELETE	= '❌';
	const EMOJI_LOCK	= '🔒';
	const EMOJI_UNLOCK	= '🔓';
	const EMOJI_USER	= '👥';
	const EMOJI_BAN		= '🚫';

	const ELLIPSIS = '…';

	// These constants represent colors used for the Discord notification
	const COLOR_BRIGHT_GREEN	= 0x2DAF32;
	const COLOR_BRIGHT_BLUE		= 0x36A1E8;
	const COLOR_BRIGHT_RED		= 0xE83535;
	const COLOR_BRIGHT_ORANGE	= 0xD66539;
	const COLOR_BRIGHT_PURPLE	= 0xD635E8;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \mober\discordnotifications\notification_service */
	protected $notification_service;

	/** @var string php file extension  */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language                         $language
	 * @param \mober\discordnotifications\notification_service $notification_service
	 * @param string                                           $php_ext
	 * @access public
	 */
	public function __construct(
		\phpbb\language\language $language,
		\mober\discordnotifications\notification_service $notification_service,
		$php_ext
	) {
		$this->language = $language;
		$this->notification_service = $notification_service;
		$this->php_ext = $php_ext;

		// Add notifications text from the langauge file
		$this->language->add_lang('discord_notification_messages', 'mober/discordnotifications');
	}

	/**
	 * Assigns functions defined in this class to event listeners
	 *
	 * @return array
	 * @static
	 * @access public
	 *
	 * For description of the event types, refer to https://wiki.phpbb.com/Event_List
	 */
	static public function getSubscribedEvents()
	{
		return array(
			// This event is used for performing actions directly after a post or topic has been submitted.
			'core.submit_post_end'				=> 'handle_post_submit_action',
			// This event is used for performing actions directly after a post or topic has been deleted.
			'core.delete_post_after'			=> 'handle_post_delete_action',
			// Perform additional actions after locking/unlocking posts and topics
			'core.mcp_lock_unlock_after'		=> 'handle_lock_action',
			// Perform additional actions before topic(s) deletion
			'core.delete_topics_before_query'	=> 'handle_topic_delete_action',
			// Event that returns user id, user details and user CPF of newly registered user
			'core.user_add_after'				=> 'handle_user_add_action',
			// This event can be used to modify data after user account's activation
			'core.ucp_activate_after'			=> 'handle_user_activate_action',
			// Perform additional actions after the users have been activated/deactivated
			'core.user_active_flip_after'		=> 'handle_user_activate_action',
			// Event after a user is deleted
			'core.delete_user_after'			=> 'handle_user_delete_action',
			// Topic approval
			'core.approve_topics_after'			=> 'handle_topic_approval_action',
			// Post approval
			'core.approve_posts_after'			=> 'handle_post_approval_action',
		);
	}

	// ----------------------------------------------------------------------------
	// -------------------------- Event Handler Functions -------------------------
	// ----------------------------------------------------------------------------

	/**
	 * @param \phpbb\event\data $event Event object
	 */
	public function handle_topic_approval_action($event)
	{
		$notification_type_config_name = 'discord_notification_type_topic_approve';

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name))
		{
			foreach ($event['topic_info'] as $topic)
			{
				$webhook_url = $this->notification_service->get_forum_notification_url($topic['forum_id']);
				if ($webhook_url)
				{
					$this->notify_topic_approved($topic, $webhook_url);
				}
			}
		}
	}

	/**
	 * @param \phpbb\event\data $event Event object
	 */
	public function handle_post_approval_action($event)
	{
		$notification_type_config_name = 'discord_notification_type_post_approve';

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name))
		{
			foreach ($event['post_info'] as $post)
			{
				$webhook_url = $this->notification_service->get_forum_notification_url($post['forum_id']);
				if ($webhook_url)
				{
					$this->notify_post_approved($post, $webhook_url);
				}
			}
		}
	}

	/**
	 * Handles events generated by submitting a post. This could result in a notification of several different types.
	 *
	 * @param \phpbb\event\data $event Event object -- [data, mode, poll, post_visibility, subject, topic_type,
	 *                                 update_message, update_search_index, url, username]
	 *
	 * The possible notifications that can be generated as a result of these events include:
	 * - New post created
	 * - Post updated
	 * - New topic created
	 * - Topic updated
	 */
	public function handle_post_submit_action($event)
	{
		// Check for visibility of the post/topic. We don't send notifications for content that are hidden from normal users.
		// Note that there are three visibility settings here. The first is the post visibility when it is generated. For example,
		// users may require moderator approval before their posts appear. The other two are the existing visibility status of the topic and post.
		if ($event['post_visibility'] === 0 || $event['data']['topic_visibility'] === 0 || $event['data']['post_visibility'] === 0)
		{
			return;
		}

		// Verify that the forum that the post submit action happened in has notifications enabled. If not, we have nothing further to do.
		$webhook_url = $this->notification_service->get_forum_notification_url($event['data']['forum_id']);
		if (!$webhook_url)
		{
			return;
		}

		// Build an array of the event data that we may need to pass along to the function that will construct the notification message
		$post_data = array(
			'user_id'			=> $event['data']['poster_id'],
			'user_name'			=> $event['username'],
			'forum_id'			=> $event['data']['forum_id'],
			'forum_name'		=> $event['data']['forum_name'],
			'topic_id'			=> $event['data']['topic_id'],
			'topic_title'		=> $event['data']['topic_title'],
			'post_id'			=> $event['data']['post_id'],
			'post_title'		=> $event['subject'],
			'edit_user_id'		=> $event['data']['post_edit_user'],
			'edit_user_name'	=> $this->language->lang('UNKNOWN_USER'),
			'edit_reason'		=> $event['data']['post_edit_reason'],
			'content'			=> $event['data']['message'],
		);

		if ($post_data['edit_user_id'] == $post_data['user_id'])
		{
			$post_data['edit_user_name'] = $post_data['user_name'];
		}
		else
		{
			$post_data['edit_user_name'] = $this->language->lang('UNKNOWN_USER');
			$edit_name = $this->notification_service->query_user_name($post_data['edit_user_id']);
			if ($edit_name != null)
			{
				$post_data['edit_user_name'] = $edit_name;
			}
		}

		// Finally, based on the event characteristics we determine which kind of notification we need to send
		if ($event['mode'] == 'post') // New topic
		{
			$this->notify_topic_created($post_data, $webhook_url);
		}
		else if ($event['mode'] == 'reply' || $event['mode'] == 'quote') // New post
		{
			$this->notify_post_created($post_data, $webhook_url);
		}
		else if ($event['mode'] == 'edit' || $event['mode'] == 'edit_topic' || $event['mode'] == 'edit_first_post' || $event['mode'] == 'edit_last_post') // Edit existing post
		{
			// If the post that was edited is the first one in the topic, we consider this a topic update event.
			if ($event['data']['post_id'] == $event['data']['topic_first_post_id'])
			{
				$this->notify_topic_updated($post_data, $webhook_url);
			}
			// Otherwise we treat this as a post update event.
			else
			{
				$this->notify_post_updated($post_data, $webhook_url);
			}
		}
	}

	/**
	 * Handles events generated by deleting a post.
	 *
	 * @param \phpbb\event\data $event Event object -- [data, forum_id, is_soft, next_post_id, post_id, post_mode,
	 *                                 softdelete_reason, topic_id]
	 */
	public function handle_post_delete_action($event)
	{
		// Check for visibility of the post/topic. We don't send notifications for content that is hidden from normal users.
		if ($event['data']['topic_visibility'] == 0 || $event['data']['post_visibility'] == 0)
		{
			return;
		}

		// Verify that the forum that the post delete action happened in has notifications enabled. If not, we have nothing further to do.
		$webhook_url = $this->notification_service->get_forum_notification_url($event['forum_id']);
		if (!$webhook_url)
		{
			return;
		}

		// Build an array of the event data that we may need to pass along to the function that will construct the notification message.
		// Note that unfortunately, the event data does not give us any information indicating which user deleted the post.
		$post_data = array(
			'user_id'		=> $event['data']['poster_id'],
			'user_name'		=> $this->language->lang('UNKNOWN_USER'),
			'forum_id'		=> $event['forum_id'],
			'forum_name'	=> $this->language->lang('UNKNOWN_FORUM'),
			'topic_id'		=> $event['topic_id'],
			'topic_title'	=> $this->language->lang('UNKNOWN_TOPIC'),
			'post_id'		=> $event['post_id'],
			'delete_reason'	=> $event['softdelete_reason'],
		);

		// Fetch the forum name, topic title, and user name using the respective IDs.
		$forum_name = $this->notification_service->query_forum_name($post_data['forum_id']);
		if ($forum_name)
		{
			$post_data['forum_name'] = $forum_name;
		}
		$topic_title = $this->notification_service->query_topic_title($post_data['topic_id']);
		if ($topic_title)
		{
			$post_data['topic_title'] = $topic_title;
		}
		$user_name = $this->notification_service->query_user_name($post_data['user_id']);
		if ($user_name)
		{
			$post_data['user_name'] = $user_name;
		}

		$this->notify_post_deleted($post_data, $webhook_url);
	}

	/**
	 * Handles events generated by deleting a topic.
	 *
	 * @param \phpbb\event\data $event Event object -- [table_ary, topic_ids]
	 */
	public function handle_topic_delete_action($event)
	{
		// Notification messages can get complicated when more than one topic is deleted in a transaction. We choose to not generate
		// a notification in this case.
		if (count($event['topic_ids']) > 1)
		{
			return;
		}

		// Unfortunately the only useful data we get from this event is the topic ID. We have to run a custom query to retrieve the
		// rest of the data that we are interested in.
		$topics_ids = $event['topic_ids'];
		$topic_id = (int) array_pop($topics_ids);
		$query_data = $this->notification_service->query_topic_details($topic_id);

		// Check for visibility of the topic. We don't send notifications for content that is hidden from normal users.
		if ($query_data['topic_visibility'] == 0)
		{
			return;
		}

		// Verify that the forum that the topic delete action happened in has notifications enabled. If not, we have nothing further to do.
		$webhook_url = $this->notification_service->get_forum_notification_url($query_data['forum_id']);
		if (!$webhook_url)
		{
			return;
		}

		// Copy over the data necessary to generate the notification into a new array
		$delete_data = array();
		$delete_data['forum_id'] = $query_data['forum_id'];
		$delete_data['forum_name'] = $query_data['forum_name'];
		$delete_data['topic_title'] = $query_data['topic_title'];
		$delete_data['topic_post_count'] = $query_data['topic_posts_approved'];
		$delete_data['user_id'] = $query_data['topic_poster'];
		$delete_data['user_name'] = $query_data['topic_first_poster_name'];

		$this->notify_topic_deleted($delete_data, $webhook_url);
	}

	/**
	 * Handles events generated by changing the lock status on a topic or post.
	 *
	 * @param \phpbb\event\data $event Event object -- [action, data, ids]
	 *
	 * The possible notifications that can be generated as a result of these events include:
	 * - Post locked
	 * - Post unlocked
	 * - Topic locked
	 * - Topic unlocked
	 */
	public function handle_lock_action($event)
	{
		// Notification messages can get complicated if we have multiple topics or posts that are locked/unlocked in a single transaction.
		// Presently we choose not to take any action on such operations.
		if (count($event['ids']) > 1)
		{
			return;
		}

		// Get the ID needed to access $event['data'], then extract all relevant data from the event that we need to generate the notification
		$id = array_pop($event['ids']);

		$lock_data = array();
		$lock_data['forum_id'] = $event['data'][$id]['forum_id'];
		$lock_data['forum_name'] = $event['data'][$id]['forum_name'];
		$lock_data['post_id'] = $event['data'][$id]['post_id'];
		$lock_data['post_subject'] = $event['data'][$id]['post_subject'];
		$lock_data['topic_id'] = $event['data'][$id]['topic_id'];
		$lock_data['topic_title'] = $event['data'][$id]['topic_title'];
		// Two sets of user data captured: one for the post (if applicable) and one for the user that started the topic
		$lock_data['post_user_id'] = $event['data'][$id]['poster_id'];
		$lock_data['post_user_name'] = $event['data'][$id]['username'];
		$lock_data['topic_user_id'] = $event['data'][$id]['topic_poster'];
		$lock_data['topic_user_name'] = $event['data'][$id]['topic_first_poster_name'];

		// If the forum the post was made in does not have notifications enabled or the topic/poar is not visible, do nothing more.
		$topic_visibile = $event['data'][$id]['topic_visibility'] == 1 ? true : false;
		$webhook_url = $this->notification_service->get_forum_notification_url($lock_data['forum_id']);
		if (!$webhook_url || $topic_visibile == false)
		{
			return;
		}

		// The action determines whether the action was a lock or unlock event and thus which notification to generate
		switch ($event['action'])
		{
			case 'lock_post':
				$this->notify_post_locked($lock_data, $webhook_url);
				break;
			case 'unlock_post':
				$this->notify_post_unlocked($lock_data, $webhook_url);
				break;
			case 'lock':
				$this->notify_topic_locked($lock_data, $webhook_url);
				break;
			case 'unlock':
				$this->notify_topic_unlocked($lock_data, $webhook_url);
				break;
		}
	}

	/**
	 * Handles events generated by the creation of a new user account.
	 *
	 * @param \phpbb\event\data $event Event object -- [cp_data, user_id, user_row]
	 *
	 * If the user account that was created is initially inactive or a bot, we don't generate a user created
	 * notification. Instead, we wait until the user activates their account.
	 */
	public function handle_user_add_action($event)
	{
		// Only generate a notification if the user starts off as a normal, activated user.
		if ($event['user_row']['user_type'] != USER_NORMAL)
		{
			return;
		}

		// Notifications are only generated if user activation is disabled and this is a normal user
		$user_data['user_id'] = $event['user_id'];
		$user_data['user_name'] = $event['user_row']['username'];

		$this->notify_user_created($user_data);
	}

	/**
	 * Handles events generated by the activation or deactivation of a user account.
	 *
	 * @param \phpbb\event\data $event Event object -- [message, user_row] -or- [activated, deactivated, mode, reason,
	 *                                 sql_statements, user_id_ary]
	 *
	 * There are two different types of events that can trigger this function to be called, and each one provides
	 * different types of event data. The first type of event, "core.ucp_activate_after" occurs when a user activates
	 * their own account. The second event, "core.user_active_flip_after" occurs when an adminstrator either activates
	 * or deactivates one or more user accounts. We look at the event data to figure out which type of event occurred
	 * and from there pass along the appropriate data to the notify_user_created() function.
	 */
	public function handle_user_activate_action($event)
	{
		$user_data = array();
		if ($event['user_id'])
		{
			$user_data['user_id'] = $event['user_id'];
			$user_data['user_name'] = $event['user_row']['username'];
		}
		else if ($event['activated'] == 1)
		{
			$user_data['user_id'] = array_pop($event['user_id_ary']);
			$user_name = $this->notification_service->query_user_name($user_data['user_id']);
			$user_data['user_name'] = ($user_name != null) ? $user_name : $this->language->lang('UNKNOWN_USER');
		}
		// Ignore deactivated user case
		else
		{
			return;
		}

		$this->notify_user_created($user_data);
	}

	/**
	 * Handles events generated by the deletion of one or more user account.
	 *
	 * @param \phpbb\event\data $event Event object -- [mode, retain_username, user_ids, user_rows]
	 */
	public function handle_user_delete_action($event)
	{
		// Extract the IDs and names of all deleted users to pass along in an array of (id => name)
		$user_data = array();
		foreach ($event['user_ids'] as $id)
		{
			$user_data[$id] = $event['user_rows'][$id]['username'];
		}

		$this->notify_users_deleted($user_data);
	}

	// ----------------------------------------------------------------------------
	// --------------------- Notification Generation Functions --------------------
	// ----------------------------------------------------------------------------

	/**
	 * Sends a notification to Discord when a new post is created.
	 *
	 * @param array  $data of attributes for the new post
	 * @param string $webhook_url
	 */
	private function notify_post_created($data, $webhook_url)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_post_create';
		$color = self::COLOR_BRIGHT_GREEN;
		$emoji = self::EMOJI_CREATE;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false)
		{
			return;
		}

		// Construct the notification message using the post data
		$user_link = $this->generate_user_link($data['user_id'], $data['user_name']);
		$post_link = $this->generate_post_link($data['topic_id'], $data['post_id'], $this->language->lang('POST'));
		$topic_link = $this->generate_topic_link($data['topic_id'], $data['topic_title']);
		$forum_link = $this->generate_forum_link($data['forum_id'], $data['forum_name']);
		$message = sprintf($this->language->lang('CREATE_POST'),
			$emoji, $user_link, $post_link, $topic_link, $forum_link
		);

		// Generate a post preview if necessary
		$footer = $this->generate_footer_text($this->language->lang('PREVIEW'), $data['content']);

		$this->notification_service->send_discord_notification($color, $message, $footer, $webhook_url);
	}

	/**
	 * Sends a notification to Discord when a post is updated.
	 *
	 * @param array  $data of attributes for the updated post
	 * @param string $webhook_url
	 */
	private function notify_post_updated($data, $webhook_url)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_post_update';
		$color = self::COLOR_BRIGHT_BLUE;
		$emoji = self::EMOJI_UPDATE;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false)
		{
			return;
		}

		// Construct the notification message using the post data
		$user_link = $this->generate_user_link($data['user_id'], $data['user_name']);
		$post_link = $this->generate_post_link($data['topic_id'], $data['post_id'], $this->language->lang('POST'));
		$topic_link = $this->generate_topic_link($data['topic_id'], $data['topic_title']);
		$forum_link = $this->generate_forum_link($data['forum_id'], $data['forum_name']);

		// The notification is slightly different depending on whether the user edited their own post, or another user made the edit.
		$message = '';
		if ($data['user_id'] == $data['edit_user_id'])
		{
			$message = sprintf($this->language->lang('UPDATE_POST_SELF'),
				$emoji, $user_link, $post_link, $topic_link, $forum_link
			);
		}
		else
		{
			$edit_user_link = $this->generate_user_link($data['edit_user_id'], $data['edit_user_name']);
			$message = sprintf($this->language->lang('UPDATE_POST_OTHER'),
				$emoji, $edit_user_link, $post_link, $user_link, $topic_link, $forum_link
			);
		}

		// If we allow previews and an edit reason was given, add that information in the footer
		$footer = null;
		if (isset($data['edit_reason']) && $data['edit_reason'] !== '')
		{
			$footer = $this->generate_footer_text($this->language->lang('REASON'), $data['edit_reason']);
		}

		$this->notification_service->send_discord_notification($color, $message, $footer, $webhook_url);
	}

	/**
	 * Sends a notification to Discord when a post is deleted.
	 *
	 * @param array  $data of attributes for the deleted post
	 * @param string $webhook_url
	 */
	private function notify_post_deleted($data, $webhook_url)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_post_delete';
		$color = self::COLOR_BRIGHT_RED;
		$emoji = self::EMOJI_DELETE;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false)
		{
			return;
		}

		// Construct the notification message using the post data.
		$user_name = empty($data['user_name']) ? $this->language->lang('UNKNOWN_USER') : $data['user_name'];
		$user_link = $this->generate_user_link($data['user_id'], $user_name);

		$topic_title = empty($data['topic_title']) ? $this->language->lang('UNKNOWN_TOPIC') : $data['topic_title'];
		$topic_link = $this->generate_topic_link($data['topic_id'], $topic_title);

		$forum_name = empty($data['forum_name']) ? $this->language->lang('UNKNOWN_FORUM') : $data['forum_name'];
		$forum_link = $this->generate_forum_link($data['forum_id'], $forum_name);

		$message = sprintf($this->language->lang('DELETE_POST'),
			$emoji, $user_link, $topic_link, $forum_link
		);

		// If there was a reason specified for the delete, include that in the message footer.
		$footer = null;
		if (is_string($data['delete_reason']) && $data['delete_reason'] !== '')
		{
			$footer = $this->generate_footer_text($this->language->lang('REASON'), $data['delete_reason']);
		}

		$this->notification_service->send_discord_notification($color, $message, $footer, $webhook_url);
	}

	/**
	 * Sends a notification to Discord when a post is locked.
	 *
	 * @param array  $data of attributes for the locked post
	 * @param string $webhook_url
	 */
	private function notify_post_locked($data, $webhook_url)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_post_lock';
		$color = self::COLOR_BRIGHT_ORANGE;
		$emoji = self::EMOJI_LOCK;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false)
		{
			return;
		}

		// Construct the notification message using the argument data
		$user_link = $this->generate_user_link($data['post_user_id'], $data['post_user_name']);
		$forum_link = $this->generate_forum_link($data['forum_id'], $data['forum_name']);
		$topic_link = $this->generate_topic_link($data['topic_id'], $data['topic_title']);
		$post_link = $this->generate_post_link($data['topic_id'], $data['post_id'], $this->language->lang('POST'));
		$message = sprintf($this->language->lang('LOCK_POST'),
			$emoji, $post_link, $user_link, $topic_link, $forum_link
		);

		$this->notification_service->send_discord_notification($color, $message, null, $webhook_url);
	}

	/**
	 * Sends a notification to Discord when a post is unlocked.
	 *
	 * @param array  $data of attributes for the unlocked post
	 * @param string $webhook_url
	 */
	private function notify_post_unlocked($data, $webhook_url)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_post_unlock';
		$color = self::COLOR_BRIGHT_ORANGE;
		$emoji = self::EMOJI_UNLOCK;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false)
		{
			return;
		}

		// Construct the notification message using the argument data
		$user_link = $this->generate_user_link($data['post_user_id'], $data['post_user_name']);
		$forum_link = $this->generate_forum_link($data['forum_id'], $data['forum_name']);
		$topic_link = $this->generate_topic_link($data['topic_id'], $data['topic_title']);
		$post_link = $this->generate_post_link($data['topic_id'], $data['post_id'], $this->language->lang('POST'));
		$message = sprintf($this->language->lang('UNLOCK_POST'),
			$emoji, $post_link, $user_link, $topic_link, $forum_link
		);

		$this->notification_service->send_discord_notification($color, $message, null, $webhook_url);
	}

	/**
	 * Sends a notification to Discord when a new topic is created.
	 *
	 * @param array  $data of attributes for the new topic
	 * @param string $webhook_url
	 */
	private function notify_topic_created($data, $webhook_url)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_topic_create';
		$color = self::COLOR_BRIGHT_GREEN;
		$emoji = self::EMOJI_CREATE;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false)
		{
			return;
		}

		// Construct the notification message using the argument data
		$user_link = $this->generate_user_link($data['user_id'], $data['user_name']);
		$forum_link = $this->generate_forum_link($data['forum_id'], $data['forum_name']);
		$topic_link = $this->generate_topic_link($data['topic_id'], $data['topic_title']);
		$message = sprintf($this->language->lang('CREATE_TOPIC'),
			$emoji, $user_link, $topic_link, $forum_link
		);

		// Generates a topic preview if necessary
		$footer = $this->generate_footer_text($this->language->lang('PREVIEW'), $data['content']);

		$this->notification_service->send_discord_notification($color, $message, $footer, $webhook_url);
	}

	/**
	 * Sends notification when a topic is approved.
	 *
	 * @param array  $data Topic data
	 * @param string $webhook_url
	 */
	private function notify_topic_approved($data, $webhook_url)
	{
		// Constant properties for this notification type
		$color = self::COLOR_BRIGHT_GREEN;
		$emoji = self::EMOJI_CREATE;

		// Construct the notification message using the argument data
		$user_link = $this->generate_user_link($data['topic_poster'], $data['topic_first_poster_name']);
		$forum_link = $this->generate_forum_link($data['forum_id'], $data['forum_name']);
		$topic_link = $this->generate_topic_link($data['topic_id'], $data['topic_title']);
		$message = sprintf($this->language->lang('APPROVE_TOPIC'),
						   $emoji, $topic_link, $forum_link, $user_link
		);

		// Generates a topic preview if necessary
		$footer = $this->generate_footer_text($this->language->lang('PREVIEW'), $data['post_text']);

		$this->notification_service->send_discord_notification($color, $message, $footer, $webhook_url);
	}

	/**
	 * Sends notification when a post is approved.
	 *
	 * @param array  $data Post data
	 * @param string $webhook_url
	 */
	private function notify_post_approved($data, $webhook_url)
	{
		// Constant properties for this notification type
		$color = self::COLOR_BRIGHT_GREEN;
		$emoji = self::EMOJI_CREATE;

		// Construct the notification message using the post data
		$user_link = $this->generate_user_link($data['poster_id'], $data['username']);
		$post_link = $this->generate_post_link($data['topic_id'], $data['post_id'], $this->language->lang('POST'));
		$topic_link = $this->generate_topic_link($data['topic_id'], $data['topic_title']);
		$forum_link = $this->generate_forum_link($data['forum_id'], $data['forum_name']);
		$message = sprintf($this->language->lang('APPROVE_POST'),
						   $emoji, $post_link, $user_link, $topic_link, $forum_link
		);

		// Generate a post preview if necessary
		$footer = $this->generate_footer_text($this->language->lang('PREVIEW'), $data['post_text']);

		$this->notification_service->send_discord_notification($color, $message, $footer, $webhook_url);
	}

	/**
	 * Sends a notification to Discord when a topic is updated.
	 *
	 * @param array  $data of attributes for the updated topic
	 * @param string $webhook_url
	 */
	private function notify_topic_updated($data, $webhook_url)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_topic_update';
		$color = self::COLOR_BRIGHT_BLUE;
		$emoji = self::EMOJI_UPDATE;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false)
		{
			return;
		}

		// Construct the notification message using the topic data
		$user_link = $this->generate_user_link($data['user_id'], $data['user_name']);
		$topic_link = $this->generate_topic_link($data['topic_id'], $data['topic_title']);
		$forum_link = $this->generate_forum_link($data['forum_id'], $data['forum_name']);

		// The notification is slightly different depending on whether the user edited their own topic, or another user made the edit
		$message = '';
		if ($data['user_id'] == $data['edit_user_id'])
		{
			$message = sprintf($this->language->lang('UPDATE_TOPIC_SELF'),
				$emoji, $user_link, $topic_link, $forum_link
			);
		}
		else
		{
			$edit_user_link = $this->generate_user_link($data['edit_user_id'], $data['edit_user_name']);
			$message = sprintf($this->language->lang('UPDATE_TOPIC_OTHER'),
				$emoji, $edit_user_link, $topic_link, $user_link, $forum_link
			);
		}

		// If an edit reason was given, add that information in the footer
		$footer = null;
		if (isset($data['edit_reason']) && $data['edit_reason'] !== '')
		{
			$footer = $this->generate_footer_text($this->language->lang('REASON'), $data['edit_reason']);
		}

		$this->notification_service->send_discord_notification($color, $message, $footer, $webhook_url);
	}

	/**
	 * Sends a notification to Discord when a topic is deleted.
	 *
	 * @param array  $data of attributes for the deleted topic
	 * @param string $webhook_url
	 */
	private function notify_topic_deleted($data, $webhook_url)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_topic_delete';
		$color = self::COLOR_BRIGHT_RED;
		$emoji = self::EMOJI_DELETE;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false)
		{
			return;
		}

		// Construct the notification message using the argument data
		$user_link = $this->generate_user_link($data['user_id'], $data['user_name']);
		$forum_link = $this->generate_forum_link($data['forum_id'], $data['forum_name']);
		$topic_title = html_entity_decode($data['topic_title'], ENT_COMPAT);

		$message = sprintf($this->language->lang('DELETE_TOPIC'),
			$emoji, $user_link, $topic_title, $data['topic_post_count'], $forum_link
		);

		$this->notification_service->send_discord_notification($color, $message, null, $webhook_url);
	}

	/**
	 * Sends a notification to Discord when a topic is locked.
	 *
	 * @param array  $data of attributes for the locked topic
	 * @param string $webhook_url
	 */
	private function notify_topic_locked($data, $webhook_url)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_topic_lock';
		$color = self::COLOR_BRIGHT_ORANGE;
		$emoji = self::EMOJI_LOCK;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false)
		{
			return;
		}

		// Construct the notification message using the argument data
		$user_link = $this->generate_user_link($data['topic_user_id'], $data['topic_user_name']);
		$forum_link = $this->generate_forum_link($data['forum_id'], $data['forum_name']);
		$topic_link = $this->generate_topic_link($data['topic_id'], $data['topic_title']);
		$message = sprintf($this->language->lang('LOCK_TOPIC'),
			$emoji, $topic_link, $forum_link, $user_link
		);

		$this->notification_service->send_discord_notification($color, $message, null, $webhook_url);
	}

	/**
	 * Sends a notification to Discord when a topic is unlocked.
	 *
	 * @param array  $data of attributes for the unlocked topic
	 * @param string $webhook_url
	 */
	private function notify_topic_unlocked($data, $webhook_url)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_topic_unlock';
		$color = self::COLOR_BRIGHT_ORANGE;
		$emoji = self::EMOJI_UNLOCK;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false)
		{
			return;
		}

		// Construct the notification message using the argument data
		$user_link = $this->generate_user_link($data['topic_user_id'], $data['topic_user_name']);
		$forum_link = $this->generate_forum_link($data['forum_id'], $data['forum_name']);
		$topic_link = $this->generate_topic_link($data['topic_id'], $data['topic_title']);
		$message = sprintf($this->language->lang('UNLOCK_TOPIC'),
			$emoji, $topic_link, $forum_link, $user_link
		);

		$this->notification_service->send_discord_notification($color, $message, null, $webhook_url);
	}

	/**
	 * Sends a notification to Discord when a new user account is created.
	 *
	 * @param array $data of attributes for the new user
	 *
	 * Notification details include the user name and a link to the user's profile page.
	 */
	private function notify_user_created($data)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_user_create';
		$color = self::COLOR_BRIGHT_PURPLE;
		$emoji = self::EMOJI_USER;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false)
		{
			return;
		}

		// Construct the notification message using the argument data.
		$user_link = $this->generate_user_link($data['user_id'], $data['user_name']);
		$message = sprintf($this->language->lang('CREATE_USER'),
			$emoji, $user_link
		);

		$this->notification_service->send_discord_notification($color, $message);
	}

	/**
	 * Sends a notification to Discord when one or more user accounts are deleted.
	 *
	 * @param array $data of attributes for the deleted users (id => username)
	 *
	 * The notification lists the usernames of those deleted but provides no links or additional information such as
	 * deletion reason.
	 */
	private function notify_users_deleted($data)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_user_delete';
		$color = self::COLOR_BRIGHT_PURPLE;
		$emoji = self::EMOJI_USER;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false)
		{
			return;
		}

		// Construct the notification message using the argument data.
		$message = '';
		// The message format is slightly different depending on how many users were deleted.
		if (count($data) == 1)
		{
			$user_name = array_pop($data);
			$message = sprintf($this->language->lang('DELETE_USER'),
				$emoji, $user_name
			);
		}
		else
		{
			$deleted_users_text = '';
			$and = $this->language->lang('AND');
			$comma = $this->language->lang('CONJ');
			if (count($data) == 2)
			{
				$deleted_users_text = array_pop($data) . " $and " . array_pop($data);
			}
			else if (count($data) == 3)
			{
				$deleted_users_text = array_pop($data) . "$comma " . array_pop($data) . "$comma $and " . array_pop($data);
			}
			// If more than three users were deleted, we display three user names and then the number of additional deletions
			else if (count($data) > 3)
			{
				$deleted_users_text = array_pop($data) . "$comma " . array_pop($data) . "$comma " . array_pop($data) . "$comma $and " . count($data);
				// Singular vs plural case check
				if (count($data) == 1)
				{
					$deleted_users_text .= " " . $this->language->lang('OTHER');
				}
				else
				{
					$deleted_users_text .= " " . $this->language->lang('OTHERS');
				}
			}

			$message = sprintf($this->language->lang('DELETE_MULTI_USER'),
				$emoji, $deleted_users_text
			);
		}

		$this->notification_service->send_discord_notification($color, $message);
	}

	// ----------------------------------------------------------------------------
	// ----------------------------- Helper Functions -----------------------------
	// ----------------------------------------------------------------------------

	/**
	 * The Discord webhook api does not accept urlencoded text. This function replaces problematic characters.
	 *
	 * @param string $url
	 * @return string Formatted URL text
	 */
	private function reformat_link_url($url)
	{
		$url = str_replace(" ", "%20", $url);
		$url = str_replace("(", "%28", $url);
		$url = str_replace(")", "%29", $url);
		return $url;
	}

	/**
	 * Discord link text must be surrounded by []. This function replaces problematic characters
	 *
	 * @param string $text Text link
	 * @return string Formatted link-safe text
	 */
	private function reformat_link_text($text)
	{
		$text = str_replace("[", "(", $text);
		$text = str_replace("]", ")", $text);
		$text = html_entity_decode($text, ENT_COMPAT);
		return $text;
	}

	/**
	 * Removes all HTML and BBcode formatting tags from a string
	 *
	 * @param string $text Text link
	 * @return string Formatted text
	 *
	 * Note that there is some risk here of the text not coming out exactly as we may like. The user text
	 * may include characters that look like pseudo-HTML and get picked up by the regex used.
	 */
	private function remove_formatting($text)
	{
		$text = strip_tags($text);
		$text = preg_replace('|[[\/\!]*?[^\[\]]*?]|si', '', $text);
		$text = html_entity_decode($text, ENT_COMPAT);
		return $text;
	}

	/**
	 * Given the ID of a forum, returns text that contains a link to view the forum
	 *
	 * @param int    $forum_id The ID of the forum
	 * @param string $text     The text to display for the post link
	 * @return string Text formatted in the notation that Discord would interpret.
	 */
	private function generate_forum_link($forum_id, $text)
	{
		$url = generate_board_url() . '/viewforum.' . $this->php_ext . '?f=' . $forum_id;
		$url = $this->reformat_link_url($url);
		$text = $this->reformat_link_text($text);
		return sprintf('[%s](%s)', $text, $url);
	}

	/**
	 * Given the ID of a valid post, returns text that contains the post title with a link to the post.
	 *
	 * @param int    $topic_id The ID of the topic
	 * @param int    $post_id  The ID of the post
	 * @param string $text     The text to display for the post link
	 * @return string Text formatted in the notation that Discord would interpret.
	 */
	private function generate_post_link($topic_id, $post_id, $text)
	{
		$url = generate_board_url() . '/viewtopic.' . $this->php_ext . '?t=' . $topic_id . '&p=' . $post_id . '#p' . $post_id;
		$url = $this->reformat_link_url($url);
		$text = $this->reformat_link_text($text);
		return sprintf('[%s](%s)', $text, $url);
	}

	/**
	 * Given the ID of a valid topic, returns text that contains the topic title with a link to the topic.
	 *
	 * @param int    $topic_id The ID of the topic
	 * @param string $text     The text to display for the topic link
	 * @return string Text formatted in the notation that Discord would interpret.
	 */
	private function generate_topic_link($topic_id, $text)
	{
		$url = generate_board_url() . '/viewtopic.' . $this->php_ext . '?t=' . $topic_id;
		$url = $this->reformat_link_url($url);
		$text = $this->reformat_link_text($text);
		return sprintf('[%s](%s)', $text, $url);
	}

	/**
	 * Given the ID of a valid user, returns text that contains the user name with a link to their user profile.
	 *
	 * @param int    $user_id The ID of the user
	 * @param string $text    The text to display for the user link
	 * @return string Text formatted in the notation that Discord would interpret.
	 */
	private function generate_user_link($user_id, $text)
	{
		$url = generate_board_url() . '/memberlist.' . $this->php_ext . '?mode=viewprofile&u=' . $user_id;
		$url = $this->reformat_link_url($url);
		$text = $this->reformat_link_text($text);
		return sprintf('[%s](%s)', $text, $url);
	}

	/**
	 * Formats and prepares text to be placed in the footer of a notification message.
	 *
	 * @param string $prepend_text Text to add before the content
	 * @param string $content      The raw text to place in the footer
	 * @return string A string meeting the configuration requirements for a footer, or NULL if a footer should not be
	 *                generated at all.
	 */
	private function generate_footer_text($prepend_text, $content)
	{
		$preview_length = $this->notification_service->get_post_preview_length();
		if ($preview_length == 0)
		{
			return null;
		}

		$footer = $this->remove_formatting($content);

		// Truncate the content if it is too long and add '...' for the last three characters. The preview length will
		// always be at least 10 characters so we don't need to worry about really short strings.
		if (mb_strlen($footer) > $preview_length)
		{
			$footer = mb_substr($footer, 0, $preview_length - 3) . self::ELLIPSIS;
		}

		// Prepend text to the footer so that it's clear what content we are sharing in the footer
		$footer = $prepend_text . $footer;
		return $footer;
	}
}
