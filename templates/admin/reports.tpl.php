<div id="wpst-report" class="wrap sendtrace">
    <h2><?php esc_html_e('Generate Report', 'sendtrace-shipments'); ?></h2>
    <?php WPSTForm::start(['id'=>'wpst_export_form']) ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="alert d-none">
            </div>
        </div>
        <div class="col-lg-6 col-sm-12">
            <div class="card p-1">
                <div class="card-body">
                    <?php echo WPSTForm::gen_field(['type'=>'date', 'key'=>'date_from', 'label'=>__('Date From', 'sendtrace-shipments'), 'value'=>$date_from, 'required'=>true], true); ?>
                    <?php echo WPSTForm::gen_field(['type'=>'date', 'key'=>'date_to', 'label'=>__('Date To', 'sendtrace-shipments'), 'value'=>$date_to, 'required'=>true], true); ?>
                    <?php echo WPSTForm::gen_field(['type'=>'select', 'key'=>'sendtrace_status', 'label'=>__('Status', 'sendtrace-shipments'), 'placeholder'=>'- Choose -', 'value'=>$sendtrace_status, 'options'=>$status_list, 'class'=>'mw-100'], true); ?>
                    <?php echo WPSTForm::gen_field(['type'=>'select', 'key'=>'assigned_client', 'label'=>__('Assigned Client', 'sendtrace-shipments'), 'placeholder'=>'- Choose -', 'value'=>$assigned_client, 'options'=>$client_list, 'class'=>'mw-100'], true); ?>
                    <?php WPSTForm::draw_search_field(wpst_customer_field('shipper', 'key'), $shipper_name, wpst_customer_field('shipper', 'label'), '', 'Search Shipper', '', true); ?>
                    <?php WPSTForm::gen_button('gen_report', __('Generate Report', 'sendtrace-shipments')) ?>
                </div>
            </div>
        </div>
    </div>
    <?php WPSTForm::end() ?>
</div>