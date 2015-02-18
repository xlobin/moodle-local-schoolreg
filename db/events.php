<?php

$observers = array(
    array(
        'eventname' => '\core\event\course_created',
        'callback' => 'local_synchronization_event_handler::course_created',
        'includefile' => '/local/schoolreg/mylib.php'
    ),
);
