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
 * Encapsulates logging
 *
 * Simply provides the ability to append lines to a global log file with a
 * timestamp.
 */
class Logger
{
    /* File descriptor */
    private $file;

    /**
     * Initialize a new instance
     */
    public function __construct($path)
    {
        $this->file = fopen($path, 'a');
    }

    /**
     * Write a preformatted line to the log
     */
    public function write($text)
    {
        fwrite($this->file, '[' . date('r') . '] ' . $text . "\n");
        fflush($this->file);
    }
}

# vi: ts=4 et:
?>
