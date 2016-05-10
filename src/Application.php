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

include_once 'ArgumentParser.php';
include_once 'Attachment.php';
include_once 'Command.php';
include_once "Config.php";
include_once 'HttpRequest.php';
include_once 'Logger.php';

/**
 * Encapsulates main business logic
 *
 * On construction checks the run mode, either via HTTP or the CLI, and marshals
 * runtime parameters into canonical form, then parses global configuration, and
 * authorizes access.  When run() is called the arguments are parsed and the
 * correct command plugin is invoked to perform the actual work.  Once complete
 * the response is formatted and echoed back to stdin.
 *
 * Both plain text and attachment output are supported, but they are mutually
 * exclusive with attachments taking precedence.
 */
class Application
{
    /* Application name */
    public $name;

    /* Username of the person invoking the application */
    public $username;

    /* Argument vector parsed from the command line or POST data */
    public $argv;

    /* Configuration data */
    public $config;

    /* List of command plugin classes */
    public $commands = array();

    /* Output text buffer */
    public $text = '';

    /* Whether to render output publically e.g. visible to all */
    private $public = False;

    /* Ouput attachment list */
    private $attachments = array();

    /**
     * Initialize a new instance
     *
     * Interrogates runtime and static configration data and instantiates
     * the list of command plugins.
     */
    public function __construct()
    {
        $config = $this->get_runtime_config();

        $this->name = $config['name'];
        $this->username = $config['username'];
        $this->argv = $config['argv'];

        $this->config = new Config();
        $this->config->load('/etc/slack-icinga2/config.json');
        $this->config->validate();

        if(!$this->authorized()) {
            die('Request unautorized');
        }

        foreach($this->config->commands as $command) {
            $this->commands[] = new $command($this);
        }
    }

    /**
     * Checks if we are running interactively
     *
     * Checks for the existence of $argv at a global scope to determine
     * whether we are being invoked as a shell script or a web app.
     */
    public function interactive() {
        global $argv;
        return isset($argv);
    }

    /**
     * Extract runtime information
     *
     * Collects the application name, user name and argument vector
     * from either the system if run interatively or from HTTP POST
     * variables if run as a web app.
     */
    public function get_runtime_config() {
        global $argv;

        $config = array();
        if($this->interactive()) {
            $config['name'] = $argv[0];
            $config['username'] = get_current_user();
            $config['argv'] = array_slice($argv, 1);
        } else {
            # Warning: precedence extracts '$string' before "$string" due to the way
            # Icinga2 handles filters e.g. 'host.vars.role=="dns"' would generate
            # correct API configuration.
            preg_match_all('/\'[^\']*\'|"[^"]*"|\S+/', $_POST['text'], $matches);
            $post_argv = $matches[0];
    
            # Extract string contents from within quotes
            foreach($post_argv as $id => $arg) {
                if(preg_match('/^("|\').*("|\')$/', $arg)) {
                    $post_argv[$id] = substr($arg, 1, strlen($arg) - 2);
                }
            }
    
            # If no parameters are specified display the help text
            if(!sizeof($post_argv)) {
                $post_argv[] = 'help';
            }
    
            $config['name'] = $_POST['command'];
            $config['username'] = $_POST['user_name'];
            $config['argv'] = $post_argv;
        }
        return $config;
    }

    /**
     * Check if the request is authorized
     *
     * If running in non-interactive (e.g. shell) mode and a token is configured
     * check that the request is from an authorized source
     */
    public function authorized()
    {
        if(!$this->interactive() && isset($this->config->token) && ($this->config->token != $_POST['token'])) {
            return false;
        }
        return true;
    }

    /**
     * Search for a command plugin object
     *
     * Consumes parameters from the argument vector one at a time accumulating
     * them into a buffer.  If the command name matches that of a plugin then
     * it is returned in a hash along with the remainder of the argument vector.
     */
    public function get_command($argv)
    {
        $name = False;
        $args = $argv;
        foreach($args as $index => $arg) {
            if(!$name) {
                $name = $arg;
            } else {
                $name .= ' ' . $arg;
            }
            unset($argv[$index]);
            foreach($this->commands as $command) {
                if($command->name == $name) {
                    return array($command, array_values($argv));
                }
            }
        }
        return False;
    }

    /**
     * Run the application
     *
     * Searches for a command that matches the argument vector.  If found, the
     * plugin is executed with the remaining arguments.  Any output present is
     * printed back to slack.
     */
    public function run()
    {
        $command = $this->get_command($this->argv);
        if($command) {
            $command[0]->execute($command[1]);
        } else {
            $this->add_text(":exclamation: Unable to find command\n");
        }
        $this->print_text();
    }

    /**
     * Buffers output text
     */
    public function add_text(string $text)
    {
        $this->text .= $text;
    }

    /**
     * Appends an output attachment to the list
     */
    public function add_attachment(Attachment $attachment)
    {
       $this->attachments[] = $attachment; 
    }

    /**
     * Sets the output to be public
     *
     * By default all output is private to the user, if this is set then the
     * output will be visible to all in the channel.
     */
    public function set_public_response()
    {
        $this->public = true;
    }

    /**
     * Format and print output
     *
     * Prints attachments if they exist or any output text that had been buffered.
     */
    private function print_text()
    {
        $response = array();
        if($this->public) {
            $response['response_type'] = 'in_channel';
        }
        if($this->attachments) {
            $response['attachments'] = array();
            foreach($this->attachments as $attachment) {
                $response['attachments'][] = $attachment->attachment;
            }
        } else {
            $response['text'] = $this->text;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }
}

# vi: ts=4 et:
?>
