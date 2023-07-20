<?php

/**
 * @desc User Account Import: user account import model class
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */

namespace assays_import\models;

use \PDO;

class UserAccountImport {

    private $session_key = "";
    public $db;

    public function __construct($db_connection = false, $session_key = false) {
        if ($db_connection && is_object($db_connection)) {
            $this->db = $db_connection;
        }
        global $final_global_template_vars;
        $this->final_global_template_vars = $final_global_template_vars;
        $this->session_key = $session_key;
        $this->account_id = $_SESSION[$this->session_key]["account_id"];
        $this->user_laboratory_ids = $_SESSION[$this->session_key]["associated_groups"];
    }

    public function find_user_account_import($import_log_id = false) {
        $statement = $this->db->prepare("
                SELECT SQL_CALC_FOUND_ROWS user_account_import_id 
                FROM user_account_import
                WHERE import_log_id = :import_log_id
                  AND account_id = :account_id
            ");

        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->bindValue(":account_id", $this->account_id, PDO::PARAM_INT);

        $statement->execute();
        $statement->fetchAll(PDO::FETCH_ASSOC);

        $statement = $this->db->prepare("SELECT FOUND_ROWS()");
        $statement->execute();

        $count = $statement->fetch(PDO::FETCH_ASSOC);

        return $count["FOUND_ROWS()"];
    }

    public function add_user_account_import($import_log_id = false) {
        $data = false;

        if ($import_log_id) {
            $count = $this->find_user_account_import($import_log_id);

            if ($count == 0) {

                $statement = $this->db->prepare("
                    INSERT INTO user_account_import
                      ( import_log_id, account_id )
                    VALUES
                      ( :import_log_id, :account_id )
                ");

                $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
                $statement->bindValue(":account_id", $this->account_id, PDO::PARAM_INT);

                $data = $statement->execute();
            }
        }

        return $data;
    }

}

?>