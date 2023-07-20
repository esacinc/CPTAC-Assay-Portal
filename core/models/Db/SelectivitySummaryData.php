<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 8/6/18
 * Time: 6:07 PM
 */

namespace core\models\Db;

use Illuminate\Database\Eloquent\Model;

class SelectivitySummaryData extends Model {

    protected $table = "panorama_selectivity_summary_data";

    protected $primaryKey = 'selectivity_summary_data_id';

    protected $fillable = [
        'fragment_ion',
        'cell_line',
        'estimated_slope',
        'peptide_sequence',
        'analyte_peptide_id',
        'laboratory_id',
        'import_log_id'
    ];

    // Do not set updated_at timestamp.
    public $timestamps = false;

}