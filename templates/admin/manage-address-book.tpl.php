<div id="manage-address-book" class="p-1 sendtrace advanced-table">
    <input type="hidden" value="<?php echo $type ?>" id="ab_type" />
    <h3><?php esc_html_e('Manage Address Book', 'sendtrace-shipments'); ?> <a href="#" class="btn btn-sm btn-light ab-action" data-action="add"><?php esc_html_e('Add New', 'sendtrace-shipments'); ?></a></h3> 
    
    <?php if (!wpst_is_ab_premium()) : ?>
        <div class="alert-warning p-3 mb-4">Buy <strong>Address Book Addon</strong> to enjoy unlimited users.</div>
    <?php endif; ?>

    <!-- Address Book Type -->
    <div id="wpst-navigation" class="wpst-navigation mb-3">
        <ul class="d-flex flex-row m-0 p-0">
            <li class="m-0"> <a href="<?php echo $menu_slug ?>&type=shippper" class="btn btn-lg btn-light p-2 m-0 mr-2 <?php echo $type == 'shipper' ? 'active' : '' ?>"> <?php _e('Shipper', 'sendtrace-shipments') ?> </a> </li>
            <li class="m-0"> <a href="<?php echo $menu_slug ?>&type=receiver" class="btn btn-lg btn-light p-2 m-0 <?php echo $type == 'receiver' ? 'active' : '' ?>"> <?php _e('Receiver', 'sendtrace-shipments') ?> </a> </li>
        </ul>
    </div>

    <!-- Bulk options -->
    <div class="mb-2">
        <?php echo WPSTForm::gen_button('ab_bulk_delete', __('Bulk Delete', 'sendtrace-shipments'), 'button', 'wpst-update-post-status btn btn-sm btn-danger m-0', 'data-status=delete data-item_label=Address_book'); ?>
    </div>
    
    <!-- Address Books -->
    <div class="table-responsive shadow">
        <table id="address-book-list" class="table m-0 wpst-post-list">
            <thead class="bg-light">
                <tr>
                    <?php do_action('wpst_ab_list_before_head_column'); ?>
                    <?php if (!empty($tbl_columns)): ?>
                        <th class="px-2"><input class="form-check-input m-0 select-all text-center" type="checkbox" id="check-all-ab"/></th>
                        <?php foreach ($tbl_columns as $key => $label): ?>
                            <th class="px-2"><?php echo $label ?></th>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php do_action('wpst_ab_list_after_head_column'); ?>
                </tr>
            </thead>
            <tbody>
            <?php if ($address_books->have_posts()): ?>
                    <?php while ($address_books->have_posts()): 
                        $address_books->the_post(); 
                        $ab_id = get_the_ID();
                        $meta_values = $sendtrace->get_shipment_details($ab_id);
                    ?>
                    <tr class="shipment-row">
                        <?php do_action('wpst_address_book_list_before_data_column', $ab_id); ?>
                        <?php
                        if (!empty($tbl_columns)) {
                            $idx = 0;
                            foreach ($tbl_columns as $meta_key => $meta_label) {
                                $column_value = array_key_exists($meta_key, $meta_values) ? maybe_unserialize($meta_values[$meta_key]) : '';
                                if (is_array($column_value)) {
                                    $column_value = wpst_array_to_string($column_value);
                                }
                                if ($idx == 0) {
                                    echo "<td class='p-1 px-2 text-center' style='width: 20px'><input type='checkbox' class='form-check-input wpst-post-item ab-item' value='".esc_html($ab_id)."'/></td>";
                                    echo "<td class='p-1 px-2 form-control-sm'>";
                                        echo "<a class='row-title' href='#'>".esc_html($column_value)."</a>";
                                            echo "<div class='row-actions'>";
                                                echo "<span class='untrash'><a class='ab-action' href='#' data-id='".esc_html($ab_id)."' data-action='edit'>" .__('Edit', 'wpcb_calendar'). "</a> | </span>";
                                                echo "<span class='trash'><a class='ab-action' href='#' data-id='".esc_html($ab_id)."' data-action='delete'>" .__('Delete', 'wpcb_calendar'). "</a> </span>";
                                            echo "</div>";
                                        echo "</a>";
                                    echo "</td>";
                                } else {
                                    echo "<td class='p-1 px-2 form-control-sm'>".esc_html($column_value)."</td>";
                                }                                
                                $idx++;
                            }
                        }
                        ?>
                        <?php do_action('wpst_address_book_list_after_data_column', $ab_id); ?>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="p-1 px-2"><?php _e('No Address Book Found!', 'sendtrace-shipments'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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
        <div class="col-md-6 pt-3"><?php wpst_pagination(array('custom_query' => $address_books)); ?></div>
    </div>
</div>