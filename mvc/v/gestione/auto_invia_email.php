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
    <?php if ($this->data['rows']->have_posts()) : ?>
        <ul>
            <?php while ($this->data['rows']->have_posts()) : $this->data['rows']->the_post();
                    $post_id = get_the_ID();
                    $email = $this->send_email_by_id($post_id) ?>
                <li>
                    <h4><?php echo get_the_title() ?></h4>
                    <?php echo \fab\functions::print_r($email) ?>
                </li>
            <?php endwhile; ?>
        </ul>
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
        <?php endif; ?>
    <?php else : ?>
        <div>Non ci sono righe</div>
    <?php endif; ?>
</section>

<script>
    jQuery(document).ready(function($) {
        <?php if ($this->params['pag'] < $this->data['rows']->max_num_pages) : ?>
            window.location.href = '<?php echo $this->parent->url($this->name, 'auto_invia_email'); ?>?pag=<?php echo ($this->params['pag'] + 1) ?>';
        <?php endif; ?>
    });
</script>