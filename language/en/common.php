<?php
/**
 * Discord Notifications extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Tyler Olsen, https://github.com/rootslinux
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'DN_ACP_DESCRIPTION'		=> 'These settings allow for various forum events to send notification messages to a Discord server',

	'DN_MAIN_SETTINGS'			=> 'Main Settings',
	'DN_MASTER_ENABLE'			=> 'Enable Discord Notifications',
	'DN_WEBHOOK_URL'			=> 'Discord Webhook URL',
	'DN_WEBHOOK_DESCRIPTION'	=> 'The URL of the webhook generated on the Discord server',
	'DN_TEST_MESSAGE'			=> 'Message to send to Discord for testing',
	'DN_TEXT_MESSAGE_DEFAULT'	=> 'Test: Hello Discord -- sent from phpBB',
	'DN_TEST_DESCRIPTION'		=> 'Use for testing purposes only to verify that your Discord webhook is setup correctly.',

	'DN_TYPE_SETTINGS'			=> 'Notification Types',
	'DN_TYPE_DESCRIPTION'		=> 'Select which types of notifications you wish to be sent to Discord',
	'DN_POST_CREATE'			=> 'Post created',
	'DN_POST_UPDATE'			=> 'Post updated',
	'DN_POST_DELETE'			=> 'Post deleted',
	'DN_POST_LOCK'				=> 'Post locked',
	'DN_POST_UNLOCK'			=> 'Post unlocked',
	'DN_TOPIC_CREATE'			=> 'Topic created',
	'DN_TOPIC_UPDATE'			=> 'Topic updated',
	'DN_TOPIC_DELETE'			=> 'Topic deleted',
	'DN_TOPIC_LOCK'				=> 'Topic locked',
	'DN_TOPIC_UNLOCK'			=> 'Topic unlocked',
	'DN_USER_CREATE'			=> 'User created',

	'DN_FORUM_SETTINGS'			=> 'Notification Forums',
	'DN_FORUM_DESCRIPTION'		=> 'Enable or disable notifications for event types that are generated within a forum, such as posts and topics',

	'DN_SETTINGS_SAVED'			=> 'Discord Notification settings modified successfully.',
));
