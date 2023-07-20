<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 8/6/18
 * Time: 6:07 PM
 */

namespace core\models\Db;

use Illuminate\Database\Eloquent\Model;

class ImportInitialStartRecords extends Model {

    protected $table = "import_initial_start_records";

    protected $primaryKey = 'import_initial_start_records_id';

    // Do not set updated_at timestamp.
    public $timestamps = false;

}