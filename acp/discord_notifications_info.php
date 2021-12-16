<?php
/**
 * Discord Notifications extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Tyler Olsen, https://github.com/rootslinux
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace mober\discordnotifications\acp;

/**
 * Discord Notifications ACP module info.
 */
class discord_notifications_info
{
	public function module()
	{
		return array(
			'filename'	=> '\mober\discordnotifications\acp\discord_notifications_module',
			'title'		=> 'ACP_DISCORD_NOTIFICATIONS',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'ACP_DISCORD_NOTIFICATIONS_TITLE',
					'auth'	=> 'ext_mober/discordnotifications && acl_a_board',
					'cat'	=> array('ACP_DISCORD_NOTIFICATIONS')
				),
			),
		);
	}
}
