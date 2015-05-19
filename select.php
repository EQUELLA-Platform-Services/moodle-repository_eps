<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__DIR__) . '/lib.php');
$contextid = required_param('contextid', PARAM_INT);
$repositoryid = required_param('repositoryid', PARAM_INT);
$context = context::instance_by_id($contextid);
$json = required_param('json', PARAM_RAW);

require_login();
require_capability('repository/eps:view', $context);

//$repository = repository::get_repository_by_id($repositoryid, $contextid);
//$epsoptions = ($repository->get_eps_options());
//$baseurl = $epsoptions['baseurl'];
//$accesstoken = $epsoptions['access_token'];
//$eps = new eps_rest_client($baseurl, $accesstoken);

//$info = $eps->getItem($uuid, $version);

$info = json_decode($json);

$url = '';
$thumbnail = '';
if (isset($info->links)) {
    if (isset($info->links->delivery)) {
        $url = s(clean_param($info->links->delivery, PARAM_URL));
    }
}
$params = array(
    'repositoryid' => $repositoryid,
    'contextid' => $contextid,
    'url' => $info->links->thumbnail,
);

$thumburl = new moodle_url('/repository/eps/thumbnail.php', $params);
$thumbnail = $thumburl->out(false);

$filename = '';
// Use $info->filename if exists, $info->name is a display name,
// it may not have extension
if (isset($info->filename)) {
    $filename  = s(clean_param($info->filename, PARAM_FILE));
} else if (isset($info->name)) {
    $filename  = s(clean_param($info->name, PARAM_FILE));
}

$source = base64_encode($json);

$js =<<<EOD
<html>
<head>
   <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <script type="text/javascript">
    window.onload = function() {
        var resource = {};
        resource.title = "$filename";
        resource.source = "$source";
        resource.thumbnail = '$thumbnail';
        resource.author = "";
        resource.license = "";
        parent.M.core_filepicker.select_file(resource);
    }
    </script>
</head>
<body><noscript></noscript></body>
</html>
EOD;

header('Content-Type: text/html; charset=utf-8');
die($js);
