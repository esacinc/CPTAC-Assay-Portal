<?php

namespace swpg\models;

use core\models\Db\DbResource;
use core\models\Db\DbStatement;

class db {
    const RESOURCE_OPTIONS = [
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8 COLLATE utf8_general_ci;"
    ];
    private $db;

    /**
     * @param array|bool $db_connection_params
     */
    public function __construct($db_connection_params = false) {
        if ($db_connection_params && is_array($db_connection_params)) {
            $this->db = self::db_connect($db_connection_params);
        }
    }

    /**
     * @param array $db_connection_params
     * @return DbResource|null
     */
    private static function db_connect(array $db_connection_params) {
        $email_on_connection_failure = (is_bool($db_connection_params["email_on_connection_failure"]) && $db_connection_params["email_on_connection_failure"]);
        $die_on_connection_failure = (is_bool($db_connection_params["die_on_connection_failure"]) && $db_connection_params["die_on_connection_failure"]);

        if (!empty($db_connection_params["type"]) && ($db_connection_params["type"] !== "mysql")) {
            if ($email_on_connection_failure) {
                self::send_connection_failure_email($db_connection_params, "Unknown database type: {$db_connection_params["type"]}");
            }

            if ($die_on_connection_failure) {
                die($db_connection_params["connection_error_message"]);
            }

            return null;
        }

        $dsn = "mysql:host={$db_connection_params["host"]};";

        if (!empty($db_connection_params["port"])) {
            $dsn .= "port={$db_connection_params["port"]};";
        }

        $dsn .= "dbname={$db_connection_params["name"]};charset=utf8";

        try {
            $resource = new DbResource($dsn, $db_connection_params["user"], $db_connection_params["password"], self::RESOURCE_OPTIONS);
            $resource->setAttribute(\PDO::ATTR_STATEMENT_CLASS, [
                DbStatement::class,
                []
            ]);

            return $resource;
        } catch (\Throwable $e) {
            if ($email_on_connection_failure) {
                self::send_connection_failure_email($db_connection_params, $e);
            }

            if ($die_on_connection_failure) {
                die($db_connection_params["connection_error_message"]);
            }
        }

        return null;
    }

    private static function send_connection_failure_email(array $db_connection_params, string $error_msg) {
        mail($db_connection_params["admin_emails"], "{$_SERVER["SERVER_NAME"]}: Database Connection Failure",
            ("Failed connection on: {$_SERVER["SERVER_NAME"]}\nConnection parameters: " . json_encode($db_connection_params, JSON_PRETTY_PRINT) .
                "\nError message: {$error_msg}"));
    }

    public function close_connection() {
        $this->db = null;
    }

    /**
     * @return DbResource|null
     */
    public function get_resource() {
        return $this->db;
    }
}