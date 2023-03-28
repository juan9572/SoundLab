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
require_once __DIR__ . "/vendor/autoload.php";

use duncan3dc\Speaker\TextToSpeech;
use duncan3dc\Speaker\Providers\VoiceRssProvider;


defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $provider = new VoiceRssProvider("6a42c931a8124001a4f6270db149d3de", "es-mx");
    $tts = new TextToSpeech("Helloooooo", $provider);
    $tts->save("/var/moodledata/hello.mp3", $tts->getAudioData());
    // Add your block's settings fields here.
}
