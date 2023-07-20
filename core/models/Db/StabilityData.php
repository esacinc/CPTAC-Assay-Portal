<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 8/6/18
 * Time: 6:07 PM
 */

namespace core\models\Db;

use Illuminate\Database\Eloquent\Model;

class StabilityData extends Model {

    protected $table = "panorama_stability_data";

    protected $primaryKey = 'stability_data_id';

    protected $fillable = [
        'fragment_ion',
        'control_intra_CV',
        'actual_temp_intra_CV',
        'frozen_intra_CV',
        'FTx1_intra_CV',
        'FTx2_intra_CV',
        'all_intra_CV',
        'all_inter_CV',
        'control_count',
        'control_count_light_area_0',
        'control_count_heavy_area_0',
        'actual_temp_count',
        'actual_temp_count_light_area_0',
        'actual_temp_count_heavy_area_0',
        'frozen_count',
        'frozen_count_light_area_0',
        'frozen_count_heavy_area_0',
        'FTx1_count',
        'FTx1_count_light_area_0',
        'FTx1_count_heavy_area_0',
        'FTx2_count',
        'FTx2_count_light_area_0',
        'FTx2_count_heavy_area_0',
        'peptide_sequence',
        'analyte_peptide_id',
        'laboratory_id',
        'import_log_id'
    ];

    // Do not set updated_at timestamp.
    public $timestamps = false;

}