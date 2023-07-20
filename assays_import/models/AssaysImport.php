<?php

/**
 * @desc Import Assays: assay import model class
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */
namespace assays_import\models;

use \PDO;

class AssaysImport {
    private $session_key = "";
    public $db;

    public function __construct($db_connection = false, $session_key = false) {
        if ($db_connection && is_object($db_connection)) {
            $this->db = $db_connection;
        }
        global $final_global_template_vars;
        $this->final_global_template_vars = $final_global_template_vars;
        $this->session_key = $session_key;
        $this->user_laboratory_id = $_SESSION[$this->session_key]["associated_groups"][0];
        $this->user_laboratory_ids = $_SESSION[$this->session_key]["associated_groups"];
    }


    public function get_peptide_standard_purity_types() {
        $sql = "SELECT * FROM peptide_standard_purity_types WHERE active ORDER BY type";
        $statement = $this->db->prepare($sql);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }


    public function browse_assay_imports(
        $sort_field = false
        , $sort_order = 'DESC'
        , $start_record = 0
        , $stop_record = 20
        , $search = false
        , $column_filters = false
        , $sortable_fields = false) {

        $sort = "";
        $search_sql = "";
        $column_filter_sql = "";
        $pdo_params = array();

        $limit_sql = " LIMIT {$start_record}, {$stop_record} ";

        if ($sort_field) {
            switch ($sort_field) {
                case 'last_modified':
                    $sort = " ORDER BY import_log.last_modified {$sort_order} ";
                    break;
                default:
                    $sort = " ORDER BY {$sort_field} {$sort_order} ";
            }
        }

        if ($search) {
            $pdo_params[] = '%' . $search . '%';
            $pdo_params[] = '%' . $search . '%';
            $pdo_params[] = '%' . $search . '%';
            $search_sql = "
        AND (
          import_log.import_date LIKE ?
          OR import_log.last_modified LIKE ?
        ) ";
        }

        $comparison_array = array(
            "gt" => " > "
        , "gt_or_eq" => " >= "
        , "lt" => "<"
        , "lt_or_eq" => " <= "
        , "equals" => " = "
        , "contains" => "contains"
        , "not_contain" => "not_contain"
        , "start_with" => "start_with"
        , "end_with" => "end_with"
        );

        if ($column_filters) {
            $column_filter_array = array();
            foreach ($column_filters as $filter) {
                $params = $filter;
                if (is_object($filter)) {
                    $params = get_object_vars($filter);
                }

                if (isset($params['value'])
                    && $params['value']
                    && isset($params['comparison'])
                    && $params['comparison']
                    && isset($comparison_array[$params['comparison']])
                    && $comparison_array[$params['comparison']]
                    && isset($params['column'])
                    && array_key_exists($params['column'], $sortable_fields)) // $sortable_fields -- insuring against SQL injection
                {

                    switch ($params['comparison']) {
                        case "contains": //contains
                            $comparison_and_value = " LIKE ? ";
                            $pdo_params[] = "%" . $params['value'] . "%";
                            break;
                        case "not_contains": //does not contain
                            $comparison_and_value = " NOT LIKE ? ";
                            $pdo_params[] = "%" . $params['value'] . "%";
                            break;
                        case "start_with": //starts with
                            $comparison_and_value = " LIKE ? ";
                            $pdo_params[] = $params['value'] . "%";
                            break;
                        case "end_with": //ends with
                            $comparison_and_value = " LIKE ? ";
                            $pdo_params[] = "%" . $params['value'];
                            break;
                        default:
                            $comparison_and_value = $comparison_array[$params['comparison']] . " ? ";
                            $pdo_params[] = $params['value'];
                    }

                    $column_filter_array[] = " ({$params['column']} {$comparison_and_value}) ";
                }
            }

            if ($column_filter_array) {
                $column_filter_sql = " AND (" . implode(' AND ', $column_filter_array) . ") ";
            }

        }

        $statement = $this->db->prepare("SELECT SQL_CALC_FOUND_ROWS
      import_log.import_log_id AS manage
      , import_log.import_log_id
      , DATE_FORMAT(import_log.import_date,'%m/%d/%Y %r') AS created_date
      , DATE_FORMAT(import_log.last_modified,'%m/%d/%Y %r') AS last_modified
      , import_log.import_log_id AS DT_RowId
      , group.abbreviation as laboratory_abbreviation
      , group.name as laboratory_name
      -- , CONCAT(LEFT(assay_parameters_new.celllysate_path,30),'...') as panorama_directory
      , assay_parameters_new.celllysate_path as panorama_directory
      FROM import_log
      LEFT JOIN `assay_parameters_new` ON assay_parameters_new.import_log_id = import_log.import_log_id
      LEFT JOIN `group` ON group.group_id = import_log.laboratory_id
      WHERE 1 = 1
      AND import_log.laboratory_id IN(" . implode(",", $this->user_laboratory_ids) . ")
      ");
        $statement->execute($pdo_params);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    public function get_assay_import_record($import_log_id = false) {

        $data = false;

        if ($import_log_id) {
            $statement = $this->db->prepare("
        SELECT *
          , assay_parameters_new.import_log_id as log_id1
          -- , sop_files_join.import_log_id as log_id2
          -- , sop_files_join.sop_files_id as sop_id1
        FROM import_log
        LEFT JOIN assay_parameters_new ON assay_parameters_new.import_log_id = import_log.import_log_id
        -- LEFT JOIN sop_files_join ON sop_files_join.import_log_id = import_log.import_log_id
        -- LEFT JOIN sop_files ON sop_files.sop_files_id = sop_files_join.sop_files_id
        WHERE import_log.import_log_id = :import_log_id
        AND import_log.laboratory_id IN(" . implode(",", $this->user_laboratory_ids) . ")
      ");
            $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);
        }

        return $data;
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
        (file_name, internal_file_name, file_type, file_size, created_date, created_by_user_id, last_modified_by_user_id)
        VALUES (:file_name, :internal_file_name, :file_type, :file_size, NOW(), :account_id, :account_id)");
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

    public function insert_update_assays_import($data = false, $import_log_id = false) {

        $assay_parameters_id = false;

        // Inserts
        if ($data && !$import_log_id) {

            $statement = $this->db->prepare("INSERT INTO import_log
        (laboratory_id, import_date, imported_by_user_id, last_modified_by_user_id)
        VALUES (:laboratory_id, NOW(), :account_id, :account_id)");
            $statement->bindValue(":laboratory_id", $this->user_laboratory_id, PDO::PARAM_INT);
            $statement->bindValue(":account_id", $_SESSION[$this->session_key]["account_id"], PDO::PARAM_INT);
            $statement->execute();
            $error = $this->db->errorInfo();
            if ($error[0] != "00000") {
                var_dump($this->db->errorInfo());
                die('import_log table insert');
            }

            $data["import_log_id"] = $this->db->lastInsertId();

            // Remove primary_investigators key from the data array, so the query doesn't blow up due to the incorrect amout of $placeholders
            $primary_investigators = isset($data["primary_investigators"]) ? explode(",", $data["primary_investigators"]) : array();
            if (isset($data["primary_investigators"])) {
                unset($data["primary_investigators"]);
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

            // Insert Primary Investigators.
            foreach ($primary_investigators as $primary_investigator_full_name) {
                $statement = $this->db->prepare("INSERT INTO assay_parameters_primary_investigators
          (import_log_id, primary_investigator_full_name)
          VALUES (:import_log_id, :primary_investigator_full_name)
        ");
                $statement->bindValue(":import_log_id", $data["import_log_id"], PDO::PARAM_INT);
                $statement->bindValue(":primary_investigator_full_name", $primary_investigator_full_name, PDO::PARAM_STR);
                $statement->execute();
            }

        }

        // Updates
        if ($data && $import_log_id) {

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
            $primary_investigators = isset($data["primary_investigators"]) ? explode(",", $data["primary_investigators"]) : array();
            if (isset($data["primary_investigators"])) {
                unset($data["primary_investigators"]);
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

            // Insert Primary Investigators.
            foreach ($primary_investigators as $primary_investigator_full_name) {
                $statement = $this->db->prepare("INSERT INTO assay_parameters_primary_investigators
          (import_log_id, primary_investigator_full_name)
          VALUES (:import_log_id, :primary_investigator_full_name)
        ");
                $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
                $statement->bindValue(":primary_investigator_full_name", $primary_investigator_full_name, PDO::PARAM_STR);
                $statement->execute();
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

        return $assay_parameters_id;

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

    public function get_executed_imports($import_log_id = false) {

        $data = false;

        if ($import_log_id) {
            $statement = $this->db->prepare("
        SELECT
            imports_executed_log_id
          , imports_executed_log.number_of_records
          , import_in_progress
          , reimport_in_progress
          , DATE_FORMAT(imports_executed_log.import_executed_date, '%m/%d/%Y \at %h:%i %p \EST') as import_executed_date
        FROM imports_executed_log
        WHERE imports_executed_log.import_log_id = :import_log_id
        ORDER BY imports_executed_log.import_executed_date DESC
        LIMIT 1
      ");
            $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        return $data;
    }

    public function delete_import($import_log_id = false) {

        $data = false;

        if ($import_log_id) {
            foreach ($this->final_global_template_vars["import_logged_database_tables"] as $table_name) {
                $statement = $this->db->prepare("
          DELETE FROM `" . $table_name . "` WHERE import_log_id = :import_log_id
        ");
                $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
                $statement->execute();
                // Reset the auto increment value.
                $statement = $this->db->prepare("ALTER TABLE `" . $table_name . "` AUTO_INCREMENT = 1");
                $statement->execute();
            }
            $data = "import_deleted";
        }

        return $data;

    }

    public function reset_import($import_log_id = false) {

        $data = false;

        if ($import_log_id) {
            foreach ($this->final_global_template_vars["import_logged_database_tables"] as $table_name) {
                $statement = $this->db->prepare("
          DELETE FROM `imports_executed_log` WHERE import_log_id = :import_log_id
        ");
                $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
                $statement->execute();
                // Reset the auto increment value.
                $statement = $this->db->prepare("ALTER TABLE `imports_executed_log` AUTO_INCREMENT = 1");
                $statement->execute();
            }
            $data = "import_reset";
        }

        return $data;

    }
    ///@@@CAP-61 - resolve blockers for assay import
    /*
    public function populate_import_log_ids() {

        $statement = $this->db->prepare("SELECT
          protein.protein_id
        , analyte_peptide.analyte_peptide_id
        , protein.import_log_id
      FROM protein
      LEFT JOIN analyte_peptide ON analyte_peptide.protein_id = protein.protein_id");
        $statement->execute();
        $base_data = $statement->fetchAll(PDO::FETCH_ASSOC);

        $update_tables_array = array(
            "analyte_peptide"
        , "lod_loq_comparison"
        , "panorama_chromatogram_images"
        , "panorama_response_curve_images"
        , "panorama_validation_sample_data"
        , "panorama_validation_sample_images"
        , "response_curves_data"
        );

        foreach ($base_data as $data) {
            foreach ($update_tables_array as $table_name) {
                $statement = $this->db->prepare("UPDATE `" . $table_name . "` SET import_log_id = :import_log_id WHERE analyte_peptide_id = :analyte_peptide_id");
                $statement->bindValue(":import_log_id", $data{"import_log_id"}, PDO::PARAM_INT);
                $statement->bindValue(":analyte_peptide_id", $data["analyte_peptide_id"], PDO::PARAM_INT);
                $statement->execute();
            }
        }

        return "All tables updated with the import_log_id.";

    }
    */

}

?>
