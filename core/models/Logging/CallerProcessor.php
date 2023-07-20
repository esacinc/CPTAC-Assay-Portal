<?php
namespace core\models\Logging;

use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;

class CallerProcessor extends IntrospectionProcessor
{
    private $level;

    public function __construct($level = Logger::INFO)
    {
        parent::__construct($level);

        $this->level = $level;
    }

    public function __invoke(array $record)
    {
        if ($record["level"] < $this->level) {
            return $record;
        }

        $record = parent::__invoke($record);
        $caller = (!empty($record["extra"]["file"]) ? (basename($record["extra"]["file"]) . ":") : ":");

        if (!empty($record["extra"]["class"])) {
            $caller .= "{$record["extra"]["class"]}:";
        }

        if (!empty($record["extra"]["function"])) {
            $caller .= "{$record["extra"]["function"]}:";
        }

        if (!empty($record["extra"]["line"])) {
            $caller .= $record["extra"]["line"];
        }

        $record["context"]["caller"] = $caller;

        return $record;
    }
}