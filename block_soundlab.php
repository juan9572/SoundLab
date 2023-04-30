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

    function get_content() {
        global $CFG, $COURSE, $PAGE, $DB;
        //Get global/admin tts configs
        $PAGE->requires->css('/blocks/soundlab/styles.css');
        require_once('settings_base.php');
        $volumen = volumeSelection()[$CFG->SoundLabVolumen];
        $speed = speedSelection()[$CFG->SoundLabVelocidad];
        $active = activeSelection()[$CFG->SoundLabActive];
        $course = $this->page->course;
        //Replace get_context_instance by the class for moodle 2.6+
        $quizid = $PAGE->cm->instance;
        if(class_exists('context_module'))
        {
            $context = context_course::instance($course->id);
        }
        else
        {
            $context = get_context_instance(CONTEXT_COURSE, $course->id);
        }
        if($active == 1){
            $this->content = new stdClass;
            $ttsAppURL = $CFG->wwwroot . '/blocks/soundlab/';
            $this->content->text = '
            <h5>Activo</h5>
            <label class="switch">
                <input type="checkbox" checked>
                <span class="slider round"></span>
            </label>
            <h5>Volumen</h5>
            <span id="volume-value">' . $volumen . '</span>
            <input type="range" id="volume" class="control-volume" min="0" max="1" value="' . $volumen . '" step="0.01" data-action="volume" />
            <h5>Velocidad</h5>
            <span id="speed-value">' . $speed . '</span>
            <input type="range" id="velocity" class="control-velocity" min="-5" max="5" value="'. $speed .'" step="5" data-action="velocity" />';
            $this->content->text .= '
                <script src="/moodle/blocks/soundlab/get_questions_from_dom.js"></script>
                <script src="/moodle/blocks/soundlab/controler.js"></script>
            ';
            $filename_questions = '/var/www/html/moodle/blocks/soundlab/questions_dom.json';
            if (file_exists($filename_questions)) { // Si ya se cargo el archivo.
                $json_data = json_decode(file_get_contents($filename_questions));
                $questions_data = array();
                $number_of_question = 1;
                foreach($json_data as $question_text) {
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
                if(!file_exists('/var/www/html/moodle/blocks/soundlab/audio/' . $quizid)) { // Si no se ha creado el contenido de los audios.
                    for ($i = 0; $i < count($questions_data); $i++) {
                        $filename = "/var/www/html/moodle/blocks/soundlab/audio/". $quizid ."/pregunta_" . $i . "/data.mp3";
                        if(!file_exists($filename)){
                            mkdir(dirname($filename), 0777, true);
                        }
                    }
                    $providerSlow = new VoiceRssProvider("8a35f597a70b42a8a86ba737d2a1ee2a", "es-mx", -5);
                    $providerNormal = new VoiceRssProvider("8a35f597a70b42a8a86ba737d2a1ee2a", "es-mx", 0);
                    $providerFast = new VoiceRssProvider("8a35f597a70b42a8a86ba737d2a1ee2a", "es-mx", 5);
                    $info = $DB->get_record('quiz', array('id' => $quizid));
                    $hour = (intval($info->timelimit) / 3600);
                    $start_text = "Estas realizando el cuestionario, " . $info->name . ", tienes " . $hour . " hora" . ($hour > 1 ? "s" : "") . " para finalizarlo.";
                    $tts_slowed = new TextToSpeech($start_text, $providerSlow);
                    $tts_normal = new TextToSpeech($start_text, $providerNormal);
                    $tts_fast = new TextToSpeech($start_text, $providerFast);
                    $filename = "/var/www/html/moodle/blocks/soundlab/audio/". $quizid;
                    $tts_slowed->save($filename . "/start_s.mp3", $tts_slowed->getAudioData());
                    $tts_normal->save($filename . "/start_n.mp3", $tts_normal->getAudioData());
                    $tts_fast->save($filename . "/start_f.mp3", $tts_fast->getAudioData());
                    for ($i = 0; $i < count($questions_data); $i++) {
                        $enunciado = "Pregunta número " . $questions_data[$i]['number'] . ". " .
                            $questions_data[$i]['statement'] . ". ";
                        $texto_hablar = $enunciado;
                        $tts_slowed = new TextToSpeech($enunciado, $providerSlow);
                        $tts_normal = new TextToSpeech($enunciado, $providerNormal);
                        $tts_fast = new TextToSpeech($enunciado, $providerFast);
                        $filename = "/var/www/html/moodle/blocks/soundlab/audio/". $quizid ."/pregunta_" . $i;
                        $tts_slowed->save($filename . "/enunciado_s.mp3", $tts_slowed->getAudioData());
                        $tts_normal->save($filename . "/enunciado_n.mp3", $tts_normal->getAudioData());
                        $tts_fast->save($filename . "/enunciado_f.mp3", $tts_fast->getAudioData());
                        if ($questions_data[$i]['type'] == "multichoice") {
                            $a = "A, " . $questions_data[$i]["answers"][0] . ". ";
                            $tts_slowed = new TextToSpeech($a, $providerSlow);
                            $tts_normal = new TextToSpeech($a, $providerNormal);
                            $tts_fast = new TextToSpeech($a, $providerFast);
                            $tts_slowed->save($filename . "/a_s.mp3", $tts_slowed->getAudioData());
                            $tts_normal->save($filename . "/a_n.mp3", $tts_normal->getAudioData());
                            $tts_fast->save($filename . "/a_f.mp3", $tts_fast->getAudioData());

                            $b = "B, " . $questions_data[$i]["answers"][1] . ". ";
                            $tts_slowed = new TextToSpeech($b, $providerSlow);
                            $tts_normal = new TextToSpeech($b, $providerNormal);
                            $tts_fast = new TextToSpeech($b, $providerFast);
                            $tts_slowed->save($filename . "/b_s.mp3", $tts_slowed->getAudioData());
                            $tts_normal->save($filename . "/b_n.mp3", $tts_normal->getAudioData());
                            $tts_fast->save($filename . "/b_f.mp3", $tts_fast->getAudioData());

                            $c = "C, " . $questions_data[$i]["answers"][2] . ". ";
                            $tts_slowed = new TextToSpeech($c, $providerSlow);
                            $tts_normal = new TextToSpeech($c, $providerNormal);
                            $tts_fast = new TextToSpeech($c, $providerFast);
                            $tts_slowed->save($filename . "/c_s.mp3", $tts_slowed->getAudioData());
                            $tts_normal->save($filename . "/c_n.mp3", $tts_normal->getAudioData());
                            $tts_fast->save($filename . "/c_f.mp3", $tts_fast->getAudioData());

                            $d = "D, " . $questions_data[$i]["answers"][3] . ".";
                            $tts_slowed = new TextToSpeech($d, $providerSlow);
                            $tts_normal = new TextToSpeech($d, $providerNormal);
                            $tts_fast = new TextToSpeech($d, $providerFast);
                            $tts_slowed->save($filename . "/d_s.mp3", $tts_slowed->getAudioData());
                            $tts_normal->save($filename . "/d_n.mp3", $tts_normal->getAudioData());
                            $tts_fast->save($filename . "/d_f.mp3", $tts_fast->getAudioData());

                            $texto_hablar .= $a . $b . $c . $d;
                        }else if ($questions_data[$i]['type'] == "truefalse") {                            
                            $a = "A, " . $questions_data[$i]["answers"][0] . ". ";
                            $tts_slowed = new TextToSpeech($a, $providerSlow);
                            $tts_normal = new TextToSpeech($a, $providerNormal);
                            $tts_fast = new TextToSpeech($a, $providerFast);
                            $tts_slowed->save($filename . "/a_s.mp3", $tts_slowed->getAudioData());
                            $tts_normal->save($filename . "/a_n.mp3", $tts_normal->getAudioData());
                            $tts_fast->save($filename . "/a_f.mp3", $tts_fast->getAudioData());

                            $b = "B, " . $questions_data[$i]["answers"][1] . ". ";
                            $tts_slowed = new TextToSpeech($b, $providerSlow);
                            $tts_normal = new TextToSpeech($b, $providerNormal);
                            $tts_fast = new TextToSpeech($b, $providerFast);
                            $tts_slowed->save($filename . "/b_s.mp3", $tts_slowed->getAudioData());
                            $tts_normal->save($filename . "/b_n.mp3", $tts_normal->getAudioData());
                            $tts_fast->save($filename . "/b_f.mp3", $tts_fast->getAudioData());

                            $texto_hablar .= $a . $b;
                        }else if ($questions_data[$i]['type'] == "match") {
                            $options = "";
                            $answers_options = "";
                            for($j = 0; $j < count($questions_data[$i]["answers"]["options"]); $j++){
                                $option = chr($j + 65) . ", " . $questions_data[$i]["answers"]["options"][$j];
                                $answer_option = $j . ", " . $questions_data[$i]["answer_options"]["options"][$j];
                                $tts_slowed = new TextToSpeech($option, $providerSlow);
                                $tts_normal = new TextToSpeech($option, $providerNormal);
                                $tts_fast = new TextToSpeech($option, $providerFast);
                                $tts_slowed->save($filename . "/". chr(65 + $j) ."_s.mp3", $tts_slowed->getAudioData());
                                $tts_normal->save($filename . "/". chr(65 + $j) ."_n.mp3", $tts_normal->getAudioData());
                                $tts_fast->save($filename . "/". chr(65 + $j) ."_f.mp3", $tts_fast->getAudioData());
                                $tts_slowed = new TextToSpeech($answer_option, $providerSlow);
                                $tts_normal = new TextToSpeech($answer_option, $providerNormal);
                                $tts_fast = new TextToSpeech($answer_option, $providerFast);
                                $tts_slowed->save($filename . "/". $j ."_s.mp3", $tts_slowed->getAudioData());
                                $tts_normal->save($filename . "/". $j ."_n.mp3", $tts_normal->getAudioData());
                                $tts_fast->save($filename . "/". $j ."_f.mp3", $tts_fast->getAudioData());
                                $options .= $option;
                                $answer_options .= $answer_option;
                            }
                            $texto_hablar .= $options . $answers_options;
                        }
                        $tts_slowed = new TextToSpeech($texto_hablar, $providerSlow);
                        $tts_normal = new TextToSpeech($texto_hablar, $providerNormal);
                        $tts_fast = new TextToSpeech($texto_hablar, $providerFast);
                        $tts_slowed->save($filename . "/total_s.mp3", $tts_slowed->getAudioData());
                        $tts_normal->save($filename . "/total_n.mp3", $tts_normal->getAudioData());
                        $tts_fast->save($filename . "/total_f.mp3", $tts_fast->getAudioData());
                    }
                }
                $data = htmlspecialchars(json_encode($questions_data), ENT_QUOTES, 'UTF-8');
                $this->content->text .= '<audio id="player"></audio>';
                $this->content->text .= '<script src="/moodle/blocks/soundlab/player.js" data-quiz_id="' .
                    $quizid . '" data-questions="' . $data . '"></script>';
            }else{// Si no se ha terminado de cargar se reinicia la página.
                $this->content->text .= '
                <script>
                    setTimeout(() => {
                        location.reload();
                    }, 300);
                </script>';
            }
        }else{
            $this->content = new stdClass;
            $ttsAppURL = $CFG->wwwroot . '/blocks/soundlab/';
            $this->content->text = '
            <h5>Activo</h5>
            <label class="switch">
                <input type="checkbox">
                <span class="slider round"></span>
            </label>
            <h5>Volumen</h5>
            <span id="volume-value">' . $volumen . '</span>
            <input type="range" id="volume" class="control-volume" min="0" max="1" value="' . $volumen . '" step="0.01" data-action="volume" />
            <h5>Velocidad</h5>
            <span id="speed-value">' . $speed . '</span>
            <input type="range" id="velocity" class="control-velocity" min="-5" max="5" value="'. $speed .'" step="5" data-action="velocity" />';
            $this->content->text .= '
                <script src="/moodle/blocks/soundlab/controler.js"></script>
            ';
        }
    }

    function formatting_quiz($statement, $id, $type, $number) {
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
            'answers' => $answer,
            'type' => $type
        );
        return $data;
    }

    function refresh_content(){
        $this->get_content();
    }
}