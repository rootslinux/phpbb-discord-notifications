<?php
/**
 *
 * Discord Notifications. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Tyler Olsen, https://github.com/rootslinux
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace roots\discordnotifications;

/**
 * Contains the core logic for formatting and sending notification message to Discord.
 * This includes common utilities, such as verifying notification configuration settings
 */
class notification_service
{
	// Maximum number of characters allowed by Discord in a message description.
	// Reference: https://discordapp.com/developers/docs/resources/channel#embed-limits
	const MAX_MESSAGE_SIZE = 2048;

	// Maximum number of characters allowed by Discord in a message footer.
	const MAX_FOOTER_SIZE = 2048;

	// The notification color to use as a default if a missing or invalid color value is received.
	const DEFAULT_COLOR = 11777212; // Gray

	/** @var \phpbb\config\config */
	protected $config;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config $config
	 */
	public function __construct(\phpbb\config\config $config)
	{
		$this->config = $config;
	}

	/**
	 * Check whether notifications are enabled for a certain type
	 * @param $notification_type The name of the notification type to check
	 * @return False if the global notification setting is disabled or the notification type is disabled
	 */
	public function is_notification_type_enabled($notification_type)
	{
		// Also check the global extension enabled setting. We don't generate any notifications if this is disabled
		if ($this->config['discord_notifications_enabled'] == 1 && $this->config[$notification_type] == 1)
		{
			return true;
		}

		return false;
	}

	/**
	 * Check whether notifications that occur on a specific forum should be generated
	 * @param $forum_id The ID of the forum to check
	 * @return False if the global notification setting is disabled, the notification type is disabled, or notifications are disabled for the forum
	 */
	public function is_notification_forum_enabled($forum_id)
	{
		// TODO: Check the forum table to see if discord notifications are enabled on it
		if ($this->config['discord_notifications_enabled'] == 1)
		{
			return true;
		}

		return false;
	}

	/**
	 * Retrieve the value for the ACP settings configuration related to post preview length
	 * @return The number of characters to display in the post preview. A zero value indicates that no preview should be displayed
	 */
	public function get_post_preview_length()
	{
		return $this->config['discord_notifications_post_preview_length'];
	}

	/**
	 * Sends a notification message to Discord. This function checks the master switch configuration for the extension, but does
	 * no further checks. The caller is responsible for performing full validation of the notification prior to calling this function.
	 * @param $color The color to use in the notification (decimal value of a hexadecimal RGB code)
	 * @param $message The message text to send.
	 * @param $footer Text to place in the footer of the message. Optional.
	 */
	public function send_discord_notification($color, $message, $footer)
	{
		if ($this->config['discord_notifications_enabled'] == 0 || isset($message) == false)
		{
			return;
		}

		// Note that the value stored in the config table will always be a valid URL when discord_notifications_enabled is set
		$discord_webhook_url = $this->config['discord_notifications_webhook_url'];

		$this->send_message($discord_webhook_url, $color, $message, $footer);
	}

	/**
	 * Sends a basic message to Discord, disregarding any configurations that are currently set. This method is primarily used by users
	 * to test their notifications from the ACP.
	 * @param $discord_webhook_url The URL of the Discord webhook to transmit the message to. If this is an invalid URL, no message will be sent.
	 * @param $message The message text to send. Must be a non-empty string.
	 */
	public function force_send_discord_notification($discord_webhook_url, $message)
	{
		if (!filter_var($discord_webhook_url, FILTER_VALIDATE_URL) || is_string($message) == false)
		{
			return;
		}

		$this->send_message($discord_webhook_url, self::DEFAULT_COLOR, $message, NULL);
	}

	/**
	 * Helper function that performs the message transmission. This method checks the inputs to prevent any problematic characters in
	 * strings. Note that this function checks that the message and footer do not exceed the maximum allowable limits by the Discord
	 * API, but it does -not- check configuration settings such as the post_preview_length. The code invoking this method is responsible
	 * for checking those settings.
	 *
	 * @param $discord_webhook_url The URL of the Discord webhook to transmit the message to.
	 * @param $color Color to set for the message. Should be a positive non-zero integer representing a hex color code.
	 * @param $message The message text to send. Must be a non-empty string.
	 * @param $footer The text to place in the footer. Optional. Must be a non-empty string.
	 * @return Boolean indicating whether the message transmission resulted in success or failure.
	 * @see https://discordapp.com/developers/docs/resources/webhook#execute-webhook
	 */
	private function send_message($discord_webhook_url, $color, $message, $footer)
	{
		if (isset($discord_webhook_url) == false || $discord_webhook_url === '')
		{
			return false;
		}
		if (is_integer($color) == false || $color < 0)
		{
			// Use the default color if we did not receive a valid color value
			$color = self::DEFAULT_COLOR;
		}
		if (is_string($message) == false)
		{
			return false;
		}
		if (isset($footer) == true && is_string($footer) == false)
		{
			return false;
		}

		// Clean up the message and footer text before sending by trimming whitespace from the front and end of the message and footer strings.
		// TODO: newline characters actually should work...
		$message = trim($message);
		$message = str_replace('"', "'", $message); // Replace " characters that would break the JSON encoding that our message must be wrapped in.
		$message = str_replace(array("\r", "\n"), ' ', $message); // Newline characters will break messages as well
		if (isset($footer))
		{
			$footer = trim($footer);
			$footer = str_replace('"', "'", $footer);
			$footer = str_replace('"', "'", $footer);
			$footer = str_replace(array("\r", "\n"), ' ', $footer);
		}

		// Abort if we find that either of our text fields are now empty strings
		if ($message === '')
		{
			return false;
		}
		if (isset($footer) && $footer === '')
		{
			return false;
		}

		// Verify that the message and footer size is within the allowable limit and truncate if necessary. We add "..." as the last three characters
		// when we require truncation.
		if (strlen($message) > self::MAX_MESSAGE_SIZE)
		{
			$message = substr($message, 0, self::MAX_MESSAGE_SIZE - 3) . '...';
		}
		if (isset($footer))
		{
			if (strlen($footer) > self::MAX_FOOTER_SIZE)
			{
				$footer = substr($footer, 0, self::MAX_FOOTER_SIZE - 3) . '...';
			}
		}

		// Place the message inside the JSON structure that Discord expects to receive at the REST endpoint.
		$post = '';
		if (isset($footer))
		{
			$post = sprintf('{"embeds": [{"color": "%d", "description" : "%s", "footer": {"text": "%s"}}]}', $color, $message, $footer);
		}
		else {
			$post = sprintf('{"embeds": [{"color": "%d", "description" : "%s"}]}', $color, $message);
		}

		// Use the CURL library to transmit the message via a POST operation to the webhook URL.
		$h = curl_init();
		curl_setopt($h, CURLOPT_URL, $discord_webhook_url);
		curl_setopt($h, CURLOPT_POST, 1);
		curl_setopt($h, CURLOPT_POSTFIELDS, $post);
		// This disables SSL. Its not ideal, but we don't expect to be transmitting sensitive data anyway.
// 		curl_setopt($h, CURLOPT_SSL_VERIFYHOST, 1);
// 		curl_setopt($h, CURLOPT_SSL_VERIFYPEER, 1);
		$response = curl_exec($h);
		curl_close($h);

		// Check if the response was not successful
		if (is_array($response) && $response['message'])
		{
			// TODO: if the response includes a message then an error has occurred. Determine whether we want to log it, queue it up to try again, etc.
			return false;
		}

		return true;
	}
}
