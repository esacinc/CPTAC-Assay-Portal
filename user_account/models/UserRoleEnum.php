<?php

namespace user_account\models;

use MabeEnum\Enum;

final class UserRoleEnum extends Enum {
    const APPROVER = 1;
    const IMPORTER = 2;
    const ADMIN = 3;
    const UNI_ADMIN = 4;
    const NONE = 5;
    const REVIEWER = 6;
    const METADATA_CREATOR = 7;
    const PUBLIC_UPLOAD = 8;
}