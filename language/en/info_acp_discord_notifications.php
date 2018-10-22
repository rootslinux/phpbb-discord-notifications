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
	// ACP Module
	'ACP_DISCORD_NOTIFICATIONS'					=> 'Discord Notifications',
	'ACP_DISCORD_NOTIFICATIONS_TITLE'			=> 'Discord Notification Settings',

	// ACP Logs
	'ACP_DISCORD_NOTIFICATIONS_LOG_UPDATE'		=> '<strong>Modified Discord notification settings</strong>',
));
