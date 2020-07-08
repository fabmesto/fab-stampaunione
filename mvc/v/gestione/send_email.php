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
    <?php echo \fab\functions::print_r($this->data['res']) ?>
</section>