<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__DIR__) . '/lib.php');
$uuid = required_param('uuid', PARAM_TEXT);
$version = required_param('version', PARAM_TEXT);
$contextid = required_param('contextid', PARAM_INT);
$repositoryid = required_param('repositoryid', PARAM_INT);
$context = context::instance_by_id($contextid);
$PAGE->set_context($context);
$PAGE->set_pagelayout('embedded');
$urlparams = array(
    'contextid'=>$contextid,
    'repositoryid'=>$repositoryid,
);
$pageurl = new moodle_url('/repository/eps/view.php', $urlparams);

require_login();

$repository = repository::get_repository_by_id($repositoryid, $contextid);
$epsoptions = ($repository->get_eps_options());
$baseurl = $epsoptions['baseurl'];
$accesstoken = $epsoptions['access_token'];
$eps = new eps_rest_client($baseurl, $accesstoken);

$info = $eps->getItem($uuid, $version);

echo $OUTPUT->header();

echo $OUTPUT->heading($info->name, 2);
if (!empty($info->description)) {
    echo $OUTPUT->heading($info->description, 5);
}

echo html_writer::tag('pre', htmlentities($info->metadata));
echo $OUTPUT->box($info->createdDate);
echo $OUTPUT->box($info->modifiedDate);

$attachments = array();
if (!empty($info->attachments)) {
    foreach($info->attachments as $attachment) {
        var_dump($attachment->links);
        $params = array(
            'contextid'=>$contextid,
            'repositoryid'=>$repositoryid,
            'json'=>json_encode($attachment),
        );
        $html = $attachment->filename;
        $html .= ' ';
        $selecturl = new moodle_url('/repository/eps/select.php', $params);
        $html .= $OUTPUT->single_button($selecturl, get_string('select'), 'post');

        $attachments[] = $html;
    }
}
echo html_writer::alist($attachments);

echo $OUTPUT->footer();
