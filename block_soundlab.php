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
        $this->content = new stdClass;
        $ttsAppURL = $CFG->wwwroot . '/blocks/soundlab/';
        $this->content->text = '<h5>Volumen</h5>
        <input type="range" id="volume" class="control-volume" min="0" max="100" value="75" step="1" data-action="volume" />
        <h5>Velocidad</h5>
        <input type="range" id="velocity" class="control-velocity" min="-10" max="10" value="2" step="1" data-action="velocity" />';
        $this->content->text .= '
            <script src="/moodle/blocks/soundlab/get_questions_from_dom.js"></script>
        ';
        $filename_questions = '/var/www/html/moodle/blocks/soundlab/questions_dom.json';
        $json_data;
        if (file_exists($filename_questions)) { // Si ya se cargo el archivo.
            $json_data = json_decode(file_get_contents($filename_questions));
        }else{// Si no se a terminado de cargar se reinicia la página.
            $this->content->text .= '
                <script>location.reload();</script>
            ';
        }
        $questions_data = array();
        $number_of_question = 1;
        foreach($json_data as $question_text){
            $sql = "SELECT * FROM {question} WHERE questiontext LIKE :questiontext";
            $params = array('questiontext' => '%' . $question_text . '%');
            $result_question = $DB->get_record_sql($sql, $params); // Tomar toda la info de la pregunta
            $formatted_question = $this->formatting_quiz(
                $question_text,
                $result_question->id,
                $result_question->qtype,
                $number_of_question++
            );
            $questions_data[] = $formatted_question;
        }
        $texto_hablar = "Pregunta #" . $questions_data[0]['number'] . "." .
        $questions_data[0]['statement'] . "." .
        "A." . $questions_data[0]["answers"][0] . "." .
        "B." . $questions_data[0]["answers"][1] . "." .
        "C." . $questions_data[0]["answers"][2] . "." .
        "D." . $questions_data[0]["answers"][3];
        /* #Esta velocidad esta rara revisar
            $provider = new VoiceRssProvider("8a35f597a70b42a8a86ba737d2a1ee2a", "es-mx", (int) $CFG->SoundLabVelocidad);
        */
        $provider = new VoiceRssProvider("8a35f597a70b42a8a86ba737d2a1ee2a", "es-mx", 0); #Porque en 0 se escucha muy bien.
        $tts = new TextToSpeech(
            $texto_hablar
            ,$provider);
        $tts->save("/var/www/html/moodle/blocks/soundlab/data.mp3", $tts->getAudioData());
        $this->content->text .= '<audio autoplay> <source src="/moodle/blocks/soundlab/data.mp3" type="audio/mpeg"> </audio>';
    }

    function formatting_quiz($statement, $id, $type, $number){
        global $DB;
        $answer;
        $answer_data = array();
        if($type == "match"){
            $options = array();
            $answers = $DB->get_records('qtype_match_subquestions', array('questionid' => $id));
            foreach($answers as $answer){
                $option_text = strip_tags($answer->questiontext);
                $answer_text = strip_tags($answer->answertext);
                $options[] = $option_text;
                $answer_data[] = $answer_text;
            }
            $answer = array('options' => $options, 'answer_options' => $answer_data);
        }else{
            $answers = $DB->get_records('question_answers', array('question' => $id));
            foreach($answers as $answer){
                $answer_text = strip_tags($answer->answer);
                $answer_data[] = $answer_text;
            }
            $answer = $answer_data;
        }
        $data = array(
            'number' => $number,
            'statement' => $statement,
            'answers' => $answer
        );
        return $data;
    }

    function refresh_content(){
        $this->get_content();
    }
}
