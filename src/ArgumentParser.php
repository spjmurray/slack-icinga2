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
 * Class to encapsulate arguments
 *
 * Once parsed by the parser the arguments and their operands are inserted into
 * this class.  It uses magic functions so they can be accessed via $args->argument
 */
class _Arguments
{
    /* Hash of argument/parameter pairs */
    private $args = array();

    /**
     * Initialize a new instance
     */
    public function __construct()
    {
    }

    /**
     * Gets the requested value
     *
     * Returns false if the value is not set
     */
    public function __get($key)
    {
        if(isset($this->args[$key])) {
            return $this->args[$key];
        }
        return False;
    }

    /**
     * Sets the requested value
     */
    public function __set($key, $value)
    {
        $this->args[$key] = $value;
    }
}

/**
 * Encapsulates an argument definition
 *
 * Specifies the argument name, its help text, whether the argument
 * is required and a default value.
 */
class _Argument
{
    /* Option name */
    public $opt;

    /* Help text */
    public $help;

    /* Whether the argument is required e.g. non-optional */
    public $required;

    /* Default value if the argument is not specified */
    public $default;

    /**
     * Initialize a new instance
     */
    public function __construct($opt, $help, $required, $default)
    {
        $this->opt = $opt;
        $this->help = $help;
        $this->required = $required;
        $this->default = $default;
    }
}

/**
 * Encapsulate a muteually exclusive group
 *
 * Represents a set of arguments of which only one can be specified at a time.
 * Like individual arguments a group may be required or optional.
 */
class _MutexGroup
{
    /* List of arguments in the group */
    public $args = array();

    /* Whether of not an argument is required */
    public $required;

    /**
     * Initialize a new instance
     */
    public function __construct($required)
    {
        $this->required = $required;
    }

    /**
     * Adds an argument to the list
     *
     * Individual arguments within a group cannot be required and also cannot
     * have a default.
     */
    public function add_argument($opt, $help)
    {
        $this->args[] = new _Argument($opt, $help, false, null);
    }
}

/**
 * Encapsulates argument parsing
 *
 * The PHP analogue of python's argparse module.
 */
class ArgumentParser
{
    /* List of arguments to look for while parsing */
    private $args = array();

    /* List of mutually exclusive argument groups */
    private $groups = array();

    /* The command name */
    private $name;

    /**
     * Initialize a new instance
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Buffers a new argument
     */
    public function add_argument($opt, $help, $required=false, $default=null)
    {
        $this->args[] = new _Argument($opt, $help, $required, $default);
    }

    /**
     * Buffers and returns a new mutex group
     */
    public function add_mutually_exclusive_group($required=false)
    {
        $group = new _MutexGroup($required);
        $this->groups[] = $group;
        return $group;
    }

    /**
     * Parse an argument vector
     *
     * Sets default parameters, examines each argument in the argument vector
     * buffering the output if it is an expected argument.  Then performs
     * sanity checks to ensure for mutually exclusive parameters only one is
     * set, and for both mutext groups and normal arguments required arguments
     * have been provided.
     */
    public function parse_args(array $argv)
    {
        $res = new _Arguments();

        // If a parameter has a default, set it in the output as it will
        // be over written if specified in the argument vector
        foreach($this->args as $a) {
            if($a->default) {
                $opt = substr($a->opt, 2);
                $res->$opt = $a->default;
            }
        }

        // Keep tabs on which arguments have been seen so we can alert on
        // missing required arguments
        $seen_args = array();

        // For each argument in the vector check if it is defined in a
        // mutext group or the argument list, adding to the output argument
        // list if it exists.
        $argc = sizeof($argv);
        for($i=0; $i<$argc; $i++) {
            $arg = null;
            foreach($this->groups as $g) {
                foreach($g->args as $a) {
                    if($argv[$i] == $a->opt) {
                        $arg = $a;
                        break;
                    }
                }
            }
            foreach($this->args as $a) {
                if($argv[$i] == $a->opt) {
                    $arg = $a;
                    break;
                }
            }
            if(!isset($arg)) {
                throw new Exception('Illegal argument "' . $argv[$i] . '"');
            }
            // Strip leading dashes from the argument name
            $opt = substr($arg->opt, 2);
            $res->$opt = $argv[$i++ + 1];
            $seen_args[] = $arg;
        }

        // For each mutex group check to see if multiple options were specified or
        // any if an argument is required
        foreach($this->groups as $g) {
            $hit = null;
            foreach($g->args as $a) {
                if(in_array($a, $seen_args)) {
                    if($hit) {
                        throw new Exception('Mutually exclusive arguments "' . $hit->opt . '" and "' . $a->opt . '"');
                    }
                    $hit = $a;
                }
            }
            if($g->required && !isset($hit)) {
                throw new Exception('Missing required mutually exclusive option');
            }
        }

        // For each required argument check that it has been specified
        foreach($this->args as $a) {
            if($a->required and !in_array($a, $seen_args)) {
                throw new Exception('Missing required argument "' . $a->opt . '"');
            }
        }

        return $res;
    }

    /**
     * Get formatted help
     *
     * Returns a foramtted syntax string containing a single line showing
     * command and all arguments and mutually exclusive argument groups.
     * Individual argument help is then provided on subsequent lines.
     */
    public function format_help()
    {
        // Render the syntax
        $text = '*' . $this->name;
        if(sizeof($this->groups)) {
            foreach($this->groups as $group) {
                $text .= ' ';
                if(!$group->required) {
                    $text .= '[';
                }
                $text .= '(';
                $temp = array();
                foreach($group->args as $arg) {
                    $temp[] = $arg->opt . ' ' . strtoupper(substr($arg->opt, 2));
                }
                $text .= join(' | ', $temp) . ')';
                if(!$group->required) {
                    $text .= ']';
                }
            }
        }
        if(sizeof($this->args)) {
            foreach($this->args as $arg) {
                $text .= ' ';
                if(!$arg->required) {
                    $text .= '[';
                }
                $text .= $arg->opt . ' ' . strtoupper(substr($arg->opt, 2));
                if(!$arg->required) {
                    $text .= ']';
                }
            }
        }
        $text .= "*\n\n";

        // Render argument specific help for mutex groups
        if(sizeof($this->groups)) {
            foreach($this->groups as $group) {
                foreach($group->args as $arg) { 
                    $text .= '*' . $arg->opt . ' ' . strtoupper(substr($arg->opt, 2)) . "*\t";
                    if($arg->help) {
                        $text .= $arg->help;
                    }
                    if(isset($arg->default)) { 
                        $text .= ' (default: ' . $arg->default . ')';
                    }
                    $text .= "\n";
                }
            }
        }

        // Render argument specific help
        if(sizeof($this->args)) {
            foreach($this->args as $arg) {
                $text .= '*' . $arg->opt . ' ' . strtoupper(substr($arg->opt, 2)) . "*\t";
                if($arg->help) {
                    $text .= $arg->help;
                }
                if(isset($arg->default)) {
                    $text .= ' (default: ' . $arg->default . ')';
                }
                if($arg->required) {
                    $text .= ' [REQUIRED]';
                }
                $text .= "\n";
            }
        }

        return $text;
    }
}

# vi: ts=4 et:
?>
