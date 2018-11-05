<?php
/**
 *
 * Discord Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Tyler Olsen, https://github.com/rootslinux
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace roots\discordnotifications\tests\functional;

/**
 * @group functional
 */
class demo_test extends \phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return array('roots/discordnotifications');
	}
}
