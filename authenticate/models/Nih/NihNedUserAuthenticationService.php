<?php
namespace authenticate\models\Nih;

use authenticate\models\AbstractUserAuthenticationService;
use core\models\Logging\LoggerBuilder;
use Monolog\Logger;
use Slim\Slim;
use user_account\models\AccountTypeEnum;

class NihNedUserAuthenticationService extends AbstractUserAuthenticationService
{
    public function __construct(Slim $app)
    {
        global $final_global_template_vars;

        parent::__construct(AccountTypeEnum::byValue(AccountTypeEnum::NIH_NED),
            (new LoggerBuilder("authenticate-user-nih-ned"))->addErrorLogHandler(($final_global_template_vars["is_dev"] ? Logger::DEBUG : Logger::WARNING))
                ->build(), $app);
    }

    public function authenticate(string $username, string $password): array
    {
        // TODO: implement
    }
}