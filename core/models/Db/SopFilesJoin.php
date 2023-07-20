<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 8/6/18
 * Time: 6:07 PM
 */

namespace core\models\Db;

use Illuminate\Database\Eloquent\Model;

class SopFilesJoin extends Model {

    protected $table = "sop_files_join";

    protected $primaryKey = 'sop_files_join_id';

    // Do not set updated_at timestamp.
    public $timestamps = false;

}