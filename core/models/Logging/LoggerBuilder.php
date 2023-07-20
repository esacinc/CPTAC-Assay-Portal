<?php
namespace core\models\Logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerBuilder
{
    const DATE_FORMAT = "Y-m-d H:i:s O";
    const FILE_PERMS = 0640;
    const LINE_FORMAT = "[%context.caller%] %channel% %level_name% - %message%\n";
    const DATETIME_LINE_FORMAT = "%datetime% " . self::LINE_FORMAT;
    private $name;
    private $handlers = [];
    private $processors = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function build(): Logger
    {
        return new Logger($this->name, $this->handlers, $this->processors);
    }

    public function addErrorLogHandler(int $level = Logger::NOTICE): self
    {
        return $this->addHandler((new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $level))->setFormatter(self::buildLineFormatter(self::LINE_FORMAT))
            ->pushProcessor(new CallerProcessor($level)));
    }

    public function addFileHandler($file, int $level = Logger::INFO): self
    {
        return $this->addHandler((new StreamHandler($file, $level, true, self::FILE_PERMS,
            true))->setFormatter(self::buildLineFormatter(self::DATETIME_LINE_FORMAT))->pushProcessor(new CallerProcessor($level)));
    }

    private static function buildLineFormatter(string $format): LineFormatter
    {
        $formatter = new LineFormatter($format, self::DATE_FORMAT, true, true);
        $formatter->includeStacktraces();

        return $formatter;
    }

    public function addHandler(HandlerInterface $handler): self
    {
        $this->handlers[] = $handler;

        return $this;
    }

    /**
     * @return HandlerInterface[]
     */
    public function &getHandlers(): array
    {
        return $this->handlers;
    }

    public function setHandlers(HandlerInterface ... $handlers): self
    {
        $this->handlers = $handlers;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function addProcessor(callable $processor): self
    {
        $this->processors[] = $processor;

        return $this;
    }

    /**
     * @return callable[]
     */
    public function &getProcessors(): array
    {
        return $this->processors;
    }

    public function setProcessors(callable ... $processors): self
    {
        $this->processors = $processors;

        return $this;
    }
}