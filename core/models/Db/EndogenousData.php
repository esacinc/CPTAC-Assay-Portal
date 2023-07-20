<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 8/6/18
 * Time: 6:07 PM
 */

namespace core\models\Db;

use Illuminate\Database\Eloquent\Model;

class EndogenousData extends Model {

    protected $table = "panorama_endogenous_data";

    protected $primaryKey = 'panorama_endogenous_data_id';

    protected $fillable = [
        'fragment_ion',
        'intra_CV',
        'inter_CV',
        'total_CV',
        'total_count',
        'peptide_sequence',
        'analyte_peptide_id',
        'laboratory_id',
        'import_log_id'
    ];

    // Do not set updated_at timestamp.
    public $timestamps = false;

}