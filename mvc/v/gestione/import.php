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
    <div class="panel panel-default">
        <div class="panel-heading text-center">
            <button type="button" class="btn btn-success btn-lg" onclick="addFile()">
                <span class="fa fa-upload"></span> Carica File da importare
            </button>
        </div>
        <div class="panel-body">
            <div id="files">
                <?php foreach ($this->data['files'] as $key => $file) : ?>
                    <div class="row fab-file-container">
                        <div class="col-xs-2 text-center">
                            <button class="btn btn-danger btn-sm" type="button" onclick="deleteFile(this)">
                                <span class="fa fa-trash" aria-hidden="true"></span>
                            </button>
                        </div>
                        <div class="col-xs-6">
                            <input type="hidden" class="file_old" name="file[<?php echo  $key ?>][old]" value="<?php echo  $file ?>">
                            <div class="fineuploader" id="<?php echo  $key ?>"></div>
                            <a href="<?php echo  \fab\functions::filedir_to_url($file) ?>" target="_blank"><?php echo  basename($file) ?></a>
                        </div>
                        <div class="col-xs-2">
                            <small class="size"><?php echo  \fab\functions::human_filesize(filesize($file)) ?></small>
                        </div>
                        <div class="col-xs-2 text-center">
                            <form method="POST">
                                <input type="hidden" name="filetoimport" value="<?php echo basename($file) ?>" />
                                <button type="submit" class="btn btn-default">
                                    Importa
                                </button>
                            </form>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h4>Nome dei campi della prima riga del file excel</h4>
            <div style="overflow:auto; height:150px; border:1px #eee solid;">
                <ul>
                    <li>fab_type (*)</li>
                    <li>email (*)</li>
                </ul>
            </div>

            <div style="color:red">
                <h3>ERRORI:</h3>
                <?php echo  \fab\functions::print_r($this->data['error']); ?>
            </div>

            <div style="color:green">
                <h3>OK:</h3>
                <?php echo  \fab\functions::print_r($this->data['ok']); ?>
            </div>

        </div>
    </div>

    <!-- TEMPLATE File -->
    <div style="display:none" id="file-predefinito">
        <div class="row fab-file-container">
            <div class="col-xs-2 text-center">
                <button class="btn btn-danger btn-sm" type="button" onclick="deleteFile(this)">
                    <span class="fa fa-trash" aria-hidden="true"></span>
                </button>
            </div>
            <div class="col-xs-6">
                <input type="hidden" class="file_old" name="file_fake" value="">
                <div class="fineuploader" id="0"></div>
                <div class="file"></div>
            </div>
            <div class="col-xs-4">
                <div class="fineuploader" id="0"></div>
            </div>
        </div>
    </div>
</section>

<script>
    var indexFile = -1;
    var urlUpload = '<?php echo $this->url_ajax_by_action('ajax_fineuploader', array('upload_dir' => $this->import_dir)); ?>';

    function deleteFile(element) {
        if (confirm('Sei sicuto di eliminare questo file?')) {
            var element = jQuery(element);

            while (!element.hasClass("fab-file-container")) {
                element = element.parent();
            }
            //console.log(element);
            var filename = element.find('.file_old').val();
            var id = element.find('.id').val();
            var controller = 'gestione';
            var action = 'ajax_delete_file';
            var data = 'action=ajax_action&<?php echo  $this->parent->namespace_name ?>=<?php echo  $this->parent->NAMESPACE ?>&<?php echo  $this->parent->controller_name ?>=' + controller + '&<?php echo  $this->parent->action_name ?>=' + action;
            data += '&security=<?php echo  wp_create_nonce($this->parent->nonce) ?>';
            data += '&filedir=' + filename;
            data += '&id_file=' + id;
            jQuery.ajax({
                url: '<?php echo  admin_url('admin-ajax.php') ?>',
                type: 'post',
                data: data,
                beforeSend: function() {
                    jQuery('#logs').css('display', 'block');
                    jQuery('#logs-text').html('Loading...');
                },
                error: function() {
                    alert("Errore di connessione");
                },
                success: function(json_string) {
                    element.remove();
                    var text = 'File eliminata con successo!';
                    jQuery('#logs').css('display', 'block');
                    jQuery('#logs-text').html(text);
                }
            });
            console.log(id, filename);
        }
    }


    function addFile() {
        jQuery('#file-predefinito .id').attr('name', 'file[' + indexFile + '][id]');
        jQuery('#file-predefinito .file').attr('id', 'file_' + indexFile);
        jQuery('#file-predefinito .file_new').attr('name', 'file[' + indexFile + '][new]');
        jQuery('#file-predefinito .file_new').attr('id', 'file_new_' + indexFile);
        jQuery('#file-predefinito .fineuploader').attr('id', indexFile);
        var html = jQuery('#file-predefinito').html();
        jQuery('#files').append(html);
        var divs = jQuery('#files .fineuploader');
        loadFineUploader(divs[divs.length - 1]);
        indexFile--;
    }

    function loadFineUploader(div) {
        var t = new Date().getTime();
        var id_div = jQuery(div).attr('id');
        //var url = urlUpload+'&filename='+t;
        var url = urlUpload;
        console.log(url);
        var uploader1 = new qq.FineUploader({
            debug: false,
            multiple: false,
            element: div,
            request: {
                endpoint: url
            },
            deleteFile: {
                enabled: false,
                endpoint: url + '&uuid='
            },
            chunking: {
                enabled: true,
                concurrent: {
                    enabled: true
                },
                success: {
                    endpoint: url + '&done'
                }
            },
            resume: {
                enabled: true
            },
            retry: {
                enableAuto: true,
                showButton: true
            },
            callbacks: {
                onSubmit: function(id, filename) {
                    console.log('onSubmit:' + filename + ' - id:' + id);
                },
                onCancel: function(id, filename) {
                    console.log('onCancel:' + filename);
                },
                onComplete: function(id, filename, response) {
                    console.log('onComplete:' + filename);
                    console.log('response:', response);
                    if (response.success == true) {
                        var t = new Date().getTime();
                        jQuery('#file_old_' + id_div).val(response.dir_path + '/' + response.uploadName);
                        jQuery('#file_' + id_div).text(response.uploadName);
                        //jQuery('#file_'+id_div).attr('src', response.folder+"/"+response.uploadName+"?"+t);
                    }
                },
                onDeleteComplete: function(id) {

                }
            }
        });
    }
