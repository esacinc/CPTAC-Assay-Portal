<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 2019-02-26
 * Time: 14:33
 */

namespace assays_import\models;


class ImportErrorLog {

    public function log_import_error($error_data = []) {
        $error_log = new PanoramaQueryErrorLog();

        $error_log->fill($error_data);
        $error_log->created_date = date("Y-m-d H:i:s");

        $error_log->save();
    }

}