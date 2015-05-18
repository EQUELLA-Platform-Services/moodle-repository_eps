<?php

require_once($CFG->dirroot . '/lib/formslib.php');

class eps_search_form extends moodleform {
    private $collections = array();
    public function eps_search_form($url, $data = array()) {
        if (!empty($data['collections'])) {
            $this->collections = $data['collections'];
        }
        parent::moodleform($url);
    }
    public function definition() {
        global $CFG;
        $strrequired = get_string('required');

        $mform = $this->_form;

        $mform->addElement('text', 'searchterm', get_string('form_searchterm', 'repository_eps'));
        $mform->setType('searchterm', PARAM_NOTAGS);
        $mform->addRule('searchterm', $strrequired, 'required', null, 'client');
        $mform->setDefault('searchterm', 'item');

        $select = $mform->addElement('select', 'collections', get_string('form_collections', 'repository_eps'), $this->collections);
        $select->setMultiple(true);
        $this->add_action_buttons(false, get_string('search'));
    }
}
