<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 4/4/18
 * Time: 1:56 PM
 */

namespace assays_manage\models;

use MabeEnum\Enum;

class AssayApprovalStatusEnum extends Enum {

    const ZERO = 0;
    const PUBLIC = 1;
    const AWAITING_APPROVAL = 2;
    const DELETE = 3;
    const APPROVED = 4;
    const NOT_SUBMITTED = 5;
    const WITHDRAWN = 5;

}