</script>


<!-- Fine Uploader -->
<script type="text/template" id="qq-template">
    <div class="qq-uploader-selector qq-uploader" qq-drop-area-text="Drop files here">
    <div class="qq-total-progress-bar-container-selector qq-total-progress-bar-container">
      <div role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" class="qq-total-progress-bar-selector qq-progress-bar qq-total-progress-bar"></div>
    </div>
    <div class="qq-upload-drop-area-selector qq-upload-drop-area" qq-hide-dropzone>
      <span class="qq-upload-drop-area-text-selector"></span>
    </div>
    <div class="qq-upload-button-selector qq-upload-button">
      <div>Carica un File</div>
    </div>
    <span class="qq-drop-processing-selector qq-drop-processing">
      <span>Caricamento files...</span>
      <span class="qq-drop-processing-spinner-selector qq-drop-processing-spinner"></span>
    </span>
    <ul class="qq-upload-list-selector qq-upload-list" aria-live="polite" aria-relevant="additions removals">
      <li>
        <div class="qq-progress-bar-container-selector">
          <div role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" class="qq-progress-bar-selector qq-progress-bar"></div>
        </div>
        <span class="qq-upload-spinner-selector qq-upload-spinner"></span>
        <img class="qq-thumbnail-selector" qq-max-size="100" qq-server-scale>
        <span class="qq-upload-file-selector qq-upload-file"></span>
        <span class="qq-edit-filename-icon-selector qq-edit-filename-icon" aria-label="Edit filename"></span>
        <input class="qq-edit-filename-selector qq-edit-filename" tabindex="0" type="text">
        <span class="qq-upload-size-selector qq-upload-size"></span>
        <button type="button" class="qq-btn qq-upload-cancel-selector qq-upload-cancel">Annulla</button>
        <button type="button" class="qq-btn qq-upload-retry-selector qq-upload-retry">Riprova</button>
        <button type="button" class="qq-btn qq-upload-delete-selector qq-upload-delete">Elimina</button>
        <span role="status" class="qq-upload-status-text-selector qq-upload-status-text"></span>
      </li>
    </ul>

    <dialog class="qq-alert-dialog-selector">
      <div class="qq-dialog-message-selector"></div>
      <div class="qq-dialog-buttons">
        <button type="button" class="qq-cancel-button-selector">Close</button>
      </div>
    </dialog>

    <dialog class="qq-confirm-dialog-selector">
      <div class="qq-dialog-message-selector"></div>
      <div class="qq-dialog-buttons">
        <button type="button" class="qq-cancel-button-selector">No</button>
        <button type="button" class="qq-ok-button-selector">Yes</button>
      </div>
    </dialog>

    <dialog class="qq-prompt-dialog-selector">
      <div class="qq-dialog-message-selector"></div>
      <input type="text">
      <div class="qq-dialog-buttons">
        <button type="button" class="qq-cancel-button-selector">Cancel</button>
        <button type="button" class="qq-ok-button-selector">Ok</button>
      </div>
    </dialog>
  </div>
</script>