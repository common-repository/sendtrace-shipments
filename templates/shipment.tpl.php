<div id="sendtrace-post" class="wrap sendtrace">
    <form method="POST" action="" id="sendtrace-admin-form" class="">
        <?php wp_nonce_field('sendtrace_post_nonce_action', 'sendtrace_post_nonce_field') ?>
        <?php WPSTForm::draw_hidden('action', $action) ?>
        <div class="row">
            <div class="col-md-9 col-sm-12">
                <div class="row">
                    <!-- Title -->
                    <h3>
                        <?php 
                        echo esc_html(ucwords($action).' '.wpst_shipment_label()); 
                        if (strtolower($action) == 'edit') {
                            ?><a class="btn btn-sm btn-light my-0" href="<?php echo esc_url(admin_url("admin.php?page={$plugin_slug}-item&action=new")) ?>"><?php _e('Add New', 'sendtrace-shipments'); ?></a><?php
                        }
                        ?>                        
                    </h3>
                    <div class="col-sm-12">
                        <div class="card mt-1 mb-3 p-0 mw-100 border-0">
                            <div class="card-body  p-0">
                                <div class="form-group m-0">
                                    <input type="text" name="post_title" class="shipment-title-input form-control p-1 px-3 border-0" value="<?php echo esc_html($title); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Custom Field Details -->
                    <?php wpst_draw_form_fields($shipment_id); ?>
                </div>                
            </div>

            <!-- Status -->
            <div class="col-md-3 col-sm-12">
                <?php do_action('wpst_top_sidebar', $shipment_id) ?>
                <?php if ($action != 'view') : ?>
                    <div class="card p-0 text-right mt-5">
                        <button type="submit" class="btn btn-info m-0 p-2"><?php $action == 'new' ? _e('Save', 'sendtrace-shipments') :  _e('Update', 'sendtrace-shipments') ?></button>
                    </div>
                <?php endif; ?>
                <?php do_action('wpst_after_top_sumbit_btn', $shipment_id) ?>
                <div class="card p-0 mt-4 status-card">
                    <h5 class="h4 m-0 card-header"> <?php _e('Status', 'sendtrace-shipments') ?>: <span class="badge badge-primary bg-info font-weight-normal"><?php echo esc_html($sendtrace_status) ?></span> </h5>
                    <div class="card-body">
                        <?php
                        if (!empty($WPSTField->history_fields())) {
                            foreach ($WPSTField->history_fields($shipment_id, true) as $field) {
                                WPSTForm::gen_field($field, true);
                            }
                        }
                        ?>                        
                    </div>
                </div>
                <?php do_action('wpst_before_bottom_sumbit_btn', $shipment_id) ?>
                <?php if ($action != 'view') : ?>
                    <div class="card p-0 text-right border-0">
                        <button type="submit" class="btn btn-info m-0 p-2"><?php $action == 'new' ? _e('Save', 'sendtrace-shipments') :  _e('Update', 'sendtrace-shipments') ?></button>
                    </div>
                <?php endif; ?>
                <?php do_action('wpst_bottom_sidebar', $shipment_id) ?>
            </div>
        </div>
    </form>
</div>