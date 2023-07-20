<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 7/31/18
 * Time: 1:55 PM
 */

namespace authenticate\models\Db;

use Illuminate\Database\Eloquent\Model;

class User extends Model {

    protected $table = 'users';
    protected $primaryKey = 'user_id';

    protected $guarded = ['email'];

    public $timestamps = false;

}
