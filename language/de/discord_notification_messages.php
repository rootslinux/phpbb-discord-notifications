<?php
/**
 * Discord Notifications extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Tyler Olsen, https://github.com/rootslinux
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * This file contains the language strings for the notification messages that are transmitted to Discord.
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
	// Post Notifications
	'CREATE_POST'				=> '%1$s %2$s hat eine %3$s auf das Thema %4$s im Forum %5$s geschrieben.', // %s == emoji, user, post, topic, forum
	'UPDATE_POST_SELF'			=> '%1$s %2$s hat eine eigene %3$s auf das Thema %4$s im Forum %5$s bearbeitet.', // %s == emoji, user, post, topic, forum
	'UPDATE_POST_OTHER'			=> '%1$s %2$s hat eine %3$s von %4$s auf das Thema %5$s im Forum %6$s bearbeitet.', // %s == emoji, edit user, post, user, topic, forum
	'DELETE_POST'				=> '%1$s Eine Antwort von %2$s auf das Thema %3$s im Forum %4$s wurde gelöscht.', // %s == emoji, user, topic, forum
	'LOCK_POST'					=> '%1$s Eine %2$s von %3$s auf das Thema %4$s im Forum %5$s wurde gesperrt.', // %s == emoji, post, user, topic, forum
	'UNLOCK_POST'				=> '%1$s Eine %2$s von %3$s auf das Thema %4$s im Forum %5$s wurde entsperrt.', // %s == emoji, post, user, topic, forum
	'APPROVE_POST'				=> '%1$s Eine %2$s von %3$s auf das Thema %4$s im Forum %5$s wurde freigeschaltet.', // %s == emoji, post, user, topic, forum

	// Topic Notifications
	'CREATE_TOPIC'				=> '%1$s %2$s hat das Thema %3$s im Forum %4$s erstellt.', // %s == emoji, user, topic, forum
	'UPDATE_TOPIC_SELF'			=> '%1$s %2$s hat das eigene Thema %3$s im Forum %4$s bearbeitet.', // %s == emoji, user, topic, forum
	'UPDATE_TOPIC_OTHER'		=> '%1$s %2$s hat das Thema %3$s von %4$s im Forum %5$s bearbeitet.', // %s == emoji, edit user, topic, user, forum
	'DELETE_TOPIC'				=> '%1$s Das Thema \'%3$s\' von %2$s im Forum %5$s wurde zusammen mit %4$d Antwort(en) gelöscht.', // %s/d == emoji, user, topic title, post count, forum
	'LOCK_TOPIC'				=> '%1$s Das Thema %2$s von %4$s im Forum %3$s wurde gesperrt.', // %s == emoji, topic, forum, user
	'UNLOCK_TOPIC'				=> '%1$s Das Thema %2$s von %4$s im Forum %3$s wurde entsperrt.', // %s == emoji, topic, forum, user
	'APPROVE_TOPIC'				=> '%1$s Das Thema %2$s von %4$s im Forum %3$s wurde freigeschaltet.', // %s == emoji, topic, forum, user

	// User Notifications
	'CREATE_USER'				=> '%1$s Es wurde ein neuer Account für %2$s erstellt.', // %s == emoji, user
	'DELETE_USER'				=> '%1$s Der Account von %2$s wurde gelöscht.', // %s == emoji, user
	'DELETE_MULTI_USER'			=> '%1$s Die Accounts von %2$s wurden gelöscht.', // %s == emoji, list of users

	// Additional Text
	'PREVIEW'					=> 'Vorschau: ',
	'REASON'					=> 'Grund: ',
	'POST'						=> 'Antwort',
	'AND'						=> 'und',
	'CONJ'						=> ',', // short for "conjunction character"
	'OTHER'						=> 'weiterer',
	'OTHERS'					=> 'weitere',
	'UNKNOWN_USER'				=> '{user}',
	'UNKNOWN_FORUM'				=> '{forum}',
	'UNKNOWN_TOPIC'				=> '{topic}',
));
