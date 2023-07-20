<?php
namespace core\models\Slim;

use Monolog\Logger;
use Slim\Log;
use Slim\LogWriter;

class LoggerLogWriter extends LogWriter
{
    private static $LEVELS = [
        Log::EMERGENCY => Logger::EMERGENCY,
        Log::ALERT => Logger::ALERT,
        Log::CRITICAL => Logger::CRITICAL,
        Log::FATAL => Logger::CRITICAL,
        Log::ERROR => Logger::ERROR,
        Log::WARN => Logger::WARNING,
        Log::NOTICE => Logger::NOTICE,
        Log::INFO => Logger::INFO,
        Log::DEBUG => Logger::DEBUG
    ];
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function write($msg, $level = null)
    {
        return ($this->logger->log(self::$LEVELS[$level], ((string)$msg)) ? 1 : false);
    }
}