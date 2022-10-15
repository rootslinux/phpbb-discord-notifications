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

$lang = array_merge($lang, array(
	// ACP Extension Settings Page
	'DN_ACP_DESCRIPTION'			=> 'Depuis cette page il est possible de définir les paramètres permettant à différents évènements du forum d’être envoyés comme notification sur un serveur Discord.',

	'DN_MAIN_SETTINGS'				=> 'Paramètres généraux',
	'DN_MASTER_ENABLE'				=> 'Activer les notifications Discord',
	'DN_WEBHOOK_URL'				=> 'URL Webhook de Discord',
	'DN_WEBHOOK_DESCRIPTION'		=> 'Permet de saisir l’adresse URL Webhook du serveur Discord généré par le serveur Discord. Voir <a href="https://support.discordapp.com/hc/fr/articles/228383668-Intro-to-Webhooks">cet article</a> pour apprendre comment créer un nouveau Webhook.',
	'DN_POST_PREVIEW_LENGTH'		=> 'Longueur de l’aperçu des messages',
	'DN_POST_PREVIEW_DESCRIPTION'	=> 'Permet de saisir le nombre de caractères à afficher dans l’aperçu des messages (10 - 2000). Définir sur la valeur 0 pour désactiver l’aperçu des messages.',
	'DN_TEST_MESSAGE'				=> 'Message de test',
	'DN_TEST_MESSAGE_TEXT'			=> 'Texte du message de test : Hello Discord!',
	'DN_TEST_DESCRIPTION'			=> 'Permet d’envoyer un message sur le serveur Discord afin de vérifier que la connexion avec phpBB est fonctionnelle.',
	'DN_SEND_TEST'					=> 'Envoyer un message de test',
	'DN_SEND_TEST_DESCRIPTION'		=> 'Permet d’envoyer le contenu du message de test au Webhook du serveur Discord. À utiliser pour vérifier que le Webhook est fonctionnel.',

	'DN_TYPE_SETTINGS'				=> 'Types de notification',
	'DN_TYPE_DESCRIPTION'			=> 'Permet de sélectionner quels types de notification devraient être générés sous forme de messages envoyés sur le serveur Discord.',
	'DN_POST_CREATE'				=> 'Message créé',
	'DN_POST_UPDATE'				=> 'Message modifié',
	'DN_POST_DELETE'				=> 'Message supprimé',
	'DN_POST_LOCK'					=> 'Message verrouillé',
	'DN_POST_UNLOCK'				=> 'Message déverrouillé',
	'DN_TOPIC_CREATE'				=> 'Sujet créé',
	'DN_TOPIC_UPDATE'				=> 'Sujet modifié',
	'DN_TOPIC_DELETE'				=> 'Sujet supprimé',
	'DN_TOPIC_LOCK'					=> 'Sujet verrouillé',
	'DN_TOPIC_UNLOCK'				=> 'Sujet déverrouillé',
	'DN_USER_CREATE'				=> 'Compte utilisateur créé',
	'DN_USER_DELETE'				=> 'Compte utilisateur supprimé',

	'DN_FORUM_SETTINGS'				=> 'Forums concernés par les notifications',
	'DN_FORUM_DESCRIPTION'			=> 'Permet de sélectionner les forums pour lesquels des évènements seront notifiés sur le serveur Discord, tels que ceux liés aux messages et aux sujets.',

	// Messages that appear after a user tries to send a test message
	'DN_TEST_SUCCESS'				=> 'Test effectué avec succès ! Merci de vérifier sur le serveur Discord que le message de test apparait.',
	'DN_TEST_FAILURE'				=> 'Un problème a été rencontré lors de l’envoi du message de test. Merci de vérifier l’adresse URL du Webhook du serveur Discord.',
	'DN_TEST_BAD_MESSAGE'			=> 'Merci de saisir au moins un caractère pour le message de test.',
	'DN_TEST_BAD_WEBHOOK'			=> 'Le texte saisi pour l’adresse URL Webhook du serveur Discord est incorrecte. Merci de vérifier ce paramètre puis d’essayer l’envoi à nouveau.',

	// Success/Failure messages that can be generated once the user saves
	'DN_SETTINGS_SAVED'				=> 'Les paramètres de notification Discord ont été modifiés avec succès !',
	'DN_MASTER_WEBHOOK_REQUIRED'	=> 'Une adresse URL Webhook du serveur Discord est requise pour activer les notifications.',
	'DN_WEBHOOK_URL_INVALID'		=> 'L’adresse URL Webhook du serveur Discord doit être complète et correspondre à une adresse URL valide.',
	'DN_POST_PREVIEW_INVALID'		=> 'La longueur de l’aperçu des messages doit être comprise entre 10 et 2000 ou définie sur 0 pour la désactivation de l’aperçu.',
));
