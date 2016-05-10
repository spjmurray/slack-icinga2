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
 * Encapsulates a slack attachment
 *
 * Stores attachment fields in a data structure that can be directly used
 * to generate valid JSON to be used by slack.
 */
class Attachment {

    /* Attachment data structure */
    public $attachment = array();

    /**
     * Initialize a new instance
     */
    public function __construct()
    {
    }

    /**
     * Sets the attachment border color
     */
    public function set_color($color)
    {
        $this->attachment['color'] = $color;
    }

    /**
     * Sets the attachment author name, link and icon
     */
    public function set_author($name, $link = null, $icon = null)
    {
        $this->attachment['author_name'] = $name;
        if(isset($link)) {
            $this->attachment['author_link'] = $name;
        }
        if(isset($icon)) {
            $this->attachment['author_icon'] = $icon;
        }
    }

    /**
     * Sets the attachment title
     */
    public function set_title($title)
    {
        $this->attachment['title'] = $title;
    }

    /**
     * Accumulates attachment text
     */
    public function add_text($text)
    {
        if(!isset($this->attachment['text'])) {
            $this->attachment['text'] = '';
        }
        $this->attachment['text'] .= $text;
    }

    /**
     * Sets an attachment field
     */
    public function set_field($title, $value, $short = false)
    {
        if(!isset($this->attachment['fields'])) {
            $this->attachment['fields'] = array();
        }
        $this->attachment['fields'][] = array(
            'title' => $title,
            'value' => $value,
            'short' => $short,
        );
    }

    /**
     * Sets flags allowing markdown in an attachment field
     */
    public function set_markdown_in($field)
    {
        if(!isset($this->attachment['mrkdwn_in'])) {
            $this->attachment['mrkdwn_in'] = array();
        }
        $this->attachment['mrkdwn_in'][] = $field;
    }
}

# vi: ts=4 et:
?>
