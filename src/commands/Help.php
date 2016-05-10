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
 * Encapsulates help command
 *
 * If executed with an argument list will print the verbose help for a specific
 * command, otherwise iterates through all commands and prints out terse output
 * for each command.
 */
class Help extends Command
{
    /**
     * Return a concise help string for the command
     */
    public function get_help()
    {
        return 'Display help for all or a specific command';
    }

    /**
     * Return an argument parser for this command
     */
    public function get_parser($name)
    {
        return new ArgumentParser($name);
    }

    /**
     * Execute a command with the given arguments
     */
    public function execute($argv)
    {
        if($argv) {
            $command = $this->application->get_command($argv);
            if($command) {
                $command_name = $this->application->name . ' ' . $command[0]->name;
                $parser = $command[0]->get_parser($command_name);
                $this->application->add_text($parser->format_help());
            } else {
                $this->application->add_text(":exclamation: Unable to find command\n");
            }
        } else {
            foreach($this->application->commands as $command) {
                $this->application->add_text('*' . $command->name . "*\t" . $command->get_help() . "\n");
            }
        }
    }
}

# vi: ts=4 et:
?>
