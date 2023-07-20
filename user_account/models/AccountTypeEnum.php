<?php

namespace user_account\models;

use MabeEnum\Enum;

final class AccountTypeEnum extends Enum {
    const NIH_NED = 1;
    const GOOGLE = 2;
    const LOCAL = 3;
}