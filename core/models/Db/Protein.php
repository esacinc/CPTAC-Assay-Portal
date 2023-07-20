<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 8/6/18
 * Time: 6:07 PM
 */

namespace core\models\Db;

use Illuminate\Database\Eloquent\Model;

class Protein extends Model {

    protected $table = "protein";

    protected $primaryKey = 'protein_id';

    // Do not set updated_at timestamp.
    public $timestamps = false;

}