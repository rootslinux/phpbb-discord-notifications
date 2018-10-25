<?php
/**
 * Discord Notifications extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Tyler Olsen, https://github.com/rootslinux
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace roots\discordnotifications\event;

if (!defined('IN_PHPBB'))
{
	exit;
}

// TODO: temporary debugging function -- remove before any official release
function dumpy($anything)
{
	global $phpbb_root_path;
	$log_file = $phpbb_root_path . 'store/dn_ext.log';
	$entry = print_r($anything, true) . PHP_EOL;
	file_put_contents($log_file, $entry, FILE_APPEND | LOCK_EX);
}

/**
 * The Discord webhook api does not accept urlencoded text. This function replaces problematic characters.
 * @param $url
 * @return Formatted URL text
 */
function reformat_link_url($url)
{
	$url = str_replace(" ", "%20", $url);
	$url = str_replace("(", "%28", $url);
	$url = str_replace(")", "%29", $url);
	return $url;
}

/**
 * Discord link text must be surrounded by []. This function replaces problematic characters
 * @param $text Text link
 * @return Formatted link-safe text
 */
function reformat_link_text($text)
{
	$text = str_replace("[", "(", $text);
	$text = str_replace("]", ")", $text);
	return $text;
}

/**
 * Given the ID of a forum, returns text that contains a link to view the forum
 * @param $topic_id The ID of the topic
 * @param $post_id The ID of the post
 * @param $text The text to display for the post link
 * @return Text formatted in the notation that Discord would interpret.
 */
function generate_forum_link($forum_id, $text)
{
	$url = generate_board_url() . '/viewforum.php?f=' . $forum_id;
	$url = reformat_link_url($url);
	$text = reformat_link_text($text);
	return sprintf('[%s](%s)', $text, $url);
}

/**
 * Given the ID of a valid post, returns text that contains the post title with a link to the post.
 * @param $topic_id The ID of the topic
 * @param $post_id The ID of the post
 * @param $text The text to display for the post link
 * @return Text formatted in the notation that Discord would interpret.
 */
function generate_post_link($topic_id, $post_id, $text)
{
	$url = generate_board_url() . '/viewtopic.php?t=' . $topic_id . '#p' . $post_id;
	$url = reformat_link_url($url);
	$text = reformat_link_text($text);
	return sprintf('[%s](%s)', $text, $url);
}

/**
 * Given the ID of a valid topic, returns text that contains the topic title with a link to the topic.
 * @param $topic_id The ID of the topic
 * @param $text The text to display for the topic link
 * @return Text formatted in the notation that Discord would interpret.
 */
function generate_topic_link($topic_id, $text)
{
	$url = generate_board_url() . '/viewtopic.php?t=' . $topic_id;
	$url = reformat_link_url($url);
	$text = reformat_link_text($text);
	return sprintf('[%s](%s)', $text, $url);
}

/**
 * Given the ID of a valid user, returns text that contains the user name with a link to their user profile.
 * @param $user_id The ID of the user
 * @param $text The text to display for the user link
 * @return Text formatted in the notation that Discord would interpret.
 */
