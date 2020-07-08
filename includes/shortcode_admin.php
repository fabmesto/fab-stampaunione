<?php

namespace fabstampaunione;

require_once FAB_BASE_PLUGIN_DIR_PATH . 'fab_base/fab_shortcode_adminlte.php';

if (!class_exists('fabstampaunione\shortcode_admin')) {
    class shortcode_admin extends \fab\fab_shortcode_adminlte
    {
        public $macaddress_name = "fabstampaunione_macaddress";
        public $hide_admin_bar = false;
        public $onlyregister_user = false;

        public function load_scripts_styles()
        {
            parent::load_scripts_styles();

            // sweetalert2
            wp_register_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@9', array(), '1.0');
            wp_enqueue_script('sweetalert2');
        }
    }
}
