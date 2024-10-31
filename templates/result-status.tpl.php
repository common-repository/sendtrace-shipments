<?php
if (!empty($history_data)) {
    echo "<div id='track-result-status' class='container my-4'>";
        echo "<div class='status-data d-flex align-items-center justify-content-center'>";
            foreach ($history_data as $idx => $history) {
                if ($idx != 0) {
                    echo "<div class='line-icon'></div>";
                }
                echo "<div class='status-item text-center'>";
                    echo "<div class='circle-icon'><i class='fa fa-circle fa-2x ".($idx == count($history_data)-1 ? 'color-primary' : '')."'></i></div>";
                    echo "<div class='status-label ".($idx == count($history_data)-1 ? 'current' : '')."'>".esc_html($history['sendtrace_status'])."</div>";
                    echo "<div class='status-date ".($idx == count($history_data)-1 ? 'current' : '')."'>".esc_html(date(wpst_date_format(), strtotime(trim(str_replace(array('am','AM','pm','PM'), array('','','',''), $history['sendtrace_datetime'])))))."</div>";
                echo "</div>";
            }
        echo "</div>";
    echo "</div>";   
}