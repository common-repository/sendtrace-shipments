<?php 
$current_tab = isset($_GET['tab']) ? wpst_sanitize_data($_GET['tab']) : 'general';
?>
<div id="wpst-navigation" class="wpst-navigation">
    <ul class="d-flex flex-row m-0 p-0">
        <?php if (!empty($sendtrace->settings_menu())): ?>
            <?php foreach ($sendtrace->settings_menu() as $menu_key => $menu): 
                $active_class = ($current_tab == $menu_key) ? 'active' : '';
                ?>
                <li class="m-0">
                    <span class="btn btn-lg btn-light p-2 m-1 <?php echo esc_html($active_class); ?>" data-tab="<?php echo esc_html($menu_key); ?>" data-tab_container="#<?php echo esc_html($menu_key); ?>-container"><?php echo esc_html($menu['label']); ?></span>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>