<?php

namespace assays_manage\models;

use \PDO;

class AssaysManage {

    private $session_key = "";
    public $db;

    public function __construct($db_connection = false, $session_key = false) {
        if ($db_connection && is_object($db_connection)) {
            $this->db = $db_connection;
        }
        global $final_global_template_vars;
        $this->final_global_template_vars = $final_global_template_vars;
        $this->session_key = $session_key;
    }

    public function browse_assays(
        $sort_field = false
        , $sort_order = 'DESC'
        , $start_record = 0
        , $stop_record = 20
        , $search = false
        , $column_filters = false
        , $sortable_fields = false
        , $side_bar_filter = array()) {

        $sort = "";
        $search_sql = " WHERE 1 = 1 ";
        $pdo_params = array();
        $limit_sql = " LIMIT {$start_record}, {$stop_record} ";

        if ($sort_field) {
            switch ($sort_field) {
                case 'last_modified':
                    $sort = " ORDER BY group.last_modified, analyte_peptide.peptide_sequence {$sort_order} ";
                    break;
                default:
                    $sort = " ORDER BY {$sort_field}, analyte_peptide.peptide_sequence {$sort_order} ";
            }
        }

        if ($search) {
            $seach_stmt = array();
            foreach ($this->final_global_template_vars['datatables'][0]['search_fields'] as $key => $value) {
                $pdo_params[] = '%' . $search . '%';
                $seach_stmt[] = $value . " LIKE ?"; // '%".$search."%'
            }
            $search_sql = " WHERE " . implode(" \nOR ", $seach_stmt);
        }

        $side_bar_filter_where_sql = "";
        $side_bar_filter_where_array = array();
        if (!empty($side_bar_filter)) {
            foreach ($side_bar_filter as $sidebar_key => $single_sidebar_filter) {
                if (!empty($single_sidebar_filter)) {
                    switch ($sidebar_key) {
                        case "import_set_filter":
                            $placeholder = array();
                            foreach ($single_sidebar_filter as $single_filter_value) {
                                $placeholder[] = "?";
                                $pdo_params[] = (int)$single_filter_value;
                            }
                            $side_bar_filter_where_array["import_set_filter"] = " AND import_log.import_log_id IN (" . implode(",", $placeholder) . ") ";
                            break;
                        case "import_set_filter_lab_id":
                            $lab_ids = $this->get_import_log_ids_by_lab_id($single_sidebar_filter["import_set_filter_lab_id"]);
                            $side_bar_filter_where_array["import_set_filter"] = " AND import_log.import_log_id IN (" . implode(",", $lab_ids) . ") ";
                            break;
                        case "status_filter":
                            $placeholder = array();
                            foreach ($single_sidebar_filter as $single_filter_value) {
                                $placeholder[] = "?";
                                $pdo_params[] = (int)$single_filter_value;
                            }
                            $side_bar_filter_where_array["status_filter"] = " AND protein.approval_status IN (" . implode(",", $placeholder) . ") ";
                            break;
                    }
                }
            }
        }


        if (!empty($side_bar_filter_where_array)) {
            $side_bar_filter_where_sql = implode("", $side_bar_filter_where_array);
        }
        $side_bar_filter_having_sql = "";
        if (!empty($side_bar_filter_having_array)) {
            $side_bar_filter_having_sql = implode("", $side_bar_filter_having_array);
        }

        $class_icon_plus_sign = '"icon-collapse-alt"';

        $sql = "SELECT SQL_CALC_FOUND_ROWS
      protein.protein_id as manage
      , protein.protein_id
      , protein.cptac_id
      , protein.gene_symbol as gene_symbol
      , CONCAT('<i class=', {$class_icon_plus_sign}, '></i> ', protein.gene_symbol,' - UniProt Accession ID: ',protein.uniprot_accession_id) as gene
      , protein.chromosome_number
      , protein.chromosome_start
      , protein.chromosome_stop
      , protein.uniprot_accession_id as uniprot
      , protein.uniprot_protein_name
      , protein.protein_molecular_weight as protein_molecular_weight
      , protein.homology
      , protein.approval_status
      , protein.additional_approval_status                   
      , assay_parameters_new.experiment_345
      , assay_parameters_new.protein_species_label as protein_species_label
      , analyte_peptide.peptide_sequence as peptide_sequence
      , analyte_peptide.peptide_start as peptide_start
      , analyte_peptide.peptide_end as peptide_end
      , analyte_peptide.modification_type as modification
      , (CASE WHEN analyte_peptide.site_of_modification_protein IS NULL THEN CONCAT('N/A') ELSE analyte_peptide.site_of_modification_protein END) AS site_of_modification_protein
      , assay_types.label as assay_type
      , assay_parameters_new.peptide_standard_purity
      , assay_parameters_new.instrument
      , (CASE WHEN assay_parameters_new.endogenous_detected = '0' THEN CONCAT('no') ELSE CONCAT('yes') END) AS endogenous_detected
      , assay_parameters_new.matrix
      , analyte_peptide.hydrophobicity as hydrophobicity
      -- , CONCAT(FORMAT( lod_loq_comparison.LOD,2 )) as lod
      -- , CONCAT(FORMAT( lod_loq_comparison.LLOQ,2 )) as lloq
      , group.name as laboratory_name
      , panorama_validation_sample_data.med_total_CV
      , analyte_peptide.analyte_peptide_id AS DT_RowId
      FROM analyte_peptide
      LEFT JOIN protein on analyte_peptide.protein_id = protein.protein_id
      LEFT JOIN import_log ON import_log.import_log_id = protein.import_log_id
      LEFT JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
      LEFT JOIN assay_types on assay_types.assay_types_id = assay_parameters_new.assay_types_id
      LEFT JOIN `group` ON import_log.laboratory_id = group.group_id
      LEFT JOIN panorama_validation_sample_data on panorama_validation_sample_data.analyte_peptide_id = analyte_peptide.analyte_peptide_id
      {$search_sql}
      {$side_bar_filter_where_sql}
      AND protein.approval_status <> 3
      GROUP BY analyte_peptide.analyte_peptide_id
      HAVING 1 = 1
      {$side_bar_filter_having_sql}
      {$sort}
      ";
        $statement = $this->db->prepare($sql);
        $statement->execute($pdo_params);
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

  

    public function get_laboratories($laboratories_id = false) {

        $data = false;
        $and = '';

        if ($laboratories_id) {
            $and = "AND group.group_id = " . (int)$laboratories_id;
        }

        $statement = $this->db->prepare("
      SELECT DISTINCT
          group_closure_table.descendant as laboratories_id
        , group.name as laboratory_name
        , group.abbreviation
      FROM group_closure_table
      LEFT JOIN `group` ON group.group_id = group_closure_table.descendant
      WHERE group_closure_table.ancestor = 6
      AND group_closure_table.pathlength = 1
      {$and}
    ");
        $statement->execute();
        $data = $laboratories_id ? $statement->fetch(PDO::FETCH_ASSOC) : $statement->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    public function get_import_log() {
        $statement = $this->db->prepare("
      SELECT
        import_log.import_log_id
        ,import_log.laboratory_id
        ,DATE_FORMAT(import_log.import_date, '%m/%d/%Y') as import_date
        ,import_log.imported_by_user_id
        ,import_log.note
        ,assay_parameters_new.celllysate_path as folder
      FROM import_log
      JOIN assay_parameters_new on import_log.import_log_id = assay_parameters_new.import_log_id
      ");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_import_logs_by_lab_id($laboratory_id = false) {
        $statement = $this->db->prepare("
      SELECT
        import_log.import_log_id
        ,import_log.laboratory_id
        ,DATE_FORMAT(import_log.import_date, '%m/%d/%Y') as import_date
        ,import_log.imported_by_user_id
        ,import_log.note
        ,assay_parameters_new.celllysate_path as folder
      FROM import_log
      JOIN assay_parameters_new on import_log.import_log_id = assay_parameters_new.import_log_id
      WHERE laboratory_id = :laboratory_id
      ");
        $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_laboratory_by_id($laboratories_id = false) {
        $statement = $this->db->prepare("
      SELECT
        group.name
        ,group.primary_contact_name
        ,group.primary_contact_email_address
      FROM `group`
      WHERE group_id = :group_id");
        $statement->bindValue(":group_id", $laboratories_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function get_import_log_ids_by_lab_id($laboratory_id = false) {
        $statement = $this->db->prepare("
      SELECT import_log_id
      FROM import_log
      WHERE laboratory_id = :laboratory_id");
        $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $value) {
            $data[] = $value['import_log_id'];
        }
        return $data;
    }

    public function approval_process($id = false, $status = false) {
        $data = false;
        if ($id) {
            $statement = $this->db->prepare("
        UPDATE protein
        SET approval_status = :approval_status
        WHERE protein_id = :protein_id
      ");
            $statement->bindValue(":protein_id", $id, PDO::PARAM_INT);
            if ($status == 1) {
                $status = 4;
            }
            $statement->bindValue(":approval_status", $status, PDO::PARAM_INT);
            $statement->execute();
            $data = $id;
        }
        return $data;
    }

    public function additional_approval_process($id = false, $status = false) {
        $data = false;
        if ($id) {
            $statement = $this->db->prepare("
        UPDATE protein
        SET additional_approval_status = :approval_status
        WHERE protein_id = :protein_id
      ");
            $statement->bindValue(":protein_id", $id, PDO::PARAM_INT);
            if ($status == 1) {
                $status = 4;
            }
            $statement->bindValue(":approval_status", $status, PDO::PARAM_INT);
            $statement->execute();
            $data = $id;
        }
        return $data;
    }

    public function add_approval_moderation_notes(
        $this_protein_id = false
        , $all_protein_ids = false
        , $import_set_id = false
        , $user_id = false
        , $comment_text = false
        , $apply_to_all = false
        , $has_been_emailed = false
    ) {

        $data = array();

        if ($this_protein_id && $user_id && $comment_text) {
            // If "apply to all", loop through each id
            if ($all_protein_ids && $apply_to_all) {
                $all_protein_ids_array = explode(',', $all_protein_ids);
                foreach ($all_protein_ids_array as $single_protein_id) {
                    $statement = $this->db->prepare("
            INSERT INTO approval_moderation_notes
            ( protein_id, import_set_id, note_content, has_been_emailed, created_date, created_by_user_id, last_modified_by_user_id )
            VALUES ( :protein_id, :import_set_id, :note_content, :has_been_emailed, NOW(), :created_by_user_id, :last_modified_by_user_id )");
                    $statement->bindValue(":protein_id", $single_protein_id, PDO::PARAM_INT);
                    $statement->bindValue(":import_set_id", $import_set_id, PDO::PARAM_INT);
                    $statement->bindValue(":note_content", $comment_text, PDO::PARAM_STR);
                    $statement->bindValue(":has_been_emailed", $has_been_emailed, PDO::PARAM_INT);
                    $statement->bindValue(":created_by_user_id", $user_id, PDO::PARAM_INT);
                    $statement->bindValue(":last_modified_by_user_id", $user_id, PDO::PARAM_INT);
                    $statement->execute();
                    $data[]['last_inserted_id'] = $this->db->lastInsertId();
                }
            } else {
                // Insert a single note
                $statement = $this->db->prepare("
          INSERT INTO approval_moderation_notes
          ( protein_id, import_set_id, note_content, has_been_emailed, created_date, created_by_user_id, last_modified_by_user_id )
          VALUES ( :protein_id, :import_set_id, :note_content, :has_been_emailed, NOW(), :created_by_user_id, :last_modified_by_user_id )");
                $statement->bindValue(":protein_id", $this_protein_id, PDO::PARAM_INT);
                $statement->bindValue(":import_set_id", $import_set_id, PDO::PARAM_INT);
                $statement->bindValue(":note_content", $comment_text, PDO::PARAM_STR);
                $statement->bindValue(":has_been_emailed", $has_been_emailed, PDO::PARAM_INT);
                $statement->bindValue(":created_by_user_id", $user_id, PDO::PARAM_INT);
                $statement->bindValue(":last_modified_by_user_id", $user_id, PDO::PARAM_INT);
                $statement->execute();
                $data['last_inserted_id'] = $this->db->lastInsertId();
            }
        }

        return $data;
    }

    public function get_notes($protein_id = false) {
        $data = false;
        if ($protein_id) {
            $statement = $this->db->prepare("
        SELECT
          approval_moderation_notes.approval_moderation_notes_id
          ,approval_moderation_notes.note_content
          ,approval_moderation_notes.has_been_emailed
          ,approval_moderation_notes.has_been_emailed_count
          ,DATE_FORMAT(approval_moderation_notes.created_date,'%m/%d/%Y') AS note_created_date
          ,account.given_name
          ,account.sn
        FROM approval_moderation_notes
        LEFT JOIN account ON approval_moderation_notes.created_by_user_id = account.account_id
        WHERE protein_id = :protein_id
        AND approval_moderation_notes.is_deleted = 0
        ORDER BY approval_moderation_notes.created_date DESC");
            $statement->bindValue(":protein_id", $protein_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        return $data;
    }

    public function get_notes_totals($protein_id = false) {
        $data = false;
        if ($protein_id) {
            $statement = $this->db->prepare("
        SELECT SQL_CALC_FOUND_ROWS approval_moderation_notes_id
        FROM approval_moderation_notes
        WHERE protein_id = :protein_id
        AND approval_moderation_notes.is_deleted = 0");
            $statement->bindValue(":protein_id", $protein_id, PDO::PARAM_INT);
            $statement->execute();
            $statement->fetchAll(PDO::FETCH_ASSOC);

            $statement = $this->db->prepare("SELECT FOUND_ROWS()");
            $statement->execute();
            $count = $statement->fetch(PDO::FETCH_ASSOC);
            $data = (int)$count["FOUND_ROWS()"];
        }
        return $data;
    }

    public function get_note_by_id($note_id = false) {
        $data = false;
        if ($note_id) {
            $statement = $this->db->prepare("
        SELECT
          approval_moderation_notes.approval_moderation_notes_id
          ,approval_moderation_notes.note_content
          ,approval_moderation_notes.has_been_emailed
          ,approval_moderation_notes.has_been_emailed_count
          ,DATE_FORMAT(approval_moderation_notes.created_date,'%m/%d/%Y') AS note_created_date
          ,account.given_name
          ,account.sn
        FROM approval_moderation_notes
        LEFT JOIN account ON approval_moderation_notes.created_by_user_id = account.account_id
        WHERE approval_moderation_notes_id = :approval_moderation_notes_id
        AND approval_moderation_notes.is_deleted = 0");
            $statement->bindValue(":approval_moderation_notes_id", $note_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);
        }
        return $data;
    }

    public function increment_approval_moderation_note($approval_moderation_notes_id = false) {
        $data = false;
        if ($approval_moderation_notes_id) {
            $statement = $this->db->prepare("UPDATE approval_moderation_notes
        SET has_been_emailed = 1, has_been_emailed_count = has_been_emailed_count + 1
        WHERE approval_moderation_notes_id = :approval_moderation_notes_id
      ");
            $statement->bindValue(":approval_moderation_notes_id", $approval_moderation_notes_id, PDO::PARAM_INT);
            $statement->execute();
            $data = true;
        }
        return $data;
    }

    public function get_record_data($protein_id = false) {
        $statement = $this->db->prepare("
      SELECT protein.gene_symbol, analyte_peptide.peptide_sequence
      FROM protein
      LEFT JOIN analyte_peptide ON protein.protein_id = analyte_peptide.protein_id
      WHERE protein.protein_id = :protein_id");
        $statement->bindValue(":protein_id", $protein_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function get_notes_by_import_set_id($import_set_id = false) {
        $data = false;
        if ($import_set_id) {
            $statement = $this->db->prepare("
        SELECT
          protein.gene_symbol
          ,analyte_peptide.peptide_sequence
          ,approval_moderation_notes.note_content
          ,CONCAT(account.given_name, ' ', account.sn) as note_submitted_by
        FROM approval_moderation_notes
        LEFT JOIN account ON approval_moderation_notes.created_by_user_id = account.account_id
        LEFT JOIN protein ON protein.protein_id = approval_moderation_notes.protein_id
        LEFT JOIN analyte_peptide ON analyte_peptide.protein_id = approval_moderation_notes.protein_id
        WHERE approval_moderation_notes.import_set_id = :import_set_id
        AND approval_moderation_notes.is_deleted = 0");
            $statement->bindValue(":import_set_id", $import_set_id, PDO::PARAM_INT);
            $statement->execute();
            return $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        return $data;
    }

    public function delete_note($note_id = false) {
        $data = false;
        if ($note_id) {
            $statement = $this->db->prepare("
        UPDATE approval_moderation_notes
        SET is_deleted = 1
        WHERE approval_moderation_notes.approval_moderation_notes_id = :note_id");
            $statement->bindValue(":note_id", $note_id, PDO::PARAM_INT);
            $statement->execute();
            $data = true;
        }
        return $data;
    }

    public function submit_process($id = false, $status = false) {
        $data = false;
        if ($id) {
            $statement = $this->db->prepare("
        UPDATE protein
        SET approval_status = :approval_status
        WHERE protein_id = :protein_id
      ");
            $statement->bindValue(":protein_id", $id, PDO::PARAM_INT);
            $statement->bindValue(":approval_status", $status, PDO::PARAM_INT);
            $statement->execute();
            $data['id'] = $id;
            $data['status'] = $status;
        }
        return $data;
    }

}
