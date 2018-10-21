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
class discord_notifications_module
{
	/** @var string */
	public $page_title;

	/** @var string */
	public $tpl_name;

	/** @var string */
	public $u_action;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;


	public function main($id, $mode)
	{
		global $phpbb_container;
		$this->config = $phpbb_container->get('config');
		$this->config_text = $phpbb_container->get('config_text');
		$this->log = $phpbb_container->get('log');
		$this->request = $phpbb_container->get('request');
		$this->template = $phpbb_container->get('template');
		$this->user = $phpbb_container->get('user');

		$this->user->add_lang_ext('roots/discordnotifications', 'common');
		$this->tpl_name = 'acp_demo_body';
		$this->page_title = $this->user->lang('ACP_DISCORD_NOTIFICATIONS');
		add_form_key('roots/discordnotifications');

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('roots/discordnotifications'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			$this->config->set('discord_notifications_enabled', $this->request->variable('master_enable_extension', 0));
			$this->config_text->set('discord_webhook_url', $this->request->variable('dn_webhook_url', '', true));

			$this->config->set('discord_notification_type_post_create', $this->request->variable('dn_post_create', 0));
			$this->config->set('discord_notification_type_post_update', $this->request->variable('dn_post_update', 0));
			$this->config->set('discord_notification_type_post_delete', $this->request->variable('dn_post_delete', 0));
			$this->config->set('discord_notification_type_post_lock', $this->request->variable('dn_post_lock', 0));
			$this->config->set('discord_notification_type_post_unlock', $this->request->variable('dn_post_unlock', 0));

			$this->config->set('discord_notification_type_topic_create', $this->request->variable('dn_topic_create', 0));
			$this->config->set('discord_notification_type_topic_update', $this->request->variable('dn_topic_update', 0));
			$this->config->set('discord_notification_type_topic_delete', $this->request->variable('dn_topic_delete', 0));
			$this->config->set('discord_notification_type_topic_lock', $this->request->variable('dn_topic_lock', 0));
			$this->config->set('discord_notification_type_topic_unlock', $this->request->variable('dn_topic_unlock', 0));

			$this->config->set('discord_notification_type_user_create', $this->request->variable('dn_user_create', 0));

			trigger_error($this->user->lang('DN_SETTINGS_SAVED') . adm_back_link($this->u_action));
		}

		$this->template->assign_vars(array(
			'DN_MASTER_ENABLE'		=> $this->config['discord_notifications_enabled'],
			'DN_WEBHOOK_URL'		=> $this->config_text->get('discord_webhook_url'),

			'DN_POST_CREATE'		=> $this->config['discord_notification_type_post_create'],
			'DN_POST_UPDATE'		=> $this->config['discord_notification_type_post_update'],
			'DN_POST_DELETE'		=> $this->config['discord_notification_type_post_delete'],
			'DN_POST_LOCK'			=> $this->config['discord_notification_type_post_lock'],
			'DN_POST_UNLOCK'		=> $this->config['discord_notification_type_post_unlock'],

			'DN_TOPIC_CREATE'		=> $this->config['discord_notification_type_topic_create'],
			'DN_TOPIC_UPDATE'		=> $this->config['discord_notification_type_topic_update'],
			'DN_TOPIC_DELETE'		=> $this->config['discord_notification_type_topic_delete'],
			'DN_TOPIC_LOCK'			=> $this->config['discord_notification_type_topic_lock'],
			'DN_TOPIC_UNLOCK'		=> $this->config['discord_notification_type_topic_unlock'],

			'DN_USER_CREATE'		=> $this->config['discord_notification_type_user_create'],

			'U_ACTION'				=> $this->u_action,
		));
	}
}
