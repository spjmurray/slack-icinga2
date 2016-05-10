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
 * Encapsulates service downtime creation
 *
 * Allows a downtime to be applied to a specifc service as a shortcut or a
 * number of services as defined by a filter.  Downtimes are from now for a
 * specified duration, defaulting to 1h.  A comment is mandatory.
 */
class ServiceDowntimeCreate extends Command
{
    /**
     * Return a concise help string for the command
     */
    public function get_help()
    {
        return 'Create a service downtime';
    }

    /**
     * Return an argument parser for this command
     */
    public function get_parser($name)
    {
        $parser = new ArgumentParser($name);
        $group = $parser->add_mutually_exclusive_group(true);
        $group->add_argument('--service', 'Service to apply downtime to');
        $group->add_argument('--filter', 'Filter to apply downtime to');
        $parser->add_argument('--duration', 'Duration to apply downtime for', false, 3600);
        $parser->add_argument('--comment', 'Comment to apply to the downtime', true);
        return $parser;
    }

    /**
     * Execute a command with the given arguments
     */
    public function execute($argv)
    {
        $parser = $this->get_parser($this->application->name . ' ' . $this->name);
        try {
            $args = $parser->parse_args($argv);
        } catch (Exception $e) {
            $this->application->add_text(':exclamation: *' . $e->getMessage() . "*\n\n");
            $this->application->add_text($parser->format_help());
            return;
        }

        if($args->service) {
            $filter = 'service.name=="' . $args->service . '"';
        } else {
            $filter = $args->filter;
        }

        $query = array(
            'type' => 'Service',
            'filter' => $filter,
        );

        $data = array(
            'start_time' => time(),
            'end_time' => time() + $args->duration,
            'duration' => $args->duration,
            'author' => $this->application->username,
            'comment' => $args->comment,
        );

        $request = new HttpRequest($this->application->config);

        $response = $request->post('/v1/actions/schedule-downtime', $query, json_encode($data));
        if($response['results']) {
            $attachment = new Attachment();
            $attachment->set_title('Service Downtimes Set');
            $attachment->set_color('#0095bf');
            $attachment->set_markdown_in('text');
            foreach($response['results'] as $result) {
                $host = explode('!', $result['name'])[0];
                $icon = (int)$result['code'] == 200 ? ':white_check_mark:' : ':x:';
                $attachment->add_text($icon . ' ' . $host . "\n");
            }
            $this->application->add_attachment($attachment);
            $this->application->set_public_response();
        } else {
            $this->application->add_text("No results found\n");
        }
    }
}

# vi: ts=4 et:
?>
