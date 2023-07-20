<?php
namespace core\models\Db;

class DbStatement extends \PDOStatement
{
    private $num_positional_params = 0;

    private function __construct()
    {
    }

    public function executeAndFetchAll(int $fetch_mode = \PDO::ATTR_DEFAULT_FETCH_MODE): array
    {
        $this->execute();

        return $this->fetchAll(... func_get_args());
    }

    /**
     * @param int $fetch_mode
     * @return mixed|null
     */
    public function executeAndFetch(int $fetch_mode = \PDO::ATTR_DEFAULT_FETCH_MODE)
    {
        $this->execute();

        return $this->fetch(... func_get_args());
    }

    public function bindAll(array $params = [], array $param_types = []): self
    {
        foreach ($params as $param_key => &$param_value) {
            if (isset($param_types[$param_key])) {
                $param_type = $param_types[$param_key];
            } else if (is_string($param_key)) {
                if (SqlUtils::isParameterName($param_key)) {
                    $param_type = ($param_types[SqlUtils::toFieldName($param_key)] ?? \PDO::PARAM_STR);
                } else {
                    $param_type = ($param_types[($param_key = SqlUtils::toParameterName($param_key))] ?? \PDO::PARAM_STR);
                }
            } else {
                $param_type = ($param_types[$param_key] ?? \PDO::PARAM_STR);
                $param_key = ($this->num_positional_params + $param_key + 1);
            }

            $this->bind($param_key, $param_value, $param_type);
        }

        return $this;
    }

    public function bind($param_key, $param_value, int $param_type = \PDO::PARAM_STR): self
    {
        if (is_int($param_key)) {
            $this->bindValue($param_key, $param_value, $param_type);

            $this->num_positional_params++;
        } else {
            $this->bindValue(SqlUtils::toParameterName($param_key), $param_value, $param_type);
        }

        return $this;
    }
}