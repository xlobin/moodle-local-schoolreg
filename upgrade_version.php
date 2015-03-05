<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Accept uploading files by web service token
 *
 * POST params:
 *  token => the web service user token (needed for authentication)
 *  filepath => the private file aera path (where files will be stored)
 *  [_FILES] => for example you can send the files with <input type=file>,
 *              or with curl magic: 'file_1' => '@/path/to/file', or ...
 *  filearea => 'private' or 'draft' (default = 'private'). These are the only 2 areas we are allowing
 *              direct uploads via webservices. The private file area is deprecated - please don't use it.
 *  itemid   => For draft areas this is the draftid - this can be used to add a list of files
 *              to a draft area in separate requests. If it is 0, a new draftid will be generated.
 *              For private files, this is ignored.
 *
 * @package    core_webservice
 * @copyright  2011 Dongsheng Cai <dongsheng@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * AJAX_SCRIPT - exception will be converted into JSON
 */
define('AJAX_SCRIPT', true);

/**
 * NO_MOODLE_COOKIES - we don't want any cookie
 */
define('NO_MOODLE_COOKIES', true);

require_once('../../config.php');
require_once('lib/SyncWebService.php');
echo $OUTPUT->header();

// authenticate the user
//$token = required_param('token', PARAM_ALPHANUMEXT);
$token = optional_param('token', '', PARAM_RAW_TRIMMED);
$webservicelib = new SyncWebService();
$authenticationinfo = $webservicelib->authenticate_user($token);
if (!$authenticationinfo) {
    throw new moodle_exception('invalidtoken', 'webservice');
}

// check the user can manage his own files (can upload)
$USER->id = 2;
$context = context_user::instance($USER->id);
require_capability('moodle/user:manageownfiles', $context);

$results = $DB->get_records_sql('SELECT *from {ls_upgrade_version} where version = (SELECT max(version)from {ls_upgrade_version})');

echo json_encode($results);
