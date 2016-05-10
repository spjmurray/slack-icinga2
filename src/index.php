<?php
/**
 * Slack-Icinga2
 * Copyright (C) 2012-2016 Icinga Development Team (https://www.icinga.org/)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation
 * Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301, USA.
 */

include_once 'Application.php';
include_once 'Logger.php';


/**
 * Register command autoloader
 *
 * Automatically loads plugin classes from commands/ when they are
 * encountered by the parser.
 */
spl_autoload_register(function ($class) {
  include 'commands/' . $class . '.php';
});


/**
 * Main entry point
 *
 * Initialise global logging, gather runtime configuration, configure the
 * application and execute the command.
 */
$logger = new Logger('/var/log/slack-icinga2/slack-icinga2.log');
$application = new Application();
$application->run();


# vi: ts=4 et:
?>
