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
 * Encapsulates configuration
 *
 * Reads global static configuration and validates supported options are
 * present where required.  After parsing arguments are made available via
 * magic functions e.g. $config->config_item.
 */
class Config
{
    /* Hash of configuration variables */
    private $config = array();

    /**
     * Initialize a new instance
     */
    public function __construct()
    {
    }

    /**
     * Loads the specified configuration file from disk
     */
    public function load($file)
    {
        $file = file_get_contents($file);
        $this->config = json_decode($file, true);
    }

    /**
     * Validates configuration
     *
     * Ensures an API host is present, that some form of authentication is specified
     * be it simple or via X.509.  Ensure a password is specified if using basic
     * authentication, or a certificate/key pair is specified when using X.509.
     * Ensures a CA certificate is provided so we can authenticate the remote host.
     */
    public function validate()
    {
        // Check the API has a host
        if(!isset($this->config['host'])) {
            die('no api host in configuration');
        }

        // Check for a port, defaulting to 5665
        if(!isset($this->config['port'])) {
            $this->config['port'] = 5665;
        }

        // Ensure we have some form of authentication
        if(!isset($this->config['username']) && !isset($this->config['ssl_cert'])) {
            die('no authentication mechanism provided');
        }

        // If a username is given check for a password
        if(isset($this->config['username']) && !isset($this->config['password'])) {
            die('username provided for api but no password');
        }

        // Ensure a CA certificate is provided
        if(!isset($this->config['ssl_ca'])) {
            die('no ca certificate provided');
        }

        // Ensure we have a key set if the certificate is provided
        if(isset($this->config['ssl_cert']) && !isset($this->config['ssl_key'])) {
            die('certificate provided for api but no key');
        }
    }

    /**
     * Gets the requested config value if it exists or false
     */
    public function __get($name)
    {
        if(isset($this->config[$name])) {
            return $this->config[$name];
        }
        return false;
    }
}

# vi: ts=4 et:
?>
