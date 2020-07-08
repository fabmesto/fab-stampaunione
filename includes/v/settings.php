<div class="wrap">
    <h1>Stampa unione</h1>

    <?php settings_errors(); ?>

    <form method="post" action="options.php">
        <?php settings_fields('fabstampaunione-options');
        do_settings_sections('fabstampaunione-options');
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Default type</th>
                <td>
                    <input type="text" name="fabstampaunione-default-type" value="<?php echo esc_attr(get_option('fabstampaunione-default-type', '')); ?>" style="width:100%" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Button confirm text</th>
                <td>
                    <input type="text" name="fabstampaunione-button-confirm-text" value="<?php echo esc_attr(get_option('fabstampaunione-button-confirm-text', '')); ?>" style="width:100%" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Re-Invia l'email anche se è già stata inviata</th>
                <td>
                    <select name="fabstampaunione-resend">
                        <option value="0" <?php echo (esc_attr(get_option('fabstampaunione-resend', '0')) == '0' ? 'selected' : ''); ?>>NO, invia solo 1 volta</option>
                        <option value="1" <?php echo (esc_attr(get_option('fabstampaunione-resend', '0')) == '1' ? 'selected' : ''); ?>>SI, re-invia</option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Invia email reale</th>
                <td>
                    <select name="fabstampaunione-real-email">
                        <option value="0" <?php echo (esc_attr(get_option('fabstampaunione-real-email', '0')) == '0' ? 'selected' : ''); ?>>NO</option>
                        <option value="1" <?php echo (esc_attr(get_option('fabstampaunione-real-email', '0')) == '1' ? 'selected' : ''); ?>>SI</option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Email di test</th>
                <td>
                    <input type="text" name="fabstampaunione-test-email" value="<?php echo esc_attr(get_option('fabstampaunione-test-email', '')); ?>" style="width:100%" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Email oggetto</th>
                <td>
                    <input type="text" name="fabstampaunione-email-subject" value="<?php echo esc_attr(get_option('fabstampaunione-email-subject', '')); ?>" style="width:100%" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">Email messaggio</th>
                <td><?php wp_editor(get_option('fabstampaunione-email-message', ''), 'fabstampaunione-email-message'); ?></td>
            </tr>
            <tr valign="top">
                <th scope="row">Email con allegato</th>
                <td>
                    <select name="fabstampaunione-email-con-allegato">
                        <option value="0" <?php echo (esc_attr(get_option('fabstampaunione-email-con-allegato', '0')) == '0' ? 'selected' : ''); ?>>NO</option>
                        <option value="1" <?php echo (esc_attr(get_option('fabstampaunione-email-con-allegato', '0')) == '1' ? 'selected' : ''); ?>>SI</option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">PDF orientamento <br><small>(P=>verticale L=>orizzonatale)</small></th>
                <td>
                    <input type="text" name="fabstampaunione-pdf-orientamento" value="<?php echo esc_attr(get_option('fabstampaunione-pdf-orientamento', 'P')); ?>" style="width:100%" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">PDF font-size (10)</th>
                <td>
                    <input type="text" name="fabstampaunione-pdf-font-size" value="<?php echo esc_attr(get_option('fabstampaunione-pdf-font-size', '10')); ?>" style="width:100%" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">PDF titolo</th>
                <td>
                    <input type="text" name="fabstampaunione-pdf-title" value="<?php echo esc_attr(get_option('fabstampaunione-pdf-title', '')); ?>" style="width:100%" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">PDF html</th>
                <td><?php wp_editor(get_option('fabstampaunione-pdf-html', ''), 'fabstampaunione-pdf-html'); ?></td>
            </tr>
            <tr valign="top">
                <th scope="row">Licenza</th>
                <td><input type="text" name="<?php echo $this->parent->shortcode_admin->macaddress_name ?>" value="<?php echo esc_attr(get_option($this->parent->shortcode_admin->macaddress_name)); ?>" style="width:100%" /></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>

    <div>
        Codice da comunicare a TELNET:<br />
        <input type="text" value="<?php echo $this->parent->shortcode_admin->internal_code() ?>" style="color:#999; background-color:#ccc; width:100%" />
    </div>
</div>