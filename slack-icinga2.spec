Name: slack-icinga2
Version: 0.0.1
Release: 1
License: GPLv2
Summary: Icinga2 integration for Slack messaging
Group: System Environment/Base
URL: http://www.icinga.org
BuildArch: noarch
Requires: httpd

%description
Slack-Icinga2 is a PHP web application which accepts HTTPS slash commands from
the Slack messaging service and uses them to generate commands against the
Icinga2 API.

%install
make install DESTDIR=%{buildroot}

%files
/etc/slack-icinga2/config.json
/usr/share/slack-icinga2/Application.php
/usr/share/slack-icinga2/ArgumentParser.php
/usr/share/slack-icinga2/Attachment.php
/usr/share/slack-icinga2/Command.php
/usr/share/slack-icinga2/Config.php
/usr/share/slack-icinga2/HttpRequest.php
/usr/share/slack-icinga2/index.php
/usr/share/slack-icinga2/Logger.php
/usr/share/slack-icinga2/commands/Help.php
/usr/share/slack-icinga2/commands/HostDowntimeCreate.php
/usr/share/slack-icinga2/commands/HostDowntimeList.php
/usr/share/slack-icinga2/commands/HostDowntimeRemove.php
/usr/share/slack-icinga2/commands/ServiceDowntimeCreate.php
/usr/share/slack-icinga2/commands/ServiceDowntimeList.php
/usr/share/slack-icinga2/commands/ServiceDowntimeRemove.php

%post
mkdir -p 0640 /var/log/slack-icinga2
chgrp apache /var/log/slack-icinga2
