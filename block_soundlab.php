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
        require_once($CFG->dirroot.'/mod/quiz/lib.php');
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
        $this->content = new stdClass;
        $ttsAppURL = $CFG->wwwroot . '/blocks/soundlab/';
        if($active == 1){
            $text = '
                <script src="/moodle/blocks/soundlab/get_questions_from_dom.js"></script>
                <script src="/moodle/blocks/soundlab/controler.js"></script>
            ';
            $this->content->text = $this->set_structure_for_plugin($volume, $speed, 'checked', $text);
            $filename_questions = '/var/www/html/moodle/blocks/soundlab/questions_dom.json';
            if (file_exists($filename_questions)) { // Si ya se cargo el archivo.
                $json_data = json_decode(file_get_contents($filename_questions));
                $questions_data = $this->get_content_from_db($json_data);
                if(!file_exists('/var/www/html/moodle/blocks/soundlab/audio/' . $quizid)) { // Si no se ha creado el contenido de los audios.
                    $this->create_folders($questions_data, $quizid);
                    $providerSlow = new VoiceRssProvider("8a35f597a70b42a8a86ba737d2a1ee2a", "es-mx", -5);
                    $providerNormal = new VoiceRssProvider("8a35f597a70b42a8a86ba737d2a1ee2a", "es-mx", 0);
                    $providerFast = new VoiceRssProvider("8a35f597a70b42a8a86ba737d2a1ee2a", "es-mx", 5);
                    $speeds = array(
                        array('speed' => $providerSlow, 'suffix' => '_s.mp3'),
                        array('speed' => $providerNormal, 'suffix' => '_n.mp3'),
                        array('speed' => $providerFast, 'suffix' => '_f.mp3')
                    );
                    $this->save_alphabet($providerNormal, $quizid);
                    $filename = "/var/www/html/moodle/blocks/soundlab/audio/". $quizid;
                    foreach ($speeds as $speed) {
                        $this->save_text_to_speach($this->get_info_quiz(), $speed['speed'], $filename, "/start" . $speed['suffix']);
                        $this->save_text_to_speach($this->get_info_plugin(), $speed['speed'], $filename, "/help" . $speed['suffix']);
                    }
                    for ($i = 0; $i < count($questions_data); $i++) {
                        $enunciado = "Pregunta número " . $questions_data[$i]['number'] . ". " .
                            $questions_data[$i]['statement'] . ". ";
                        $filename = "/var/www/html/moodle/blocks/soundlab/audio/". $quizid ."/pregunta_" . $i;
                        foreach ($speeds as $speed) {
                            $this->save_text_to_speach($enunciado, $speed['speed'], $filename, "/enunciado" . $speed['suffix']);
                        }
                        if ($questions_data[$i]['type'] == "multichoice" || $questions_data[$i]['type'] == "truefalse") {
                            $answers = $questions_data[$i]["answers"];
                            $options = range('A', 'D');
                            foreach ($speeds as $speed) {
                                foreach ($answers as $index => $answer) {
                                    $option = $options[$index] . ", " . $answer . ". ";
                                    $this->save_text_to_speach($option, $speed['speed'], $filename, "/" . $options[$index] . $speed['suffix']);
                                }
                            }
                        }else if ($questions_data[$i]['type'] == "match") {
                            $options = $questions_data[$i]["answers"]["options"];
                            $answerOptions = $questions_data[$i]["answer_options"]["options"];
                            for ($j = 0; $j < count($options); $j++) {
                                $option = chr($j + 65) . ", " . $options[$j];
                                $answer_option = $j . ", " . $answerOptions[$j];
                                foreach ($speeds as $speed) {
                                    $this->save_text_to_speach($option, $speed['speed'], $filename, "/" . chr(65 + $j) . $speed['suffix']);
                                    $this->save_text_to_speach($answer_option, $speed['speed'], $filename, "/" . $j . $speed['suffix']);
                                }
                            }
                        }
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
            $text = '
                <script src="/moodle/blocks/soundlab/controler.js"></script>
            ';
            $this->content->text = $this->set_structure_for_plugin($volume, $speed, '', $text);
        }
    }

    function get_info_quiz(){
        global $DB;
        $info = $DB->get_record('quiz', array('id' => $quizid));
        $hour = (intval($info->timelimit) / 3600);
        $start_text = "Estas realizando el cuestionario, " . $info->name .
            ", que tiene " . $number_of_question . " preguntas" .
            ", tienes " . $hour . " hora" . ($hour > 1 ? "s" : "") .
            " para finalizarlo.";
    }

    function get_info_plugin(){
        return "
        Manual de Usuario:
        . Tecla q: Reproduce la última pregunta
        . Tecla r: Reproduce la última respuesta
        . Tecla h: Avanza a la siguiente pregunta en el cuestionario
        . Tecla j: Retrocede a la pregunta anterior en el cuestionario
        . Tecla a rouh up: Navegar hacia arriba en las opciones de respuesta de una pregunta
        . Tecla a rouh down: Navegar hacia abajo en las opciones de respuesta de una pregunta
        . Tecla t: Obtener el tiempo restante para finalizar el cuestionario
        . Tecla a: Activa o desactiva el plugin
        . Tecla Alt más 1: Aumentar el volumen
        . Tecla Alt más 2: Reducir el volumen
        . Tecla Alt más 3: Aumentar la velocidad de reproducción
        . Tecla Alt más 4: Reducir la velocidad de reproducción
        . Tecla F2: Manual de usuario";
    }

    function save_alphabet($provider, $quizid) {
        $filename = "/var/www/html/moodle/blocks/soundlab/audio/". $quizid;
        $letra = 'A';
        while ($letra <= 'Z') {
            $this->save_text_to_speach($letra, $provider, $filename, "/". $letra . ".mp3");
            $letra++;
        }
    }

    function save_text_to_speach($text, $provider, $filename, $name) {
        $tts_fast = new TextToSpeech($text, $provider);
        $tts_slowed->save($filename . $name, $tts_slowed->getAudioData());     
    }

    function create_folders($questions_data, $quizid) {
        for ($i = 0; $i < count($questions_data); $i++) {
            $filename = "/var/www/html/moodle/blocks/soundlab/audio/". $quizid ."/pregunta_" . $i . "/data.mp3";
            if(!file_exists($filename)) {
                mkdir(dirname($filename), 0777, true);
            }
        }
    }

    function get_content_from_db($json_data) {
        global $DB;
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
        return $questions_data;
    }

    function set_structure_for_plugin($volume, $speed, $active, $text) {
        $structure = '
        <h5>Activo</h5>
        <label class="switch">
            <input type="checkbox" ' . $active . '>
            <span class="slider round"></span>
        </label>
        <h5>Volumen</h5>
        <span id="volume-value">' . $volumen . '</span>
        <input type="range" id="volume" class="control-volume" min="0" max="1" value="' . $volumen . '" step="0.01" data-action="volume" />
        <h5>Velocidad</h5>
        <span id="speed-value">' . $speed . '</span>
        <input type="range" id="velocity" class="control-velocity" min="-5" max="5" value="'. $speed .'" step="5" data-action="velocity" />';
        $structure .= $text;
        return $structure;
    }

    function formatting_quiz($statement, $id, $type, $number) {
        global $DB;
        $answer;
        $answer_data = array();
        if($type == "match") {
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

    function refresh_content() {
        $this->get_content();
    }
}