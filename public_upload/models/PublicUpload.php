<?php

namespace public_upload\models;

use \PDO;

class PublicUpload {
    private $session_key = "";
    public $db;

    const UPLOAD_FILE_DATA_TYPES = [
        "import_log_id" => PDO::PARAM_INT,
        "experiment_type" => PDO::PARAM_STR,
        "file_name" => PDO::PARAM_STR,
    ];

    public function __construct($db_connection = false, $session_key = false) {
        if ($db_connection && is_object($db_connection)) {
            $this->db = $db_connection;
        }
        global $final_global_template_vars;
        $this->final_global_template_vars = $final_global_template_vars;
        $this->session_key = $session_key;
        $this->user_group_id = $_SESSION[$this->session_key]["associated_groups"][0];
    }

    public function get_submission_id($account_id = false) {
        $sql = "select submission_id from import_log where submission_id != 'NULL' and imported_by_user_id = :account_id order by last_modified DESC limit 1";
        $statement = $this->db->prepare($sql);
        $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);

        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    public function get_import_count()
    {
        $sql = "SELECT import_log_id from import_log";
        $statement = $this->db->prepare($sql);

        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }


    public function get_account_details($account_id = false) {
        $sql = "select DATE_FORMAT(NOW(), '%y') as submission_year, account_id, given_name, sn, email from account where account_id = :account_id";
        $statement = $this->db->prepare($sql);
        $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);

