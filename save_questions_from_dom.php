<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $jsonString = file_get_contents('php://input');
  $questions = json_decode($jsonString, true);

  if (!empty($questions)) {
    $file = fopen('questions_dom.json', 'w');
    fwrite($file, $jsonString);
    fclose($file);
  }
}
