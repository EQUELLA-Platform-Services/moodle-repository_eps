<?php

/**
 * This plugin is used to access eps files
 *
 * @package    repository_eps
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->dirroot . '/repository/eps/eps_rest_client.php');

class repository_eps extends repository {

    private $baseurl = '';
    private $accesstoken = '';
    private $eps = null;
    /**
     * Constructor
     * @param int $repositoryid
     * @param object $context
     * @param array $options
     */
    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
        parent::__construct($repositoryid, $context, $options);
        if (isset($this->options['baseurl'])) {
            $this->baseurl = $this->options['baseurl'];
        }
        if (isset($this->options['access_token'])) {
            $this->accesstoken = $this->options['access_token'];
        }

        $this->eps = new eps_rest_client($this->baseurl, $this->accesstoken);
    }

    public function get_eps_options() {
        return $this->options;
    }

    /**
     * Get eps file list
     *
     * @param string $uuid
     * @return array The file list and options
     */
    public function get_listing($uuid = '', $page = '') {
        global $CFG, $OUTPUT;
        $contextid = $this->context->id;
        $params = array(
            'contextid'=>$contextid,
            'repositoryid'=>$this->id
        );
        $url = new moodle_url('/repository/eps/index.php', $params);
        $list = array();
        $list['object'] = array();
        $list['object']['type'] = 'text/html';
        $list['object']['src'] = $url->out(false);
        $list['nologin']  = true;
        $list['nosearch'] = true;
        $list['norefresh'] = true;
        return $list;
    }

    /**
     *
     * @param string $filepath
     * @param string $file The file path in moodle
     * @return array The local stored path
     */
    public function get_file($filepath, $filename = '') {
        $json = base64_decode($filepath);
        $info = json_decode($json);
        $path = $this->prepare_file($filename);
        try {
            $this->eps->download_attachment($info, $path);
        } catch (Exception $e) {
            throw new moodle_exception('errorwhilecommunicatingwith', 'repository', '', $this->get_name());
        }
        return array('path' => $path);
    }
    public function send_thumbnail($url) {
        global $CFG;
        $saveas = $this->prepare_file('');
        try {
            $this->eps->download_from_url($url, $saveas);
            $content = file_get_contents($saveas);
            unlink($saveas);
            send_file($content, basename($url), 30*24*60*60, 0, true);
        } catch (Exception $e) {}
    }

    /**
     * Return the source information
     *
     * @param stdClass $filepath
     * @return string
     */
    public function get_file_source_info($filepath) {
        return 'EPS: ' . $filepath;
    }

    public function check_login() {
        return true;
    }

    /**
     * EPS doesn't provide search
     *
     * @return bool
     */
    public function global_search() {
        return true;
    }
    /**
     * Add Instance settings input to Moodle form
     *
     * @param moodleform $mform
     */
    public static function instance_config_form($mform) {
        // base url
        $mform->addElement('text', 'baseurl', get_string('option_baseurl', 'repository_eps'));
        $mform->setType('baseurl', PARAM_URL);

        $strrequired = get_string('required');
        $mform->addRule('baseurl', $strrequired, 'required', null, 'client');

        // access token
        $mform->addElement('text', 'access_token', get_string('option_accesstoken', 'repository_eps'));
        $mform->setType('access_token', PARAM_NOTAGS);
        $mform->addRule('access_token', $strrequired, 'required', null, 'client');

        // folder view
        $mform->addElement('checkbox', 'folder_view', get_string('option_folder_view', 'repository_eps'));
        $mform->setType('folder_view', PARAM_INT);

        // collection uuid
        $mform->addElement('text', 'collection_uuid', get_string('option_collectionuuid', 'repository_eps'));
        $mform->setType('collection_uuid', PARAM_NOTAGS);
    }

    /**
     * Names of the instance settings
     *
     * @return array
     */
    public static function get_instance_option_names() {
        $rv = array('baseurl', 'collection_uuid', 'folder_view', 'access_token');

        return $rv;
    }


    /**
     * eps plugins allows links
     *
     * @return int
     */
    public function supported_returntypes() {
        return FILE_INTERNAL;
    }

    /**
     * Is this repository accessing private data?
     *
     * @return bool
     */
    public function contains_private_data() {
        return false;
    }
}
