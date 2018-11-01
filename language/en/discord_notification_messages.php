<?php
/**
 * Discord Notifications extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Tyler Olsen, https://github.com/rootslinux
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// These messages are used by the event/notification_event_listener class. The notifications naturally generate dynamic content,
// and this is done using formatted strings passed to sprintf(). Each notification message below has a comment indicating what each
// %s string argument should represent (typically this is a hyperlink with text describing a user, topic, post, or forum).
// Note that the order of what gets populated in the %s arguments is unfortunately fixed, meaning that this could make good
// translations into other difficult.
$lang = array_merge($lang, array(
	// Post Notifications
	'CREATE_POST'				=> '%s %s created a new %s in the topic %s located in the forum %s', // %s == emoji, user, post, topic, forum
	'UPDATE_POST_SELF'			=> '%s %s edited their %s in the topic %s located in the forum %s', // %s == emoji, user, post, topic, forum
	'UPDATE_POST_OTHER'			=> '%s %s edited the %s written by %s in the topic %s located in the forum %s', // %s == emoji, edit user, post, user, topic, forum
	'DELETE_POST'				=> '%s Deleted post by user %s in the topic %s located in the forum %s', // %s == emoji, user, topic, forum
	'LOCK_POST'					=> '', // TODO
	'UNLOCK_POST'				=> '', // TODO

	// Topic Notifications
	'CREATE_TOPIC'				=> '%s %s created a new topic titled %s in the %s forum', // %s == emoji, user, topic, forum
	'UPDATE_TOPIC_SELF'			=> '%s %s edited their topic %s located in the forum %s', // %s == emoji, user, topic, forum
	'DELETE_TOPIC_OTHER'		=> '%s %s edited the the topic %s written by %s located in the forum %s', // %s == emoji, edit user, topic, user, forum
	'LOCK_TOPIC'				=> '%s The topic titled %s in the %s forum started by user %s has been locked', // %s == emoji, topic, forum, user
	'UNLOCK_TOPIC'				=> '%s The topic titled %s in the %s forum started by user %s has been unlocked', // %s == emoji, topic, forum, user

	// User Notifications
	'CREATE_USER'				=> '%s New user account created for %s', // %s == emoji, user
	'DELETE_USER'				=> '%s Deleted account for user %s', // %s == emoji, user
	'DELETE_MULTI_USER'			=> '%s Deleted accounts for users %s', // %s == emoji, list of users

	// Additional Text
	'PREVIEW'					=> 'Preview: ',
	'REASON'					=> 'Reason: ',
	'POST'						=> 'post',
	'AND'						=> 'and',
	'CONJ'						=> ',', // short for "conjunction character"
	'OTHER'						=> 'other',
	'OTHERS'					=> 'others',
));
