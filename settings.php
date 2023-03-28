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

require_once('settings_base.php');

if ($ADMIN->fulltree)
{
    $volume = volumeSelection();
    $speed = speedSelection();
    $settings->add(new admin_setting_configselect('soundlab_starting_volume', get_string('sm_starting_volume', 'soundlab'),
                    get_string('sm_starting_volumedef', 'soundlab'), 5, $volume));
    $settings->add(new admin_setting_configselect('soundlab_starting_speed', get_string('sm_starting_speed', 'soundlab'),
                    get_string('sm_starting_speeddef', 'soundlab'), 2, $speed));
}
