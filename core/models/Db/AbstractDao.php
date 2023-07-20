<?php
namespace core\models\Db;

abstract class AbstractDao
{
    protected $db;
    protected $session_key;

    /**
     * @param DbResource $db
     * @param bool|string $session_key
     */
    protected function __construct(\PDO $db, $session_key = false)
    {
        $this->db = $db;
        $this->session_key = $session_key;
    }
}