<?php if (current_user_can('edit_pages') == 1) : ?>
  <ul class="sidebar-menu" data-widget="tree">
    <li class="header">MAIN NAVIGATION</li>
    <li class="treeview active menu-open">
      <a href="#">
        <i class="fa fa-dashboard"></i> <span>Gestione</span>
        <span class="pull-right-container">
          <i class="fa fa-angle-left pull-right"></i>
        </span>
      </a>
      <ul class="treeview-menu">
        <li class="<?php echo $this->link_is_active('gestione') ?>">
          <a href="<?php echo $this->url('gestione') ?>">
            <i class="fa fa-address-card" aria-hidden="true"></i> Lista
          </a>
        </li>
        <li class="<?php echo $this->link_is_active('gestione', 'import') ?>">
          <a href="<?php echo $this->url('gestione', 'import') ?>">
            <i class="fa fa-upload" aria-hidden="true"></i> Import
          </a>
        </li>
        <li class="<?php echo $this->link_is_active('gestione', 'auto_genera_pdf') ?>">
          <a href="<?php echo $this->url('gestione', 'auto_genera_pdf') ?>">
            <i class="fa fa-file-pdf-o" aria-hidden="true"></i> Auto genera pdf
          </a>
        </li>
        <li class="<?php echo $this->link_is_active('gestione', 'auto_invia_email') ?>">
          <a href="<?php echo $this->url('gestione', 'auto_invia_email') ?>">
            <i class="fa fa-envelope" aria-hidden="true"></i> Auto invia email
          </a>
        </li>
        <li class="<?php echo $this->link_is_active('gestione', 'reset_all') ?>">
          <a href="<?php echo $this->url('gestione', 'reset_all') ?>">
            <i class="fa fa-trash" aria-hidden="true"></i> Reset all
          </a>
        </li>

      </ul>
    </li>
  </ul>
<?php endif; ?>