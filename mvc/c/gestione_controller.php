<?php

namespace fabstampaunione;

if (!class_exists('fabstampaunione\gestione_controller')) {
    class gestione_controller extends \fab\fab_controller
    {
        public $name = 'gestione';
        public $models_name = array();
        public $import_dir = 'files/import';
        public $actions_width_redirect = array('save', 'delete_pdf');
        protected $current_user_can_for_view = "edit_pages";


        public function home()
        {
            if ($this->can_view()) {
                $this->_get_rows(50);
            } else {
                return false;
            }
        }

        public function default_forms_fields()
        {
            $html = '';
            $html .= \fab\functions::html_input_search('Tipo', 'fab_type', $this->params['fab_type']);
            $html .= \fab\functions::html_input_search('Per pag.', 'paging', $this->params['paging'], '', 'size="5"');
            return $html;
        }

        public function ajax_update_all()
        {
            if ($this->can_delete()) {
                if (wp_verify_nonce($_POST['security'], $this->parent->nonce)) {
                    $post_id_saved = array();
                    if (isset($_POST['post_id']) && is_array($_POST['post_id'])) {

                        foreach ($_POST['post_id'] as $post_id) {
                            if (isset($_POST['action_sel'])) {
                                switch ($_POST['action_sel']) {
                                    case 'genera_pdf':

                                        $pdf_path = $this->genera_pdf_by_id($post_id);
                                        $post_id_saved[] = $post_id;

                                        break;
                                    case 'send_email':
                                        $email = $this->send_email_by_id($post_id);
                                        if ($email) {
                                            $post_id_saved[] = $post_id;
                                        }
                                        break;
                                    case 'delete_pdf':
                                        if ($this->delete_pdf_by_id($post_id)) {
                                            $post_id_saved[] = $post_id;
                                        }
                                        break;
                                    default:
                                        echo json_encode(array("id" => false, "errors" => "Nessun azione trovata", "message" => "error"));
                                        exit;
                                        break;
                                }
                            }
                        }
                    }

                    if (count($post_id_saved) > 0) {
                        echo json_encode(array("id" => $post_id_saved, "errors" => false, "message" => "Azione eseguita con successo!"));
                    } else {
                        echo json_encode(array("id" => false, "errors" => "Nessun record aggiornato", "message" => "error"));
                    }
                } else {
                    echo json_encode(array("id" => false, "errors" => "nonce", "message" => "Error nonce"));
                }
            } else {
                echo json_encode(array("id" => false, "errors" => "no auth", "message" => "Error nonce"));
            }
            wp_die();
        }


        public function import()
        {
            if ($this->can_view()) {
                $this->data['error'] = array();
                $this->data['ok'] = array();
                $this->data['files'] = array();
                $upload_dir = wp_upload_dir();
                $base_upload_dir = $upload_dir['basedir'];
                $dir = $base_upload_dir . '/' . $this->import_dir;
                $this->data['files'] = glob($dir . "/*.*");
                if (isset($_POST['filetoimport'])) {
                    $ext = pathinfo($_POST['filetoimport'], PATHINFO_EXTENSION);
                    if ($ext == 'csv') {
                        $this->import_csv($_POST['filetoimport']);
                    } else {
                        $this->import_excel($_POST['filetoimport']);
                    }
                }
            } else {
                return false;
            }
        }

        public function import_csv($filename)
        {

            $upload_dir = wp_upload_dir();
            $base_upload_dir = $upload_dir['basedir'];
            $dest_upload_dir =  $base_upload_dir . '/' . $this->import_dir;

            $rows = \fab\functions::csv_to_array($dest_upload_dir . '/' . $filename, array(), ',');
            $this->save_import($rows);
        }

        public function import_excel($filename)
        {

            $upload_dir = wp_upload_dir();
            $base_upload_dir = $upload_dir['basedir'];
            $dest_upload_dir =  $base_upload_dir . '/' . $this->import_dir;

            $excel = \fab\functions::excel_read($dest_upload_dir  . '/' . $filename, 0, 1);
            $rows = $excel['rows'];
            $this->save_import($rows);
        }

        public function save_import($rows)
        {
            $this->data['error'] = array();
            $this->data['ok'] = array();
            if ($rows) {
                foreach ($rows as $row) {
                    if (isset($row['fab_type']) && isset($row['email']) && $row['email'] != '') {

                        $post_id = posttype::row_exists($row['email']);
                        $post_id = posttype::submit_by_type($row['fab_type'], $row['email'], json_encode($row, JSON_HEX_QUOT), $post_id);
                        if ($post_id > 0) {
                            foreach ($row as $col => $value) {
                                posttype::set_post_meta($post_id, $col, $value);
                            }
                        }

                        $this->data['ok'][] = array('post_id' => $post_id, 'row' => $row);
                    } else {
                        $this->data['error'][] = array('fab_type | email error', $row);
                    }
                }
            }
        }
        public function send_email()
        {
            if ($this->can_view()) {
                $this->data['res'] = array();
                if (isset($_GET['post_id'])) {
                    $post_id = intval($_GET['post_id']);
                    $email = $this->send_email_by_id($post_id);
                    $this->data['res'] = array(
                        'code' => 'ok',
                        'data' => $email,
                    );
                } else {
                    $this->data['res'] = array(
                        'code' => 'error',
                        'data' => false,
                    );
                }
            } else {
                return false;
            }
        }

        public function send_email_by_id($post_id)
        {
            if (get_option('fabstampaunione-email-con-allegato', '0') == '1') {
                $pdf_path = $this->pdf_full_path($post_id);
                if (!is_file($pdf_path['dir'])) {
                    $pdf_path = $this->genera_pdf_by_id($post_id);
                }
                if ($pdf_path) {
                    $email = $this->_send_email($post_id, $pdf_path['dir']);
                    return $email;
                }
            } else {
                $email = $this->_send_email($post_id);
                return $email;
            }
            return false;
        }

        private function _send_email($post_id, $allegato = array())
        {
            $this->data['post'] = get_post($post_id);
            if ($this->data['post']) {
                $metas = get_post_meta($post_id);

                if (get_option('fabstampaunione-resend', '0') == '1') {
                    // rinvia email
                } else {
                    // non rinviare email se è già stata inviata
                    if (isset($metas['inviato'][0]) and $metas['inviato'][0] > 0) {
                        date('Y-m-d H:i:s', $metas['inviato'][0]);
                        return false;
                    }
                }
                $this->data['post']->metas = $metas;
                $email = trim($this->data['post']->post_title);

                $headers = array('Content-Type: text/html; charset=UTF-8');
                $subject = $this->_replace_special(get_option('fabstampaunione-email-subject'), $this->data['post']);
                $message = $this->_replace_special(wpautop(get_option('fabstampaunione-email-message')), $this->data['post']);

                if (get_option('fabstampaunione-real-email', '0') == '0') {
                    $email = get_option('fabstampaunione-test-email', '');
                }
                if ($email != '') {
                    if (wp_mail($email, $subject, $message, $headers, $allegato)) {
                        // salva inviato
                        if (get_option('fabstampaunione-real-email', '0') == '1') {
                            posttype::set_post_meta($post_id, 'inviato', time());
                        }
                        return true;
                    }
                }
            }
            return false;
        }

        private function _replace_special($text, $post)
        {

            $text = str_replace('{button_confirm_received}', $this->button_confirm_received($post->ID, $post->post_title), $text);

            $special_values = array(
                'post_id' => $post->ID,
            );
            foreach ($special_values as $key => $value) {
                if (\is_string($value) or \is_numeric($value)) {
                    $text = str_replace('{' . $key . '}', $value, $text);
                } else {
                    echo 'ERRORE:' . $key . '' . print_r($value);
                }
            }
            foreach ($post->metas as $key => $row) {
                if (\is_string($row[0])) {
                    $text = str_replace('{' . $key . '}', $row[0], $text);
                } else {
                    echo 'ERRORE:' . $key . '' . print_r($row[0]);
                }
            }
            return $text;
        }

        public function genera_pdf()
        {
            if ($this->can_view()) {
                $this->data['res'] = array();
                if (isset($_GET['post_id'])) {
                    $post_id = intval($_GET['post_id']);
                    $pdf_path = $this->genera_pdf_by_id($post_id);
                    $this->data['res'] = array(
                        'code' => 'ok',
                        'data' => $pdf_path,
                    );
                } else {
                    $this->data['res'] = array(
                        'code' => 'error',
                        'data' => false,
                    );
                }
            } else {
                return false;
            }
        }

        public function genera_pdf_by_id($post_id)
        {
            $this->data['post'] = get_post($post_id);
            if ($this->data['post']) {
                $metas = get_post_meta($post_id);
                $this->data['post']->metas = $metas;
                $subject = $this->_replace_special(get_option('fabstampaunione-pdf-title'), $this->data['post']);
                $html = $this->_replace_special(wpautop(get_option('fabstampaunione-pdf-html')), $this->data['post']);

                //require_once(FAB_BASE_PLUGIN_DIR_PATH."vendor/TCPDF/examples/tcpdf_include.php");
                require FAB_PLUGIN_DIR_PATH . "vendor/autoload.php";
                $pdf = new \TCPDF(get_option('fabstampaunione-pdf-orientamento', 'P'), PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

                // set document information
                $pdf->SetCreator(PDF_CREATOR);
                $pdf->SetAuthor(get_bloginfo('name'));
                $pdf->SetTitle($subject);

                // remove default header/footer
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);

                // set default monospaced font
                $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

                // set margins
                $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_RIGHT);

                // set auto page breaks
                $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

                // set image scale factor
                $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

                // set font
                $pdf->SetFont('helvetica', '', get_option('fabstampaunione-pdf-font-size', 10));

                // add a page
                $pdf->AddPage();

                // print a block of text using Write()
                $pdf->writeHTML($html, true, false, false, false, '');

                // ---------------------------------------------------------

                //Close and output PDF document
                $pdf_path = $this->pdf_full_path($post_id);
                $pdf->Output($pdf_path['dir'], 'F');
                posttype::set_post_meta($post_id, 'pdf_generato', time());

                return $pdf_path;
            }
            return false;
        }

        public function url_genera_pdf($id)
        {
            $id_key = 'post_id';
            return $this->parent->url($this->name, 'genera_pdf', array($id_key => $id));
        }
        public function url_send_email($id)
        {
            $id_key = 'post_id';
            return $this->parent->url($this->name, 'send_email', array($id_key => $id));
        }
        public function url_delete_pdf($id)
        {
            $id_key = 'post_id';
            return $this->parent->url($this->name, 'delete_pdf', array($id_key => $id));
        }

        private function pdf_dir()
        {
            $base_pdf_dir = "files/fabstampaunione";
            $upload_dir = wp_upload_dir();
            $dir = join(DIRECTORY_SEPARATOR, array($upload_dir['basedir'], $base_pdf_dir));
            $url = join(DIRECTORY_SEPARATOR, array($upload_dir['baseurl'], $base_pdf_dir));
            return array(
                'dir' => $dir,
                'url' => $url
            );
        }

        private function filenamepdf($post_id)
        {
            return 'pdf-' . $post_id . '.pdf';
        }

        private function pdf_full_path($post_id, $create_dir_if_not_exist = true)
        {
            $pdf_path = $this->pdf_dir();
            if ($create_dir_if_not_exist) {
                if (wp_mkdir_p($pdf_path['dir']) === true) {
                    // cartella creata con successo
                }
            }
            //echo $dest_upload_dir;
            $pdf_full_dir_path = join(DIRECTORY_SEPARATOR, array($pdf_path['dir'], $this->filenamepdf($post_id)));
            $pdf_full_url_path = join(DIRECTORY_SEPARATOR, array($pdf_path['url'], $this->filenamepdf($post_id)));
            return array(
                'dir' => $pdf_full_dir_path,
                'url' => $pdf_full_url_path
            );
        }

        public function reset_all()
        {
            if ($this->can_view()) {
                $this->data['ret'] = array();
                if (isset($_POST['confirm_reset']) && $_POST['confirm_reset'] == '1') {
                    $this->data['ret'] = posttype::delete_all();
                }
            } else {
                return false;
            }
        }

        private function _get_rows($paging = 5)
        {
            $this->params['fab_type'] = get_option('fabstampaunione-default-type', '');
            $this->params['paging'] = $paging;
            $this->params['pag'] = 1;
            if (isset($_GET['pag']) && $_GET['pag'] != '') {
                $this->params['pag'] = $_GET['pag'];
            }

            if (isset($_GET['fab_type']))  $this->params['fab_type'] = $_GET['fab_type'];
            if (isset($_GET['paging']))  $this->params['paging'] = $_GET['paging'];
            $this->data['rows'] = posttype::rows_by_type($this->params['fab_type'], $this->params['paging'], $this->params['pag']);
        }

        public function auto_genera_pdf()
        {
            if ($this->can_view()) {
                $this->_get_rows(5);
            } else {
                return false;
            }
        }

        public function auto_invia_email()
        {
            if ($this->can_view()) {
                $this->_get_rows(5);
            } else {
                return false;
            }
        }

        public function button_confirm_received($post_id, $email)
        {
            return '<a style="text-decoration:none; display:inline-block; padding:15px; border:1px #ccc solid;" href="' . $this->parent->url('front', 'confirm_received', array('email' => $email, 'post_id' => $post_id)) . '">
            ' . get_option('fabstampaunione-button-confirm-text', 'RICEVUTO') . '
            </a>';
        }

        public function delete_pdf_by_id($post_id)
        {
            $pdf_path = $this->pdf_full_path($post_id);
            if (is_file($pdf_path['dir'])) {
                wp_delete_file($pdf_path['dir']);
                return true;
            }
            return false;
        }

        public function delete_pdf()
        {
            $url = $this->parent->url($this->name, 'home');

            if ($this->can_delete()) {
                $id_key = 'post_id';
                if (isset($_GET[$id_key])) {
                    $post_id = intval($_GET[$id_key]);
                    if ($this->delete_pdf_by_id($post_id)) {
                        $this->parent->add_notice('Eliminato con successo!', 'info');
                    } else {
                        $this->parent->add_notice('Nessun file trovato!', 'danger');
                    }
                    wp_redirect($url);
                    exit();
                }
            } else {
                // no access
                $this->parent->add_notice('Non hai permessi per la cancellazione!', 'danger');
                wp_redirect($url);
                exit();
            }
        }
    }
}
