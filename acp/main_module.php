<?php
/**
 * Discord Notifications extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Tyler Olsen, https://github.com/rootslinux
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace roots\discordnotifications\acp;

/**
 * Discord Notifications ACP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	public function main($id, $mode)
	{
		global $config, $request, $template, $user;

		$user->add_lang_ext('roots/discordnotifications', 'common');
		$this->tpl_name = 'acp_demo_body';
		$this->page_title = $user->lang('ACP_DISCORD_NOTIFICATIONS');
		add_form_key('roots/discordnotifications');

		if ($request->is_set_post('submit'))
		{
			if (!check_form_key('roots/discordnotifications'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			$config->set('discord_notifications_enabled', $request->variable('master_enable_extension', 0));

			trigger_error($user->lang('ACP_SETTINGS_SAVED') . adm_back_link($this->u_action));
		}

		$template->assign_vars(array(
			'U_ACTION'				=> $this->u_action,
			'ACP_ENABLE_EXTENSION'	=> $config['discord_notifications_enabled'],
		));
	}
}
