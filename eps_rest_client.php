<?php

/**
 * EPS REST API for PHP
 *
 * @author Dongsheng Cai
 */
defined('MOODLE_INTERNAL') || die();

require_once ($CFG->libdir . '/filelib.php');
require_once (dirname(__FILE__) . '/lib.php');

class eps_rest_client {
    private $baseurl = null;
    private $accesstoken = null;
    private $http = null;

    public function __construct($baseurl, $accesstoken) {
        $this->baseurl = trim($baseurl, '/') . '/api/';
        $this->accesstoken = $accesstoken;
        $this->http = new curl(array('cache'=>true));
        $this->http->setHeader('X-Authorization: access_token=' . $this->accesstoken);
    }

    public static function get_endpoint() {
        global $CFG;
        if (empty($this->baseurl)) {
            throw new moodle_exception('eps baseurl not set');
        }
        return $url;
    }
    public function listCollections() {
        $endpoint = $this->baseurl . 'collection/';
        $json = $this->http->get($endpoint);
        return json_decode($json);
    }
    private function eps_search($params = array()) {
        $endpoint = $this->baseurl . 'search/';
        $endpoint = new moodle_url($endpoint, $params);
        $url = $endpoint->out(false);
        $json = $this->http->get($endpoint);
        return json_decode($json);
    }
    public function getFiles($uuid, $term = '') {
        $params = array('collections'=>$uuid, 'q'=>$term);
        return $this->eps_search($params);
    }
    public function getItem($uuid, $version) {
        $endpoint = $this->baseurl . 'item/' . $uuid . '/' . $version;
        $json = $this->http->get($endpoint);
        return json_decode($json);
    }

    public function download_attachment($info, $savepath) {
        $file = fopen($savepath, 'w');
        $result = $this->http->download_one($info->links->delivery, null, array('file' => $file, 'timeout' => 5, 'followlocation' => true, 'maxredirs' => 3));
        fclose($file);
    }
    public function download_from_url($url, $savepath) {
        $file = fopen($savepath, 'w');
        $result = $this->http->download_one($url, null, array('file' => $file, 'timeout' => 5, 'followlocation' => true, 'maxredirs' => 3));
        fclose($file);
    }
}
