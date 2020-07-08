<!-- Content Header (Page header) -->
<section class="content-header">
    <h1><?php echo ucwords($this->name) ?> <small><?php echo ucwords($this->parent->current_action) ?></small></h1>
    <ol class="breadcrumb">
        <li>
            <a href="<?php echo $this->url_home() ?>">
                <i class="fa fa-dashboard"></i>
                <?php echo ucwords($this->name) ?>
            </a>
        </li>
        <li class="active"><?php echo ucwords($this->parent->current_action) ?></a></li>
    </ol>
</section>

<!-- Main content -->
<section class="content">
    <form method="get" role="search" class="form-inline" action="<?php echo $this->url_home() ?>">
        <?php echo $this->inputs_hidden('home'); ?>

        <?php echo $this->default_forms_fields(); ?>

        <button type="submit" class="btn btn-default">
            <span class="fa fa-search" aria-hidden="true"></span>
            Cerca
        </button>
    </form>

    <?php if ($this->data['rows']->have_posts()) :
        $metas_key = get_post_meta($this->data['rows']->posts[0]->ID);
        ?>
        <table class="table">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" class="toggle_all">
                    </th>
                    <th colspan="2"></th>
                    <th>ID</th>
                    <th>POST_TITLE</th>
                    <?php foreach ($metas_key as $key => $value) : ?>
                        <th><?php echo $key; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($this->data['rows']->have_posts()) : $this->data['rows']->the_post();
                        $post_id = get_the_ID();
                        $metas = get_post_meta($post_id);
                        $array = json_decode(trim(get_the_content()), true);
                        ?>
                    <tr>
                        <td><input type="checkbox" class="post_id" name="post_id[]" class="" value="<?php echo $post_id ?>"></td>
                        <td><a href="<?php echo $this->url_genera_pdf($post_id) ?>">Genera PDF</a></td>
                        <td><a href="<?php echo $this->url_delete_pdf($post_id) ?>" onclick="return confirm('Sei sicuro di eliminare il PDF?')">Elimina PDF</a></td>
                        <td><a href="<?php echo $this->url_send_email($post_id) ?>">Invia email</a></td>
                        <td><?php echo $post_id ?></td>
                        <td><?php echo get_the_title() ?></td>

                        <?php foreach ($metas_key as $key => $value) : ?>
                            <td><?php echo (isset($metas[$key][0]) ? $metas[$key][0] : ''); ?></td>
                        <?php endforeach; ?>

                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php
            $page_args = array(
                'base' => add_query_arg('pag', '%#%'),
                'format' => '',
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'total' => $this->data['rows']->max_num_pages,
                'current' => $this->params['pag'],
                'type' => 'array'
            );

            $pagination_array = paginate_links($page_args);
            if (is_array($pagination_array)) : ?>
            <div class="row">
                <div class="col-sm-4">Righe: <b><?php echo $this->data['rows']->found_posts ?></b></div>
                <div class="col-sm-8 text-right">
                    <ul class="fab-pagination pagination">
                        <?php foreach ($pagination_array as $page) : ?>
                            <li><?php echo $page ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            </div>

            <form method="POST" id="form_all" style="display:none">
                <input type="hidden" name="action" value="ajax_action" />
                <input type="hidden" name="<?php echo $this->parent->controller_name ?>" value="<?php echo $this->name ?>" />
                <input type="hidden" name="<?php echo $this->parent->namespace_name ?>" value="<?php echo $this->parent->NAMESPACE ?>" />
                <input type="hidden" name="<?php echo $this->parent->action_name ?>" value="ajax_update_all" />
                <input type="hidden" name="submitted" value="true" />

                <div id="post_id_hidden"></div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Salva per tutti i selezionati <span id="count_selezionati"></span>
                    </div>
                    <div class="panel-body">

                        <?php echo \fab\functions::html_select_edit('Azione', "action_sel", \fab\functions::options_array(0, array('genera_pdf' => 'Genera PDF', 'send_email' => 'Invia email', 'delete_pdf' => 'Elimina PDF',))) ?>

                    </div>
                    <div class="panel-footer">
                        <div class="ajax_message ajax_message" role="alert"></div>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <span class="fa fa-floppy-o" aria-hidden="true"></span> esegui azione multipla
                        </button>
                    </div>
                </div>
            </form>
        <?php endif ?>
    <?php else : ?>
        <div>Non ci sono righe</div>
    <?php endif; ?>
</section>


<script>
    jQuery(document).ready(function($) {

        $('form#form_all').submit(function(event) {
            event.preventDefault();
            Swal.fire({
                title: "Sei sicuro di procedere con l'azione multipla?",
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Conferma',
                cancelButtonText: 'Annulla',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    return $.fn.fab_ajax_submit(this);
                }
            });
        });

        $(".toggle_all").change(function() {
            if ($(this).prop("checked")) {
                $(".post_id").prop("checked", true).change();
            } else {
                $(".post_id").prop("checked", false).change();
            }
        });

        <?php foreach (array('id_user_assegnato', 'id_categoria') as $name) : ?>
            $('input[name="ok_<?php echo $name ?>"]').change(function() {
                if ($(this).prop("checked")) {
                    $('input[name="<?php echo $name ?>"]').prop("disabled", false);
                    $('select[name="<?php echo $name ?>"]').prop("disabled", false);
                    $('.avviso_<?php echo $name ?>').css("display", 'block');
                } else {
                    $('input[name="<?php echo $name ?>"]').prop("disabled", true);
                    $('select[name="<?php echo $name ?>"]').prop("disabled", true);
                    $('.avviso_<?php echo $name ?>').css("display", 'none');
                }
            });
        <?php endforeach; ?>

        $(".post_id").change(function() {
            var post_id_checked = [];
            $(".post_id").each(function(index) {
                if ($(this).prop("checked")) {
                    post_id_checked.push($(this).val());
                }
            });

            $('#count_selezionati').html(" (" + post_id_checked.length + ") ");
            if (post_id_checked.length > 0) {
                let html = '';
                console.log('post_id_checked', post_id_checked);
                for (let post_id in post_id_checked) {
                    html += '<input type="hidden" name="post_id[]" value="' + post_id_checked[post_id] + '">';
                }
                $('#post_id_hidden').html(html);
                $('#form_all').css("display", "block");
            } else {
                $('#post_id_hidden').html("");
                $('#form_all').css("display", "none");
            }
        });
    });
</script>