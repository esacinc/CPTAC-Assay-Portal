<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 8/6/18
 * Time: 6:07 PM
 */

namespace core\models\Db;

use Illuminate\Database\Eloquent\Model;

class EndogenousImages extends Model {

    protected $table = "panorama_endogenous_images";

    protected $primaryKey = 'panorama_endogenous_images_id';

    protected $fillable = [
        'file_name',
        'peptide_sequence',
        'analyte_peptide_id',
        'laboratory_id',
        'import_log_id',
        'active'
    ];

    // Do not set updated_at timestamp.
    public $timestamps = false;

}