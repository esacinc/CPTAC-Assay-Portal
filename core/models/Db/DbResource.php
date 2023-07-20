<?php
namespace core\models\Db;

/**
 * @method DbStatement|void prepare(string $statement, array $driver_options = [])
 * @method DbStatement|void query(string $statement, int $fetch_mode = \PDO::ATTR_DEFAULT_FETCH_MODE, mixed | null $fetch_arg = null, array $ctor_args = [])
 */
class DbResource extends \PDO
{
    /**
     * @param \Closure|callable $callback
     * @return mixed|null
     */
    public function transactionally($callback)
    {
        $this->beginTransaction();

        try {
            $return_value = $callback();

            $this->commit();

            return $return_value;
        } catch (\Throwable $e) {
            $this->rollBack();

            throw $e;
        }
    }
}