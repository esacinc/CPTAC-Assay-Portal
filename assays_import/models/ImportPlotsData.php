<?php

namespace assays_import\models;

use \PDO;

class ImportPlotsData {

    public $db;

    public function __construct($db_connection = false) {
        if ($db_connection && is_object($db_connection)) {
            $this->db = $db_connection;
        }
    }

    public function import_lod_loq_comparison_data($row = false) {

        if ($row) {

            // $row array looks like this:
            //
            // [0] transition
            // [1] transition_id
            // [2] blank_low_conc_LOD,
            // [3] blank_only_LOD,
            // [4] rsd_limit_LOD,
            // [5] blank_low_conc_LOQ,
            // [6] blank_only_LOQ,
            // [7] rsd_limit_LOQ
            // [8] peptide
            // [9] analyte_peptide_id
            // [10] laboratory_id
            // [11] import_log_id
            // [12] lod_loq_units

            // Query the Portal database to see if the record exists, which will determine if we're updating or inserting.
            $statement = $this->db->prepare("
        SELECT lod_loq_comparison_id
        FROM lod_loq_comparison
        WHERE transition = :transition
        AND peptide = :peptide
        AND analyte_peptide_id = :analyte_peptide_id
        AND laboratory_id = :laboratory_id
      ");
            $statement->bindValue(":analyte_peptide_id", $row[9], PDO::PARAM_INT);
            $statement->bindValue(":laboratory_id", $row[10], PDO::PARAM_INT);
            $statement->bindValue(":peptide", $row[8], PDO::PARAM_STR);
            $statement->bindValue(":transition", trim(str_replace(array('\'', '"'), '', $row[0])), PDO::PARAM_INT);
            $statement->execute();
            $existing_data = $statement->fetch(PDO::FETCH_ASSOC);

            if (!$existing_data) {
                // Insert the record into the database.
                $statement = $this->db->prepare("
          INSERT INTO lod_loq_comparison
          (
              import_log_id
            , analyte_peptide_id
            , laboratory_id
            , peptide
            , transition
            , transition_id
            , lod_loq_units
            , blank_low_conc_LOD
            , blank_low_conc_LOQ
            , blank_only_LOD
            , blank_only_LOQ
            , rsd_limit_LOD
            , rsd_limit_LOQ
            , created_date
          )
          VALUES (
              :import_log_id
            , :analyte_peptide_id
            , :laboratory_id
            , :peptide
            , :transition
            , :transition_id
            , :lod_loq_units
            , :blank_low_conc_LOD
            , :blank_low_conc_LOQ
            , :blank_only_LOD
            , :blank_only_LOQ
            , :rsd_limit_LOD
            , :rsd_limit_LOQ
            , NOW()
          )");
                $statement->bindValue(":lod_loq_units", $row[12], PDO::PARAM_STR); // (quantification_units)
                $statement->bindValue(":import_log_id", $row[11], PDO::PARAM_INT);
                $statement->bindValue(":analyte_peptide_id", $row[9], PDO::PARAM_INT);
                $statement->bindValue(":laboratory_id", $row[10], PDO::PARAM_INT);
                $statement->bindValue(":peptide", $row[8], PDO::PARAM_STR);
                $statement->bindValue(":transition", trim(str_replace(array('\'', '"'), '', $row[0])), PDO::PARAM_INT);
                $statement->bindValue(":transition_id", trim(str_replace(array('\'', '"'), '', $row[1])), PDO::PARAM_STR);
                $statement->bindValue(":blank_low_conc_LOD", trim(str_replace(array('\'', '"'), '', $row[2])), PDO::PARAM_STR);
                $statement->bindValue(":blank_only_LOD", trim(str_replace(array('\'', '"'), '', $row[3])), PDO::PARAM_STR);
                $statement->bindValue(":rsd_limit_LOD", trim(str_replace(array('\'', '"'), '', $row[4])), PDO::PARAM_STR);
                $statement->bindValue(":blank_low_conc_LOQ", trim(str_replace(array('\'', '"'), '', $row[5])), PDO::PARAM_STR);
                $statement->bindValue(":blank_only_LOQ", trim(str_replace(array('\'', '"'), '', $row[6])), PDO::PARAM_STR);
                $statement->bindValue(":rsd_limit_LOQ", trim(str_replace(array('\'', '"'), '', $row[7])), PDO::PARAM_STR);
                $statement->execute();
            } else {
                // Update the table.
                $statement = $this->db->prepare("
          UPDATE lod_loq_comparison
          SET
              transition_id = :transition_id
            , blank_low_conc_LOD = :blank_low_conc_LOD
            , blank_low_conc_LOQ = :blank_low_conc_LOQ
            , blank_only_LOD = :blank_only_LOD
            , blank_only_LOQ = :blank_only_LOQ
            , rsd_limit_LOD = :rsd_limit_LOD
            , rsd_limit_LOQ = :rsd_limit_LOQ
          WHERE lod_loq_comparison_id = " . $existing_data["lod_loq_comparison_id"]);
                $statement->bindValue(":transition_id", trim(str_replace(array('\'', '"'), '', $row[1])), PDO::PARAM_STR);
                $statement->bindValue(":blank_low_conc_LOD", trim(str_replace(array('\'', '"'), '', $row[2])), PDO::PARAM_STR);
                $statement->bindValue(":blank_only_LOD", trim(str_replace(array('\'', '"'), '', $row[3])), PDO::PARAM_STR);
                $statement->bindValue(":rsd_limit_LOD", trim(str_replace(array('\'', '"'), '', $row[4])), PDO::PARAM_STR);
                $statement->bindValue(":blank_low_conc_LOQ", trim(str_replace(array('\'', '"'), '', $row[5])), PDO::PARAM_STR);
                $statement->bindValue(":blank_only_LOQ", trim(str_replace(array('\'', '"'), '', $row[6])), PDO::PARAM_STR);
                $statement->bindValue(":rsd_limit_LOQ", trim(str_replace(array('\'', '"'), '', $row[7])), PDO::PARAM_STR);
                $statement->execute();
            }

        }
    }

    // Not being used anymore (may need to use again, not sure) 2014-05-10.
    public function insert_lod_loq_method_type($lod_loq_method_type_label = false) {
        // Check to see if it's already in the database
        $statement = $this->db->prepare("
      SELECT lod_loq_method_type_id
      FROM lod_loq_method_type
      WHERE lod_loq_method_type_label = :lod_loq_method_type_label");
        $statement->bindValue(":lod_loq_method_type_label", $lod_loq_method_type_label, PDO::PARAM_STR);
        $statement->execute();
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        // If it's in the database, set the $lod_loq_method_type_id variable
        // If not, insert it set the $lod_loq_method_type_id variable to the last insert id
        if ($data) {
            $lod_loq_method_type_id = $data["lod_loq_method_type_id"];
        } else {
            $statement = $this->db->prepare("
        INSERT INTO lod_loq_method_type
        (lod_loq_method_type_label)
        VALUES ( :lod_loq_method_type_label )");
            $statement->bindValue(":lod_loq_method_type_label", $lod_loq_method_type_label, PDO::PARAM_STR);
            $statement->execute();
            $lod_loq_method_type_id = $this->db->lastInsertId();
        }
        return (int)$lod_loq_method_type_id;
    }

    // Original method name = import_response_curves_data
    public function import_curve_fit_data($row = false) {

        // Query the Portal database to see if the record exists, which will determine if we're updating or inserting.
        $statement = $this->db->prepare("
      SELECT response_curves_data_id
      FROM response_curves_data
      WHERE transition = :transition
      AND peptide = :peptide
      AND analyte_peptide_id = :analyte_peptide_id
      AND laboratory_id = :laboratory_id
    ");
        $statement->bindValue(":analyte_peptide_id", $row[8], PDO::PARAM_INT);
        $statement->bindValue(":laboratory_id", $row[9], PDO::PARAM_INT);
        $statement->bindValue(":peptide", $row[7], PDO::PARAM_STR);
        $statement->bindValue(":transition", trim(str_replace(array('\'', '"'), '', $row[0])), PDO::PARAM_INT);
        $statement->execute();
        $existing_data = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$existing_data) {
            // Insert the record into the database.
            $statement = $this->db->prepare("
        INSERT INTO response_curves_data
        (
            import_log_id
          , analyte_peptide_id
          , laboratory_id
          , peptide
          , transition
          , transition_id
          , Slope
          , SlopeStdErr
          , Intercept
          , InterceptStdErr
          , RSquare
          , created_date
        )
        VALUES (
            :import_log_id
          , :analyte_peptide_id
          , :laboratory_id
          , :peptide
          , :transition
          , :transition_id
          , :Slope
          , :SlopeStdErr
          , :Intercept
          , :InterceptStdErr
          , :RSquare
          , NOW()
        )");
            $statement->bindValue(":import_log_id", $row[10], PDO::PARAM_INT);
            $statement->bindValue(":analyte_peptide_id", $row[8], PDO::PARAM_INT);
            $statement->bindValue(":laboratory_id", $row[9], PDO::PARAM_INT);
            $statement->bindValue(":peptide", $row[7], PDO::PARAM_STR);
            $statement->bindValue(":transition", trim(str_replace(array('\'', '"'), '', $row[0])), PDO::PARAM_INT);
            $statement->bindValue(":transition_id", trim(str_replace(array('\'', '"'), '', $row[1])), PDO::PARAM_STR);
            $statement->bindValue(":Slope", trim(str_replace(array('\'', '"'), '', $row[2])), PDO::PARAM_STR);
            $statement->bindValue(":Intercept", trim(str_replace(array('\'', '"'), '', $row[3])), PDO::PARAM_STR);
            $statement->bindValue(":SlopeStdErr", trim(str_replace(array('\'', '"'), '', $row[4])), PDO::PARAM_STR);
            $statement->bindValue(":InterceptStdErr", trim(str_replace(array('\'', '"'), '', $row[5])), PDO::PARAM_STR);
            $statement->bindValue(":RSquare", trim(str_replace(array('\'', '"'), '', $row[6])), PDO::PARAM_STR);
            $statement->execute();
        } else {
            // Update the table.
            $statement = $this->db->prepare("
        UPDATE response_curves_data
        SET  
            transition_id = :transition_id
          , Slope = :Slope
          , SlopeStdErr = :SlopeStdErr
          , Intercept = :Intercept
          , InterceptStdErr = :InterceptStdErr
          , RSquare = :RSquare
        WHERE response_curves_data_id = " . $existing_data["response_curves_data_id"]);
            $statement->bindValue(":transition_id", trim(str_replace(array('\'', '"'), '', $row[1])), PDO::PARAM_STR);
            $statement->bindValue(":Slope", trim(str_replace(array('\'', '"'), '', $row[2])), PDO::PARAM_STR);
            $statement->bindValue(":Intercept", trim(str_replace(array('\'', '"'), '', $row[3])), PDO::PARAM_STR);
            $statement->bindValue(":SlopeStdErr", trim(str_replace(array('\'', '"'), '', $row[4])), PDO::PARAM_STR);
            $statement->bindValue(":InterceptStdErr", trim(str_replace(array('\'', '"'), '', $row[5])), PDO::PARAM_STR);
            $statement->bindValue(":RSquare", trim(str_replace(array('\'', '"'), '', $row[6])), PDO::PARAM_STR);
            $statement->execute();
        }
    }

}
