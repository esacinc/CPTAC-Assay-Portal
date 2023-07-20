<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 8/6/18
 * Time: 6:07 PM
 */

namespace core\models\Db;

use Illuminate\Database\Eloquent\Model;

class ValidationSampleImages extends Model {

    protected $table = "panorama_validation_sample_images";

    protected $primaryKey = 'validation_sample_images_id';

    // Do not set updated_at timestamp.
    public $timestamps = false;

}