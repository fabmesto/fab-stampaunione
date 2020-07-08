<?php

namespace fabstampaunione;

if (!class_exists('fabstampaunione\fab_install_db')) {
    class fab_install_db
    {
        public $db_version = '0.5';
        public $db_version_key = 'fabstampaunione-db-version';
        public $prefix_plugin_table = 'fabstampaunione_';
        public $query_install_db = array();
        public $query_alter_db = array();
        public $query_initialdata_db = array();

        public function prepare_install_db()
        {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            $this->query_install_db = array(
                               
            );

            $this->query_alter_db = array(
                
            );
        }

        public function install_db()
        {
            $this->prepare_install_db();

            global $wpdb;
            $installed_ver = get_option($this->db_version_key);

            if ($installed_ver != $this->db_version) {

                $this->install_roles();

                require_once ABSPATH . 'wp-admin/includes/upgrade.php';
                foreach ($this->query_install_db as $key => $query) {
                    dbDelta($query);
                }

                foreach ($this->query_alter_db as $key => $query) {
                    $wpdb->query($query);
                }

                foreach ($this->query_initialdata_db as $key => $query) {
                    $wpdb->query($query);
                }

                update_option($this->db_version_key, $this->db_version);
            }
        }

        public function install_roles()
        { }
    }
}
