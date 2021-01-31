<?php
/**
 * Discord Notifications extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Tyler Olsen, https://github.com/rootslinux
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * This file contains the language strings for the ACP settings page for this extension.
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	// ACP Extension Settings Page
	'DN_ACP_DESCRIPTION'			=> 'Diese Einstellungen erlauben es, bei verschiedenen Ereignissen eine Benachrichtigung an einen Discord-Kanal zu schicken.',

	'DN_MAIN_SETTINGS'				=> 'Konfiguration',
	'DN_MASTER_ENABLE'				=> 'Discord Benachrichtigungen aktivieren',
	'DN_WEBHOOK_URL'				=> 'Discord Webhook URL',
	'DN_WEBHOOK_DESCRIPTION'		=> 'Webhook für einen Discord-Kanal. Weitere Informationen gibt es in <a href="https://support.discordapp.com/hc/en-us/articles/228383668-Intro-to-Webhooks">diesem Artikel</a>.',
	'DN_POST_PREVIEW_LENGTH'		=> 'Länge der Vorschau',
	'DN_POST_PREVIEW_DESCRIPTION'	=> 'Wie viele Zeichen eines Posts sollen in der Vorschau angezeigt werden? (10-2000, 0 deaktiviert die Vorschau)',
	'DN_TEST_MESSAGE'				=> 'Nachricht',
	'DN_TEST_MESSAGE_TEXT'			=> 'Dies ist ein Test: Hallo, Discord!',
	'DN_TEST_DESCRIPTION'			=> 'Nachricht, die beim Starten eines Tests geschickt wird.',
	'DN_SEND_TEST'					=> 'Test starten',
	'DN_SEND_TEST_DESCRIPTION'		=> 'Wenn alles korrekt konfiguriert ist, sollte die Test-Nachricht im entsprechenden Discord-Kanal auftauchen.',
	'DN_CONNECT_TIMEOUT'			=> 'Timeout Verbindungsaufbau',
	'DN_CONNECT_TO_DESCRIPTION'     => 'Zeit in Sekunden, die maximal auf einen Verbindungsaufbau gewartet wird.',
	'DN_EXEC_TIMEOUT'               => 'Timeout Übertragung',
	'DN_EXEC_TO_DESCRIPTION'        => 'Maximale Zeit in Sekunden, die die Datenübertragung dauern darf.',
	'DN_TEST_SETTINGS'				=> 'Test',

	'DN_WEBHOOK_SETTINGS'			=> 'Einträge Bearbeiten',
	'DN_WEBHOOK_SETTINGS_DESCRIPTION' => 'Hier können bestehende Einträge bearbeitet werden. Ein Eintrag wird gelöscht, wenn das URL-Feld leer ist.',
	'DN_WEBHOOK_NEW'				=> 'Neuen Eintrag anlegen',
	'DN_WEBHOOK_NEW_DESCRIPTION'	=> 'Hier kann ein neuer Eintrag angelegt werden. Die URL muss vollständig sein und mit "http" oder "https" beginnen.',
	'DN_WEBHOOK_NEW_ALIAS'			=> 'Neuer Alias',
	'DN_WEBHOOK_NEW_URL'			=> 'Neue URL',
	'DN_WEBHOOK_SELECT'				=> 'Webhook auswählen',
	'DN_WEBHOOK_DEFAULT'			=> 'Forenübergreifende Ereignisse',
	'DN_WEBHOOK_DEFAULT_DESCRIPTION' => 'Webhook für Ereignisse, die nicht zu einem bestimmten Forum gehören (z.B. Benutzer erstellt/gelöscht)',
	'DN_NO_WEBHOOKS'				=> 'Noch kein Webhook hinterlegt.',

	'DN_TYPE_SETTINGS'				=> 'Benachrichtigungstypen',
	'DN_TYPE_DESCRIPTION'			=> 'Wähle aus, welche Ereignisse eine Benachrichtigung auslösen sollen.',
	'DN_POST_CREATE'				=> 'Post erstellt',
	'DN_POST_UPDATE'				=> 'Post aktualisiert',
	'DN_POST_DELETE'				=> 'Post gelöscht',
	'DN_POST_LOCK'					=> 'Post gesperrt',
	'DN_POST_UNLOCK'				=> 'Post entsperrt',
	'DN_POST_APPROVE'				=> 'Post freigeschaltet',
	'DN_TOPIC_CREATE'				=> 'Thema erstellt',
	'DN_TOPIC_UPDATE'				=> 'Thema aktualisiert',
	'DN_TOPIC_DELETE'				=> 'Thema gelöscht',
	'DN_TOPIC_LOCK'					=> 'Thema gesperrt',
	'DN_TOPIC_UNLOCK'				=> 'Thema entsperrt',
	'DN_TOPIC_APPROVE'				=> 'Thema freigeschaltet',
	'DN_USER_CREATE'				=> 'Benutzer erstellt',
	'DN_USER_DELETE'				=> 'Benutzer gelöscht',

	'DN_FORUM_SETTINGS'				=> 'Forenspezifische Konfiguration',
	'DN_FORUM_DESCRIPTION'			=> 'Wähle aus, für welche Foren Benachrichtigungen (neues Thema, Antwort, ...) ausgelöst werden sollen.',

	// Messages that appear after a user tries to send a test message
	'DN_TEST_SUCCESS'				=> 'Erfolg! Die Test-Nachricht sollte jetzt im entsprechenden Discord-Kanal zu sehen sein.',
	'DN_TEST_FAILURE'				=> 'Das Senden der Nachricht war leider nicht erfolgreich. Bitte überprüfe die Konfiguration.',
	'DN_TEST_BAD_MESSAGE'			=> 'Die Test-Nachricht darf nicht leer sein.',
	'DN_TEST_BAD_WEBHOOK'			=> 'Die Webhook-URL ist ungültig. Bitte überprüfe die Konfiguration.',

	// Success/Failure messages that can be generated once the user saves
	'DN_SETTINGS_SAVED'				=> 'Einstellungen aktualisiert.',
	'DN_MASTER_WEBHOOK_REQUIRED'	=> 'Benachrichtigungen können nur aktiviert werden, wenn eine gültige Webhook-URL hinterlegt ist.',
	'DN_WEBHOOK_URL_INVALID'		=> 'Die Webhook-URL ist ungültig. Bitte überprüfe die Konfiguration.',
	'DN_POST_PREVIEW_INVALID'		=> 'Länge der Vorschau muss zwischen 10 und 2000 Zeichen sein (oder 0 zum Deaktivieren der Vorschau).',
));
