<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 8/6/18
 * Time: 6:07 PM
 */

namespace core\models\Db;

use Illuminate\Database\Eloquent\Model;

class ImportsExecutedLog extends Model {

    protected $table = "imports_executed_log";

    protected $primaryKey = 'imports_executed_log_id';

    // Do not set updated_at timestamp.
    public $timestamps = false;

}