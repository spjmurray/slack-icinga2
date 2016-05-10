# Slack Icinga2

Integrates Slack messaging with the Icinga2 API.

![Slack Icinga2](https://gist.githubusercontent.com/spjmurray/509328e7efac7a2328a4a677585b6222/raw/6b682f2d597008ee1528e385f8fd153bfb8bb326/slack-icinga2.png)

## Contents

* [Description](#description)
* [Installation](#installation)
* [Configuration](#configuration)
* [Commands](#commands)
* [Building](#building)
* [Authors](#authors)

## Description

Slack Icinga2 is a simple PHP application which resides on a webserver, typically a small cloud instance.  It accepts slash commands from the Slack messaging application and interracts with the Icinga2 API.  The architecture is based on Openstack Cliff in that commands are in human readable form e.g. `host downtime create`, and uses command plugins.  The plugins that are presented to the end user are configurable and may be selectively enabled or disabled as required by your environment.  The following commands are currently available:

* help
* host downtime list
* host downtime create
* host downtime remove
* service downtime list
* service downtime create
* service downtime remove

## Installation

This application in available in rpm and deb flavors.  It installs to the following locations:

* `/etc/slack-icinga2/config.json` - Main configuration file
* `/usr/share/slack-icinga2` - Main source files
* `/usr/share/slack-icinga2/commands` - Command source files
* `/var/log/slack-icinga2/slack-icinga2.log` - Audit and debug logging

## Configuration

### PHP

Your php.ini configuration should set its timezone to be that you wish to output times in.

### Apache

A typical virtual host configuration will look like the following:

```apache
<VirtualHost #:443>
    ServerName slack-icinga2.example.com
    DocumentRoot /usr/share/slack-icinga2

    SSLEngine on
    SSLCipherSuite HIGH:!aNull:!MD5
    SSLCertificateFile "/etc/ssl/private/slack-icinga2.example.com.crt"
    SSLCertificateKeyFile "/etc/ssl/private/slack-icinga2.example.com.key"
</VirtualHost>

# vim: syntax#apache ts#4 sw#4 sts#4 sr noet
```

### config.json

The main configuration is defined as follows:

```json
{
  "host": "icinga2.example.com",
  "port": "5665",
  "username": "johndoe",
  "password": "password",
  "ssl_ca": "/etc/slack-icinga2/ssl/ca.pem",
  "ssl_cert": "/etc/slack-icinga2/ssl/slack-icinga2.pem",
  "ssl_key": "/etc/slack-icinga2/ssl/slack-icinga2.key",
  "token": "xxxxxxxxxxxxxxxxxxxxxxxxx",
  "commands": [
    "Help",
    "HostDowntimeList",
    "HostDowntimeCreate",
    "HostDowntimeRemove",
    "ServiceDowntimeList",
    "ServiceDowntimeCreate",
    "ServiceDowntimeRemove"
  ]
}
```

**host**

The host to connect to which surfaces the Icinga2 API.  Must be a fully qualified DNS name which matches the server side certificate's common name or DNS alternative name.

**port**

The port on the host to connect to the Icinga2 API.  Optional, defaults to 5665.

**username**

The username of an Icinga2 User object if basic authentication is required.  Optional.

**password**

The password of the Icinga2 User.  Required if username is specified.

**ssl\_ca**

The CA certificate of the Icinga2 API endpoint.  Required for server validation.

**ssl\_cert**

The SSL client certificate of the user if required by the corresponding Icinga2 User object for mutual verification.  Optional.

**ssl\_key**

The SSL key for the corresponding ssl\_certificate.  Required if ssl\_certificate is specified.

**token**

The Slack security token used to authenticate inbound requests.  Optional.

**commands**

A list on enabled commands suerfaced by the application.  The names correspond with the command classes found in `/usr/share/slack-icinga2/commands`.  Required.

## Commands

**help [command]**

By default lists all enabled commands.  If a command name is specified as a parameter it will print out verbose help.

**host downtime list**

Lists all host downtimes currently active.  Each attachment specifies the user who created the downtime, the comment documenting the reason for the downtime and the start/end times.

**host downtime create (--host HOST | --filter FILTER) [--duration DURATION] --comment COMMENT**

Creates a host downtime object for a host or all hosts that match a filter, from now for a specified duration.  If specified, the host name matches the name of an Icinga2 Host object.  If specified, filter applies a downtime to all hosts that match the filter e.g. 'host.vars.role##"webserver"'.  See the Icinga2 API documentation for further information.  Either the host or filter option must be specified.

The duration is optional and specifies the length of the downtime.  This option is an integral value in seconds, defaulting to 3600.

The comment argument tells operators why the downtime was put in place.  This argument is required.

**host downtime remove (--host | --filter)**

Removes a host downtime object for a host or all hosts that match a filter.  If specified, the host name matches the name of an Icinga2 Host object.  If specified, filter applies a downtime to all hosts that match the filter.  See the Icinga2 API documentation for further information.

**service downtime list**

Lists all service downtimes currently active.  Each attachment specifies the user who created the downtime, the comment documenting the reason for the downtime and the start/end times.

**service downtime create (--service SERVICE | --filter FILTER) [--duration DURATION] --comment COMMENT**

Creates a service downtime object for a service or all services that match a filter, from now for a specified duration.  If specified, the service name matches the name of an Icinga2 Service object.  If specified, filter applies a downtime to all services that match the filter e.g. 'match("dns\#", service.name').  See the Icinga2 API documentation for further information.  Either the service or filter option must be specified.

The duration is optional and specifies the length of the downtime.  This option is an integral value in seconds, defaulting to 3600.

The comment argument tells operators why the downtime was put in place.  This argument is required.

**service downtime remove (--service | --filter)**

Removes a service downtime object for a service or all services that match a filter.  If specified, the service name matches the name of an Icinga2 Service object.  If specified, filter applies a downtime to all services that match the filter.  See the Icinga2 API documentation for further information.

## Building

### Ubuntu/Debian

```sh
dpkg -I -us -uc
```

### RedHat/Centos/Fedora

```sh
rm -rf ~/rpmbuild/BUILD/*
cp -a * ~/rpmbuild/BUILD
rpmbuild -ba slack-icinga2.spec
```

## Authors

Simon Murray <spjmurray@yahoo.co.uk>
