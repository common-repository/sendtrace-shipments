<div id="wpst-track-form">
    <form method="POST" action="">
        <?php wp_nonce_field('sendtrace_trackform_action', 'sendtrace_trackform_field') ?>
        <div class="container">
            <h3 class="row h3"><?php echo esc_html(apply_filters('track_form_heading', __('Enter Shipment No.', 'sendtrace-shipments'))) ?></h3>
            <div class="row p-2 border-primary border">
                <div class="col-md-9 p-0">
                    <input type="text" name="tracking_no" class="form-control rounded-0 m-0" placeholder="<?php echo esc_html(apply_filters('track_form_placeholder', __('Ex. 1234', 'sendtrace-shipments'))) ?>" value="<?php echo esc_html($tracking_no) ?>" required/>
                </div>
                <div class="col-md-3 p-0">
                    <button class="btn bg-primary rounded-0 px-5 col-sm-12"><?php echo strtoupper(esc_html(wpst_track_button_label())) ?></button>
                </div>
            </div>
        </div>
    </form>
</div>