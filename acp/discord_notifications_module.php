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

	// The name for the form used for this page
	const PAGE_FORM_NAME = 'acp_roots_discord_notifications';

	// Inputs on the page for enabling/disabling a forum for notifications are all named with this prefix
	const FORUM_INPUT_PREFIX = 'dn_forum_';

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

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $language;

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
		$this->db = $phpbb_container->get('dbal.conn');
		$this->language = $phpbb_container->get('language');
		$this->log = $phpbb_container->get('log');
		$this->request = $phpbb_container->get('request');
		$this->template = $phpbb_container->get('template');
		$this->user = $phpbb_container->get('user');

		$this->user->add_lang_ext('roots/discordnotifications', 'acp_discord_notifications');
		$this->tpl_name = 'acp_discord_notifications';
		$this->page_title = $this->user->lang('ACP_DISCORD_NOTIFICATIONS_TITLE');

		add_form_key(self::PAGE_FORM_NAME);

		// Process form submission if present
		if ($this->request->is_set_post('submit'))
		{
			$this->process_form_submit();
		}

		// Generate some of the dynamic page HTML content
		$forum_section_html = $this->generate_forum_section();

		// Assign template values so that the page reflects the state of the extension settings
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

			'DN_FORUM_SECTION'			=> $forum_section_html,

			'U_ACTION'					=> $this->u_action,
		));
	}

	/*
	 * Handles all error checking and database changes when the user hits the submit button on the ACP page.
	 */
	private function process_form_submit()
	{
		if (!check_form_key(self::PAGE_FORM_NAME))
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
		// Verify that the post preview length is a numeric string, convert to an int and check the valid range
		if (is_numeric($preview_length) == false)
		{
			trigger_error($this->user->lang('DN_POST_PREVIEW_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
		}
		$preview_length = (int)$preview_length;
		if (($preview_length < self::MIN_POST_PREVIEW_LENGTH || $preview_length > self::MAX_POST_PREVIEW_LENGTH) && $preview_length != 0)
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

		// Set the discord_notifications_enabled in the forum table.
		$forum_id_names = array(); // This array will be built up to contain {forum_id} => {input_name}

		// First grab all variables in the submit request and match each against a regex to find the ones that are tied to a forum enabled setting.
		$form_inputs = $this->request->variable_names();
		foreach ($form_inputs as $input)
		{
			$matches = array();
			$match = preg_match('/^' . self::FORUM_INPUT_PREFIX . '(\d+)$/', $input, $matches);
			if ($match === 1)
			{
				$forum_id_names[$matches[1]] = $input;
			}
		}

		// Grab all of the values for all of the forum inputs and update the row in the forum table
		foreach ($forum_id_names as $id => $input_name)
		{
			$enabled = (int)$this->request->variable($input_name, 0);
			$sql = "UPDATE " . FORUMS_TABLE . " SET discord_notifications_enabled = $enabled WHERE forum_id = $id";
			$this->db->sql_query($sql);
			// TODO: It would be better to do this update in a single operation instead of once for each input inside this loop
		}

		// Log the settings change
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_DISCORD_NOTIFICATIONS_LOG_UPDATE');
		// Destroy any cached discord notification data
		$this->cache->destroy('roots_discord_notifications');

		trigger_error($this->user->lang('DN_SETTINGS_SAVED') . adm_back_link($this->u_action));
	}

	/**
	 * Generates the section of the ACP page listing all of the forums, in order, with the radio button option
	 * that allows the user to enable or disable discord notifications for that forum.
	 *
	 * @return HTML to render for the forum section
	 */
	private function generate_forum_section()
	{
		// Grab the raw language values to insert here. We must do this because if we used {L_YES} or another label in the HTML text,
		// it will not get interpretted like it otherwise would if it was static content in the .html file.
		$colon = $this->language->lang_raw('COLON');
		$yes = $this->language->lang_raw('YES');
		$no = $this->language->lang_raw('NO');

		$sql = "SELECT forum_id, forum_type, forum_name, discord_notifications_enabled FROM " . FORUMS_TABLE . " ORDER BY left_id ASC";
		$result = $this->db->sql_query($sql);

		$html = '';
		while ($row = $this->db->sql_fetchrow($result))
		{
			// Category forums are displayed for organizational purposes, but have no configuration
			if ($row['forum_type'] == FORUM_CAT)
			{
				$html .= "<dl>\n"
					. "<dt><label><u>" . $row['forum_name'] . "</u></label></dt>\n"
					. "</dl>\n";
			}
			// Normal forums have a radio input with the value selected basd on the value of the discord_notifications_enabled setting
			else if ($row['forum_type'] == FORUM_POST)
			{
				// The labels for all the inputs are constructed based on the forum IDs to make it easy to know which
				$input_name = self::FORUM_INPUT_PREFIX . $row['forum_id'];
				$yes_checked = $row['discord_notifications_enabled'] == 1 ? 'checked' : '';
				$no_checked = $yes_checked === '' ? 'checked' : '';
				$html .= "<dl>\n"
					. "<dt><label for=" . $input_name . ">" . $row['forum_name'] . $colon . "</label></dt>\n"
					. "<dd>\n"
					. "<label><input type=\"radio\" class=\"radio\" name=\"" . $input_name . "\" value=\"1\" " . $yes_checked . "/>" . $yes . "</label>\n"
					. "<label><input type=\"radio\" class=\"radio\" name=\"" . $input_name . "\" value=\"0\" " . $no_checked . "/>" . $no ."</label>\n"
					. "</dd>\n"
					. "</dl>\n";
			}
			// Other forum types (links) are ignored
		}
		$this->db->sql_freeresult($result);

		return $html;
	}
}
