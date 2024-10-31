<?php
$sub_tab = isset($_GET['sub']) ? wpst_sanitize_data($_GET['sub']) : 'admin';
echo "<div class='wpst-sub-container'>";
    echo "<div id='".esc_attr($menu_key)."-container' class='shadow-lg tab-container p-2 ".(($current_tab == $menu_key) ? 'active' : '')."'>";
        echo "<div class='wpst-sub-navigation wpst-navigation mb-3 pb-2 border-bottom'>";
            echo "<div class='btn-group btn-group-toggle sub-menus' data-toggle='buttons'>";
                // Admin
                echo "<label class='btn btn-sm btn-light m-0 mr-2 ".($sub_tab == 'admin' || $current_tab != $menu_key ? 'active' : '')."'>";
                    echo "<input type='radio' name='options' id='option1' class='options' data-tab='admin' data-tab_container='#admin-email-container'/> ".__('Admin', 'sendtrace-shipments');
                echo "</label>";
                // Client
                echo "<label class='btn btn-sm btn-light m-0 ".($sub_tab == 'client' ? 'active' : '')."'>";
                    echo "<input type='radio' name='options' id='option2' class='options' data-tab='client' data-tab_container='#client-email-container'/> ".__('Client', 'sendtrace-shipments');
                echo "</label>";
            echo "</div>";
        echo "</div>";

        echo "<div class='row'>";
            echo "<div class='col-md-4 col-sm-12'>";
                $sendtrace->draw_shortcode_list();
            echo "</div>";
            echo "<div class='col-md-8 col-sm-12'>";
                echo "<div id='admin-email-container' class='wpst-sub-content ".($sub_tab == 'admin' || $current_tab != $menu_key ? 'active' : '')."'>";
                    require_once wpst_get_template('settings/email-admin.tpl', true);
                echo "</div>";
                echo "<div id='client-email-container' class='wpst-sub-content ".($sub_tab == 'client' ? 'active' : '')."'>";
                    require_once wpst_get_template('settings/email-client.tpl', true);
                echo "</div>";
            echo "</div>";
        echo "</div>";
    echo "</div>";
echo "</div>";
