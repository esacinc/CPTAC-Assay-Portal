<?php

/* Write import log activity to a text file for monitoring import activity */
function write_log($import_log_id = false, $message = false) {

    global $final_global_template_vars;

    if ($import_log_id) {
        $log_file_name = $import_log_id . '.txt';
        $folder_path = $_SERVER['DOCUMENT_ROOT'] . $final_global_template_vars['import_log_location'] . '/' . date("Y-m-d");

        // does the folder already exist?
        if (!is_dir($folder_path))
            mkdir($folder_path);
        // does the log already exist?
        $log = fopen($folder_path . '/' . $log_file_name, "a") or die("Unable to open file!");

        //chown($folder_path.'/'.$log_file_name, 'lossm');
        chmod($folder_path . '/' . $log_file_name, 0777);

        $log_entry = (date("Y-m-d g:i:sa") . ' - ' . $message . "\n");

        fwrite($log, $log_entry);
        fclose($log);
    } else {
        $log_file_name = 'debug.log';
        $folder_path = $_SERVER['DOCUMENT_ROOT'] . $final_global_template_vars['debug_log_location'] . '/' . date("Y-m-d");

        // does the folder already exist?
        if (!is_dir($folder_path))
            mkdir($folder_path);
        // does the log already exist?
        $log = fopen($folder_path . '/' . $log_file_name, "a") or die("Unable to open file!");

        //chown($folder_path.'/'.$log_file_name, 'lossm');
        chmod($folder_path . '/' . $log_file_name, 0777);

        $log_entry = (date("Y-m-d g:i:sa") . ' - ' . $message . "\n");

        fwrite($log, $log_entry);
        fclose($log);
    }
}

/* Clear import log activity */
function clear_log($import_log_id = false) {
    global $final_global_template_vars;

    if ($import_log_id) {

        $log_file_name = $import_log_id . '.txt';
        $folder_path = $_SERVER['DOCUMENT_ROOT'] . $final_global_template_vars['import_log_location'] . '/' . date("Y-m-d");

        if (!is_dir($folder_path))
            mkdir($folder_path);

        // if file already exists, archive it
        if (is_file($folder_path . '/' . $log_file_name)) {
            // rename the file adding a timestamp
            $new_file_name = date("YmdHis") . '_' . $import_log_id . '.txt';
            copy($folder_path . '/' . $log_file_name, $folder_path . '/' . $new_file_name);
            chmod($folder_path . '/' . $new_file_name, 0777);

            // delete old
            unlink($folder_path . '/' . $log_file_name);

        }

        // create new log file
        $log = fopen($folder_path . '/' . $log_file_name, "w") or die("Unable to open file!");
        chmod($folder_path . '/' . $log_file_name, 0777);

        fwrite($log, '');
        fclose($log);
    }
}