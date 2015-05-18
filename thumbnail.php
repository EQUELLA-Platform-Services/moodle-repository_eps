<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$repositoryid   = optional_param('repositoryid', 0, PARAM_INT);
$contextid = optional_param('contextid', SYSCONTEXTID, PARAM_INT);
$url    = optional_param('url', '', PARAM_URL);

if (isloggedin() && $repositoryid && $url
        && ($repo = repository::get_repository_by_id($repositoryid, $contextid))
        && method_exists($repo, 'send_thumbnail')) {
    $repo->send_thumbnail($url);
}

// send default icon for the file type
$fileicon = file_extension_icon($url, 64);
send_file($CFG->dirroot.'/pix/'.$fileicon.'.png', basename($fileicon).'.png');
