<?php
/**
 *
 * Discord Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Tyler Olsen, https://github.com/rootslinux
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace roots\discordnotifications\acp;

/**
 * Discord Notifications ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\roots\discordnotifications\acp\main_module',
			'title'		=> 'ACP_DEMO_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'ACP_DEMO',
					'auth'	=> 'ext_roots/discordnotifications && acl_a_board',
					'cat'	=> array('ACP_DEMO_TITLE')
				),
			),
		);
	}
}
