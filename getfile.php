<?php

require_once('../../config.php');

$id = optional_param('id', 0, PARAM_INT);
if (!empty($id)){
    $file = $DB->get_record('files', array('id' => $id));
    $fs = get_file_storage();
    $file = $fs->get_file_instance($file);
    send_file($file);
}