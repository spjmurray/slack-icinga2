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
 * Encapsulates listing of host downtimes
 *
 * Prints a list of attachments which describe who created the downtime, why and
 * the downtime window extents.
 */
class HostDowntimeList extends Command
{
    /**
     * Return a concise help string for the command
     */
    public function get_help()
    {
        return 'List host downtimes';
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
        $request = new HttpRequest($this->application->config);
        $response = $request->get('/v1/objects/downtimes');

        if($response['results']) {
            foreach($response['results'] as $result) {
                if(isset($result['attrs']['service_name'])) {
                    continue;
                }
                $title = $result['attrs']['host_name'];
                $start = date('r', (int)$result['attrs']['start_time']);
                $end = date('r', (int)$result['attrs']['end_time']);
                $author = $result['attrs']['author'];
                $comment = $result['attrs']['comment'];

                $attachment = new Attachment();
                $attachment->set_author($author);
                $attachment->set_title($title);
                $attachment->add_text($comment);
                $attachment->set_color('#0095bf');
                $attachment->set_field('Start', $start, true);
                $attachment->set_field('End', $end, true);

                $this->application->add_attachment($attachment);
                $this->application->set_public_response();
            }
        } else {
            $this->application->add_text("No results found\n");
        }
    }
}

# vi: ts=4 et:
?>
