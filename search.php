<?php

require_once ('../../config.php');
require_once(dirname(__DIR__) . '/lib.php');
require_once($CFG->dirroot . '/repository/eps/eps_rest_client.php');
require_login();
$contextid = required_param('contextid', PARAM_INT);
$repositoryid = required_param('repositoryid', PARAM_INT);
$term = required_param('q', PARAM_TEXT);
$uuid = optional_param('uuid', '', PARAM_TEXT);

$context = context::instance_by_id($contextid);
$PAGE->set_context($context);
$PAGE->set_pagelayout('embedded');
$urlparams = array(
    'contextid'=>$contextid,
    'repositoryid'=>$repositoryid,
);
$pageurl = new moodle_url('/repository/eps/view.php', $urlparams);
$PAGE->set_url($pageurl);
require_capability('repository/eps:view', $context);

$repository = repository::get_repository_by_id($repositoryid, $contextid);
$epsoptions = ($repository->get_eps_options());
$baseurl = $epsoptions['baseurl'];
$accesstoken = $epsoptions['access_token'];
$eps = new eps_rest_client($baseurl, $accesstoken);

echo $OUTPUT->header();

echo $OUTPUT->heading('EPS');

$response = $eps->getFiles($uuid, $term);
$files = $response->results;
$links = array();
if(!empty($files)) {
    foreach($files as $file) {
        $attributes = array();
        if (!empty($file->description)) {
            $attributes['title'] = $file->description;
        }
        $params = array(
            'version'=>$file->version,
            'uuid'=>$file->uuid,
            'contextid'=>$contextid,
            'repositoryid'=>$repositoryid,
        );
        $url = new moodle_url('/repository/eps/view.php', $params);
        $html = html_writer::link($url, $file->name, $attributes);
        $html .= ' ';
        $selecturl =  new moodle_url('/repository/eps/select.php', $params);
        $html .= $OUTPUT->single_button($selecturl, get_string('select'));
        $links[] = $html;
    }
}
echo html_writer::alist($links);
echo $OUTPUT->footer();
