<?php
/**
 * Discord Notifications extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Tyler Olsen, https://github.com/rootslinux
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace roots\discordnotifications\acp;

/**
 * Discord Notifications ACP module info.
 */
class discord_notifications_info
{
	public function module()
	{
		return array(
			'filename'	=> '\roots\discordnotifications\acp\discord_notifications_module',
			'title'		=> 'ACP_DISCORD_NOTIFICATIONS',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'Discord Notifications',
					'auth'	=> 'ext_roots/discordnotifications && acl_a_board',
					'cat'	=> array('ACP_DISCORD_NOTIFICATIONS')
				),
			),
		);
	}
}
