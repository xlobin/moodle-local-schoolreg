<?php

$observers = array(
    array(
        'eventname' => '\core\event\course_created',
        'callback' => 'local_synchronization_event_handler::course_created',
        'includefile' => '/local/schoolreg/mylib.php'
    ),
    array(
        'eventname' => '\core\event\course_updated',
        'callback' => 'local_synchronization_event_handler::course_updated',
        'includefile' => '/local/schoolreg/mylib.php'
    ),
    array(
        'eventname' => '\core\event\course_deleted',
        'callback' => 'local_synchronization_event_handler::course_deleted',
        'includefile' => '/local/schoolreg/mylib.php'
    ),
);
