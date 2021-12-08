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

// These messages are used by the event/notification_event_listener class. The notifications naturally generate dynamic content,
// and this is done using formatted strings passed to sprintf(). Each notification message below has a comment indicating what each
// %s string argument should represent (typically this is a hyperlink with text describing a user, topic, post, or forum).
// Note that the order of what gets populated in the %s arguments is unfortunately fixed, meaning that this could make good
// translations into other difficult.
$lang = array_merge($lang, array(
	// Post Notifications
	'CREATE_POST'				=> '%s %s criou uma nova %s no tópico %s situado no fórum %s', // %s == emoji, user, post, topic, forum
	'UPDATE_POST_SELF'			=> '%s %s editou a sua própria %s no tópico %s situado no fórum %s', // %s == emoji, user, post, topic, forum
	'UPDATE_POST_OTHER'			=> '%s %s editou a %s criada por %s no tópico %s situado no fórum %s', // %s == emoji, edit user, post, user, topic, forum
	'DELETE_POST'				=> '%s Mensagem Apagada. Tinha sido criada pelo utilizador %s no tópico %s situado no fórum %s', // %s == emoji, user, topic, forum
	'LOCK_POST'					=> '%s A %s criada pelo utilizador %s no tópico %s no fórum %s foi bloqueada', // %s == emoji, post, user, topic, forum
	'UNLOCK_POST'				=> '%s A %s criada pelo utilizador %s no tópico %s no fórum %s foi desbloqueada', // %s == emoji, post, user, topic, forum
	'APPROVE_POST'				=> '%s A %s criada pelo utilizador %s no tópico %s no fórum %s foi aprovada', // %s == emoji, post, user, topic, forum

	// Topic Notifications
	'CREATE_TOPIC'				=> '%s %s criou um novo tópico intitulado %s no fórum %s', // %s == emoji, user, topic, forum
	'UPDATE_TOPIC_SELF'			=> '%s %s editou o seu tópico %s situado no fórum %s', // %s == emoji, user, topic, forum
	'UPDATE_TOPIC_OTHER'		=> '%s %s editou o tópico %s criado por %s situado no fórum %s', // %s == emoji, edit user, topic, user, forum
	'DELETE_TOPIC'				=> '%s Tópico Apagado. Tinha sido criado pelo utilizador %s intitulado \'%s\' contendo %d mensagens no fórum %s', // %s/d == emoji, user, topic title, post count, forum
	'LOCK_TOPIC'				=> '%s O tópico intitulado %s no fórum %s criado pelo utilizador %s foi bloqueado', // %s == emoji, topic, forum, user
	'UNLOCK_TOPIC'				=> '%s O tópico intitulado %s no fórum %s criado pelo utilizador %s foi desbloqueado', // %s == emoji, topic, forum, user
	'APPROVE_TOPIC'				=> '%s O tópico intitulado %s no fórum %s criado pelo utilizador %s foi aprovado', // %s == emoji, topic, forum, user

	// User Notifications
	'CREATE_USER'				=> '%s Nova conta de utilizador criada %s', // %s == emoji, user
	'DELETE_USER'				=> '%s Conta apagada para o utilizador %s', // %s == emoji, user
	'DELETE_MULTI_USER'			=> '%s Contas apagadas para os utilizadores %s', // %s == emoji, list of users

	// Additional Text
	'PREVIEW'					=> 'Pré-visualização: ',
	'REASON'					=> 'Razão: ',
	'POST'						=> 'mensagem',
	'AND'						=> 'e',
	'CONJ'						=> ',', // short for "conjunction character"
	'OTHER'						=> 'outro',
	'OTHERS'					=> 'outros',
	'UNKNOWN_USER'				=> '{utilizador}',
	'UNKNOWN_FORUM'				=> '{fórum}',
	'UNKNOWN_TOPIC'				=> '{tópico}',
));
