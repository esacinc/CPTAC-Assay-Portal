<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 8/6/18
 * Time: 6:07 PM
 */

namespace core\models\Db;

use Illuminate\Database\Eloquent\Model;

class ValidationSampleData extends Model {

    protected $table = "panorama_validation_sample_data";

    protected $primaryKey = 'panorama_validation_sample_data_id';

    // Do not set updated_at timestamp.
    public $timestamps = false;

}