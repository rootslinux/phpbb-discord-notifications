<?php
/**
 *
 * Discord Notifications. An extension for the phpBB Forum Software package.
 * French translation by Galixte (http://www.galixte.com)
 *
 * @copyright (c) 2018 Tyler Olsen <https://github.com/rootslinux>
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 *
 * This file contains the language strings for the ACP settings page for this extension.
 */

/**
 * DO NOT CHANGE
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ « » “ ” …
//

// These messages are used by the event/notification_event_listener class. The notifications naturally generate dynamic content,
// and this is done using formatted strings passed to sprintf(). Each notification message below has a comment indicating what each
// %s string argument should represent (typically this is a hyperlink with text describing a user, topic, post, or forum).
// Note that the order of what gets populated in the %s arguments is unfortunately fixed, meaning that this could make good
// translations into other difficult.
$lang = array_merge($lang, array(
	// Post Notifications
	'CREATE_POST'				=> '%s %s a publié un %s dans le sujet « %s » du forum « %s ».', // %s == emoji, user, post, topic, forum
	'UPDATE_POST_SELF'			=> '%s %s a modifié son « %s dans le sujet « %s » du forum « %s ».', // %s == emoji, user, post, topic, forum
	'UPDATE_POST_OTHER'			=> '%s %s a modifié le message « %s » publié par « %s » dans le sujet « %s » du forum « %s ».', // %s == emoji, edit user, post, user, topic, forum
	'DELETE_POST'				=> '%s Message supprimé de l’auteur « %s » dans le sujet « %s » du forum « %s ».', // %s == emoji, user, topic, forum
	'LOCK_POST'					=> '%s Le message « %s » publié par le membre « %s » dans le sujet intitulé « %s » du forum « %s » a été verrouillé.', // %s == emoji, post, user, topic, forum
	'UNLOCK_POST'				=> '%s Le message « %s » publié par le membre « %s » dans le sujet intitulé « %s » du forum « %s » a été déverrouillé.', // %s == emoji, post, user, topic, forum

	// Topic Notifications
	'CREATE_TOPIC'				=> '%s %s a publié un nouveau sujet intitulé « %s » dans le forum « %s ».', // %s == emoji, user, topic, forum
	'UPDATE_TOPIC_SELF'			=> '%s %s a modifié son sujet « %s » dans le forum « %s ».', // %s == emoji, user, topic, forum
	'UPDATE_TOPIC_OTHER'		=> '%s %s a modifié le sujet « %s », dont l’auteur est « %s » dans le forum « %s ».', // %s == emoji, edit user, topic, user, forum
	'DELETE_TOPIC'				=> '%s Sujet supprimé de l’auteur « %s », intitulé « %s », contenant %d messages dans le forum « %s ».', // %s/d == emoji, user, topic title, post count, forum
	'LOCK_TOPIC'				=> '%s Le sujet intitulé « %s » dans le forum « %s » et dont l’auteur est « %s » a été verrouillé.', // %s == emoji, topic, forum, user
	'UNLOCK_TOPIC'				=> '%s Le sujet intitulé « %s » dans le forum « %s » et dont l’auteur est « %s » a été déverrouillé.', // %s == emoji, topic, forum, user

	// User Notifications
	'CREATE_USER'				=> '%s Nouveau compte utilisateur créé pour le membre « %s ».', // %s == emoji, user
	'DELETE_USER'				=> '%s Compte utilisateur supprimé pour le membre « %s ».', // %s == emoji, user
	'DELETE_MULTI_USER'			=> '%s Comptes utilisateurs supprimés pour les membres : « %s ».', // %s == emoji, list of users

	// Additional Text
	'PREVIEW'					=> 'Aperçu : ',
	'REASON'					=> 'Raison : ',
	'POST'						=> 'message',
	'AND'						=> 'et',
	'CONJ'						=> ',', // short for "conjunction character"
	'OTHER'						=> 'autre',
	'OTHERS'					=> 'autres',
	'UNKNOWN_USER'				=> '{user}',
	'UNKNOWN_FORUM'				=> '{forum}',
	'UNKNOWN_TOPIC'				=> '{topic}',
));
