<?php

$functions = array(
    'local_schoolreg_getcontent' => array(// local_schoolreg_FUNCTIONNAME is the name of the web service function that the client will call.                                                                                
        'classname' => 'local_schoolreg_external', // create this class in local/schoolreg/externallib.php
        'methodname' => 'getcontent', // implement this function into the above class
        'classpath' => 'local/schoolreg/externallib.php',
        'description' => 'This documentation will be displayed in the generated API documentation 
                                          (Administration > Plugins > Webservices > API documentation)',
        'type' => 'read', // the value is 'write' if your function does any database change, otherwise it is 'read'.
    ),
    'local_schoolreg_getquery' => array(// local_schoolreg_FUNCTIONNAME is the name of the web service function that the client will call.                                                                                
        'classname' => 'local_schoolreg_external', // create this class in local/schoolreg/externallib.php
        'methodname' => 'getquery', // implement this function into the above class
        'classpath' => 'local/schoolreg/externallib.php',
        'description' => 'This documentation will be displayed in the generated API documentation 
                                          (Administration > Plugins > Webservices > API documentation)',
        'type' => 'read', // the value is 'write' if your function does any database change, otherwise it is 'read'.
    ),
);

$services = array(
    'My service' => array(
        'functions' => array('local_schoolreg_getcontent'),
        'restrictedusers' => 0,
        'enabled' => 1,
    )
);
