<?php

$observers = array(
    array(
        'eventname' => '\core\event\course_created',
        'callback' => 'local_synchronization_event_handler::course_created',
        'includefile' => '/local/schoolreg/mylib.php',
        'priority'    => 9999,
    ),
    array(
        'eventname' => '\core\event\course_updated',
        'callback' => 'local_synchronization_event_handler::course_updated',
        'includefile' => '/local/schoolreg/mylib.php',
        'priority'    => 9999,
    ),
    array(
        'eventname' => '\core\event\course_deleted',
        'callback' => 'local_synchronization_event_handler::course_deleted',
        'includefile' => '/local/schoolreg/mylib.php',
        'priority'    => 9999,
    ),
    array(
        'eventname' => '*',
        'callback' => 'local_synchronization_event_handler::manager',
        'includefile' => '/local/schoolreg/mylib.php',
    ),
);
