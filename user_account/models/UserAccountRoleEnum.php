<?php
namespace user_account\models;

use MabeEnum\Enum;

final class UserAccountRoleEnum extends Enum
{
    const ASSAY_APPROVER = 1;
    const ASSAY_IMPORTER = 2;
    const ADMINISTRATOR = 3;
    const UNIVERSAL_ADMINISTRATOR = 4;
    const NO_PERMISSIONS = 5;
    const ASSAY_REVIEWER = 6;
    const METADATA_CREATOR = 7;
}