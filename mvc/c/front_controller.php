<?php

namespace fabstampaunione;

if (!class_exists('fabstampaunione\front_controller')) {
    class front_controller extends \fab\fab_controller
    {
        public $name = 'front';
        public $models_name = array();

        public function confirm_received()
        {
            if (isset($_GET['post_id']) && isset($_GET['email'])) {
                $post_id = intval($_GET['post_id']);
                $this->data['post'] = get_post($post_id);
                if ($this->data['post']) {
                    if ($this->data['post']->post_title == $_GET['email']) {
                        // ok puoi salvare
                        posttype::set_post_meta($post_id, 'ricevuto', time());
                    }
                }
            }
        }
    }
}
