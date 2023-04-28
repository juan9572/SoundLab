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
 * Return an array of numbers for the volume
 * @return array Return an array of strings
 */
function volumeSelection()
{
    for ($i = 1; $i > 0; $i = $i - 0.01)
    {
        $volume[] = strval(round($i, 2));
    }
    return $volume;
}

/**
 * Return an array of numbers for the speed
 * @return array Return an array of strings
 */
function speedSelection()
{
    for ($i = -10; $i <= 10; $i = $i + 1)
    {
        $speed[] = strval($i);
    }
    return $speed;
}

/**
 * Return an array of options for the active state
 * @return array Return an array of strings
 */
function activeSelection()
{
    $options = ["1","0"];
    return $options;
}

?>
