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
	// The minimum number of characters that the user can set for the post preview setting.
	// Note that a zero value is valid and disables previews.
	const MIN_POST_PREVIEW_LENGTH = 10;

	// The maximum number of characters that the user can set for the post preview setting.
	// Note that this is slightly less than the actual allowed maximum by Discord (2048), but we reserve
	// some space to prepend the preview text with something like "Preview: " or "Reason: "
	const MAX_POST_PREVIEW_LENGTH = 2000;

	/** @var string */
	public $page_title;

	/** @var string */
	public $tpl_name;

	/** @var string */
	public $u_action;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

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
		$this->cache = $phpbb_container->get('cache.driver');
		$this->config = $phpbb_container->get('config');
		$this->log = $phpbb_container->get('log');
		$this->request = $phpbb_container->get('request');
		$this->template = $phpbb_container->get('template');
		$this->user = $phpbb_container->get('user');

		$this->user->add_lang_ext('roots/discordnotifications', 'acp_discord_notifications');
		$this->tpl_name = 'acp_discord_notifications';
		$this->page_title = $this->user->lang('ACP_DISCORD_NOTIFICATIONS_TITLE');

		$form_name = 'roots_discord_notifications';
		add_form_key($form_name);

		// Process setting changes
		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key($form_name))
			{
				trigger_error('FORM_INVALID', E_USER_WARNING);
			}

			// Get form values for the main settings
			$master_enable = $this->request->variable('dn_master_enable', 0);
			$webhook_url = $this->request->variable('dn_webhook_url', '');
			$preview_length = $this->request->variable('dn_post_preview_length', '');

			// If the master enable is set to on, a webhook URL is required
			if ($master_enable == 1 && $webhook_url == '')
			{
				trigger_error($this->user->lang('DN_MASTER_WEBHOOK_REQUIRED') . adm_back_link($this->u_action), E_USER_WARNING);
			}
			// Check that the webhook URL is a valid URL string if it is not empty
			if ($webhook_url != '' && !filter_var($webhook_url, FILTER_VALIDATE_URL))
			{
				trigger_error($this->user->lang('DN_WEBHOOK_URL_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}
			// Verify that the post preview length is an integer value and within the valid range
			if (is_integer($preview_length) == false)
			{
				trigger_error($this->user->lang('DN_POST_PREVIEW_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}
			elseif (($preview_length < self::MIN_POST_PREVIEW_LENGTH || $preview_length > self::MAX_POST_PREVIEW_LENGTH) && $preview_length != 0)
			{
				trigger_error($this->user->lang('DN_POST_PREVIEW_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->config->set('discord_notifications_enabled', $master_enable);
			$this->config->set('discord_notifications_webhook_url', $webhook_url);
			$this->config->set('discord_notifications_post_preview_length', $preview_length);

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

			// Log the settings change
			$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_DISCORD_NOTIFICATIONS_LOG_UPDATE');
			// Destroy any cached discord notification data
			$this->cache->destroy('roots_discord_notifications');

			trigger_error($this->user->lang('DN_SETTINGS_SAVED') . adm_back_link($this->u_action));
		}

		$this->template->assign_vars(array(
			'DN_MASTER_ENABLE'			=> $this->config['discord_notifications_enabled'],
			'DN_WEBHOOK_URL'			=> $this->config['discord_notifications_webhook_url'],
			'DN_POST_PREVIEW_LENGTH'	=> $this->config['discord_notifications_post_preview_length'],
			'DN_TEST_MESSAGE_TEXT'		=> $this->user->lang('DN_TEST_MESSAGE_TEXT'),

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
