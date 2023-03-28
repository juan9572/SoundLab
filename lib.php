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

defined('MOODLE_INTERNAL') || die();

/**
 * Library of functions and constants for module soundlab
 *
 * @package    block_soundlab
 */

/**
 * Add the block-specific settings to the settings navigation.
 *
 * @param navigation_node $settings The settings navigation object.
 * @param navigation_node $node     The node to add the soundlab settings to.
 */
function block_soundlab_extend_settings_navigation($settings, $node) {
    // Add a new node to the settings navigation.
    $soundlabsettings = $node->add(get_string('pluginname', 'block_soundlab'), null, navigation_node::TYPE_CONTAINER);

    // Add a link to the soundlab settings page.
    $soundlabsettings->add(get_string('soundlabsettings', 'block_soundlab'), new moodle_url('/blocks/soundlab/settings.php'));
}
