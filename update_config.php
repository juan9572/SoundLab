<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/adminlib.php');

$context = context_system::instance();
require_capability('moodle/site:config', $context);

// Obtenemos los datos enviados por fetch
$data = json_decode(file_get_contents('php://input'), true);
$key = $data['key'];
$value = $data['value'];

// Actualizamos la opción de configuración
set_config($key, $value);
