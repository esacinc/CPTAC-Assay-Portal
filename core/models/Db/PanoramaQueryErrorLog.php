<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 8/6/18
 * Time: 6:07 PM
 */

namespace core\models\Db;

use Illuminate\Database\Eloquent\Model;

class PanoramaQueryErrorLog extends Model {

    protected $table = "panorama_query_error_log";

    protected $primaryKey = 'id';

    protected $fillable = [
        "import_log_id",
        "analyte_peptide_id",
        "peptide_sequence",
        "modified_peptide_sequence",
        "laboratory_name",
        "laboratory_abbreviation",
        "error_response",
        "panorama_url",
        "experiment_type",
        "data_type",
        "created_date"
    ];

    // Do not set updated_at timestamp.
    public $timestamps = false;

}