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

/**
 * Encapsulates a command
 *
 * Defines required interface.
 */
abstract class Command
{
    /* The command name */
    public $name;

    /* Reference back to the application */
    protected $application;

    /**
     * Initialize a new instance
     *
     * The name is set based on the name of the leaf class.  Camelcase class
     * names are split on uppercase characters within the string, then normlized
     * to be all lower case.  For example 'MyCoolCommand' becomes 'my cool command'.
     */
    public function __construct($application)
    {
        $this->name = strtolower(preg_replace('/(.)([A-Z])/', '$1 $2', get_class($this)));
        $this->application = $application;
    }

    /**
     * Return a concise help string for the command
     */
    abstract public function get_help();

    /**
     * Return an argument parser for this command
     */
    abstract public function get_parser($name);

    /**
     * Execute a command with the given arguments
     */
    abstract public function execute($argv);
}

# vi: ts=4 et:
?>
