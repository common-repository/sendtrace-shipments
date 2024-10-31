<?php

class WPSTReport
{
    function create_report($headers, $data, $format='csv', $file_name='')
    {
        global $sendtrace;
        $formats = $sendtrace->export_file_format_list();
        $format = array_key_exists($format, $formats) ? $format : 'csv';
        $delimeter = array_key_exists($format, $formats) ? $formats[$format] : ',';
        $file_directory = WPST_PLUGIN_PATH."tmp".DIRECTORY_SEPARATOR;
        if (empty($file_name)) {
            $file_name = "sendtrace-report-".time().'.'.trim($format);
        } else {
            $file_name .= '.'.trim($format);
        }
        $file_url = WPST_PLUGIN_URL."tmp".DIRECTORY_SEPARATOR.$file_name;
        $sendtrace->clean_files('csv');
        $csv_file = fopen($file_directory.$file_name, "w");	
        

        //write utf-8 characters to file with fputcsv in php
        fprintf($csv_file, chr(0xEF).chr(0xBB).chr(0xBF));
        if (!empty($headers)) {
            fputcsv($csv_file, $headers, $delimeter);
        }
        if (!empty($data)) {
            foreach ($data as $post_id => $datas) {
                foreach ($datas as $_key => $_data) {
                    if (is_array($_data)) {
                        $datas[$_key] = implode(' | ', $_data);
                    }
                }  
                fputcsv($csv_file, $datas, $delimeter);          
            }
        }
        fclose($csv_file);
        return array('fileurl'=>$file_url, 'filename'=> $file_name);
    }
}