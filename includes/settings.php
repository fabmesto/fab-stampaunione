<?php

namespace fabstampaunione;

if (!class_exists('fabstampaunione\settings')) {
    class settings
    {
        var $parent = false;

        public function __construct($parent)
        {
            $this->parent = $parent;
            if (is_admin()) {
                // wp-admin actions
                add_action('admin_menu', array(&$this, 'add_admin_menu'));
                add_action('admin_init', array(&$this, 'register_settings'));
            }
        }

        public function add_admin_menu()
        {
            // add_management_page -> Strumenti
            // add_options_page -> Impostazioni
            // add_menu_page -> in ROOT
            add_menu_page(
                'Fab Stampa unione',
                'Fab Stampa unione',
                'manage_options',
                'fabstampaunione_settings',
                array(&$this, 'settings')
            );
        }

        public function settings()
        {
            ob_start();
            $action_file = FAB_PLUGIN_DIR_PATH . 'includes/v/settings.php';
            if (file_exists($action_file)) {
                require_once($action_file);
            } else {
                echo "settings: Nessuna azione trovata: " . $action_file;
            }
            echo ob_get_clean();
        }

        public function register_settings()
        { // whitelist options
            register_setting('fabstampaunione-options', $this->parent->shortcode_admin->macaddress_name);
            register_setting('fabstampaunione-options', 'fabstampaunione-default-type');
            register_setting('fabstampaunione-options', 'fabstampaunione-button-confirm-text');

            register_setting('fabstampaunione-options', 'fabstampaunione-email-subject');
            register_setting('fabstampaunione-options', 'fabstampaunione-email-message');

            register_setting('fabstampaunione-options', 'fabstampaunione-email-con-allegato');
            register_setting('fabstampaunione-options', 'fabstampaunione-pdf-title');
            register_setting('fabstampaunione-options', 'fabstampaunione-pdf-html');
            register_setting('fabstampaunione-options', 'fabstampaunione-pdf-orientamento');
            register_setting('fabstampaunione-options', 'fabstampaunione-pdf-font-size');

            register_setting('fabstampaunione-options', 'fabstampaunione-real-email');
            register_setting('fabstampaunione-options', 'fabstampaunione-test-email');
            register_setting('fabstampaunione-options', 'fabstampaunione-resend');
        }
    }
}
