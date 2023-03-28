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
 * @return array Return an array of numbers
 */
function volumeSelection()
{
    for ($i = 100; $i > 0; $i = $i - 5)
    {
        $volume[] = $i;
    }
    return $volume;
}

/**
 * Return an array of numbers for the speed
 * @return array Return an array of numbers
 */
function speedSelection()
{
    for ($i = -10; $i <= 10; $i = $i + 2)
    {
        $speed[] = $i;
    }
    return $speed;
}

?>
