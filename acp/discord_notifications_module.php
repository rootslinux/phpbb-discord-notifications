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
	// The maximum number of characters that the user can set for the max preview setting
	const MAX_POST_PREVIEW_LENGTH = 2048;

	/** @var string */
	public $page_title;

	/** @var string */
	public $tpl_name;

	/** @var string */
	public $u_action;

	/** @var \phpbb\config\config */
	protected $config;

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
		$this->log = $phpbb_container->get('log');
		$this->request = $phpbb_container->get('request');
		$this->template = $phpbb_container->get('template');
		$this->user = $phpbb_container->get('user');

		$this->user->add_lang_ext('roots/discordnotifications', 'acp_discord_notifications');
		$this->tpl_name = 'acp_discord_notifications';
		$this->page_title = $this->user->lang('ACP_DISCORD_NOTIFICATIONS_TITLE');
		add_form_key('roots_discord_notifications');

		// Process setting changes
		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key('roots_discord_notifications'))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}
			$master_enable = $this->request->variable('dn_master_enable', 0);
			$webhook_url = $this->request->variable('dn_webhook_url', '');
			$max_length = $this->request->variable('dn_post_preview_length', 0);

			// If the master enable is set to on, a webhook URL is required
			if ($master_enable && $webhook_url = '')
			{
				trigger_error('DN_MASTER_WEBHOOK_REQUIRED', E_USER_WARNING);
			}
			// Check that the webhook URL is a valid URL string if it is not empty
			if ($webhook_url != '' && !filter_var($webhook_url, FILTER_VALIDATE_URL))
			{
				trigger_error('DN_WEBHOOK_URL_INVALID', E_USER_WARNING);
			}
			// Verify that the post preview length is a numeric value and within the valid range
			if (!is_numeric($max_length))
			{
				trigger_error('DN_POST_PREVIEW_INVALID', E_USER_WARNING);
			}
			elseif ($max_length < 0 || $max_length > MAX_POST_PREVIEW_LENGTH)
			{
				trigger_error('DN_POST_PREVIEW_BAD_VALUE', E_USER_WARNING);
			}

			$this->config->set('discord_notifications_enabled', $master_enable);
			$this->config->set('discord_notifications_webhook_url', $webhook_url);
			$this->config->set('discord_notifications_post_preview_length', $max_length);

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
			$this->config->set('discord_notification_type_user_delete', $this->request->variable('dn_user_delete', 0));

			// TODO: Add entry in ACP log that user changed DN settings

			trigger_error($this->user->lang('DN_SETTINGS_SAVED') . adm_back_link($this->u_action));
		}

		$this->template->assign_vars(array(
			'DN_MASTER_ENABLE'			=> $this->config['discord_notifications_enabled'],
			'DN_WEBHOOK_URL'			=> $this->config['discord_notification_webhook_url'],
			'DN_POST_PREVIEW_LENGTH'	=> $this->config['discord_notification_post_preview_length'],

			'DN_POST_CREATE'			=> $this->config['discord_notification_type_post_create'],
			'DN_POST_UPDATE'			=> $this->config['discord_notification_type_post_update'],
			'DN_POST_DELETE'			=> $this->config['discord_notification_type_post_delete'],
			'DN_POST_LOCK'				=> $this->config['discord_notification_type_post_lock'],
			'DN_POST_UNLOCK'			=> $this->config['discord_notification_type_post_unlock'],

			'DN_TOPIC_CREATE'			=> $this->config['discord_notification_type_topic_create'],
			'DN_TOPIC_UPDATE'			=> $this->config['discord_notification_type_topic_update'],
			'DN_TOPIC_DELETE'			=> $this->config['discord_notification_type_topic_delete'],
			'DN_TOPIC_LOCK'				=> $this->config['discord_notification_type_topic_lock'],
			'DN_TOPIC_UNLOCK'			=> $this->config['discord_notification_type_topic_unlock'],

			'DN_USER_CREATE'			=> $this->config['discord_notification_type_user_create'],
			'DN_USER_DELETE'			=> $this->config['discord_notification_type_user_delete'],

			'U_ACTION'					=> $this->u_action,
		));
	}
}
