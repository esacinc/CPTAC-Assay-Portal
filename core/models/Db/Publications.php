<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 8/6/18
 * Time: 6:07 PM
 */

namespace core\models\Db;

use Illuminate\Database\Eloquent\Model;

class Publications extends Model {

    protected $table = "publications";

    protected $primaryKey = 'publications_id';

    // Do not set updated_at timestamp.
    public $timestamps = false;

}