function generate_user_link($user_id, $text)
{
	$url = generate_board_url() . '/memberlist.php?mode=viewprofile&u=' . $user_id;
	$url = reformat_link_url($url);
	$text = reformat_link_text($text);
	return sprintf('[%s](%s)', $text, $url);
}

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

	// These constants represent colors used for the Discord notification. The numbers are decimal representations of hexadecimal color codes.
	const COLOR_BRIGHT_GREEN	= 2993970;
	const COLOR_BRIGHT_BLUE		= 3580392;
	const COLOR_BRIGHT_RED		= 15217973;
	const COLOR_BRIGHT_ORANGE	= 14050617;
	const COLOR_BRIGHT_PURPLE	= 14038504;

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

		// TODO: load data from cache
	}

	/**
	 * Assigns functions defined in this class to event listeners
	 * @return array
	 * @static
	 * @access public
	 */
	static public function getSubscribedEvents()
	{
		return array(
			// This event is used for performing actions directly after a post or topic has been submitted.
			'core.submit_post_end'			=> 'handle_post_submit_action',
			// This event is used for performing actions directly after a post or topic has been deleted.
			'core.delete_post_after'		=> 'handle_post_delete_action',
			// Perform additional actions after locking/unlocking posts/topics
			'core.mcp_lock_unlock_after'	=> 'handle_post_lock_action',
			// Event that returns user id, user details and user CPF of newly registered user
			'core.user_add_after'			=> 'handle_user_add_action',
			// Event after a user is deleted
			'core.delete_user_after'		=> 'handle_user_delete_action',

// 			// This event is used for performing actions directly after a post or topic
// 			// has been submitted. When a new topic is posted, the topic ID is
// 			// available in the $data array.
// 			'core.submit_post_end' => 'notify_post_created',
//
// 			// This event allows you to define errors after the post action is performed
// 			'core.posting_modify_submit_post_after' => 'notify_post_updated',
//
// 			// This event is used for performing actions directly after a post or topic has been deleted.
// 			// TODO: also consider listening for delete_posts_after
// 			'core.delete_post_after' => 'notify_post_deleted',
//
// 			// Perform additional actions after locking/unlocking posts/topics
// 			'core.mcp_lock_unlock_after' => 'notify_post_lock_status',
//
// 			// Event to modify the post data for the MCP topic review before assigning the posts
// 			'core.mcp_topic_modify_post_data' => 'notify_topic_updated',
//
// 			// Perform additional actions after topic(s) deletion
// 			'core.delete_topics_after_query' => 'notify_topic_deleted',
//
// 			// Event that returns user id, user details and user CPF of newly registered user
// 			'core.user_add_after' => 'notify_user_created',
//
// 			// Event after a user is deleted
// 			'core.delete_user_after' => 'notify_user_deleted',
		);
	}

	/**
	 * Handles events generated by submitting a post. This could result in a single notification being sent among several notification types.
	 * @param \phpbb\event\data	$event Event object -- [data, mode, poll, post_visibility, subject, topic_type, update_message, update_search_index, url, username]
	 *
	 * The possible notifications that can be generated as a result of these events include:
	 * - New post created
	 * - Post updated
	 * - New topic created
	 * - TODO: topic updated?
	 */
	public function handle_post_submit_action($event)
	{
		dumpy('----- handle_post_submit_action');
		dumpy($event['url']);
		dumpy($event['mode']);
		dumpy($event['subject']);
		dumpy($event['topic_type']);
		dumpy($event['update_message']);
		dumpy($event['post_visibility']);
		dumpy($event['username']);
		dumpy($event['data']);

		// Check for visibility of the post/topic. We don't send notifications for topics that are hidden from normal users.
		// Note that there are three visibility settings here. The first is the post visibility when it is generated. For example,
		// users may require moderator approval before their posts appear. The other two are the existing visibility status of the topic and post.
// 		if ($event['post_visibility'] == 0 || $event['data']['topic_visibility'] == 0 || $event['data']['post_visibility'] == 0)
// 		{
// 			return;
// 		}

		// Verify that the forum that the post submit action happened in has notifications enabled. If not we have nothing further to do
		if ($this->notification_service->is_notification_forum_enabled($event['data']['forum_id']) == false)
		{
			return;
		}

		// Build an array of the event data that we may need to pass along to the function that will construct the notification message
		$post_data = array(
			'user_id'		=> $event['data']['poster_id'],
			'user_name'		=> $event['username'],
			'forum_id'		=> $event['data']['forum_id'],
			'forum_name'	=> $event['data']['forum_name'],
			'topic_id'		=> $event['data']['topic_id'],
			'topic_title'	=> $event['data']['topic_title'],
			'post_id'		=> $event['data']['post_id'],
			'post_title'	=> $event['subject'],
			'edit_user_id'	=> $event['data']['post_edit_user'],
			'edit_reason'	=> $event['data']['post_edit_reason'],
			'content'		=> $event['data']['message'],
		);

		// Finally, based on the event data determine which kind of notification we need to send
		if ($event['mode'] == 'post') // New topic
		{
			$this->notify_topic_created($post_data);
		}
		elseif ($event['mode'] == 'reply') // New post
		{
			$this->notify_post_created($post_data);
		}
		elseif ($event['mode'] == 'edit') // Edit topic, edit post, or edit post lock status
		{
			// TODO
			$this->notify_post_updated($post_data);
		}
	}

	/**
	 * Handles events generated by deleting a post. This could result in a single notification being sent among several notification types.
	 * @param \phpbb\event\data	$event Event object -- [data, forum_id, is_soft, next_post_id, post_id, post_mode, softdelete_reason, topic_id]
	 *
	 * The possible notifications that can be generated as a result of these events include:
	 * - Post deleted
	 * - Topic deleted
	 */
	public function handle_post_delete_action($event)
	{
		dumpy('----- handle_post_delete_action');
		dumpy($event['forum_id']);
		dumpy($event['topic_id']);
		dumpy($event['post_id']);
		dumpy($event['next_post_id']);
		dumpy($event['post_mode']);
		dumpy($event['is_soft']);
		dumpy($event['softdelete_reason']);
		dumpy($event['data']);

		// Check for visibility of the post/topic. We don't send notifications for content that is hidden from normal users.
		if ($event['post_visibility'] == 0 || $event['data']['topic_visibility'] == 0 || $event['data']['post_visibility'] == 0)
		{
			return;
		}

		// Verify that the forum that the post submit action happened in has notifications enabled. If not we have nothing further to do
		if ($this->notification_service->is_notification_forum_enabled($event['forum_id']) == false)
		{
			return;
		}

		// Build an array of the event data that we may need to pass along to the function that will construct the notification message
		$post_data = array(
			'user_id'		=> $event['data']['poster_id'],
// 			'user_name'		=> NONE!
			'forum_id'		=> $event['forum_id'],
// 			'forum_name'	=> NONE!
			'topic_id'		=> $event['topic_id'],
// 			'topic_title'	=> NONE!
			'post_id'		=> $event['post_id'],
// 			'post_title'	=> NONE!
// 			'delete_user_id'=> NONE!
			'delete_reason'	=> $event['data']['softdelete_reason'],
		);

		$this->notify_post_deleted($post_data);
	}

	/**
	 * Handles events generated by changing the lock status on a post or topic. These events could correspond to one of several types of notifications being sent.
	 * @param \phpbb\event\data	$event Event object -- [action, data, ids]
	 *
	 * The possible notifications that can be generated as a result of these events include:
	 * - Post locked
	 * - Post unlocked
	 * - Topic locked
	 * - Topic unlocked
	 */
	public function handle_post_lock_action($event)
	{
		dumpy('----- handle_post_lock_action');
		dumpy($event['is_soft']);
		dumpy($event['softdelete_reason']);
		dumpy($event['data']);

		return;
	}

	/**
	 * Handles events generated by the creation of a new user account.
	 * @param \phpbb\event\data	$event Event object -- [cp_data, user_id, user_row]
	 *
	 * The possible notifications that can be generated as a result of these events include:
	 * - Post deleted
	 * - Topic deleted
	 */
	public function handle_user_add_action($event)
	{
		dumpy('----- handle_user_add_action');
		dumpy($event['user_id']);
		dumpy($event['cp_data']);
		dumpy($event['user_row']);
		return;
	}

	/**
	 * Handles events generated by the deletion of a user account.
	 * @param \phpbb\event\data	$event Event object -- [mode, retain_username, user_ids, user_rows]
	 *
	 * The possible notifications that can be generated as a result of these events include:
	 * - Post deleted
	 * - Topic deleted
	 */
	public function handle_user_delete_action($event)
	{
		dumpy('----- handle_user_delete_action');
		dumpy($event['user_ids']);
		dumpy($event['mode']);
		dumpy($event['retain_username']);
		dumpy($event['user_rows']);
		return;
	}

	/**
	 * Sends a notification to Discord when a new post is created.
	 * @param $data Array of attributes for the new post
	 */
	private function notify_post_created($data)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_post_create';
		$color = self::COLOR_BRIGHT_GREEN;
		$emoji = self::EMOJI_CREATE;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false) {
			return;
		}

		// Construct the notification message using the post data
		$user_link = generate_user_link($data['user_id'], $data['user_name']);
		$post_link = generate_post_link($data['topic_id'], $data['post_id'], 'post');
		$topic_link = generate_topic_link($data['topic_id'], $data['topic_title']);
		$forum_link = generate_forum_link($data['forum_id'], $data['forum_name']);
		// TODO: Put this text in language/ and figure out how to reorder parameters dynamically
		$message = sprintf('%s %s created a new %s in the topic %s located in the forum %s',
			$emoji, $user_link, $post_link, $topic_link, $forum_link
		);

		// Generate a post preview if required
		$footer = NULL;
		$preview_length = $this->notification_service->get_post_preview_length();
		if ($preview_length > 0)
		{
			// TODO: figure out how to remove the tags/styling from the post text. New lines in previews break notification.
			$footer = $data['content'];

			// Truncate the preview if the post content is too long and add '...' for the last three characters.
			// The length will always be at least 10 characters so we don't need to worry about really short strings.
			if (strlen($footer) > $preview_length)
			{
				$footer = substr($footer, 0, $preview_length - 3) . '...';
			}

			// Prepend a little text to the footer so it's clear that we're sharing the post content
			$footer = 'Preview: ' . $footer;
		}

		$this->notification_service->send_discord_notification($color, $message, $footer);
	}

	/**
	 * Sends a notification to Discord when a post is updated.
	 * @param $data Array of attributes for the updated post
	 */
	private function notify_post_updated($data)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_post_update';
		$color = self::COLOR_BRIGHT_BLUE;
		$emoji = self::EMOJI_UPDATE;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false) {
			return;
		}

		// Construct the notification message using the post data
		$user_link = generate_user_link($data['user_id'], $data['user_name']);
		$post_link = generate_post_link($data['topic_id'], $data['post_id'], 'post');
		$topic_link = generate_topic_link($data['topic_id'], $data['topic_title']);
		$forum_link = generate_forum_link($data['forum_id'], $data['forum_name']);

		// TODO: Put this text in language/ and figure out how to reorder parameters dynamically
		$message = sprintf('%s %s edited their %s in the topic %s located in the forum %s',
			$emoji, $user_link, $post_link, $topic_link, $forum_link
		);
		// TODO: generate post preview if needed with $data['content']


		$this->notification_service->send_discord_notification($color, $message);
	}

	/**
	 * Sends a notification to Discord when a post is deleted.
	 * @param $data Array of attributes for the deleted post
	 */
	private function notify_post_deleted($data)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_post_delete';
		$color = self::COLOR_BRIGHT_RED;
		$emoji = self::EMOJI_DELETE;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false) {
			return;
		}

		// Construct the notification message using the post data
		$user_link = generate_user_link($data['user_id'], $data['user_name']);
		$topic_link = generate_topic_link($data['topic_id'], $data['topic_title']);
		$forum_link = generate_forum_link($data['forum_id'], $data['forum_name']);

		// TODO: Put this text in language/ and figure out how to reorder parameters dynamically
		$message = sprintf('%s %s deleted their post in the topic %s located in the forum %s',
			$emoji, $user_link, $topic_link, $forum_link
		);
		// TODO: generate post preview if needed with $data['content']

		$this->notification_service->send_discord_notification($color, $message);
	}

	/**
	 * Sends a notification to Discord when a post is locked.
	 * @param $data Array of attributes for the locked post
	 */
	private function notify_post_locked($data)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_post_lock';
		$color = self::COLOR_BRIGHT_ORANGE;
		$emoji = self::EMOJI_LOCK;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false) {
			return;
		}

		// Construct the notification message using the argument data
		$message = $emoji;
		$message .= $notification_type_config_name;

		$this->notification_service->send_discord_notification($color, $message);
	}

	/**
	 * Sends a notification to Discord when a post is unlocked.
	 * @param $data Array of attributes for the unlocked post
	 */
	private function notify_post_unlocked($data)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_post_unlock';
		$color = self::COLOR_BRIGHT_ORANGE;
		$emoji = self::EMOJI_UNLOCK;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false) {
			return;
		}

		// Construct the notification message using the argument data
		$message = $emoji;
		$message .= $notification_type_config_name;

		$this->notification_service->send_discord_notification($color, $message);
	}

	/**
	 * Sends a notification to Discord when a new topic is created.
	 * @param $data Array of attributes for the new topic
	 */
	private function notify_topic_created($data)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_topic_create';
		$color = self::COLOR_BRIGHT_GREEN;
		$emoji = self::EMOJI_CREATE;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false) {
			return;
		}

		// Construct the notification message using the argument data
		$user_link = generate_user_link($data['user_id'], $data['user_name']);
		$forum_link = generate_forum_link($data['forum_id'], $data['forum_name']);
		$topic_link = generate_topic_link($data['topic_id'], $data['topic_title']);
		// TODO: Put this text in language/ and figure out how to reorder parameters dynamically
		$message = sprintf('%s %s created a new topic titled %s in the %s forum',
			$emoji, $user_link, $topic_link, $forum_link
		);
		// TODO: generate post preview if needed with $data['content']

		$this->notification_service->send_discord_notification($color, $message);
	}

	/**
	 * Sends a notification to Discord when a topic is updated.
	 * @param $data Array of attributes for the updated topic
	 */
	private function notify_topic_updated($data)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_topic_update';
		$color = self::COLOR_BRIGHT_BLUE;
		$emoji = self::EMOJI_UPDATE;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false) {
			return;
		}

		// Construct the notification message using the argument data
		$message = $emoji;
		$message .= $notification_type_config_name;

		$this->notification_service->send_discord_notification($color, $message);
	}

	/**
	 * Sends a notification to Discord when a topic is created.
	 * @param $data Array of attributes for the deleted topic
	 */
	private function notify_topic_deleted($data)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_topic_delete';
		$color = self::COLOR_BRIGHT_RED;
		$emoji = self::EMOJI_DELETE;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false) {
			return;
		}

		// Construct the notification message using the argument data
		$message = $emoji;
		$message .= $notification_type_config_name;

		$this->notification_service->send_discord_notification($color, $message);
	}

	/**
	 * Sends a notification to Discord when a topic is locked.
	 * @param $data Array of attributes for the locked topic
	 */
	private function notify_topic_locked($data)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_topic_lock';
		$color = self::COLOR_BRIGHT_ORANGE;
		$emoji = self::EMOJI_LOCK;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false) {
			return;
		}

		// Construct the notification message using the argument data
		$message = $emoji;
		$message .= $notification_type_config_name;

		$this->notification_service->send_discord_notification($color, $message);
	}

	/**
	 * Sends a notification to Discord when a topic is unlocked.
	 * @param $data Array of attributes for the unlocked topic
	 */
	private function notify_topic_unlocked($data)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_topic_unlock';
		$color = self::COLOR_BRIGHT_ORANGE;
		$emoji = self::EMOJI_UNLOCK;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false) {
			return;
		}

		// Construct the notification message using the argument data
		$message = $emoji;
		$message .= $notification_type_config_name;

		$this->notification_service->send_discord_notification($color, $message);
	}

	/**
	 * Sends a notification to Discord when a new user account is created.
	 * @param $data Array of attributes for the new user
	 *
	 * Notification details include the user name and a link to the user's profile page
	 */
	private function notify_user_created($data)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_user_create';
		$color = self::COLOR_BRIGHT_PURPLE;
		$emoji = self::EMOJI_USER;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false) {
			return;
		}

		$user_id = $data['user_id'];

		// Construct the notification message using the argument data
		$message = $emoji;
		$message .= $notification_type_config_name;

		$this->notification_service->send_discord_notification($color, $message);
	}

	/**
	 * Sends a notification to Discord when a user account is deleted.
	 * @param $data Array of attributes for the deleted user
	 */
	private function notify_user_deleted($data)
	{
		// Constant properties for this notification type
		$notification_type_config_name = 'discord_notification_type_user_delete';
		$color = self::COLOR_BRIGHT_PURPLE;
		$emoji = self::EMOJI_USER;

		// Verify that this notification type is enabled. If not, we have nothing further to do.
		if ($this->notification_service->is_notification_type_enabled($notification_type_config_name) == false) {
			return;
		}

		// Construct the notification message using the argument data
		$message = $emoji;
		$message .= $notification_type_config_name;

		$this->notification_service->send_discord_notification($color, $message);
	}
}
