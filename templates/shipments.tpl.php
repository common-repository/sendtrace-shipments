<div id="manage-shipments" class="p-1 sendtrace advanced-table">
    <h3><?php esc_html_e('Manage Shipments', 'sendtrace-shipments'); ?> <a class="btn btn-sm btn-light" href="<?php echo esc_url(admin_url("admin.php?page={$plugin_slug}-item&action=new")) ?>"><?php esc_html_e('Add New', 'sendtrace-shipments'); ?></a></h3>
    <div id="wpst-status-nav" class="row">
        <ul class="subsubsub col-sm-12">
            <li class="active"><a class="<?php echo $is_active_shipments ? 'current' : '' ?>" href="<?php echo esc_url(admin_url("admin.php?page={$plugin_slug}")) ?>"><?php esc_html_e('Active', 'sendtrace-shipments') ?> <span class="count">(<?php echo esc_html($active_count) ?>)</span></a></li>
            <?php if ($user_can_delete) : ?>
                | <li class="trash"><a class="<?php echo $is_active_shipments ? '' : 'current' ?>" href="<?php echo esc_url(admin_url("admin.php?page={$plugin_slug}&status=trash")) ?>"><?php esc_html_e('Trash', 'sendtrace-shipments') ?> <span class="count">(<?php echo esc_html($trash_count) ?>)</span></a></li>
            <?php endif; ?>
        </ul>
    </div>
    <div id="shipments-filter" class="row mb-2 m-0 shadow p-2">
        <?php require_once wpst_get_template('shipments-filter.tpl'); ?>
    </div>
    <!-- Bulk Options -->
    <div class="row align-items-end mb-2">
        <div class="col-md-9 col-sm-12">
            <div class="tablenav top">
                <?php if(!$is_active_shipments): ?>
                    <?php echo WPSTForm::gen_button('', 'Restore', 'button', 'bulk-update-post-status btn btn-sm btn-info m-0', 'data-status=publish'); ?>
                <?php endif; ?>
                <?php echo WPSTForm::gen_button('', $bulk_update_label, 'button', 'bulk-update-post-status btn btn-sm btn-danger m-0', $status_attr); ?>
                <?php do_action('table_nav_top'); ?>
            </div>
        </div>
        <div id="show-entries" class="col-md-3 col-sm-12">
            <form method="GET">
                <?php 
                WPSTForm::draw_hidden('page', $plugin_slug);
                WPSTForm::draw_hidden('date_from', $date_from);
                WPSTForm::draw_hidden('date_to', $date_to);
                WPSTForm::draw_hidden('sendtrace_status', $q_sendtrace_status);
                WPSTForm::draw_hidden(wpst_customer_field('shipper', 'key'), $q_shipper_name);        
                WPSTForm::draw_hidden(wpst_customer_field('receiver', 'key'), $q_receiver_name);
                ?>                
                <?php echo WPSTForm::gen_field(array('key'=>'post_per_page', 'type'=>'select', 'label'=>'Show Entries', 'label_class' => 'col-6 text-right', 'options'=>$entries_options, 'value'=>$post_per_page, 'class'=>'custom-select-sm', 'group_class'=>'form-group row justify-content-end form-inline m-0', 'required'=>true), true); ?>
                <?php if(isset($_GET['paged'])): ?>
                    <?php WPSTForm::draw_hidden('paged', sanitize_text_field($_GET['paged'])) ?>
                <?php endif; ?>
                <?php if(isset($_GET['status'])): ?>
                    <?php WPSTForm::draw_hidden('status', sanitize_text_field($_GET['status'])) ?>
                <?php endif; ?>                      
            </form>
        </div>
    </div>  

    <!-- Shipments -->
    <div class="table-responsive shadow">
        <table id="shipment-list" class="table mb-1">
            <thead class="bg-primary">
                <tr>
                    <?php do_action('wpst_shipment_list_before_head_column'); ?>
                    <?php 
                    if (!empty($WPSTField->shipment_list_columns())) {
                        foreach ($WPSTField->shipment_list_columns() as $column) {
                            echo "<th class='p-1 px-2'>".wp_kses($column['label'], wpst_allowed_html_tags())."</th>";
                        }
                    } 
                    ?>
                    <?php do_action('wpst_shipment_list_after_head_column'); ?>
                    <th width="80px" class="p-1 px-2"><?php _e('Print', 'sendtrace-shipments'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($shipments->have_posts()): ?>
                    <?php while ($shipments->have_posts()): 
                        $shipments->the_post(); 
                        $shipment_id = get_the_ID();
                        $meta_values = $sendtrace->get_shipment_details($shipment_id);

                        $read_url = admin_url("admin.php?page={$plugin_slug}-item&action=view&id={$shipment_id}");
                        $update_url = admin_url("admin.php?page={$plugin_slug}-item&action=edit&id={$shipment_id}");
                        $trash_url = admin_url("admin.php?page={$plugin_slug}-item&action=trash&id={$shipment_id}");
                        $restore_url = admin_url("admin.php?page={$plugin_slug}-item&action=untrash&id={$shipment_id}");
                        $delete_url = admin_url("admin.php?page={$plugin_slug}-item&action=delete&id={$shipment_id}");
                    ?>
                    <tr class="shipment-row">
                        <?php do_action('wpst_shipment_list_before_data_column', $shipment_id); ?>
                        <?php
                        if (!empty($WPSTField->shipment_list_columns())) {
                            foreach ($WPSTField->shipment_list_columns() as $column) {
                                $column_value = array_key_exists($column['key'], $meta_values) ? $meta_values[$column['key']] : '';
                                if (array_key_exists('type', $column) && $column['type'] == 'date') {
                                    $column_value = date(wpst_date_format(), strtotime($column_value));
                                }
                                if ($column['key'] == 'tracking_no') {
                                    echo "<td class='p-1 px-2 form-control-sm'>";
                                        echo "<a class='row-title' href='" .esc_url($read_url). "'>".esc_html($column_value)."</a>";
                                        echo "<div class='row-actions'>";
                                            if($is_active_shipments) {
                                                if ($user_can_update) {
                                                    echo "<span class='edit'><a href='" .esc_url($update_url). "' data-id='".esc_html($shipment_id)."'>".__('Edit', 'wpcb_calendar')."</a></span>";
                                                }
                                                if ($user_can_delete) {
                                                    echo " | <span class='trash'><a class='update-single-shipment' href='" .esc_url($trash_url). "' data-id='".esc_html($shipment_id)."' data-status='trash'>" .__('Trash', 'wpcb_calendar'). "</a> </span>";
                                                }
                                            } else {
                                                echo "<span class='untrash'><a class='update-single-shipment' href='" .esc_url($restore_url). "' data-id='".esc_html($shipment_id)."' data-status='publish'>" .__('Restore', 'wpcb_calendar'). "</a> | </span>";
                                                echo "<span class='trash'><a class='update-single-shipment' href='" .esc_url($delete_url). "' data-id='".esc_html($shipment_id)."' data-status='delete'>" .__('Delete Permanently', 'wpcb_calendar'). "</a> </span>";
                                            }
                                        echo "</div>";
                                        echo "</a>";
                                    echo "</td>";
                                } else if ($column['key'] == 'checkbox') {
                                    echo "<td class='p-1 px-2' style='width: 20px'><input type='checkbox' class='form-check-input shipment-item' value='".esc_html($shipment_id)."'/></td>";
                                } else {
                                    echo "<td class='p-1 px-2 form-control-sm'>".esc_html($column_value)."</td>";
                                }
                            }
                        }
                        ?>
                        <?php do_action('wpst_shipment_list_after_data_column', $shipment_id); ?>
                        <td>
                            <div class="dropdown sendtrace-print-option">
                                <button class="btn btn-info btn-sm dropdown-toggle m-0 py-1 px-2" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    <i class="fa fa-print"></i>
                                </button>
                                <ul class="dropdown-menu p-1">
                                    <li class="m-0"><a class="dropdown-item" href="#" data-type='invoice' data-id="<?php echo esc_html($shipment_id) ?>">Invoice</a></li>
                                    <li class="m-0"><a class="dropdown-item" href="#" data-type='waybill' data-id="<?php echo esc_html($shipment_id) ?>">Waybill</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="p-1 px-2"><?php _e('No Shipment Found!', 'sendtrace-shipments'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Bulk Options -->
    <div class="tablenav bottom">
        <?php do_action('wpst_before_shipments_bulk_options'); ?>
        <?php if(!$is_active_shipments): ?>
            <?php echo WPSTForm::gen_button('', 'Restore', 'button', 'bulk-update-post-status btn-sm btn-info', 'data-status=publish'); ?>
        <?php endif; ?>
        <?php echo WPSTForm::gen_button('', $bulk_update_label, 'button', 'bulk-update-post-status btn btn-sm btn-danger', $status_attr); ?>
        <?php do_action('wpst_after_shipments_bulk_options'); ?>
    </div>
    <div class="sendtrace-pagination row m-0">
        <div class="col-md-3 p-0 pt-3 text-center">
            <?php
                printf(
                    '<p class="note note-primary m-0 shadow-sm">Showing %s to %s of %s entries.</p>',
                    $record_start,
                    $record_end,
                    number_format($number_records)
                );
            ?>
        </div>
        <div class="col-md-6 pt-3"><?php wpst_pagination(array('custom_query' => $shipments)); ?></div>
    </div>
</div>