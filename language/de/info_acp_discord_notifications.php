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
	'ACP_DISCORD_NOTIFICATIONS'					=> 'Discord Benachrichtigungen',
	'ACP_DISCORD_NOTIFICATIONS_TITLE'			=> 'Discord Konfiguration',

	// ACP Logs
	'ACP_DISCORD_NOTIFICATIONS_LOG_UPDATE'		=> 'Discord Benachrichtigungen: Einstellungen aktualisiert',
	'ACP_DISCORD_NOTIFICATIONS_WEBHOOK_ERROR'	=> 'Discord Benachrichtigungen: HTTP Fehlercode %d',
	'ACP_DISCORD_NOTIFICATIONS_CURL_ERROR'	    => 'Discord Benachrichtigungen: cURL Fehlercode %d',
	'ACP_DISCORD_NOTIFICATIONS_JSON_ERROR'	    => 'Discord Benachrichtigungen: JSON Fehler: %s',
));