        $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        return $data;
    }

    public function get_user_import_count($account_id = false) {
        $sql = "SELECT submission_id from import_log where imported_by_user_id = :account_id and submission_id != 'NULL'";
        $statement = $this->db->prepare($sql);
        $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);

        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function update_import_submission_id($import_log_id = false, $submission_id = false) {
        $sql = "UPDATE import_log set submission_id = :submission_id where import_log_id = :import_log_id";
        $statement = $this->db->prepare($sql);
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->bindValue(":submission_id", $submission_id, PDO::PARAM_INT);

        $statement->execute();
    }


    public function get_peptide_standard_purity_types() {
        $sql = "SELECT * FROM peptide_standard_purity_types WHERE active ORDER BY type";
        $statement = $this->db->prepare($sql);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function get_assay_import_record($import_log_id = false) {

        $data = false;

        if ($import_log_id) {

            $statement = $this->db->prepare("
        SELECT *
          , assay_parameters_new.import_log_id as log_id1
          , assay_parameters_primary_investigators.primary_investigator_full_name as primary_investigators
          -- , sop_files_join.import_log_id as log_id2
          -- , sop_files_join.sop_files_id as sop_id1
        FROM import_log
        LEFT JOIN assay_parameters_new ON assay_parameters_new.import_log_id = import_log.import_log_id
        LEFT JOIN assay_parameters_primary_investigators ON assay_parameters_primary_investigators.import_log_id = import_log.import_log_id
        -- LEFT JOIN sop_files_join ON sop_files_join.import_log_id = import_log.import_log_id
        -- LEFT JOIN sop_files ON sop_files.sop_files_id = sop_files_join.sop_files_id
        WHERE import_log.import_log_id = :import_log_id;");
            $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);


        }

        return $data;
    }

    public function get_recent_import_id($account_id = false) {
        $import_log_id = false;

        if ($account_id) {
            $statement = $this->db->prepare("
                SELECT import_log.import_log_id
                FROM import_log
                  JOIN user_account_import uai on import_log.import_log_id = uai.import_log_id
                where  import_log.last_modified = (
                         SELECT MAX(last_modified) AS max
                         FROM import_log il2
                           JOIN user_account_import uai on il2.import_log_id = uai.import_log_id
                         WHERE account_id = :account_id
                       )
                       AND account_id = :account_id
                GROUP BY import_log.import_log_id;
            ");



            $statement->bindValue(":account_id", $account_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            $import_log_id = $data['import_log_id'];
        }

        return $import_log_id;
    }


    public function get_assay_types() {
        $statement = $this->db->prepare("
      SELECT assay_types.assay_types_id, assay_types.label
      FROM assay_types
      WHERE assay_types.label <> 'direct'
      ORDER BY assay_types.label ASC
    ");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_sop_files($import_log_id = false) {

        $data = false;

        if ($import_log_id) {
            $statement = $this->db->prepare("
        SELECT sop_files.sop_files_id
          , sop_files.sop_file_type_id
          , sop_files.file_name
          , sop_files.internal_file_name
          , sop_files.file_type
          , sop_files.file_size
          , sop_file_types.label
        FROM sop_files_join
        LEFT JOIN sop_files ON sop_files.sop_files_id = sop_files_join.sop_files_id
        LEFT JOIN sop_file_types ON sop_file_types.sop_file_type_id = sop_files.sop_file_type_id
        WHERE sop_files_join.import_log_id = :import_log_id
        AND sop_files.is_deleted = 0
        ORDER BY sop_files.created_date DESC
      ");
            $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        return $data;
    }

    public function get_sop_files_by_id($sop_file_ids = false) {

        $data = false;

        if ($sop_file_ids) {
            $sop_file_ids_comma_delimited = implode(",", $sop_file_ids);
            $statement = $this->db->prepare("
        SELECT sop_files.sop_files_id
          , sop_files.sop_file_type_id
          , sop_files.file_name
          , sop_files.internal_file_name
          , sop_files.file_type
          , sop_files.file_size
          , sop_files.is_deleted
        FROM sop_files
        WHERE sop_files.sop_files_id IN(" . $sop_file_ids_comma_delimited . ")
        AND sop_files.is_deleted = 0
        ORDER BY sop_files.created_date DESC
      ");
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        return $data;
    }

    public function insert_sop_file($file_data = false) {

        $sop_files_id = false;

        if ($file_data) {

            $statement = $this->db->prepare("INSERT INTO sop_files
        ( sop_file_type_id, file_name, internal_file_name, file_type, file_size, created_date, created_by_user_id, last_modified_by_user_id)
        VALUES (:sop_file_type_id, :file_name, :internal_file_name, :file_type, :file_size, NOW(), :account_id, :account_id)");
            $statement->bindValue(":sop_file_type_id", 12, PDO::PARAM_INT);
            $statement->bindValue(":file_name", $file_data["name"], PDO::PARAM_STR);
            $statement->bindValue(":internal_file_name", $file_data["internal_file_name"], PDO::PARAM_STR);
            $statement->bindValue(":file_type", $file_data["type"], PDO::PARAM_STR);
            $statement->bindValue(":file_size", $file_data["size"], PDO::PARAM_INT);
            $statement->bindValue(":account_id", $_SESSION[$this->session_key]["account_id"], PDO::PARAM_INT);
            $statement->execute();
            $error = $this->db->errorInfo();
            if ($error[0] != "00000") {
                var_dump($this->db->errorInfo());
                die('sop_files table insert');
            }

            $sop_files_id = $this->db->lastInsertId();

            $statement = $this->db->prepare("INSERT INTO sop_files_join
        (sop_files_id)
        VALUES (:sop_files_id)");
            $statement->bindValue(":sop_files_id", $sop_files_id, PDO::PARAM_INT);
            $statement->execute();
            $error = $this->db->errorInfo();
            if ($error[0] != "00000") {
                var_dump($this->db->errorInfo());
                die('sop_files_join table insert');
            }

        }

        return $sop_files_id;

    }

    public function get_sop_file_types() {
        $statement = $this->db->prepare("
      SELECT sop_file_types.sop_file_type_id, sop_file_types.label
      FROM sop_file_types
      ORDER BY sop_file_types.label ASC
    ");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_sop_file_type_by_id($sop_file_type_id = false) {
        $data = false;
        if ($sop_file_type_id) {
            $statement = $this->db->prepare("
        SELECT sop_file_types.label
        FROM sop_file_types
        WHERE sop_file_types.sop_file_type_id = :sop_file_type_id
      ");
            $statement->bindValue(":sop_file_type_id", $sop_file_type_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);
        }
        return $data;
    }

    public function update_sop_file_type_id($sop_file_data = false) {
        $data = false;
        if ($sop_file_data && isset($sop_file_data["sop_file_id"]) && isset($sop_file_data["sop_file_type_id"])) {
            $statement = $this->db->prepare("
        UPDATE sop_files
        SET sop_file_type_id = :sop_file_type_id
        WHERE sop_files_id = :sop_files_id
      ");
            $statement->bindValue(":sop_files_id", $sop_file_data["sop_file_id"], PDO::PARAM_INT);
            $statement->bindValue(":sop_file_type_id", $sop_file_data["sop_file_type_id"], PDO::PARAM_INT);
            $statement->execute();
            $data = true;
        }
        return $data;
    }

    public function get_publications($import_log_id = false) {

        $data = false;

        if ($import_log_id) {
            $statement = $this->db->prepare("
        SELECT publications.publications_id
          , publications.publication_citation
          , publications.publication_url
        FROM publications
        WHERE publications.import_log_id = :import_log_id
        AND publications.is_deleted = 0
      ");
            $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        return $data;
    }

    public function get_primary_investigators($import_log_id = false) {

        $data = false;

        if ($import_log_id) {
            $statement = $this->db->prepare("
                    SELECT primary_investigator_full_name,assay_parameters_primary_investigators_id
                    FROM assay_parameters_primary_investigators
                    WHERE import_log_id = :import_log_id
              ");

            $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        foreach($data as $key=>$item) {
            if(empty($item['primary_investigator_full_name'])) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    public function insert_update_assays_import($data = false, $import_log_id = false, $account_id = false) {

        $assay_parameters_id = false;

        // Inserts
        if ($data && !$import_log_id) {

            $statement = $this->db->prepare("INSERT INTO import_log
        (group_id, import_date, imported_by_user_id, last_modified_by_user_id)
        VALUES (:group_id, NOW(), :account_id, :account_id)");
            $statement->bindValue(":group_id", $this->user_group_id, PDO::PARAM_INT);
            $statement->bindValue(":account_id",$account_id, PDO::PARAM_INT);
            $statement->execute();

            $error = $this->db->errorInfo();
            if ($error[0] != "00000") {
                var_dump($this->db->errorInfo());
                die('import_log table insert');
            }

            $data["import_log_id"] = $this->db->lastInsertId();

            $import = $this->db->lastInsertId();


            // Remove primary_investigators key from the data array, so the query doesn't blow up due to the incorrect amout of $placeholders

            $primary_investigators = $data["primary_investigators"];
            //$primary_investigators = isset($data["primary_investigators"]) ? explode(",", $data["primary_investigators"]) : array();
            if (isset($data["primary_investigators"])) {
                unset($data["primary_investigators"]);
            }


            $primary_investigators_first_name = isset($data["primary_investigators_first_name"])
                ? explode(",", $data["primary_investigators_first_name"]) : array();
            if (isset($data["primary_investigators_first_name"])) {
                unset($data["primary_investigators_first_name"]);
            }

            $primary_investigators_middle_initial = isset($data["primary_investigators_middle_initial"])
                ? explode(",", $data["primary_investigators_middle_initial"]) : array();
            if (isset($data["primary_investigators_middle_initial"])) {
                unset($data["primary_investigators_middle_initial"]);
            }

            $primary_investigators_last_name = isset($data["primary_investigators_last_name"])
                ? explode(",", $data["primary_investigators_last_name"]) : array();
            if (isset($data["primary_investigators_last_name"])) {
                unset($data["primary_investigators_last_name"]);
            }


            // Remove uploaded_files key from the data array, so the query doesn't blow up due to the incorrect amout of $placeholders
            $uploaded_files = isset($data["uploaded_files"]) ? $data["uploaded_files"] : array();
            if (isset($data["uploaded_files"])) {
                unset($data["uploaded_files"]);
            }

            // Remove sop_file_types key from the data array, so the query doesn't blow up due to the incorrect amout of $placeholders
            $sop_file_types = isset($data["sop_file_types"]) ? $data["sop_file_types"] : array();
            if (isset($data["sop_file_types"])) {
                unset($data["sop_file_types"]);
            }

            // Remove publication_citation key from the data array, so the query doesn't blow up due to the incorrect amout of $placeholders
            $publication_citation = isset($data["publication_citation"]) ? $data["publication_citation"] : array();
            if (isset($data["publication_citation"])) {
                unset($data["publication_citation"]);
            }

            // Remove publication_url key from the data array, so the query doesn't blow up due to the incorrect amout of $placeholders
            $publication_url = isset($data["publication_url"]) ? $data["publication_url"] : array();
            if (isset($data["publication_url"])) {
                unset($data["publication_url"]);
            }

            // Only use fields which are populated for the INSERT query.
            // So, remove keys with empty values.
            foreach ($data as $key => $value) {
                if (empty($value)) {
                    unset($data[$key]);
                }
            }

            $fields = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));

            $statement = $this->db->prepare("INSERT INTO assay_parameters_new ({$fields}) VALUES ({$placeholders})");
            foreach ($data as $key => $value) {
                // Some loose binding (not binding int values)
                $statement->bindValue(":" . $key, $value, PDO::PARAM_STR);
            }
            $statement->execute();
            
            $error = $this->db->errorInfo();
            if ($error[0] != "00000") {
                var_dump($this->db->errorInfo());
                die('assay_parameters_new table insert');
            }

            $assay_parameters_id = $this->db->lastInsertId();


            // Update SOP file data
            $i = 0;
            foreach ($uploaded_files as $uploaded_file_id) {
                // Update sop_files_join with the import_log_id
                $statement = $this->db->prepare("
          UPDATE sop_files_join
          SET import_log_id = :import_log_id
          WHERE sop_files_id = :sop_files_id
        ");
                $statement->bindValue(":import_log_id", $data["import_log_id"], PDO::PARAM_INT);
                $statement->bindValue(":sop_files_id", $uploaded_file_id, PDO::PARAM_INT);
                $statement->execute();
                // Update the sop_file_type_id in the the sop_file table
                $statement = $this->db->prepare("
          UPDATE sop_files
          SET sop_file_type_id = :sop_file_type_id
          WHERE sop_files_id = :sop_files_id
        ");
                $statement->bindValue(":sop_file_type_id", $sop_file_types[$i], PDO::PARAM_INT);
                $statement->bindValue(":sop_files_id", $uploaded_file_id, PDO::PARAM_INT);
                $statement->execute();
                $i++;
            }

            // Insert Publications.
            for ($i = 0; $i < count($publication_citation); $i++) {
                // Insert
                $statement = $this->db->prepare("INSERT INTO publications
          (import_log_id, publication_citation, publication_url)
          VALUES (:import_log_id, :publication_citation, :publication_url)
        ");
                $statement->bindValue(":import_log_id", $data["import_log_id"], PDO::PARAM_INT);
                $statement->bindValue(":publication_citation", $publication_citation[$i], PDO::PARAM_STR);
                $statement->bindValue(":publication_url", $publication_url[$i], PDO::PARAM_STR);
                $statement->execute();
            }

            // Insert Primary Investigators. Only allow 3 into the database.
            for ($i = 0; $i < count($primary_investigators); $i++) {
                if ($i < 3) {
                    $statement = $this->db->prepare("INSERT INTO assay_parameters_primary_investigators
              (import_log_id, primary_investigator_full_name)
              VALUES (:import_log_id, :primary_investigator_full_name)
             ");
                    $statement->bindValue(":import_log_id", $data["import_log_id"], PDO::PARAM_INT);
                    $statement->bindValue(":primary_investigator_full_name", $primary_investigators[$i], PDO::PARAM_STR);
                    $statement->execute();
                }
            }

        }

        // Updates
        if ($data && $import_log_id) {

            //$data["import_log_id"] = $this->db->lastInsertId();
            $data["import_log_id"] = $import_log_id;

            $investigator_count = $this->investigator_count($import_log_id);

            // Update the import_log table to change the last_modified_by_user_id
            $statement = $this->db->prepare("UPDATE import_log
        SET note = ''
        WHERE import_log_id = :import_log_id");
            $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
            $statement->execute();
            $error = $this->db->errorInfo();
            if ($error[0] != "00000") {
                var_dump($this->db->errorInfo());
                die('import_log table update');
            }

            // Remove primary_investigators key from the data array, so the query doesn't blow up due to the incorrect amout of $placeholders
            //$primary_investigators = isset($data["primary_investigators"]) ? explode(",", $data["primary_investigators"]) : array();
            $primary_investigators = $data["primary_investigators"];
            if (isset($data["primary_investigators"])) {
                unset($data["primary_investigators"]);
            }

            $primary_investigators_first_name = isset($data["primary_investigators_first_name"])
                ? explode(",", $data["primary_investigators_first_name"]) : array();
            if (isset($data["primary_investigators_first_name"])) {
                unset($data["primary_investigators_first_name"]);
            }

            $primary_investigators_middle_initial = isset($data["primary_investigators_middle_initial"])
                ? explode(",", $data["primary_investigators_middle_initial"]) : array();
            if (isset($data["primary_investigators_middle_initial"])) {
                unset($data["primary_investigators_middle_initial"]);
            }

            $primary_investigators_last_name = isset($data["primary_investigators_last_name"])
                ? explode(",", $data["primary_investigators_last_name"]) : array();
            if (isset($data["primary_investigators_last_name"])) {
                unset($data["primary_investigators_last_name"]);
            }



            // Remove uploaded_files key from the data array, so the query doesn't blow up due to the incorrect amout of $placeholders
            $uploaded_files = isset($data["uploaded_files"]) ? $data["uploaded_files"] : array();
            if (isset($data["uploaded_files"])) {
                unset($data["uploaded_files"]);
            }

            // Remove sop_file_types key from the data array, so the query doesn't blow up due to the incorrect amout of $placeholders
            $sop_file_types = isset($data["sop_file_types"]) ? $data["sop_file_types"] : array();
            if (isset($data["sop_file_types"])) {
                unset($data["sop_file_types"]);
            }

            // Remove publication_citation key from the data array, so the query doesn't blow up due to the incorrect amout of $placeholders
            $publication_citation = isset($data["publication_citation"]) ? $data["publication_citation"] : array();
            if (isset($data["publication_citation"])) {
                unset($data["publication_citation"]);
            }

            // Remove publication_url key from the data array, so the query doesn't blow up due to the incorrect amout of $placeholders
            $publication_url = isset($data["publication_url"]) ? $data["publication_url"] : array();
            if (isset($data["publication_url"])) {
                unset($data["publication_url"]);
            }

            // Only use fields which are populated for the INSERT query.
            // So, remove keys with empty values.
            foreach ($data as $key => $value) {
                if (empty($value)) {
                    unset($data[$key]);
                }
            }

            $fields_and_placeholders = '';
            foreach ($data as $key => $value) {
                $fields_and_placeholders .= $key . ' = :' . $key . ', ';
            }
            $fields_and_placeholders = preg_replace('/\, $/', '', $fields_and_placeholders);

            $statement = $this->db->prepare("UPDATE assay_parameters_new
        SET {$fields_and_placeholders}
        WHERE import_log_id = :import_log_id");
            foreach ($data as $key => $value) {
                // Some loose binding (not binding int values)
                $statement->bindValue(":" . $key, $value, PDO::PARAM_STR);
            }
            $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
            $statement->execute();
            $error = $this->db->errorInfo();
            if ($error[0] != "00000") {
                var_dump($this->db->errorInfo());
                die('assay_parameters_new table update');
            }

            // Update SOP file data
            $i = 0;
            foreach ($uploaded_files as $uploaded_file_id) {
                // Update sop_files_join with the import_log_id
                $statement = $this->db->prepare("
          UPDATE sop_files_join
          SET import_log_id = :import_log_id
          WHERE sop_files_id = :sop_files_id
        ");
                $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
                $statement->bindValue(":sop_files_id", $uploaded_file_id, PDO::PARAM_INT);
                $statement->execute();
                // Update the sop_file_type_id in the the sop_file table
                $statement = $this->db->prepare("
          UPDATE sop_files
          SET sop_file_type_id = :sop_file_type_id
          WHERE sop_files_id = :sop_files_id
        ");
                $statement->bindValue(":sop_file_type_id", $sop_file_types[$i], PDO::PARAM_INT);
                $statement->bindValue(":sop_files_id", $uploaded_file_id, PDO::PARAM_INT);
                $statement->execute();
                $i++;
            }

            // Insert publications
            for ($i = 0; $i < count($publication_citation); $i++) {
                // Insert
                $statement = $this->db->prepare("INSERT INTO publications
          (import_log_id, publication_citation, publication_url)
          VALUES (:import_log_id, :publication_citation, :publication_url)
        ");
                $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
                $statement->bindValue(":publication_citation", $publication_citation[$i], PDO::PARAM_STR);
                $statement->bindValue(":publication_url", $publication_url[$i], PDO::PARAM_STR);
                $statement->execute();
            }

            // Insert Primary Investigators. Only allow 3 into the database.

            foreach ($primary_investigators as $primary_investigator_full_name) {
                if ($investigator_count < 3) {
                    $statement = $this->db->prepare("INSERT INTO assay_parameters_primary_investigators
            (import_log_id, primary_investigator_full_name)
             VALUES (:import_log_id, :primary_investigator_full_name)
             ");
                    $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
                    $statement->bindValue(":primary_investigator_full_name", $primary_investigator_full_name, PDO::PARAM_STR);
                    $statement->execute();
                    $investigator_count++;
                }

            }

            // Update the "last_modified" column in the the "import_log" table
            $statement = $this->db->prepare("
        UPDATE import_log
        SET last_modified = NOW()
        WHERE import_log_id = :import_log_id
      ");
            $statement->bindValue(":import_log_id", $data["import_log_id"], PDO::PARAM_INT);
            $statement->execute();

            $assay_parameters_id = true;

        }

        $data['assay_parameters_id'] = $assay_parameters_id;
        //$data['import'] = 173;

        return $data;

    }


    public function investigator_count($import_log_id = false) {
        $data = false;
        if ($import_log_id) {
            $statement = $this->db->prepare("select * from assay_parameters_primary_investigators where import_log_id = :import_log_id");
            $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->rowCount();

            $error = $this->db->errorInfo();
            if ($error[0] != "00000") {
                var_dump($this->db->errorInfo());
                die('no count');
            }
        }
        return $data;
    }

    public function delete_file($file_id = false) {
        $data = false;
        if ($file_id) {
            $statement = $this->db->prepare("UPDATE sop_files SET is_deleted = 1 WHERE sop_files_id = :file_id");
            $statement->bindValue(":file_id", $file_id, PDO::PARAM_INT);
            $statement->execute();
            $error = $this->db->errorInfo();
            if ($error[0] != "00000") {
                var_dump($this->db->errorInfo());
                die('file delete');
            }
            $data = 'deleted';
        }
        return $data;
    }

    public function delete_investigator($investigator_id = false) {
        $data = false;
        if ($investigator_id) {
            $statement = $this->db->prepare("DELETE FROM assay_parameters_primary_investigators
                                       WHERE assay_parameters_primary_investigators_id = :investigator_id");
            $statement->bindValue(":investigator_id", $investigator_id, PDO::PARAM_INT);
            $statement->execute();

            $error = $this->db->errorInfo();
            if ($error[0] != "00000") {
                var_dump($this->db->errorInfo());
                die('file delete');
            }

            $data = 'deleted';
        } else {

            $data = 'no_id';

        }
        return $data;
    }

    public function add_investigator($investigator_name = false, $import_log_id = false) {
        $data = false;

        if ($investigator_name) {
            $statement = $this->db->prepare("INSERT INTO assay_parameters_primary_investigators
          (import_log_id, primary_investigator_full_name)
          VALUES (:import_log_id, :investigator_name)
        ");
            $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
            $statement->bindValue(":investigator_name", $investigator_name, PDO::PARAM_STR);
            $statement->execute();
        }

        return $data;
    }


    public function delete_file_pre_post($file_id = false) {
        $data = false;
        if ($file_id) {
            $statement = $this->db->prepare("DELETE FROM sop_files WHERE sop_files_id = :file_id");
            $statement->bindValue(":file_id", $file_id, PDO::PARAM_INT);
            $statement->execute();
            $error = $this->db->errorInfo();
            if ($error[0] != "00000") {
                var_dump($this->db->errorInfo());
                die('sop_files delete');
            }
            $statement = $this->db->prepare("DELETE FROM sop_files_join WHERE sop_files_id = :file_id");
            $statement->bindValue(":file_id", $file_id, PDO::PARAM_INT);
            $statement->execute();
            $error = $this->db->errorInfo();
            if ($error[0] != "00000") {
                var_dump($this->db->errorInfo());
                die('sop_files_join delete');
            }
            $data = 'deleted';
        }
        return $data;
    }

    public function download_file($upload_directory, $file_id = false) {

        if ($file_id) {
            $statement = $this->db->prepare("
        SELECT
        file_name,
        internal_file_name,
        file_type
        FROM sop_files
        WHERE sop_files_id = :file_id
      ");
            $statement->bindValue(":file_id", $file_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            $error = $this->db->errorInfo();
            if ($error[0] != "00000") {
                var_dump($this->db->errorInfo());
                die('select for download file (single)');
            }

            header("Content-type: " . $data['file_type']);
            header('Content-Disposition: attachment; filename="' . $data['file_name'] . '"');
            readfile($upload_directory . $data['internal_file_name']);
            exit;
        }
    }

    public function delete_publication($publication_id = false) {

        $data = false;

        if ($publication_id) {
            $statement = $this->db->prepare("UPDATE publications SET is_deleted = 1 WHERE publications_id = :publications_id");
            $statement->bindValue(":publications_id", $publication_id, PDO::PARAM_INT);
            $statement->execute();
            $error = $this->db->errorInfo();
            if ($error[0] != "00000") {
                var_dump($this->db->errorInfo());
                die('publication delete');
            }
            $data = 'deleted';
        }

        return $data;

    }



    /**
     * Insert public uploaded file
     **/
    public function insert_public_upload_file($import_log_id, $upload_file) {
        $this->db->prepare("
        	INSERT INTO public_upload_file
        	    (import_log_id, experiment_type, file_name)
        	VALUES
        	    (:import_log_id, :experiment_type, :file_name)
        ")->bindAll([
            ":import_log_id" => $import_log_id,
            ":experiment_type" => $upload_file->getExperimentType()->getValue(),
            ":file_name" => $upload_file->getFilename()
        ], PublicUpload::UPLOAD_FILE_DATA_TYPES
        )->execute();
    }

    /**
     * Insert list of public uploaded files
     **/
    public function insert_public_upload_files($import_log_id, $upload_files) {
        foreach ($upload_files as $upload_file) {
            $this->insert_public_upload_file($import_log_id, $upload_file);
        }
    }

    /**
     * Get all uploaded files
     */
    public function get_public_upload_files($import_log_id) {

        $data = false;

        if($import_log_id) {
            $statement = $this->db->prepare("
                                        SELECT *
                                        FROM public_upload_file
                                        WHERE import_log_id = :import_log_id
                                    ");
            $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        return $data;
    }

}
