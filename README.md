# phpbb-discord-notifications

[![Build Status](https://github.com/m-ober/phpbb-discord-notifications/workflows/Tests/badge.svg)](https://github.com/m-ober/phpbb-discord-notifications/actions)

A phpBB extension that publishes notification messages to a Discord channel when certain events occur on a phpBB board. The intent of this extension is meant to announce content changes on a forum to a community residing on a Discord server. It is not intended as a compliment to the announcements found within the phpBB admin or moderator control panels. See the [wiki](https://github.com/rootslinux/phpbb-discord-notifications/wiki) for additional information.

## Installation

Copy the extension to `phpBB/ext/mober/discordnotifications`

Go to "ACP" > "Customise" > "Extensions" and enable the "Discord Notifications" extension.

## Additional languages

There are translations provided by other users which are not distributed with this extension:

https://www.phpbb.com/customise/db/extension/discord_notifications_2/support/topic/237636

## Tests and Continuous Integration

We use Travis-CI as a continuous integration server and phpunit for our unit testing. See more information on the [phpBB Developer Docs](https://area51.phpbb.com/docs/dev/31x/testing/index.html).
To run the tests locally, you need to install phpBB from its Git repository. Afterwards run the following command from the phpBB Git repository's root:

Windows:

    phpBB\vendor\bin\phpunit.bat -c phpBB\ext\roots\discordnotifications\phpunit.xml.dist

Other Systems:

    phpBB/vendor/bin/phpunit -c phpBB/ext/roots/discordnotifications/phpunit.xml.dist

## License

[GPLv2](license.txt)
