<?php

require_once ('../../config.php');
require_once(__DIR__ . '/search_form.php');
require_once(dirname(__DIR__) . '/lib.php');
require_once($CFG->dirroot . '/repository/eps/eps_rest_client.php');
require_login();
$contextid = required_param('contextid', PARAM_INT);
$repositoryid = required_param('repositoryid', PARAM_INT);

$context = context::instance_by_id($contextid);
$PAGE->set_context($context);
$PAGE->set_pagelayout('embedded');
$urlparams = array(
    'contextid'=>$contextid,
    'repositoryid'=>$repositoryid,
);
$pageurl = new moodle_url('/repository/eps/index.php', $urlparams);
$PAGE->set_url($pageurl);
require_capability('repository/eps:view', $context);

$repository = repository::get_repository_by_id($repositoryid, $contextid);
$epsoptions = ($repository->get_eps_options());
$baseurl = $epsoptions['baseurl'];
$accesstoken = $epsoptions['access_token'];
$eps = new eps_rest_client($baseurl, $accesstoken);

$response = $eps->listCollections();
$collections = $response->results;
if (!empty($collections)) {
    $data['collections'] = array();
    foreach($collections as $collection) {
        $data['collections'][$collection->uuid] = $collection->name;
    }
}

$mform = new eps_search_form($pageurl, $data);

if ($mform->is_cancelled()) {
} else if ($fromform = $mform->get_data()) {
    $urlparams = array(
        'contextid'=>$contextid,
        'repositoryid'=>$repositoryid,
        'q'=>$fromform->searchterm
    );
    if (!empty($fromform->collections)) {
        if (is_array($fromform->collections)) {
            $urlparams['uuid'] = implode($fromform->collections, ',');
        } else {
            $urlparams['uuid'] = $fromform->collections;
        }
    }
    $actionurl = new moodle_url('/repository/eps/search.php', $urlparams);
    redirect($actionurl);
} else {
    echo $OUTPUT->header();

    echo $OUTPUT->heading('EPS');

    $mform->display();
    echo $OUTPUT->footer();
}

