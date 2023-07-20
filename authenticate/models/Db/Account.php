<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 7/31/18
 * Time: 12:50 AM
 */

namespace authenticate\models\Db;

use Illuminate\Database\Eloquent\Model;

class Account extends Model {

    protected $table = 'account';
    protected $primaryKey = 'account_id';

    // Do not set updated_at timestamp.
    public $timestamps = false;

}
