<?php

namespace authenticate\models;

use core\models\Logging\LoggerBuilder;
use Monolog\Logger;
use Slim\Slim;
use user_account\models\AccountTypeEnum;

abstract class AbstractUserAuthenticationService {
    protected $account_type;

    protected function __construct(AccountTypeEnum $account_type) {
        $this->account_type = $account_type;
    }
}