<!-- Filters -->
<?php if($is_active_shipments): ?>
<div class="col-lg-9 col-md-8 col-sm-12">
    <form method="GET" class="row align-items-end">
        <?php WPSTForm::draw_hidden('page', $plugin_slug) ?>
        <?php WPSTForm::draw_hidden('post_per_page', $post_per_page, 'per_page'); ?>
        <div class="col-md-2 p-0 px-1">
            <?php echo WPSTForm::gen_field(array('key'=>'date_from', 'type'=>'date', 'label' => 'Date From', 'placeholder'=>'Date From', 'value'=>$date_from, 'class'=>'wpst-datepicker form-control-sm', 'group_class' => 'm-0'), true); ?>
        </div>
        <div class="col-md-2 p-0 px-1">
            <?php echo WPSTForm::gen_field(array('key'=>'date_to', 'type'=>'date', 'label' => 'Date To', 'placeholder'=>'Date To', 'value'=>$date_to, 'class'=>'wpst-datepicker form-control-sm', 'group_class' => 'm-0'), true); ?>
        </div>
        <div class="col-md-2 p-0 px-1">
            <?php WPSTForm::draw_search_field('sendtrace_status', $q_sendtrace_status, 'Shipment Status', 'form-control-sm', 'Enter Status', '', true); ?>
        </div>
        <div class="col-md-2 p-0 px-1">
            <?php WPSTForm::draw_search_field(wpst_customer_field('shipper', 'key'), $q_shipper_name, wpst_customer_field('shipper', 'label'), 'form-control-sm', wpst_customer_field('shipper', 'label'), '', true); ?>
        </div>
        <div class="col-md-2 p-0 px-1">
            <?php WPSTForm::draw_search_field(wpst_customer_field('receiver', 'key'), $q_receiver_name, wpst_customer_field('receiver', 'label'), 'form-control-sm', wpst_customer_field('receiver', 'label'), '', true); ?>
        </div>
        <div class="col-md-2 p-0 px-1">
            <button class="btn btn-sm btn-secondary m-0" type="submit" style="padding: .375rem 1rem;"><i class="fa fa-filter"></i> <?php esc_html_e('Filter', 'sendtrace-shipments') ?></button>
        </div>
    </form>
</div>
<!-- Search -->
<div class="col-lg-3 col-md-4 col-sm-12 p-0 d-flex justify-content-end align-items-end">
    <form method="GET">
        <?php 
        WPSTForm::draw_hidden('page', $plugin_slug);
        WPSTForm::draw_hidden('date_from', $date_from);
        WPSTForm::draw_hidden('date_to', $date_to);
        WPSTForm::draw_hidden('sendtrace_status', $q_sendtrace_status);
        WPSTForm::draw_hidden(wpst_customer_field('shipper', 'key'), $q_shipper_name);        
        WPSTForm::draw_hidden(wpst_customer_field('receiver', 'key'), $q_receiver_name);
        WPSTForm::draw_hidden('post_per_page', $post_per_page, 'per_page');
        ?>
        <?php  ?>
        <div class="input-group bg-white">
            <input type="text" class="form-control form-control-sm" name="q_shipment" placeholder="Search Booking" required value="<?php echo esc_html($q_shipment); ?>">
            <div class="input-group-append">
                <button class="btn btn-sm btn-secondary m-0" type="submit"><i class="fa fa-search"></i> <?php esc_html_e('Search', 'sendtrace-shipments') ?></button>
            </div>
        </div>
    </form>
</div>
<?php endif; ?>