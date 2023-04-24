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
 * Block definition class for the block_pluginname plugin.
 *
 * @package   block_pluginname
 * @copyright Year, You Name <your@email.address>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once __DIR__ . "/vendor/autoload.php";

use duncan3dc\Speaker\TextToSpeech;
use duncan3dc\Speaker\Providers\VoiceRssProvider;

class block_soundlab extends block_base {

    /**
     * Initialises the block.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_soundlab');
    }

    public function specialization() {
        if (isset($this->config)) {
            if (empty($this->config->title)) {
                $this->title = get_string('pluginname', 'block_soundlab');
            } else {
                $this->title = $this->config->title;
            }
        } else {
            $this->config = new stdClass;
            $this->config->title = get_string('pluginname', 'block_soundlab');
        }
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function instance_allow_config()
    {
        return false;
    }

    public function has_config() {
        return true;
    }

    /**
     * Defines in which pages this block can be added.
     *
     * @return array of the pages where the block can be added.
     */
    public function applicable_formats() {
        return [
            'admin' => false,
            'site-index' => false,
            'course-view' => false,
            'mod' => true,
            'my' => false,
        ];
    }

    function get_content()
    {
        global $CFG, $COURSE, $PAGE, $DB;
        //Get global/admin tts configs
        require_once('settings_base.php');
        $volume = volumeSelection();
        $speed = speedSelection();
        $course = $this->page->course;
        //Replace get_context_instance by the class for moodle 2.6+
        if(class_exists('context_module'))
        {
            $context = context_course::instance($course->id);
        }
        else
        {
            $context = get_context_instance(CONTEXT_COURSE, $course->id);
        }
        // Obtener todas las preguntas del cuestionario
        $questions_data = array();
        $questions = $DB->get_records('question');
        foreach($questions as $question){
            $current_option = 0;
            $question_text = strip_tags($question->questiontext);
            $question_type = $question->qtype;
            $answer_data = array();
            if($question_type == "match"){
                $options = array();
                $answers = $DB->get_records('qtype_match_subquestions', array('questionid' => $question->id));
                foreach($answers as $answer){
                    $option_text = strip_tags($answer->questiontext);
                    $answer_text = strip_tags($answer->answertext);
                    $options[] = $option_text;
                    $answer_data[] = $answer_text;
                }
                $questions_data[] = array(
                    'question' => $question_text,
                    'answers' => array('options' => $options, 'answer_options' => $answer_data)
                );
            }else{
                $answers = $DB->get_records('question_answers', array('question' => $question->id));
                foreach($answers as $answer){
                    $letter = chr(65 + $current_option++);
                    $answer_text = strip_tags($answer->answer);
                    $answer_data[] = array($letter => $answer_text);
                }
                $questions_data[] = array(
                    'question' => $question_text,
                    'answers' => $answer_data
                );
            }
        }
        file_put_contents(
            dirname(__FILE__) . '/questions.json',
            json_encode(array('quiz' => $questions_data), JSON_UNESCAPED_UNICODE)
        );
        $this->content = new stdClass;
        $ttsAppURL = $CFG->wwwroot . '/blocks/soundlab/';
        $this->content->text = '<h5>Volumen</h5>
        <input type="range" id="volume" class="control-volume" min="0" max="100" value="75" step="1" data-action="volume" />
        <h5>Velocidad</h5>
        <input type="range" id="velocity" class="control-velocity" min="-10" max="10" value="2" step="1" data-action="velocity" />';
        $provider = new VoiceRssProvider("8a35f597a70b42a8a86ba737d2a1ee2a", "es-mx", (int) $CFG->SoundLabVelocidad); #Esta velocidad esta rara revisar
        $tts = new TextToSpeech($questions_data[0]['question'], $provider);
        $tts->save("/var/www/html/moodle/blocks/soundlab/data.mp3", $tts->getAudioData());
        $this->content->text .= '<audio autoplay> <source src="/moodle/blocks/soundlab/data.mp3" type="audio/mpeg"> </audio>';
        /*
        $this->content->text .= '<script> window.addEventListener("load", function() {
            var pageContent = document.getElementById(\'responseform\').innerHTML;
            const parser = new DOMParser();
            const doc = parser.parseFromString(pageContent , \'text/html\');
            const bodyContents = doc.body.textContent.replace(/\n/g, " ");
            var fecha = new Date();
            fecha.setTime(fecha.getTime() + (60 * 60 * 1000)); // 1 hora
            var expiracion = "expires=" + fecha.toUTCString();
            document.cookie = "textoPaLeerGonorrea=" + bodyContents + "; expires=" + expiracion + "; path=/";
        });</script>';
        $textoPaLeer = $_COOKIE["textoPaLeerGonorrea"];
        if ($textoPaLeer != null){
        }*/
    }
}
