<?php

function local_schoolreg_extends_navigation(navigation_node $navigation) {
    global $USER;
    
    if (empty($USER->id)){
        $navigation->add(get_string('register_school', 'local_schoolreg'), new moodle_url('/local/schoolreg/register.php'));
    }
}
