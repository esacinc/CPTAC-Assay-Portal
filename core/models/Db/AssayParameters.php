<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 8/6/18
 * Time: 6:07 PM
 */

namespace core\models\Db;

use Illuminate\Database\Eloquent\Model;

class AssayParameters extends Model {

    protected $table = "assay_parameters_new";

    protected $primaryKey = 'assay_parameters_id';

    // Do not set updated_at timestamp.
    public $timestamps = false;

}