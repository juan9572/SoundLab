<?php
// This file is part of SoundLab plugin for Moodle - http://moodle.org/
//
// SoundLab plugin for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// SoundLab plugin for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with SoundLab plugin for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version metadata for the block_pluginname plugin.
 *
 * @package   block_pluginname
 * @copyright Year, You Name <your@email.address>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2022032700; // The current version of your plugin
$plugin->requires = 2020110900; // The minimum version of Moodle required for your plugin to work
$plugin->component = 'block_soundlab'; // The name of your plugin's folder
$plugin->maturity = MATURITY_ALPHA; // The stability level of your plugin (MATURITY_ALPHA, MATURITY_BETA, MATURITY_RC, MATURITY_STABLE)
$plugin->release = '1.0.0'; // The current release number of your plugin

$plugin->dependencies = [
    'mod_forum' => 2020110900, // The minimum version of the 'mod_forum' plugin required by your plugin
    'mod_data' => 2020110900 // The minimum version of the 'mod_data' plugin required by your plugin
];