<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 4/5/18
 * Time: 11:34 AM
 */

namespace assays\models;

use core\models\Db\EndogenousImages;
use core\models\Db\EndogenousData;
use core\models\Db\SelectivityImages;
use core\models\Db\SelectivitySpikeLevelData;
use core\models\Db\SelectivitySummaryData;
use core\models\Db\StabilityImages;
use core\models\Db\StabilityData;
use \PDO;

use assays\models\WikiPathway;

class Assay {
    private $session_key = "";
    public $db;
    private $wikipathway;

    public function __construct($db_connection = false, $session_key = false) {
        if ($db_connection && is_object($db_connection)) {
            $this->db = $db_connection;
        }
        global $final_global_template_vars;
        $this->final_global_template_vars = $final_global_template_vars;
        $this->session_key = $session_key;
        $this->wikipathway = new WikiPathway($this->db);
    }

   public function getMultiplexingData($multiplex = false){
        $statement = $this->db->prepare("
              select p.gene_symbol,p.protein_id,ap.peptide_modified_sequence,p.cptac_id,p.multiplex_panel_id,m.panel_description,
              g.name from protein p
              join analyte_peptide ap on p.protein_id = ap.analyte_peptide_id
              join import_log im on im.import_log_id = p.import_log_id
              join multiplex_panel m on m.multiplex_panel_id = p.multiplex_panel_id
              join `group` g on g.group_id = im.laboratory_id
              where p.multiplex_panel_id = :multiplex_panel;"
        );

        $statement->bindValue(":multiplex_panel", $multiplex, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

   public function getAssayPanels(){
        $statement = $this->db->prepare("
              select distinct m.multiplex_panel_id,m.panel_description,
              g.name from protein p
              join analyte_peptide ap on p.protein_id = ap.analyte_peptide_id
              join import_log im on im.import_log_id = p.import_log_id
              join multiplex_panel m on m.multiplex_panel_id = p.multiplex_panel_id
              join `group` g on g.group_id = im.laboratory_id
              where p.approval_status = 1
              order by g.name, m.panel_description"
        );
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

   public function export_multiplex(
      $laboratory_name = false,
      $panel_description = false
   ){
     $statement = $this->db->prepare("SELECT
      protein.protein_id as manage
      , CONCAT( protein.gene_symbol,' - UniProt Accession ID: ',protein.uniprot_accession_id) as gene
      , analyte_peptide.peptide_sequence as peptide_sequence
      , group.name as laboratory_name
      , analyte_peptide.peptide_start as peptide_start
      , analyte_peptide.peptide_end as peptide_end
      , analyte_peptide.modification_type as modification
      , assay_types.label as assay_type
      , assay_parameters_new.matrix
      , analyte_peptide.hydrophobicity as hydrophobicity
      , (CASE WHEN analyte_peptide.site_of_modification_protein IS NULL THEN CONCAT('N/A') ELSE analyte_peptide.site_of_modification_protein END) AS site_of_modification_protein
      , (CASE WHEN assay_parameters_new.protein_species_label IS NOT NULL
                                       AND  uniprot_species.organism_common is NULL
                                       THEN assay_parameters_new.protein_species_label ELSE
                                       CONCAT(uniprot_species.organism_scientific,' (',uniprot_species.organism_common,')') END) as protein_species_label

      , peptide_standard_purity_types.type as peptide_standard_purity
      , assay_parameters_new.instrument
      , (CASE WHEN assay_parameters_new.endogenous_detected = '0' THEN CONCAT('no') ELSE CONCAT('yes') END) AS endogenous_detected
      , panorama_validation_sample_data.med_total_CV
      , protein.cptac_id
      FROM analyte_peptide
      JOIN protein on analyte_peptide.protein_id = protein.protein_id
      JOIN import_log ON import_log.import_log_id = protein.import_log_id
      JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
      LEFT JOIN peptide_standard_purity_types ON peptide_standard_purity_types.peptide_standard_purity_types_id = assay_parameters_new.peptide_standard_purity_types_id
      LEFT JOIN assay_types on assay_types.assay_types_id = assay_parameters_new.assay_types_id
      LEFT JOIN multiplex_panel on multiplex_panel.multiplex_panel_id = protein.multiplex_panel_id
      LEFT JOIN kegg_uniprot_map on kegg_uniprot_map.uniprot_accession_id = protein.uniprot_accession_id
      -- LEFT JOIN lod_loq_comparison on lod_loq_comparison.analyte_peptide_id = analyte_peptide.analyte_peptide_id
      LEFT JOIN `group` ON import_log.laboratory_id = group.group_id
      LEFT JOIN panorama_validation_sample_data on panorama_validation_sample_data.analyte_peptide_id = analyte_peptide.analyte_peptide_id
      LEFT JOIN uniprot_species on protein.uniprot_accession_id = uniprot_species.uniprot_accession_id
      where group.name = :laboratory_name and multiplex_panel.panel_description = :panel_description
      AND protein.approval_status IN (1)
      GROUP BY analyte_peptide.analyte_peptide_id;");

      $statement->bindValue(":laboratory_name", $laboratory_name,PDO::PARAM_STR);
      $statement->bindValue(":panel_description", $panel_description,PDO::PARAM_STR);
      $statement->execute();

      return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function export_uniprot( ){
        $statement = $this->db->prepare("
          SELECT
                protein.uniprot_accession_id as Uniprot_Accession
              , protein.cptac_id as cptac_id

          FROM analyte_peptide
          JOIN protein on analyte_peptide.protein_id = protein.protein_id
          LEFT JOIN uniprot_species on protein.uniprot_accession_id = uniprot_species.uniprot_accession_id
          WHERE protein.approval_status IN (1)
          GROUP BY analyte_peptide.analyte_peptide_id
      ");

        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function export_csv(
         $side_bar_filter = array()
        , $dropdown_filter = array()
        , $search = false)
    {
        $sort_field = array();
        $sort_order = array();
        $single_sidebar_filter = array();

        $search_sql = " WHERE 1 = 1 ";
        $pdo_params = array();
        $limit_sql = " LIMIT 1, 20 ";
        $protein_interactions_select = '';
        $protein_interactions_join = '';

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
            $search_sql = " WHERE (" . implode(" \nOR ", $seach_stmt) . ")";
        }

        $dropdown_filter_where_sql = "";
        $dropdown_filter_where_array = array();

        if (!empty($dropdown_filter)) {
            foreach ($dropdown_filter as $dropdown_key => $single_dropdown_filter) {
                if (!empty($single_dropdown_filter)) {
                         switch($dropdown_key){
                            case "assay_type_filter":
                               $placeholder = array();
                               foreach ($single_dropdown_filter as $single_filter_value) {
                                   $placeholder[] = "?";
                                   $pdo_params[] = trim($single_filter_value);
                               }
                               $dropdown_filter_where_array["assay_type_filter"] = " AND CONCAT(IFNULL(assay_types.label,''),' ',IFNULL(assay_parameters_new.data_type,'')) IN (".implode(",", $placeholder).") ";
                               break;

                            case "modification_type_filter":
                                $placeholder = array();
                               foreach ($single_dropdown_filter as $single_filter_value) {
                                   $placeholder[] = "?";
                                   $pdo_params[] = trim($single_filter_value);
                               }
                               $dropdown_filter_where_array["modification_type_filter"] = " AND analyte_peptide.modification_type IN (".implode(",", $placeholder).") ";
                               break;

                            case "submitting_laboratory_filter":
                                $placeholder = array();
                               foreach ($single_dropdown_filter as $single_filter_value) {
                                   $placeholder[] = "?";
                                   $pdo_params[] = trim($single_filter_value);
                               }
                               $dropdown_filter_where_array["submitting_laboratory_filter"] = " AND group.name IN (".implode(",", $placeholder).") ";
                               break;
                             case "available_peptide_filter":
                                $placeholder = array();
                               foreach ($single_dropdown_filter as $single_filter_value) {
                                   $placeholder[] = "?";
                                   $pdo_params[] = trim($single_filter_value);
                               }
                               $dropdown_filter_where_array["available_peptide_filter"] = " AND peptide_sequence IN (".implode(",", $placeholder).") ";
                               break;

                             case "peptide_start_filter":
                                $placeholder = array();
                               foreach ($single_dropdown_filter as $single_filter_value) {
                                   $placeholder[] = "?";
                                   $pdo_params[] = trim($single_filter_value);
                               }
                               $dropdown_filter_where_array["peptide_start_filter"] = " AND peptide_start IN (".implode(",", $placeholder).") ";
                               break;

                             case "peptide_end_filter":
                                $placeholder = array();
                               foreach ($single_dropdown_filter as $single_filter_value) {
                                   $placeholder[] = "?";
                                   $pdo_params[] = trim($single_filter_value);
                               }
                               $dropdown_filter_where_array["peptide_end_filter"] = " AND peptide_end IN (".implode(",", $placeholder).") ";
                               break;
                            case "instrument_filter":
                                $placeholder = array();
                               foreach ($single_dropdown_filter as $single_filter_value) {
                                   $placeholder[] = "?";
                                   $pdo_params[] = trim($single_filter_value);
                               }
                               $dropdown_filter_where_array["instrument_filter"] = " AND instrument IN (".implode(",", $placeholder).") ";
                               break;

                            case "dropdown_peptide_standard_purity_filter":
                                $placeholder = array();
                                foreach ($single_dropdown_filter as $single_filter_value) {
                                   $placeholder[] = "?";
                                   $pdo_params[] = $single_filter_value;
                               }
                               $dropdown_filter_where_array["peptide_standard_purity_filter"] = " AND assay_parameters_new.peptide_standard_purity_types_id IN (".implode(",", $placeholder).") ";
                               break;

                            case "dropdown_species_filter":
                                $placeholder = array();
                                foreach ($single_sidebar_filter as $single_filter_value) {
                                        $placeholder[] = "?";
                                        $pdo_params[] = $single_filter_value;
                                }
                                foreach ($single_sidebar_filter as $single_filter_value) {
                                        $pdo_params[] = $single_filter_value;
                                }
                                $dropdown_filter_where_array["species_filter"] =
                                    " AND ((assay_parameters_new.protein_species_label IN (" . implode(",", $placeholder) . ")
                                      AND  uniprot_species.organism_common is NULL)
                                      OR CONCAT(uniprot_species.organism_scientific,' (',uniprot_species.organism_common,')') IN (" . implode(",", $placeholder) . "))";
                                break;

                            case "matrix":
                                $placeholder = array();
                                foreach ($single_dropdown_filter as $single_filter_value) {
                                   $placeholder[] = "?";
                                   $pdo_params[] = $single_filter_value;
                               }
                               $dropdown_filter_where_array["matrix"] = " AND assay_parameters_new.matrix IN (".implode(",", $placeholder).") ";
                               break;

                           case "hydrophobicity":
                                $placeholder = array();
                                foreach ($single_dropdown_filter as $single_filter_value) {
                                   $placeholder[] = "?";
                                   $pdo_params[] = $single_filter_value;
                               }
                               $dropdown_filter_where_array["hydrophobicity"] = " AND analyte_peptide.hydrophobicity IN (".implode(",", $placeholder).") ";
                               break;

                               case "endogenous_detected":
                                $placeholder = array();
                                foreach ($single_dropdown_filter as $single_filter_value) {
                                   $placeholder[] = "?";
                                   $pdo_params[] = $single_filter_value;
                               }
                               $dropdown_filter_where_array["endogenous_detected"] = " AND analyte_peptide.endogenous_detected IN (".implode(",", $placeholder).") ";
                               break;
                }
             }
           }
        } else {
                $dropdown_filter_where_array = "";

        }
        $side_bar_filter_where_sql = "";
        $side_bar_filter_where_array = array();
        if (!empty($side_bar_filter)) {
            foreach ($side_bar_filter as $sidebar_key => $single_sidebar_filter) {
                if (!empty($single_sidebar_filter)) {
                    switch ($sidebar_key) {
                        // case "gene_ontology_filter":
                        //   $placeholder = array();
                        //   foreach($single_sidebar_filter as $single_filter_value){
                        //     $placeholder[] = "?";
                        //     $pdo_params[] = $single_filter_value;
                        //   }
                        //   $side_bar_filter_where_array["gene_ontology_filter"] = " AND protein.gene_ontology_id IN (" . implode(",",$placeholder) . ") ";
                        // break;

                        case "kegg_pathways_filter":
                            $placeholder = array();
                            if ($single_sidebar_filter[0] != NULL) {
                                foreach ($single_sidebar_filter as $single_filter_value) {
                                    foreach ($single_filter_value as $single_filter_val) {
                                        $kegg_uniprot_ids = $this->get_uniprot_ids_from_kegg((int)$single_filter_val);
                                        if ($kegg_uniprot_ids) {
                                            foreach ($kegg_uniprot_ids as $kegg_uniprot_id) {
                                                $placeholder[] = "?";
                                                $pdo_params[] = $kegg_uniprot_id["uniprot_accession_id"];
                                            }
                                        }
                                    }
                                }
                                $side_bar_filter_where_array["kegg_pathways_filter"] = " AND protein.uniprot_accession_id IN (" . implode(",", $placeholder) . ") ";
                            }
                            break;

                        case "chromosome_filter":
                            $placeholder = array();
                            foreach ($single_sidebar_filter as $single_filter_value) {
                                $placeholder[] = "?";
                                $pdo_params[] = (int)$single_filter_value;
                            }
                            //$side_bar_filter_having_array["cromosome_filter"] = " AND protein.chromosome_number IN (" . implode(",", $placeholder) . ") ";
                            $side_bar_filter_where_array["cromosome_filter"] = " AND protein.chromosome_number IN (" . implode(",", $placeholder) . ") ";
                            break;
                        case "chromosomal_location_start_filter":
                            $single_sidebar_filter_int = (int)$single_sidebar_filter;
                            $side_bar_filter_having_array["cromosomal_location_start_filter"] = " AND ( protein.chromosome_start >= {$single_sidebar_filter_int} ) ";
                            break;
                        case "chromosomal_location_stop_filter":
                            $single_sidebar_filter_int = (int)$single_sidebar_filter;
                            $side_bar_filter_having_array["cromosomal_location_stop_filter"] = " AND ( protein.chromosome_stop <= {$single_sidebar_filter_int} ) ";
                            break;
                        case "protein_interactions_filter":
                            $placeholder = array();
                            foreach ($single_sidebar_filter as $single_filter_value) {
                                $genes = $this->get_protein_interactions($single_filter_value);
                                foreach ($genes as $gene) {
                                    $placeholder[] = "?";
                                    $pdo_params[] = $gene;
                                }
                            }
                            $side_bar_filter_having_array["protein_interactions_filter"] = " AND protein.gene_symbol IN (" . implode(",", $placeholder) . ") ";
                            $protein_interactions_select = ' , external_data.protein_interactions_biogrid.official_symbol_interactor_a ';
                            $protein_interactions_join = ' LEFT JOIN external_data.protein_interactions_biogrid ON external_data.protein_interactions_biogrid.official_symbol_interactor_a = protein.gene_symbol ';
                            break;

                        case "species_filter":
                            $placeholder = array();
                            foreach ($single_sidebar_filter as $single_filter_value) {
                                $placeholder[] = "?";
                                $pdo_params[] = $single_filter_value;
                            }
                            foreach ($single_sidebar_filter as $single_filter_value) {
                                $pdo_params[] = $single_filter_value;
                            }
                            $side_bar_filter_where_array["species_filter"] =
                                " AND ((assay_parameters_new.protein_species_label IN (" . implode(",", $placeholder) . ")
                                       AND  uniprot_species.organism_common is NULL)
                                  OR CONCAT(uniprot_species.organism_scientific,' (',uniprot_species.organism_common,')') IN (" . implode(",", $placeholder) . "))";
                            break;


                        case "assay_type_filter":
                            $placeholder = array();
                            foreach ($single_sidebar_filter as $single_filter_value) {
                                $placeholder[] = "?";
                                $pdo_params[] = trim($single_filter_value);
                            }
                            $side_bar_filter_where_array["assay_type_filter"] = " AND CONCAT(IFNULL(assay_types.label,''),' ',IFNULL(assay_parameters_new.data_type,'')) IN (".implode(",", $placeholder).") ";

                            break;
                        case "peptide_standard_purity_filter":
                            $placeholder = array();
                            foreach ($single_sidebar_filter as $single_filter_value) {
                                 $placeholder[] = "?";
                                 $pdo_params[] = $single_filter_value;

                            }
                             $side_bar_filter_where_array["peptide_standard_purity"] = " AND assay_parameters_new.peptide_standard_purity_types_id IN (".implode(",", $placeholder).") ";
                            break;
                        case "panel_filter":
                            $placeholder = array();
                            if ($single_sidebar_filter[0] != NULL) {
                            foreach ($single_sidebar_filter as $single_filter_value) {
                                $placeholder[] = "?";
                                $pdo_params[] = $single_filter_value;
                            }
                            $side_bar_filter_where_array["panel_filter"] = " AND protein.multiplex_panel_id IN (" . implode(",", $placeholder) . ") ";
                            }
                            break;


                           }
                }
            }
        }
        if (!empty($side_bar_filter_where_array)) {
            $side_bar_filter_where_sql = implode("", $side_bar_filter_where_array);
        }
        if (!empty($dropdown_filter_where_array)) {
            $dropdown_filter_where_sql = implode("", $dropdown_filter_where_array);
        }

        $side_bar_filter_having_sql = "";

        if (!empty($side_bar_filter_having_array)) {
            $side_bar_filter_having_sql = implode("", $side_bar_filter_having_array);
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

        $class_icon_plus_sign = '"icon-collapse-alt"';

         $sql = "SELECT SQL_CALC_FOUND_ROWS
      protein.protein_id as manage
      {$protein_interactions_select}
      , CONCAT( protein.gene_symbol,' - UniProt Accession ID: ',protein.uniprot_accession_id) as gene
      , analyte_peptide.peptide_sequence as peptide_sequence
      , group.name as laboratory_name
      , analyte_peptide.peptide_start as peptide_start
      , analyte_peptide.peptide_end as peptide_end
      , analyte_peptide.modification_type as modification
      , assay_types.label as assay_type
      , assay_parameters_new.matrix
      , analyte_peptide.hydrophobicity as hydrophobicity
      , (CASE WHEN analyte_peptide.site_of_modification_protein IS NULL THEN CONCAT('N/A') ELSE analyte_peptide.site_of_modification_protein END) AS site_of_modification_protein
      , (CASE WHEN assay_parameters_new.protein_species_label IS NOT NULL
                                       AND  uniprot_species.organism_common is NULL
                                       THEN assay_parameters_new.protein_species_label ELSE
                                       CONCAT(uniprot_species.organism_scientific,' (',uniprot_species.organism_common,')') END) as protein_species_label

      , peptide_standard_purity_types.type as peptide_standard_purity
      , assay_parameters_new.instrument
      , (CASE WHEN assay_parameters_new.endogenous_detected = '0' THEN CONCAT('no') ELSE CONCAT('yes') END) AS endogenous_detected
      , panorama_validation_sample_data.med_total_CV
      , protein.cptac_id
      FROM analyte_peptide
      JOIN protein on analyte_peptide.protein_id = protein.protein_id
      JOIN import_log ON import_log.import_log_id = protein.import_log_id
      JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
      LEFT JOIN peptide_standard_purity_types ON peptide_standard_purity_types.peptide_standard_purity_types_id = assay_parameters_new.peptide_standard_purity_types_id
      LEFT JOIN assay_types on assay_types.assay_types_id = assay_parameters_new.assay_types_id
      {$protein_interactions_join}
      LEFT JOIN kegg_uniprot_map on kegg_uniprot_map.uniprot_accession_id = protein.uniprot_accession_id
      -- LEFT JOIN lod_loq_comparison on lod_loq_comparison.analyte_peptide_id = analyte_peptide.analyte_peptide_id
      LEFT JOIN `group` ON import_log.laboratory_id = group.group_id
      LEFT JOIN panorama_validation_sample_data on panorama_validation_sample_data.analyte_peptide_id = analyte_peptide.analyte_peptide_id
      LEFT JOIN uniprot_species on protein.uniprot_accession_id = uniprot_species.uniprot_accession_id
      LEFT JOIN multiplex_panel on protein.multiplex_panel_id = multiplex_panel.multiplex_panel_id
      {$search_sql}
      {$side_bar_filter_where_sql}
      {$dropdown_filter_where_sql}
      AND protein.approval_status IN (1)
      GROUP BY analyte_peptide.analyte_peptide_id
      HAVING 1 = 1
      {$side_bar_filter_having_sql}
      {$sort}";

      $statement = $this->db->prepare($sql);
        $statement->execute($pdo_params);

        $data["data"] = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }


    public function browse_assays(
        $sort_field = false
        , $sort_order = 'DESC'
        , $start_record = 0
        , $stop_record = 20
        , $search = false
        , $sortable_fields = false
        , $side_bar_filter = array()) {

        $sort = "";
        $search_sql = " WHERE 1 = 1 ";
        $pdo_params = array();
        $limit_sql = " LIMIT {$start_record}, {$stop_record} ";
        $protein_interactions_select = '';
        $protein_interactions_join = '';

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
            $search_sql = " WHERE (" . implode(" \nOR ", $seach_stmt) . ")";
        }

        $side_bar_filter_where_sql = "";
        $side_bar_filter_where_array = array();

        if (!empty($side_bar_filter)) {
            foreach ($side_bar_filter as $sidebar_key => $single_sidebar_filter) {
                if (!empty($single_sidebar_filter)) {
                    switch ($sidebar_key) {
                        case "species_filter":
                            $placeholder = array();

                                foreach ($single_sidebar_filter as $single_filter_value) {
                                    $placeholder[] = "?";
                                    $pdo_params[] = $single_filter_value;
                                }
                                foreach ($single_sidebar_filter as $single_filter_value) {
                                    $pdo_params[] = $single_filter_value;
                                }

                                //$side_bar_where_array["species_filter"] = " AND assay_parameters_new.protein_species_label IN ('Mus musculus (Mouse)') ";
                            /*
                                $side_bar_filter_where_array["species_filter"] =
                                    " AND CASE WHEN exists(select * from uniprot_species where uniprot_accession_id = protein.uniprot_accession_id)
                                     THEN CONCAT(uniprot_species.organism_scientific,' (',uniprot_species.organism_common,')') IN (" . implode(",", $placeholder) . ")
                                     ELSE assay_parameters_new.protein_species_label IN (" . implode(",", $placeholder) . ")
                                     END";
                              */

                            $side_bar_filter_where_array["species_filter"] = " AND ((assay_parameters_new.protein_species_label IN (" . implode(",", $placeholder) . ")
                                      AND  uniprot_species.organism_common is NULL)
                                      OR CONCAT(uniprot_species.organism_scientific,' (',uniprot_species.organism_common,')') IN (" . implode(",", $placeholder) . "))";

                                    /*
                                    " AND ((assay_parameters_new.protein_species_label IN (" . implode(",", $placeholder) . ")
                                       AND  uniprot_species.uniprot_accession_id is NULL)
                                  OR CONCAT(uniprot_species.organism_scientific,' (',uniprot_species.organism_common,')') IN (" . implode(",", $placeholder) . "))";
                                  */



                                break;
                        // case "gene_ontology_filter":
                        //   $placeholder = array();
                        //   foreach($single_sidebar_filter as $single_filter_value){
                        //     $placeholder[] = "?";
                        //     $pdo_params[] = $single_filter_value;
                        //   }
                        //   $side_bar_filter_where_array["gene_ontology_filter"] = " AND protein.gene_ontology_id IN (" . implode(",",$placeholder) . ") ";
                        // break;



                        case "kegg_pathways_filter":
                            $placeholder = array();
                            if ($single_sidebar_filter[0] != NULL) {
                                foreach ($single_sidebar_filter as $single_filter_value) {
                                    foreach ($single_filter_value as $single_filter_val) {
                                        $kegg_uniprot_ids = $this->get_uniprot_ids_from_kegg((int)$single_filter_val);
                                        if ($kegg_uniprot_ids) {
                                            foreach ($kegg_uniprot_ids as $kegg_uniprot_id) {
                                                $placeholder[] = "?";
                                                $pdo_params[] = $kegg_uniprot_id["uniprot_accession_id"];
                                            }
                                        }
                                    }
                                }
                                $side_bar_filter_having_array["kegg_pathways_filter"] = " AND protein.uniprot_accession_id IN (" . implode(",", $placeholder) . ") ";
                            }
                            break;

                        case "wiki_pathways_filter":
                            $placeholder = array();
                            if ($single_sidebar_filter[0] != NULL) {
                                foreach ($single_sidebar_filter as $single_filter_value) {
                                    foreach ($single_filter_value as $single_filter_val) {
                                        $wiki_uniprot_ids = $this->wikipathway->find_uniprot_accession((int)$single_filter_val);
                                        if ($wiki_uniprot_ids) {
                                            foreach ($wiki_uniprot_ids as $wiki_uniprot_id) {
                                                $placeholder[] = "?";
                                                $pdo_params[] = $wiki_uniprot_id["uniprot_accession_id"];
                                            }
                                        }
                                    }
                                }
                                $side_bar_filter_having_array["wiki_pathways_filter"] = " AND protein.uniprot_accession_id IN (" . implode(",", $placeholder) . ") ";
                            }
                            break;

                        case "protein_interactions_filter":
                            $placeholder = array();
                            foreach ($single_sidebar_filter as $single_filter_value) {
                                $genes = $this->get_protein_interactions($single_filter_value);
                                foreach ($genes as $gene) {
                                    $placeholder[] = "?";
                                    $pdo_params[] = $gene;
                                }
                            }
                            $side_bar_filter_having_array["protein_interactions_filter"] = " AND protein.gene_symbol IN (" . implode(",", $placeholder) . ") ";
                            $protein_interactions_select = ' , external_data.protein_interactions_biogrid.official_symbol_interactor_a ';
                            $protein_interactions_join = ' LEFT JOIN external_data.protein_interactions_biogrid ON external_data.protein_interactions_biogrid.official_symbol_interactor_a = protein.gene_symbol ';
                            break;
                        case "chromosome_filter":
                            $placeholder = array();
                            foreach ($single_sidebar_filter as $single_filter_value) {
                                $placeholder[] = "?";
                                $pdo_params[] = $single_filter_value;
                            }
                            //$side_bar_filter_having_array["chromosome_filter"] = " AND protein.chromosome_number IN (" . implode(",", $placeholder) . ") ";
                            $side_bar_filter_where_array["chromosome_filter"] = " AND protein.chromosome_number IN (" . implode(",", $placeholder) . ") ";

                            break;
                        case "chromosomal_location_start_filter":
                            $single_sidebar_filter_int = (int)$single_sidebar_filter;
                            $side_bar_filter_having_array["chromosomal_location_start_filter"] = " AND ( protein.chromosome_start <= {$single_sidebar_filter_int} )";
                            break;
                        case "chromosomal_location_stop_filter":
                            $single_sidebar_filter_int = (int)$single_sidebar_filter;
                            $side_bar_filter_having_array["chromosomal_location_stop_filter"] = " AND ( protein.chromosome_stop >= {$single_sidebar_filter_int} ) ";
                            break;
                        case "assay_type_filter":
                            $placeholder = array();
                            foreach ($single_sidebar_filter as $single_filter_value) {
                                $placeholder[] = "?";
                                $pdo_params[] = $single_filter_value;
                            }
                            $side_bar_filter_where_array["assay_type_filter"] = " AND CONCAT(IFNULL(assay_types.label,''),' ',IFNULL(assay_parameters_new.data_type,'')) IN (".implode(",", $placeholder).") ";
                            break;

                        case "panel_filter":
                            $placeholder = array();
                            if ($single_sidebar_filter[0] != NULL) {
                            foreach ($single_sidebar_filter as $single_filter_value) {
                                $placeholder[] = "?";
                                $pdo_params[] = $single_filter_value;
                            }
                            $side_bar_filter_where_array["panel_filter"] = " AND protein.multiplex_panel_id IN (" . implode(",", $placeholder) . ") ";
                            }
                            break;


                        case "peptide_standard_purity_filter":
                            $placeholder = array();
                            foreach ($single_sidebar_filter as $single_filter_value) {
                                 $placeholder[] = "?";
                                 $pdo_params[] = $single_filter_value;

                            }
                            $side_bar_filter_where_array["peptide_standard_purity"] = " AND assay_parameters_new.peptide_standard_purity_types_id IN (".implode(",", $placeholder).") ";
                            break;

                        case "lab_filter":
                            $placeholder = array();
                            foreach ($single_sidebar_filter as $single_filter_value) {
                                $placeholder[] = "?";
                                $pdo_params[] = $single_filter_value;
                            }
                            $side_bar_filter_where_array["lab_filter"] = " AND group.name IN (" . implode(",", $placeholder) . ") ";
                            break;





                        // case "reagent_type_filter":
                        //   $placeholder = array();
                        //   foreach($single_sidebar_filter as $single_filter_value){
                        //     $placeholder[] = "?";
                        //     $pdo_params[] = $single_filter_value;
                        //   }
                        //   $side_bar_filter_where_array["reagent_type_filter"] = " AND assay_parameters_new.assay_type IN (" . implode(",",$placeholder) . ") ";
                        // break;
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

        $class_icon_plus_sign = '"icon-collapse-alt"';

        $sql = "SELECT SQL_CALC_FOUND_ROWS
      protein.protein_id as manage
      {$protein_interactions_select}
      , protein.uniprot_accession_id as kegg_uniprot_accession_id
      , protein.protein_id
      , protein.cptac_id
      , protein.gene_symbol as gene_symbol
      , CONCAT(protein.gene_symbol,' - UniProt Accession ID: ',protein.uniprot_accession_id) as gene
      , protein.chromosome_number
      , protein.chromosome_start
      , protein.chromosome_stop
      , protein.multiplex_panel_id
      , protein.uniprot_accession_id as uniprot
      , protein.uniprot_protein_name
      , protein.protein_molecular_weight as protein_molecular_weight
      , protein.homology
      , protein.uniprot_gene_synonym
      /*, assay_parameters_new.protein_species_label as protein_species_label */
      , CONCAT('<i>',SUBSTRING_INDEX(assay_parameters_new.protein_species_label,' ',(LENGTH(assay_parameters_new.protein_species_label)-LENGTH(REPLACE(assay_parameters_new.protein_species_label,' ',''))+1)-1),'</i>',' ',SUBSTRING_INDEX(assay_parameters_new.protein_species_label,' ',-1)) as protein_species_label
      , analyte_peptide.peptide_sequence as peptide_sequence
      , analyte_peptide.peptide_start as peptide_start
      , analyte_peptide.peptide_end as peptide_end
      , analyte_peptide.modification_type as modification
      , (CASE WHEN analyte_peptide.site_of_modification_protein IS NULL THEN CONCAT('N/A') ELSE analyte_peptide.site_of_modification_protein END) AS site_of_modification_protein
      , assay_types.label as assay_type
      , uniprot_species.uniprot_accession_id
      , uniprot_species.organism_common
      , assay_parameters_new.data_type
      , assay_parameters_new.peptide_standard_purity_types_id
      , peptide_standard_purity_types.type as peptide_standard_purity
      , peptide_standard_purity_types.peptide_standard_purity_types_id
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
      JOIN protein on analyte_peptide.protein_id = protein.protein_id
      JOIN import_log ON import_log.import_log_id = protein.import_log_id
      JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
      LEFT JOIN peptide_standard_purity_types ON peptide_standard_purity_types.peptide_standard_purity_types_id = assay_parameters_new.peptide_standard_purity_types_id
      LEFT JOIN multiplex_panel on multiplex_panel.multiplex_panel_id = protein.multiplex_panel_id
      LEFT JOIN assay_types on assay_types.assay_types_id = assay_parameters_new.assay_types_id
      {$protein_interactions_join}
      LEFT JOIN kegg_uniprot_map on kegg_uniprot_map.uniprot_accession_id = protein.uniprot_accession_id
      -- LEFT JOIN lod_loq_comparison on lod_loq_comparison.analyte_peptide_id = analyte_peptide.analyte_peptide_id
      LEFT JOIN `group` ON import_log.laboratory_id = group.group_id
      LEFT JOIN panorama_validation_sample_data on panorama_validation_sample_data.analyte_peptide_id = analyte_peptide.analyte_peptide_id
      LEFT JOIN uniprot_species on protein.uniprot_accession_id = uniprot_species.uniprot_accession_id
      {$search_sql}

      {$side_bar_filter_where_sql}
      AND protein.approval_status = 1
      GROUP BY analyte_peptide.analyte_peptide_id
      HAVING 1 = 1
      {$side_bar_filter_having_sql}

      ";

        $statement = $this->db->prepare($sql);
        $statement->execute($pdo_params);


        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        /*
        foreach ($data as $key => $value) {
            $uniprot_species = $this->get_uniprot_species($value["uniprot"]);


            if ($uniprot_species) {
                $species = explode(" (", $uniprot_species['species']);
                $data[$key]['protein_species_label'] = "<i>" . $species[0] . "</i> (" . $species[1];
            }

        }
        */


        return $data;
    }


    public function browse_assays_updated(
      $side_bar_filter

    ) {
       $params_to_see = [];
       $pdo_params = array();
       $place_holder = array();
       $filter = array();



        $search_sql = " WHERE 1 = 1 ";
      if (!empty($side_bar_filter)) {
          foreach ($side_bar_filter as $sidebar_key => $single_sidebar_filter) {
              if (!empty($single_sidebar_filter)) {
                  switch ($sidebar_key) {
                    case "cptac_type_filter":
                        $placeholder = array();
                        foreach ($single_sidebar_filter as $single_filter_value) {
                            $placeholder[] = "?";
                            $pdo_params[] = $single_filter_value[0];
                        }
                        //$side_bar_filter_having_array["chromosome_filter"] = " AND protein.chromosome_number IN (" . implode(",", $placeholder) . ") ";
                        //$side_bar_filter_where_array["cptac_type_filter"] = " AND SUBSTRING(protein.cptac_id,1,3)  LIKE '%{$single_filter_value[0]}%'";
                        $side_bar_filter_where_array["cptac_type_filter"] = " AND SUBSTRING(protein.cptac_id,1,1) IN (" . implode(",", $placeholder) . ") ";
                        break;

                        case "certification_filter":
                            $placeholder = array();
                            foreach ($single_sidebar_filter as $single_filter_value) {
                                $placeholder[] = "?";
                                $pdo_params[] = $single_filter_value[0];
                            }
                            //$side_bar_filter_having_array["chromosome_filter"] = " AND protein.chromosome_number IN (" . implode(",", $placeholder) . ") ";
                            //$side_bar_filter_where_array["cptac_type_filter"] = " AND SUBSTRING(protein.cptac_id,1,3)  LIKE '%{$single_filter_value[0]}%'";
                            $side_bar_filter_where_array["certification_filter"] = " AND protein.clia_certification is true";
                            break;

                            case "antibody_filter":
                                $placeholder = array();
                                foreach ($single_sidebar_filter as $single_filter_value) {
                                    $placeholder[] = "?";
                                    $pdo_params[] = $single_filter_value;
                                }
                                $side_bar_filter_where_array["antibody_filter"] = " AND analyte_peptide.cptc_catalog_id IN (" . implode(",", $placeholder) . ") ";
                                break;
                    case "assay_type_filter":
                        $placeholder = array();

                        foreach ($single_sidebar_filter as $single_filter_value) {
                            $placeholder[] = "?";
                            $place_holder[] = "?";
                            $pdo_params[] = $single_filter_value;
                        }
                        $side_bar_filter_where_array["assay_type_filter"] = " AND CONCAT(IFNULL(assay_types.label,''),' ',IFNULL(assay_parameters_new.data_type,'')) IN (".implode(",", $placeholder).") ";
                        break;
                        case "species_filter":
                            $placeholder = array();

                                foreach ($single_sidebar_filter as $single_filter_value) {
                                    $placeholder[] = "?";
                                    $pdo_params[] = $single_filter_value;
                                }
                                foreach ($single_sidebar_filter as $single_filter_value) {
                                    $pdo_params[] = $single_filter_value;
                                }


                                $side_bar_filter_where_array["species_filter"] = " AND ((assay_parameters_new.protein_species_label IN (" . implode(",", $placeholder) . ")
                                      AND  uniprot_species.organism_common is NULL)
                                      OR CONCAT(uniprot_species.organism_scientific,' (',uniprot_species.organism_common,')') IN (" . implode(",", $placeholder) . "))";



                                break;
                                case "peptide_standard_purity_filter":
                                    $placeholder = array();
                                    foreach ($single_sidebar_filter as $single_filter_value) {
                                         $placeholder[] = "?";
                                         $pdo_params[] = $single_filter_value;

                                    }
                                    $side_bar_filter_where_array["peptide_standard_purity"] = " AND assay_parameters_new.peptide_standard_purity_types_id IN (".implode(",", $placeholder).") ";
                                    break;
                                    case "lab_filter":
                                        $placeholder = array();
                                        foreach ($single_sidebar_filter as $single_filter_value) {
                                            $placeholder[] = "?";
                                            $pdo_params[] = $single_filter_value;
                                        }
                                        $side_bar_filter_where_array["lab_filter"] = " AND group.name IN (" . implode(",", $placeholder) . ") ";
                                        break;
                                        case "chromosome_filter":
                                            $placeholder = array();
                                            foreach ($single_sidebar_filter as $single_filter_value) {
                                                $placeholder[] = "?";
                                                $pdo_params[] = $single_filter_value;
                                            }
                                            $side_bar_filter_having_array["chromosome_filter"] = " AND protein.chromosome_number IN (" . implode(",", $placeholder) . ") ";
                                            //$side_bar_filter_where_array["chromosome_filter"] = " AND protein.chromosome_number IN (" . implode(",", $placeholder) . ") ";
                                            break;
                                            case "kegg_pathways_filter":
                                                $placeholder = array();
                                                if ($single_sidebar_filter[0] != NULL) {
                                                    foreach ($single_sidebar_filter as $single_filter_value) {
                                                        //foreach ($single_filter_value as $single_filter_val) {
                                                            $kegg_uniprot_ids = $this->get_uniprot_ids_from_kegg((int)$single_filter_value);
                                                            if ($kegg_uniprot_ids) {
                                                                foreach ($kegg_uniprot_ids as $kegg_uniprot_id) {
                                                                    $placeholder[] = "?";
                                                                    $pdo_params[] = $kegg_uniprot_id["uniprot_accession_id"];
                                                                }
                                                            }
                                                        //}
                                                    }
                                                    $side_bar_filter_having_array["kegg_pathways_filter"] = " AND protein.uniprot_accession_id IN (" . implode(",", $placeholder) . ") ";
                                                }
                                                break;
                                                case "wiki_pathways_filter":
                                                    $placeholder = array();
                                                    if ($single_sidebar_filter[0] != NULL) {
                                                        foreach ($single_sidebar_filter as $single_filter_value) {
                                                            //foreach ($single_filter_value as $single_filter_val) {
                                                                $wiki_uniprot_ids = $this->wikipathway->find_uniprot_accession((int)$single_filter_value);
                                                                if ($wiki_uniprot_ids) {
                                                                    foreach ($wiki_uniprot_ids as $wiki_uniprot_id) {
                                                                        $placeholder[] = "?";
                                                                        $pdo_params[] = $wiki_uniprot_id["uniprot_accession_id"];
                                                                    }
                                                                }
                                                          //  }
                                                        }
                                                        $side_bar_filter_having_array["wiki_pathways_filter"] = " AND protein.uniprot_accession_id IN (" . implode(",", $placeholder) . ") ";
                                                    }
                                                    break;
                                                    case "chromosomal_location_start_filter":
                                                            $single_sidebar_filter_int = (int)$single_sidebar_filter;
                                                            $side_bar_filter_having_array["chromosomal_location_start_filter"] = " AND ( protein.chromosome_start >= {$single_sidebar_filter_int} )";
                                                            break;
                                                        case "chromosomal_location_stop_filter":
                                                            $single_sidebar_filter_int = (int)$single_sidebar_filter;
                                                            $side_bar_filter_having_array["chromosomal_location_stop_filter"] = " AND ( protein.chromosome_stop <= {$single_sidebar_filter_int} ) ";
                                                            break;

                                                            case "protein_interactions_filter":
                                                                $placeholder = array();
                                                                foreach ($single_sidebar_filter as $single_filter_value) {
                                                                    $genes = $this->get_protein_interactions($single_filter_value);
                                                                    foreach ($genes as $gene) {
                                                                        $placeholder[] = "?";
                                                                        $pdo_params[] = $gene;
                                                                    }
                                                                }
                                                                $side_bar_filter_having_array["protein_interactions_filter"] = " AND protein.gene_symbol IN (" . implode(",", $placeholder) . ") ";
                                                                $protein_interactions_select = ' , external_data.protein_interactions_biogrid.official_symbol_interactor_a ';
                                                                $protein_interactions_join = ' LEFT JOIN external_data.protein_interactions_biogrid ON external_data.protein_interactions_biogrid.official_symbol_interactor_a = protein.gene_symbol ';
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

        $sql = "SELECT SQL_CALC_FOUND_ROWS
      protein.protein_id as manage
      {$protein_interactions_select}
      , protein.uniprot_accession_id as kegg_uniprot_accession_id
      , protein.protein_id
      , protein.cptac_id
      , protein.gene_symbol as gene_symbol
      , CONCAT(protein.gene_symbol,' - UniProt Accession ID: ',protein.uniprot_accession_id) as gene
      , protein.chromosome_number
      , protein.chromosome_start
      , protein.chromosome_stop
      , protein.multiplex_panel_id
      , protein.uniprot_accession_id as uniprot
      , protein.uniprot_protein_name
      , protein.protein_molecular_weight as protein_molecular_weight
      , protein.homology
      , protein.uniprot_gene_synonym
      /*, assay_parameters_new.protein_species_label as protein_species_label */
      , CONCAT('<i>',SUBSTRING_INDEX(assay_parameters_new.protein_species_label,' ',(LENGTH(assay_parameters_new.protein_species_label)-LENGTH(REPLACE(assay_parameters_new.protein_species_label,' ',''))+1)-1),'</i>',' ',SUBSTRING_INDEX(assay_parameters_new.protein_species_label,' ',-1)) as protein_species_label
      , analyte_peptide.peptide_sequence as peptide_sequence
      , analyte_peptide.peptide_start as peptide_start
      , analyte_peptide.peptide_end as peptide_end
      , analyte_peptide.modification_type as modification
      , analyte_peptide.cptc_catalog_id
      , (CASE WHEN analyte_peptide.site_of_modification_protein IS NULL THEN CONCAT('N/A') ELSE analyte_peptide.site_of_modification_protein END) AS site_of_modification_protein
      , CONCAT(assay_types.label,' ',assay_parameters_new.data_type) as assay_type
      , uniprot_species.uniprot_accession_id
      , uniprot_species.organism_common
      , assay_parameters_new.data_type
      , assay_parameters_new.peptide_standard_purity_types_id
      , peptide_standard_purity_types.type as peptide_standard_purity
      , peptide_standard_purity_types.peptide_standard_purity_types_id
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
      JOIN protein on analyte_peptide.protein_id = protein.protein_id
      JOIN import_log ON import_log.import_log_id = protein.import_log_id
      JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
      LEFT JOIN peptide_standard_purity_types ON peptide_standard_purity_types.peptide_standard_purity_types_id = assay_parameters_new.peptide_standard_purity_types_id
      LEFT JOIN multiplex_panel on multiplex_panel.multiplex_panel_id = protein.multiplex_panel_id
      LEFT JOIN assay_types on assay_types.assay_types_id = assay_parameters_new.assay_types_id
      {$protein_interactions_join}
      LEFT JOIN kegg_uniprot_map on kegg_uniprot_map.uniprot_accession_id = protein.uniprot_accession_id
      -- LEFT JOIN lod_loq_comparison on lod_loq_comparison.analyte_peptide_id = analyte_peptide.analyte_peptide_id
      LEFT JOIN `group` ON import_log.laboratory_id = group.group_id
      LEFT JOIN panorama_validation_sample_data on panorama_validation_sample_data.analyte_peptide_id = analyte_peptide.analyte_peptide_id
      LEFT JOIN uniprot_species on protein.uniprot_accession_id = uniprot_species.uniprot_accession_id

      {$search_sql}

      {$side_bar_filter_where_sql}
      AND protein.approval_status = 1
      GROUP BY analyte_peptide.analyte_peptide_id
      HAVING 1 = 1
      {$side_bar_filter_having_sql}
      {$sort}
      ";

        $statement = $this->db->prepare($sql);
        $statement->execute($pdo_params);


        $data['data'] = $statement->fetchAll(PDO::FETCH_ASSOC);



        foreach ($data["data"] as $key => $value) {
            $uniprot_species = $this->get_uniprot_species($value["uniprot"]);


            if ($uniprot_species) {
                $species = explode(" (", $uniprot_species['species']);
                $data["data"][$key]['protein_species_label'] = "<i>" . $species[0] . "</i> (" . $species[1];
            }

        }


        return $data;
    }

    /*
     * to be a successful api call, it requires the following values to be present:
     * HGNC gene ID
     * primary gene symbol
     * uniprot ac
     * source_taxon_id
     * protein name
     */
   public function get_assay_by_uniprot_api($import_log_id = false, $uniprot_id = false, $uniprot_api_url, $xml2array, $uniprot_regions_array, $peptide_sequence = false, $PeptideGroupId_Description = false, $PeptideGroupId_Label = false) {

        $data = false;

        // If the UniProt ID is missing, send an email to the super admin and kill the import.
        if (!$uniprot_id) {

            // // Send an email to the super admin.
            // $email_body = 'Date: '.date('F j, Y h:i:s A')."\n\n".
            //   'UniProt ID Missing'."\n\n".
            //   'Sequence: '.$peptide_sequence."\n\n".
            //   'PeptideGroupId/Description: '.$PeptideGroupId_Description."\n\n".
            //   'PeptideGroupId/Label: '.$PeptideGroupId_Label."\n\n".
            //   'Go look at preg_match() functions in: /assays_import/controllers/import_panorama_protein_peptide.php'."\n\n".
            //   'Labeled with the comment: "// Get the UniProt ID"';
            // mail($this->final_global_template_vars["superadmin_email_address"],'CPTAC Import: UniProt ID Missing - '.date('F j, Y h:i:s A'),$email_body);
            // // Kill the import.
            // die();

            // If the UniProt ID is missing, log it in the database.
            $this->log_missing_uniprot_ids(
                $import_log_id
                , $peptide_sequence
                , $PeptideGroupId_Description
                , $PeptideGroupId_Label
            );

        } else {

            // Check to see if UniProt is responding with "HTTP/1.1 200 OK" or "HTTP/1.1 301 Moved Permanently".
            $uniprot_response = $this->checkUrl($uniprot_api_url . $uniprot_id . ".xml");

            /*
            if (($uniprot_response["headers"][0] != "HTTP/1.1 200 OK") && ($uniprot_response["headers"][0] != "HTTP/1.1 301 Moved Permanently")) {
                // Send an email to the super admin.
                mail(
                    $this->final_global_template_vars["superadmin_email_address"]
                    , "CPTAC Import: UniProt not responding, " . date('F j, Y h:i:s A')
                    , "Date: " . date('l F jS, Y h:i:s A') . "\n\nURL: " . $uniprot_api_url . $uniprot_id . ".xml" . "\n\ncURL Error Message: " . $uniprot_response["curl_error"]
                    , 'From: CPTAC Assay Portal <noreply@' . $_SERVER['SERVER_NAME'] . '>' . "\r\n"
                );
                // Kill the import script.
                //die("UniProt is not responding.");
            }
            */

            if (!empty($uniprot_response["content"])) {

                $uniprot_array = $xml2array->createArray($uniprot_response["content"]);
                if (!empty($uniprot_array)
                    && !empty($uniprot_array["uniprot"])
                    && !empty($uniprot_array["uniprot"]["entry"])
                ) {

                    $index = isset($uniprot_array["uniprot"]["entry"]["feature"])
                        ? $uniprot_array["uniprot"]["entry"]["feature"]
                        : $uniprot_array["uniprot"]["entry"];

                    // Get the Splice Junctions data
                    $data["splice_junctions"] = false;
                    for ($i = 0; $i < count($index); $i++) {
                        if (isset($index[$i]["@attributes"]["type"])
                            && in_array($index[$i]["@attributes"]["type"], $uniprot_regions_array)
                        ) {
                            $data["splice_junctions"][$i]["start"] = $index[$i]["location"]["begin"]["@attributes"]["position"];
                            $data["splice_junctions"][$i]["stop"] = $index[$i]["location"]["end"]["@attributes"]["position"];
                            $data["splice_junctions"][$i]["type"] = $index[$i]["@attributes"]["type"];
                            $data["splice_junctions"][$i]["description"] = isset($index[$i]["@attributes"]["description"])
                                ? $index[$i]["@attributes"]["description"] : false;
                            $data["splice_junctions"][$i]["status"] = isset($index[$i]["@attributes"]["status"])
                                ? $index[$i]["@attributes"]["status"] : false;
                        }
                    }
                    // Reindex the array
                    if ($data["splice_junctions"]) {
                        $data["splice_junctions"] = array_values($data["splice_junctions"]);
                    }

                    // Get the SNPs data
                    $data["snps"] = false;
                    for ($i = 0; $i < count($index); $i++) {
                        if (isset($index[$i]["location"]["position"]["@attributes"]["position"])) {
                            if ($index[$i]["@attributes"]["type"] == "sequence variant") {
                                $data["snps"][$i]["position"] = $index[$i]["location"]["position"]["@attributes"]["position"];
                                $data["snps"][$i]["original"] = isset($index[$i]["original"]) ? $index[$i]["original"] : "";
                                $data["snps"][$i]["variation"] = isset($index[$i]["variation"]) ? $index[$i]["variation"] : "";
                            }
                        }
                    }
                    // Reindex the array
                    if ($data["snps"]) {
                        $data["snps"] = array_values($data["snps"]);
                    }


                    // Get the isoforms data
                    $data['isoforms'] = false;
                    if (isset($uniprot_array["uniprot"]["entry"]["comment"])) {
                        for ($i = 0; $i < count($uniprot_array["uniprot"]["entry"]["comment"]); $i++) {
                            if (isset($uniprot_array["uniprot"]["entry"]["comment"][$i]['isoform'])) {
                                $data['isoforms'] = $uniprot_array["uniprot"]["entry"]["comment"][$i]['isoform'];
                            }
                        }
                    }
                    // Get and parse each fasta file and then get the sequence and length of each isoform sequence
                    if ($data['isoforms'] && !empty($data['isoforms'])) {
                        $i = 0;
                        foreach ($data['isoforms'] as $isoform) {
                            if ($isoform['id'] && !empty($isoform['id']) && !is_array($isoform['id'])) {

                                // Create a stream
                                $opts = array(
                                    'http' => array(
                                        'method' => "GET",
                                        'header' => "User-Agent:assays.cancer.gov/1.0\r\n",
                                        'timeout' => 10
                                    )
                                );
                                $context = stream_context_create($opts);

                                $isoform_file = @file_get_contents($uniprot_api_url . $isoform['id'] . ".fasta", false, $context);

                                ##############

                                ###############


                                $isoform_parts = explode("\n", $isoform_file);
                                array_shift($isoform_parts);
                                $isoform_sequence = implode('', $isoform_parts);
                                $isoform_sequence = preg_replace("/[[:cntrl:]]/", "", $isoform_sequence);
                                $data['isoforms'][$i]['sequence'] = $isoform_sequence;
                                $data['isoforms'][$i]['sequence_length'] = strlen($isoform_sequence);
                            }
                            $i++;
                        }
                        // Remove the first 'canonical' isoform
                        array_shift($data['isoforms']);
                    }

                    // Parse out the gene symbol
                    $gene_name_data = !empty($uniprot_array["uniprot"]["entry"]["gene"]["name"])
                        ? $uniprot_array["uniprot"]["entry"]["gene"]["name"]
                        : false;
                    if (is_array($gene_name_data) && empty($gene_name_data[0])) {
                        $gene_name_data = array($gene_name_data); //freakin uniprot returning differently formatted data
                    }

                    $data["gene_synonym"] = array();
                    if ($gene_name_data) {
                        foreach ($gene_name_data as $single_gene_data) {
                            if (!empty($single_gene_data["@attributes"]) && !empty($single_gene_data["@attributes"]["type"]) && !empty($single_gene_data["@value"])) {
                                if ($single_gene_data["@attributes"]["type"] == "primary") {
                                    $data["gene_symbol"] = $single_gene_data["@value"];
                                } elseif ($single_gene_data["@attributes"]["type"] == "synonym") {
                                    $data["gene_synonym"][] = $single_gene_data["@value"];
                                }
                            }
                        }
                    }

                    if (empty($data["gene_symbol"])) {
                        $data["gene_symbol"] = false;
                    }

                    /*
                    // Parse out gene id
                    foreach ($uniprot_array["uniprot"]["entry"]["dbReference"] as $single_property) {
                        if (!empty($single_property["@attributes"]) && !empty($single_property["@attributes"]["type"]) && !empty($single_property["@attributes"]["id"])) {
                            if ($single_property["@attributes"]["type"] == "HGNC") {
                                $gene_id_array = explode(":", $single_property["@attributes"]["id"]);
                                $data["hgnc_gene_id"] = (!empty($gene_id_array[1])) ? $gene_id_array[1] : false;
                            }
                        }
                    }
                    if (empty($data["hgnc_gene_id"])) {
                        //return false;
                    }
                    */

                    foreach ($uniprot_array["uniprot"]["entry"]["dbReference"] as $single_property) {
                        if (!empty($single_property["@attributes"]) && !empty($single_property["@attributes"]["type"]) && !empty($single_property["@attributes"]["id"])) {

                            if ($single_property["@attributes"]["type"] == "GeneID") {
                                $data["gene_id"] = $single_property["@attributes"]["id"];
                            }
                        }
                    }
                    if (empty($data["gene_id"])) {
                        $data["gene_id"] = false;
                    }

                    //get species and format for querying ncbi
                    if ($uniprot_array["uniprot"]["entry"]["organism"]["name"]) {
                        foreach($uniprot_array["uniprot"]["entry"]["organism"]["name"] as $name) {
                            if ($name["@attributes"]["type"] == "scientific") {
                                //$data["uniprot_species"] = str_replace(" ", "+", $name["@value"]);
                                $data["uniprot_species"] = $name["@value"];
                            }
                        }
                    }

                    if (empty($data["uniprot_species"])) {
                        $data["uniprot_species"] = false;
                    }


                    $data["uniprot_ac"] = $uniprot_id;
                    $data["uniprot_kb"] = isset($uniprot_array["uniprot"]["entry"]["name"])
                        ? $uniprot_array["uniprot"]["entry"]["name"] : "";

                    // So many ways UniProt returns the protein's full name, will we ever get to the bottom of it?
                    if (isset($uniprot_array["uniprot"]["entry"]["protein"]["recommendedName"]["fullName"])) {
                        $data["protein_name"] = $uniprot_array["uniprot"]["entry"]["protein"]["recommendedName"]["fullName"];
                    }
                    if (isset($uniprot_array["uniprot"]["entry"]["protein"]["submittedName"]["fullName"]) &&
                        !is_array($uniprot_array["uniprot"]["entry"]["protein"]["submittedName"]["fullName"])
                    ) {
                        $data["protein_name"] = $uniprot_array["uniprot"]["entry"]["protein"]["submittedName"]["fullName"];
                    }
                    if (isset($uniprot_array["uniprot"]["entry"]["protein"]["submittedName"]["fullName"]) &&
                        is_array($uniprot_array["uniprot"]["entry"]["protein"]["submittedName"]["fullName"])
                    ) {
                        $data["protein_name"] = $uniprot_array["uniprot"]["entry"]["protein"]["submittedName"]["fullName"]["@value"];
                    }

                    if ($uniprot_array["uniprot"]["entry"]["organism"]["dbReference"]["@attributes"]["type"] == "NCBI Taxonomy") {
                        $data["source_taxon_id"] = $uniprot_array["uniprot"]["entry"]["organism"]["dbReference"]["@attributes"]["id"];
                    }
                    if (empty($data["source_taxon_id"])) {
                        //return false;
                    }

                    if (!empty($uniprot_array["uniprot"]["entry"]["sequence"]) && !empty($uniprot_array["uniprot"]["entry"]["sequence"]["@value"])) {
                        $data["sequence"] = $uniprot_array["uniprot"]["entry"]["sequence"]["@value"];
                        $data["sequence_raw"] = preg_replace(array("/\r\n/", "/\n/"), "", $data['sequence']);
                        $data["sequence_length"] = $uniprot_array["uniprot"]["entry"]["sequence"]["@attributes"]["length"];
                        $data["mass"] = $uniprot_array["uniprot"]["entry"]["sequence"]["@attributes"]["mass"];
                        $data["peptide_sequence"] = $peptide_sequence;
                        // In case Panorama data returned no peptide_start and peptide_end,
                        // calculate the start and end of the peptide sequence in relation to the full sequence.
                        if ($peptide_sequence) {
                            $peptide_start = strpos($data["sequence_raw"], $peptide_sequence);
                            $data["peptide_start"] = ($peptide_start + 1);
                            $data["peptide_end"] = $peptide_start + strlen($peptide_sequence);
                        }
                    }
                }
            }
        }
        return $data;
    }


    public function log_missing_uniprot_ids(
        $import_log_id
        , $sequence
        , $PeptideGroupId_Description
        , $PeptideGroupId_Label
    ) {
        $statement = $this->db->prepare("
      INSERT INTO missing_uniprot_ids
        (import_log_id, sequence, PeptideGroupId_Description, PeptideGroupId_Label, created_date )
      VALUES ( :import_log_id, :sequence, :PeptideGroupId_Description, :PeptideGroupId_Label, NOW() )");
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->bindValue(":sequence", $sequence, PDO::PARAM_STR);
        $statement->bindValue(":PeptideGroupId_Description", $PeptideGroupId_Description, PDO::PARAM_STR);
        $statement->bindValue(":PeptideGroupId_Label", $PeptideGroupId_Label, PDO::PARAM_STR);
        $statement->execute();
    }


    public function checkUrl($url) {

        /*
         * 2014-12-04
         * file_get_contents sucks for http requests.
         * Do it the right way, and use cURL.
         * $content = file_get_contents($url, false, $context);
         */

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $response = curl_exec($ch);

        //new code to get uniprot here
        $http_response_array = explode("\n", $response);
        $j = count($http_response_array);
        $full_url = $url;
        $uniprot_base = "https://www.uniprot.org";
        for ($i = 0; $i < $j; $i++) {
            // if we find the Location header strip it and fill the redir var
            if (strpos($http_response_array[$i], "Location:") !== false) {
                $redir = trim(str_replace("Location:", "", $http_response_array[$i]));
                $full_url = $uniprot_base . $redir;
                break;
            }
        }
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch2, CURLOPT_URL, $full_url);
        //curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch2, CURLOPT_VERBOSE, 1);
        curl_setopt($ch2, CURLOPT_HEADER, 1);
        $response2 = curl_exec($ch2);
        $header_size = curl_getinfo($ch2, CURLINFO_HEADER_SIZE);
        $header = substr($response2, 0, $header_size);
        $body = substr($response2, $header_size);
        //end new redirecting uniprot code


        //$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        //$header = substr($response, 0, $header_size);
        //$body = substr($response, $header_size);
        $http_response_header = http_parse_headers($header);

        if ($response === FALSE) {
            return array(
                'headers' => false,
                'content' => false,
                'curl_error' => curl_error($ch)
            );
        } else {
            return array(
                'headers' => $http_response_header,
                'content' => $body,
                'curl_error' => false
            );
        }

    }


    public function get_entrez_gene_data($gene_symbol, $entrez_api_url, $xml2array) {

        // References
        // http://www.ncbi.nlm.nih.gov/gene/2064
        // http://www.ncbi.nlm.nih.gov/books/NBK25500/#chapter1.ESearch

        // Get the gene record
        // http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=gene&term=ERBB2[gene]+AND+Homo+sapiens[Organism]

        // Use the returned id to query for the full info of the target gene
        // http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?db=gene&id=2064&retmode=xml

        $data = false;
        $entrez_gene_id = false;

        $entrez_gene_query = @file_get_contents($entrez_api_url . 'esearch.fcgi?db=gene&term=' . $gene_symbol . '[gene]+AND+Homo+sapiens[Organism]');

        if (!empty($entrez_gene_query)) {
            $entrez_gene_query_array = $xml2array->createArray($entrez_gene_query);
            // If an array of "Id"s is returned, match the returned gene name with the intended local target gene name
            if (!isset($entrez_gene_query_array["eSearchResult"]["IdList"]["Id"]))
                return;

            if (is_array($entrez_gene_query_array["eSearchResult"]["IdList"]["Id"])) {
                foreach ($entrez_gene_query_array["eSearchResult"]["IdList"]["Id"] as $entrez_gene_id) {
                    $gene_data = @file_get_contents($entrez_api_url . 'esummary.fcgi?db=gene&id=' . $entrez_gene_id . '&retmode=xml');
                    if (!empty($gene_data)) {
                        $gene_data_array = $xml2array->createArray($gene_data);
                        if ($gene_data_array) {
                            $returned_gene_symbol = $gene_data_array["eSummaryResult"]["DocSum"]["Item"][0]["@value"];
                            if ($returned_gene_symbol == $gene_symbol) {
                                $entrez_gene_id = $gene_data_array["eSummaryResult"]["DocSum"]["Id"];
                            }
                        }
                    }
                }
            } else {
                // If an array of IDs is not returned, then just use the "Id"
                $entrez_gene_id = $entrez_gene_query_array["eSearchResult"]["IdList"]["Id"];
            }

            $all_gene_data = @file_get_contents($entrez_api_url . 'esummary.fcgi?db=gene&id=' . $entrez_gene_id . '&retmode=xml');

            if (!empty($all_gene_data)) {
                $gene_data_array = $xml2array->createArray($all_gene_data);
                if (!empty($gene_data_array["eSummaryResult"]["DocSum"]["Item"])) {
                    foreach ($gene_data_array["eSummaryResult"]["DocSum"]["Item"] as $item) {
                        // Get gene aliases
                        if ($item["@attributes"]["Name"] == "OtherAliases") {
                            $data["gene_synonyms"] = $item["@value"];
                            $data["entrez_gene_id"] = $entrez_gene_id;
                        }
                    }
                }
            }

        }

        return $data;
    }


    public function formatSequenceHTML($sequence = false, $peptide_sequence_array = false, $assay_data = array()) {
        if (empty($sequence)) {
            return false;
        }
        $increment = 10;
        $columns = 5; // dynamic?
        $char_count = 0;
        $sequence_chunk = array();
        $chunk = array();
        $sequence = chop($sequence);
        $sequence = preg_replace(array("/\r\n/", "/\n/", "/\s\s+/"), "", $sequence);
        $sequence = str_split($sequence);

        // 20150715 lossm
        // create a pop-up menu to support multiple/duplicate sequences
        $seq_array = array();
        foreach ($peptide_sequence_array as $seq)
            $seq_array[] = $seq['peptide_sequence'];
        $sequence_counts = array_count_values($seq_array);


        $pop_up_menu_output = array();
        $close_div = '<button type="button" rel="tooltip" title="Close&nbsp;Details" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>';

        $pop_up_menu = array();

        foreach ($peptide_sequence_array as $start_end) {
            // HACK! ($start_end['start']-1) and ($start_end['end']-1) HACK!
            // Had to do this after CPTAC Working Group noticed that the start and end values were 1 off (less), so...
            // data was modified in the database (increased every value by 1), then red sequences were off

            for ($i = ($start_end['start'] - 1); $i <= ($start_end['end'] - 1); $i++) {
                $sequence[$i] = '<span class="peptide_highlight seq-' . $start_end['peptide_sequence'] . '">' . $sequence[$i] . '</span>';
            }

            if(empty($pop_up_menu)) {
                $pop_up_menu[$start_end['peptide_sequence']][] = $start_end['cptac_id'] . ': View additional <a class="peptide_detail_link" href="/' . $start_end['cptac_id'] . '" target="_blank">' . $start_end['peptide_modified_sequence'] . '</a> data<br/>';
            } else {
                foreach ($pop_up_menu as $key => $value) {
                    if (strpos($start_end['peptide_sequence'], $key) === false) {
                        if (strpos($key, $start_end['peptide_sequence']) === false) {
                            $pop_up_menu[$start_end['peptide_sequence']][] = $start_end['cptac_id'] . ': View additional <a class="peptide_detail_link" href="/' . $start_end['cptac_id'] . '" target="_blank">' . $start_end['peptide_modified_sequence'] . '</a> data<br/>';
                            break;
                        } else {
                            $pop_up_menu[$start_end['peptide_sequence']] = $pop_up_menu[$key];
                            unset($pop_up_menu[$key]);
                            $pop_up_menu[$start_end['peptide_sequence']][] = $start_end['cptac_id'] . ': View additional <a class="peptide_detail_link" href="/' . $start_end['cptac_id'] . '" target="_blank">' . $start_end['peptide_modified_sequence'] . '</a> data<br/>';
                            break;
                        }
                    } else {
                        $pop_up_menu[$key][] = $start_end['cptac_id'] . ': View additional <a class="peptide_detail_link" href="/' . $start_end['cptac_id'] . '" target="_blank">' . $start_end['peptide_modified_sequence'] . '</a> data<br/>';
                    }
                }
            }

        }

        foreach($pop_up_menu as $key => $value) {
            $pop_up_menu_output[$key] = '<div style="display:none;" class="sequence_table_shadow detail-modal all-details-' . $key . '">' . $close_div . implode("\n", $value) . '</div>';;
        }

        $pop_up_menu_output = implode("\n", $pop_up_menu_output);

        ////////////////////////////


        $sequence = array_chunk($sequence, $increment);

        foreach ($sequence as &$a_single_chunk) {
            $a_single_chunk = implode('', $a_single_chunk);
        }

        $sequence = array_chunk($sequence, $columns);

        $table[] = '<table class="sequence">';
        $total = 0;
        $total_count = 0;

        foreach ($sequence as $key => $columns) {
            $table[] = '<tr>';

            foreach ($columns as $c => $count) {
                $char_count = strlen(strip_tags($count));
                $total_count = ($total_count + $char_count);
                $table[] = '<td class="sequence_char_count">' . $total_count . '</td>';
            }

            $table[] = '</tr>';
            $table[] = '<tr>';

            foreach ($columns as $k => $value) {
                $char = str_split($value);
                $total = $total + count($char);
                $table[] = '<td>' . $value . '</td>';
            }
            $table[] = '</tr>';
        }

        $table[] = '</table>';
        $table = implode("\n", $table);

        return $pop_up_menu_output . "\n" . $table;
    }

    public function get_details($assay_id = false, $gene_symbol = false, $uniprot_id = false) {

        $pdo_params = array();
        $assay_id_sql = "";
        $gene_symbol_sql = "";
        $uniprot_id_sql = "";

        // Query by assay id
        if ($assay_id) {
            $assay_id_sql = " AND analyte_peptide.analyte_peptide_id = ? ";
            $pdo_params[] = $assay_id;
        }
        // Query by gene symbol
        if ($gene_symbol) {
            $assay_id_sql = " AND protein.gene_symbol = ? ";
            $pdo_params[] = $gene_symbol;
        }
        // Query by uniprot id
        if ($uniprot_id) {
            $assay_id_sql = " AND protein.uniprot_accession_id = ? ";
            $pdo_params[] = $uniprot_id;
        }

        $statement = $this->db->prepare("SELECT
      protein.protein_id as manage
      , protein.cptac_id as cptac_id
      , protein.gene_symbol as gene
      , protein.uniprot_accession_id as uniprot
      , protein.uniprot_accession_id as uniprot_ac
      , CONCAT('http://www.uniprot.org/uniprot/',protein.uniprot_accession_id ) as uniprot_link
      , protein.chromosome_number
      , protein.chromosome_start
      , protein.chromosome_stop
      , protein.uniprot_gene_synonym
      , protein.uniprot_hgnc_gene_id
      , protein.uniprot_kb
      , protein.uniprot_source_taxon_id
      , protein.uniprot_sequence
      , protein.uniprot_sequence_raw
      , protein.uniprot_sequence_length
      , protein.uniprot_protein_name
      , protein.multiplex_panel_id
      , protein.protein_molecular_weight as protein_molecular_weight
      , protein.approval_status
      , protein.additional_approval_status
      , @status_display := IF(protein.approval_status = 1,'Approved',protein.approval_status) as status_display
      , @status_display := IF(protein.approval_status = 2,'Pending',@status_display) as status_display
      , @status_display := IF(protein.approval_status = 0,'Disapproved',@status_display) as status_display
      , @status_display := IF(protein.approval_status = 3,'Hidden',@status_display) as status_display
      , analyte_peptide.peptide_sequence as peptide_sequence
      , analyte_peptide.peptide_modified_sequence as peptide_modified_sequence
      , analyte_peptide.peptide_start as peptide_start
      , analyte_peptide.peptide_end as peptide_end
      , analyte_peptide.peptide_molecular_weight as peptide_molecular_weight
      , analyte_peptide.modification_type as modification
      , analyte_peptide.cptc_catalog_id as cptc_catalog_id
      , assay_parameters_new.matrix
      , assay_parameters_new.data_type
      , assay_parameters_new.enrichment_method
      , assay_parameters_new.fractionation_approach
      , assay_types.label as assay_type
      FROM analyte_peptide
      LEFT JOIN protein on analyte_peptide.protein_id = protein.protein_id
      LEFT JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
      LEFT JOIN assay_types on assay_types.assay_types_id = assay_parameters_new.assay_types_id
      LEFT JOIN multiplex_panel on multiplex_panel.multiplex_panel_id = protein.multiplex_panel_id
      WHERE 1=1
      AND protein.approval_status <> 3
      {$assay_id_sql}");
        $statement->execute($pdo_params);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function getUniprotAccession($protein_id = false) {
       if ($protein_id) {
            $statement = $this->db->prepare("
                      SELECT DISTINCT
                          protein.uniprot_accession_id as uniprot_ac
                      FROM protein
                      WHERE protein.approval_status <> 3
                      AND protein.protein_id = " . $protein_id
            );

            $statement->execute();

            return $statement->fetchAll(PDO::FETCH_ASSOC);
       }
       return [];
    }

    public function getAllGenes($gene_symbol) {

        $sql = "SELECT
        protein.protein_id as manage
      , protein.cptac_id as cptac_id
      , protein.gene_symbol as gene
      , protein.uniprot_accession_id as uniprot
      , protein.multiplex_panel_id
      , analyte_peptide.analyte_peptide_id
      , analyte_peptide.peptide_sequence
      , analyte_peptide.peptide_modified_sequence
      , analyte_peptide.peptide_start
      , analyte_peptide.peptide_end
      , analyte_peptide.peptide_molecular_weight
      , analyte_peptide.modification_type as modification
      , analyte_peptide.site_of_modification_peptide
      , analyte_peptide.site_of_modification_protein
      , analyte_peptide.panorama_peptide_url
      , analyte_peptide.panorama_protein_url
      , analyte_peptide.peptide_standard_label_type
      , analyte_peptide.cptc_catalog_id as cptc_catalog_id
      , assay_parameters_new.instrument
      , assay_parameters_new.internal_standard
      , peptide_standard_purity_types.type as peptide_standard_purity
      , peptide_standard_purity_types.peptide_standard_purity_types_id
      , assay_parameters_new.lc
      , assay_parameters_new.column_packing
      , assay_parameters_new.column_dimensions
      , assay_parameters_new.flow_rate
      , assay_parameters_new.matrix
      , assay_parameters_new.protein_species_label as species
      , assay_parameters_new.celllysate_path
      , assay_types.label as assay_type
      , group.group_id as laboratories_id
      , group.name as laboratory_name
      , group.abbreviation as laboratory_abbreviation
      , group.primary_contact_name
      , group.primary_contact_email_address
      , group.disclaimer
      , import_log.import_log_id
      FROM analyte_peptide
      JOIN protein on analyte_peptide.protein_id = protein.protein_id AND protein.approval_status NOT IN (0)
      JOIN import_log ON import_log.import_log_id = protein.import_log_id
      JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
      LEFT JOIN peptide_standard_purity_types ON peptide_standard_purity_types.peptide_standard_purity_types_id = assay_parameters_new.peptide_standard_purity_types_id
      JOIN `group` ON import_log.laboratory_id = group.group_id
      LEFT JOIN assay_types on assay_types.assay_types_id = assay_parameters_new.assay_types_id
      WHERE protein.gene_symbol = :gene_symbol

      ORDER BY group.name, analyte_peptide.peptide_sequence ASC";
        $statement = $this->db->prepare($sql);
        $statement->bindValue(":gene_symbol", $gene_symbol, PDO::PARAM_STR);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as $key => $value) {

            $single_assay_publication = $this->getPublicationByAssayId($value['import_log_id'],$value['manage']);

            $single_assay_with_publication = $this->getAssaysWithPublications($value['import_log_id'],$value['manage']);

            if($single_assay_with_publication){
                    $data[$key]['publication'] = $single_assay_with_publication;
            } else {
                if($single_assay_publication[0]['is_deleted'] == 0) {
                    $data[$key]['publication'] = $this->getPublicationByImportLogId($value['import_log_id']);
                }
            }

            $uniprot_species = $this->get_uniprot_species($value["uniprot"]);

            if ($uniprot_species) {
                $data[$key]['species'] = $uniprot_species['species'];
            }
        }

        return $data;
    }

    public function getApprovedGenes($gene_symbol) {

        $sql = "SELECT
        protein.protein_id as manage
      , protein.cptac_id as cptac_id
      , protein.gene_symbol as gene
      , protein.uniprot_accession_id as uniprot
      , protein.multiplex_panel_id
      , analyte_peptide.analyte_peptide_id
      , analyte_peptide.peptide_sequence
      , analyte_peptide.peptide_modified_sequence
      , analyte_peptide.peptide_start
      , analyte_peptide.peptide_end
      , analyte_peptide.peptide_molecular_weight
      , analyte_peptide.modification_type as modification
      , analyte_peptide.site_of_modification_peptide
      , analyte_peptide.site_of_modification_protein
      , analyte_peptide.panorama_peptide_url
      , analyte_peptide.panorama_protein_url
      , analyte_peptide.peptide_standard_label_type
      , analyte_peptide.cptc_catalog_id as cptc_catalog_id
      , assay_parameters_new.instrument
      , assay_parameters_new.internal_standard
      , peptide_standard_purity_types.type as peptide_standard_purity
      , peptide_standard_purity_types.peptide_standard_purity_types_id
      , assay_parameters_new.lc
      , assay_parameters_new.column_packing
      , assay_parameters_new.column_dimensions
      , assay_parameters_new.flow_rate
      , assay_parameters_new.matrix
      , assay_parameters_new.protein_species_label as species
      , assay_parameters_new.celllysate_path
      , assay_types.label as assay_type
      , group.group_id as laboratories_id
      , group.name as laboratory_name
      , group.abbreviation as laboratory_abbreviation
      , group.primary_contact_name
      , group.primary_contact_email_address
      , group.disclaimer
      , import_log.import_log_id
      FROM protein

      JOIN analyte_peptide on analyte_peptide.protein_id = protein.protein_id
      JOIN import_log ON import_log.import_log_id = protein.import_log_id
      JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
      LEFT JOIN peptide_standard_purity_types ON peptide_standard_purity_types.peptide_standard_purity_types_id = assay_parameters_new.peptide_standard_purity_types_id
      JOIN `group` ON import_log.laboratory_id = group.group_id
      LEFT JOIN assay_types on assay_types.assay_types_id = assay_parameters_new.assay_types_id
      WHERE protein.gene_symbol = :gene_symbol
  	  AND protein.approval_status = 1

      ORDER BY group.name, analyte_peptide.peptide_sequence ASC";
        $statement = $this->db->prepare($sql);
        $statement->bindValue(":gene_symbol", $gene_symbol, PDO::PARAM_STR);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as $key => $value) {

            $single_assay_publication = $this->getPublicationByAssayId($value['import_log_id'],$value['manage']);

            $single_assay_with_publication = $this->getAssaysWithPublications($value['import_log_id'],$value['manage']);

            if($single_assay_with_publication){
                    $data[$key]['publication'] = $single_assay_with_publication;
            } else {
                if($single_assay_publication[0]['is_deleted'] == 0) {
                    $data[$key]['publication'] = $this->getPublicationByImportLogId($value['import_log_id']);
                }
            }

            $uniprot_species = $this->get_uniprot_species($value["uniprot"]);

            if ($uniprot_species) {
                $data[$key]['species'] = $uniprot_species['species'];
            }
        }

        return $data;
    }

    public function getApprovedGenesByGeneSymbol($gene_symbol) {

        $sql = "SELECT
        protein.protein_id as manage
      , protein.cptac_id as cptac_id
      , protein.gene_symbol as gene
      , protein.uniprot_accession_id as uniprot
      , protein.uniprot_protein_name
      , analyte_peptide.analyte_peptide_id
      , analyte_peptide.peptide_sequence
      , analyte_peptide.peptide_modified_sequence
      , analyte_peptide.peptide_start
      , analyte_peptide.peptide_end
      , analyte_peptide.peptide_molecular_weight
      , analyte_peptide.modification_type as modification
      , analyte_peptide.site_of_modification_peptide
      , analyte_peptide.site_of_modification_protein
      , analyte_peptide.panorama_peptide_url
      , analyte_peptide.panorama_protein_url
      , analyte_peptide.peptide_standard_label_type
      , analyte_peptide.cptc_catalog_id as cptc_catalog_id
      , assay_parameters_new.instrument
      , assay_parameters_new.internal_standard
      , peptide_standard_purity_types.type as peptide_standard_purity
      , peptide_standard_purity_types.peptide_standard_purity_types_id
      , assay_parameters_new.lc
      , assay_parameters_new.column_packing
      , assay_parameters_new.column_dimensions
      , assay_parameters_new.flow_rate
      , assay_parameters_new.matrix
      , assay_parameters_new.protein_species_label as species
      , assay_parameters_new.celllysate_path
      , assay_types.label as assay_type
      , group.group_id as laboratories_id
      , group.name as laboratory_name
      , group.abbreviation as laboratory_abbreviation
      , group.primary_contact_name
      , group.primary_contact_email_address
      , group.disclaimer
      , import_log.import_log_id
      FROM protein

      JOIN analyte_peptide on analyte_peptide.protein_id = protein.protein_id
      JOIN import_log ON import_log.import_log_id = protein.import_log_id
      JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
      LEFT JOIN peptide_standard_purity_types ON peptide_standard_purity_types.peptide_standard_purity_types_id = assay_parameters_new.peptide_standard_purity_types_id
      JOIN `group` ON import_log.laboratory_id = group.group_id
      LEFT JOIN assay_types on assay_types.assay_types_id = assay_parameters_new.assay_types_id
      WHERE protein.gene_symbol = :gene_symbol
  	  AND protein.approval_status = 1

      ORDER BY group.name, analyte_peptide.peptide_sequence ASC";
        $statement = $this->db->prepare($sql);
        $statement->bindValue(":gene_symbol", $gene_symbol, PDO::PARAM_STR);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    public function getPublicationByImportLogId($import_log_id = false) {

        $sql = "
      SELECT
          publications.publication_citation
        , publications.publication_url
      FROM publications

      WHERE publications.import_log_id = :import_log_id
      and publications.publications_id not in (select publications_id from assays_with_publications)
      and publications.is_deleted = 0;
      ";


        $statement = $this->db->prepare($sql);
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_STR);
        $statement->execute();
        $publication_data = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $publication_data;
    }

    public function getPublicationByAssayId($import_log_id = false,$protein_id = false) {
        $sql = "
                  SELECT
                      publications.publication_citation
                    , publications.publication_url
                    , publications.is_deleted
                  FROM publications

                  WHERE publications.import_log_id = :import_log_id and publications.protein_id = :protein_id";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_STR);
        $statement->bindValue(":protein_id", $protein_id, PDO::PARAM_INT);
        $statement->execute();
        $publication_data = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $publication_data;
    }

    public function getAssaysWithPublications($import_log_id = false,$protein_id = false) {
        $sql = "
                  select p.publication_citation, p.publication_url, p.is_deleted
                  from publications p
                  join assays_with_publications ap on ap.publications_id = p.publications_id
                  WHERE ap.import_log_id = :import_log_id and ap.protein_id = :protein_id";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_STR);
        $statement->bindValue(":protein_id", $protein_id, PDO::PARAM_INT);
        $statement->execute();
        $publication_data = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $publication_data;
    }

    public function getAllPeptideSequences($gene_symbol = false) {

        $all_data = array();

        $statement = $this->db->prepare("
      SELECT group_closure_table.descendant as laboratories_id
      FROM group_closure_table
      LEFT JOIN `group` ON group.group_id = group_closure_table.descendant
      WHERE group_closure_table.ancestor = 6
      AND group_closure_table.pathlength = 1
    ");
        $statement->execute();
        $laboratories = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($laboratories as $laboratory) {
            $statement = $this->db->prepare("SELECT
          protein.protein_id
          ,analyte_peptide.peptide_modified_sequence as peptide_sequence
          ,analyte_peptide.peptide_sequence as orig_peptide_sequence
          ,analyte_peptide.peptide_start
          ,analyte_peptide.peptide_end
          ,group.name as submitting_laboratory
          FROM analyte_peptide
          LEFT JOIN protein on analyte_peptide.protein_id = protein.protein_id AND protein.approval_status NOT IN (0)
          LEFT JOIN import_log ON import_log.import_log_id = protein.import_log_id
          LEFT JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
          LEFT JOIN `group` ON import_log.laboratory_id = group.group_id
          WHERE protein.gene_symbol = :gene_symbol
          AND group.group_id = :laboratory_id
        ");
            $statement->bindValue(":gene_symbol", $gene_symbol, PDO::PARAM_STR);
            $statement->bindValue(":laboratory_id", $laboratory['laboratories_id'], PDO::PARAM_STR);
            $statement->execute();
            $data[] = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        $results = array();
        foreach ($data as $key => $value) {
            if (!empty($value)) {
                foreach ($value as $k => $v) {
                    $results[$v['orig_peptide_sequence']][] = $v['peptide_sequence'];
                }
            }
        }

        $data['total_sequences'] = count($results);

        return $data;
    }

    public function getApprovedPeptideSequences($gene_symbol = false) {

        $all_data = array();

        $statement = $this->db->prepare("
      SELECT group_closure_table.descendant as laboratories_id
      FROM group_closure_table
      LEFT JOIN `group` ON group.group_id = group_closure_table.descendant
      WHERE group_closure_table.ancestor = 6
      AND group_closure_table.pathlength = 1
    ");
        $statement->execute();
        $laboratories = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($laboratories as $laboratory) {
            $statement = $this->db->prepare("SELECT
          protein.protein_id
          ,analyte_peptide.peptide_modified_sequence as peptide_sequence
          ,analyte_peptide.peptide_sequence as orig_peptide_sequence
          ,analyte_peptide.peptide_start
          ,analyte_peptide.peptide_end
          ,group.name as submitting_laboratory
          FROM analyte_peptide
          JOIN protein on analyte_peptide.protein_id = protein.protein_id
          JOIN import_log ON import_log.import_log_id = protein.import_log_id
          JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
          JOIN `group` ON import_log.laboratory_id = group.group_id
          WHERE protein.gene_symbol = :gene_symbol
  		  AND protein.approval_status = 1
          AND group.group_id = :laboratory_id
        ");
            $statement->bindValue(":gene_symbol", $gene_symbol, PDO::PARAM_STR);
            $statement->bindValue(":laboratory_id", $laboratory['laboratories_id'], PDO::PARAM_STR);
            $statement->execute();
            $data[] = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        $results = array();
        foreach ($data as $key => $value) {
            if (!empty($value)) {
                foreach ($value as $k => $v) {
                    $results[$v['orig_peptide_sequence']][] = $v['peptide_sequence'];
                }
            }
        }

        $data['total_sequences'] = count($results);

        return $data;
    }

    public function getProteinSpecies() {
        $statement = $this->db->prepare("
         SELECT DISTINCT protein_species_label,
                          SUBSTRING_INDEX(SUBSTRING_INDEX(protein_species_label, '(', -1), ')', 1) as organism_common,
                          SUBSTRING_INDEX(protein_species_label, '(', 1) as organism_scientific
          FROM assay_parameters_new
          JOIN protein on assay_parameters_new.import_log_id = protein.import_log_id
          WHERE protein.approval_status = 1

          UNION

          SELECT * from (SELECT DISTINCT CONCAT(organism_scientific,\" (\",organism_common,\")\") as protein_species_label,
                          organism_common,
                          organism_scientific
          FROM uniprot_species
          JOIN protein on protein.uniprot_accession_id = uniprot_species.uniprot_accession_id
          WHERE protein.approval_status = 1 ) tbl

          ORDER BY
          CASE organism_common
                when 'Human' then 1
                when 'Mouse' then 2
                when 'Dog' then 3
                when 'Bovine' then 4
                when 'Horse' then 5
                when 'Horseradish' then 6
                else 7
                end
        ");

        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssayTypes() {
        $statement = $this->db->prepare("
          SELECT DISTINCT apn.assay_types_id,apn.data_type,CONCAT(IFNULL(at1.label,''),' ',IFNULL(apn.data_type,'')) as assay_type
               from assay_parameters_new apn
               join assay_types at1 on apn.assay_types_id = at1.assay_types_id
               join protein p on p.import_log_id = apn.import_log_id
               where p.approval_status = 1
               ORDER by assay_type ASC
        ");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

     public function getPeptideStandardPurity() {
        $statement = $this->db->prepare("
            SELECT distinct psp.type,psp.peptide_standard_purity_types_id
                FROM assay_parameters_new apn
                join peptide_standard_purity_types psp on apn.peptide_standard_purity_types_id = psp.peptide_standard_purity_types_id
                join protein p on p.import_log_id = apn.import_log_id
                where p.approval_status = 1
        ");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

     public function getLaboratories()
    {
        $statement = $this->db->prepare("
          select distinct g.name from `group` g
          join import_log i on g.group_id = i.laboratory_id
          join protein p on i.import_log_id = p.import_log_id
          where p.approval_status = 1
        ");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssaysWithAntibodies()
   {
       $statement = $this->db->prepare("
         select distinct cptc_catalog_id from analyte_peptide where cptc_catalog_id is not null
       ");
       $statement->execute();
       return $statement->fetchAll(PDO::FETCH_ASSOC);
   }


    public function get_all_genes() {
        $statement = $this->db->prepare("
      SELECT DISTINCT(gene_symbol), uniprot_accession_id
      FROM protein
    ");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_uniprot_ids_from_kegg($kegg_id = false) {
        $data = false;
        $statement = $this->db->prepare("
                          SELECT kegg_uniprot_map.uniprot_accession_id
                          FROM kegg
                          JOIN kegg_uniprot_map on kegg_uniprot_map.kegg_id = kegg.real_kegg_id
                          WHERE kegg.kegg_id = :kegg_id
                          ");
        $statement->bindValue(":kegg_id", $kegg_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    public function getKeggEnabledSearchKeywords($flat_kegg_hierarchy = false) {
        foreach($flat_kegg_hierarchy as $key=>$value) {
            $uniprot_ids = $this->get_uniprot_ids_from_kegg($value["kegg_id"]);

            if ($uniprot_ids) {
                foreach($uniprot_ids as $uniprot_id) {
                    $sql = "SELECT count(protein_id) as count
                        FROM protein
                        WHERE protein.uniprot_accession_id = :uniprot_accession_id";

                    $statement = $this->db->prepare($sql);
                    $statement->bindValue(":uniprot_accession_id", $uniprot_id["uniprot_accession_id"], PDO::PARAM_STR);
                    $statement->execute();
                    $count = $statement->fetch(PDO::FETCH_ASSOC);

                    if ((int)$count["count"] > 0) {
                        $flat_kegg_hierarchy[$key]["enabled"] = true;
                        break;
                    }
                }
            }
        }

        foreach ($flat_kegg_hierarchy as $key=>$value) {
            if(!isset($value["enabled"]) || $value["enabled"] == false)
            {
                if (strlen($value['indent']) > 2) {
                    unset($flat_kegg_hierarchy[$key]);
                }
            }
        }

        $flat_kegg_hierarchy = array_values($flat_kegg_hierarchy);

        $i = 0;

        while ($i < sizeof($flat_kegg_hierarchy)) {
            if ($flat_kegg_hierarchy[$i]['indent'] == '--') {
                if ($flat_kegg_hierarchy[$i + 1]['indent'] != '----') {
                    $flat_kegg_hierarchy[$i]['enabled'] = 0;
                    //print_r($data[$i]);
                }
            }
            $i++;
        }

        foreach ($flat_kegg_hierarchy as $key => $value) {
            if (isset($value["enabled"]) && $value["enabled"] == 0) {
                if (strlen($value['indent']) == 2) {
                    unset($flat_kegg_hierarchy[$key]);
                }
            }
        }

        $flat_kegg_hierarchy = array_values($flat_kegg_hierarchy);

        $i = 0;
        while ($i < sizeof($flat_kegg_hierarchy)) {
            if (!array_key_exists('indent', $flat_kegg_hierarchy[$i])) {
                if (!array_key_exists('indent', $flat_kegg_hierarchy[$i + 1])) {
                    $flat_kegg_hierarchy[$i]['enabled'] = 0;
                    //print_r($data[$i]);
                }
            }
            $i++;
        }

        foreach ($flat_kegg_hierarchy as $key => $value) {
            if (isset($value["enabled"]) && $value["enabled"] == 0) {
                unset($flat_kegg_hierarchy[$key]);
            }
        }


        return array_values($flat_kegg_hierarchy);
    }

    public function get_protein_interactions($gene_symbol = false) {
        $data = array();
        if ($gene_symbol) {
            $statement = $this->db->prepare("
        SELECT DISTINCT(official_symbol_interactor_b)
        FROM external_data.protein_interactions_biogrid
        WHERE official_symbol_interactor_a = :gene_symbol
      ");
            $statement->bindValue(":gene_symbol", $gene_symbol, PDO::PARAM_STR);
            $statement->execute();
            $genes = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($genes as $gene) {
                $data[] = $gene['official_symbol_interactor_b'];
            }
        }
        return $data;
    }

    public function getChromosomeNumbers() {
        $data = range(1, 22);
        $data[] = 'X';
        $data[] = 'Y';

        return $data;
    }

    public function curl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public function scrape_between($data, $start, $end) {
        $data = stristr($data, $start); // Stripping all data from before $start
        $data = substr($data, strlen($start));  // Stripping $start
        $stop = stripos($data, $end);   // Getting the position of the $end of the data to scrape
        $data = substr($data, 0, $stop);    // Stripping all data from after and including the $end of the data to scrape
        return $data;   // Returning the scraped data from the function
    }

    public function insert_google_analytics($total_visits) {
        $statement = $this->db->prepare("
      INSERT INTO google_analytics_data
        (total_visits)
      VALUES ( :total_visits )");
        $statement->bindValue(":total_visits", $total_visits, PDO::PARAM_INT);
        $statement->execute();
    }

    public function getDistinctPeptideSequences() {

        $statement = $this->db->prepare("
      SELECT DISTINCT(peptide_sequence)
      FROM analyte_peptide
    ");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);

    }

    /*
     * For panorama imports. Break-out into an import-based class?
     *
     */

    public function getPeptideSequences($import_log_id = false) {

        //  -- ,analyte_peptide.peptide_sequence

        $statement = $this->db->prepare("
      SELECT
         protein.cptac_id
        ,analyte_peptide.analyte_peptide_id
        ,analyte_peptide.peptide_modified_sequence AS peptide_sequence
        ,analyte_peptide.peptide_modified_sequence
        ,group.group_id as laboratories_id
        ,group.name as laboratory_name
        ,group.abbreviation as laboratory_abbreviation
        ,assay_parameters_new.celllysate_path
        ,import_log.import_log_id
      FROM analyte_peptide
      LEFT JOIN protein ON protein.protein_id = analyte_peptide.protein_id
      LEFT JOIN import_log ON import_log.import_log_id = protein.import_log_id
      LEFT JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
      LEFT JOIN `group` ON import_log.laboratory_id = group.group_id
      WHERE import_log.import_log_id = :import_log_id
      GROUP BY analyte_peptide.analyte_peptide_id
    ");
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);

    }

    public function getPeptideSequenceBySequence($peptide_sequence = false) {

        $statement = $this->db->prepare("
      SELECT
        analyte_peptide.analyte_peptide_id
        ,analyte_peptide.peptide_modified_sequence AS peptide_sequence
        ,analyte_peptide.peptide_modified_sequence
        ,group.group_id as laboratories_id
        ,group.name as laboratory_name
        ,group.abbreviation as laboratory_abbreviation
        ,assay_parameters_new.celllysate_path
      FROM analyte_peptide
      LEFT JOIN protein ON protein.protein_id = analyte_peptide.protein_id
      LEFT JOIN import_log ON import_log.import_log_id = protein.import_log_id
      LEFT JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
      LEFT JOIN `group` ON import_log.laboratory_id = group.group_id
      WHERE analyte_peptide.peptide_sequence = :peptide_sequence
    ");
        $statement->bindValue(":peptide_modified_sequence", $peptide_sequence, PDO::PARAM_STR);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);

    }

    /*
     * Get peptide sequense by modified sequence and import log id
     */
    public function getPeptideSequenceByModifiedSequenceAndImportLogId($peptide_modified_sequence = false, $import_log_id = false) {

        $statement = $this->db->prepare("
      SELECT
        analyte_peptide.analyte_peptide_id
        ,analyte_peptide.peptide_modified_sequence AS peptide_sequence
        ,analyte_peptide.peptide_modified_sequence
        ,group.group_id as laboratories_id
        ,group.name as laboratory_name
        ,group.abbreviation as laboratory_abbreviation
        ,assay_parameters_new.celllysate_path
      FROM analyte_peptide
      JOIN protein ON protein.protein_id = analyte_peptide.protein_id
      JOIN import_log ON import_log.import_log_id = protein.import_log_id
      JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
      JOIN `group` ON import_log.laboratory_id = group.group_id
      WHERE analyte_peptide.peptide_modified_sequence = :peptide_modified_sequence
          AND import_log.import_log_id = :import_log_id
    ");
        $statement->bindValue(":peptide_modified_sequence", $peptide_modified_sequence, PDO::PARAM_STR);
        $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_STR);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);

    }

    /*
     * For panorama imports ends.
     *
     */

    public function get_lod_loq_comparison_data($peptide, $laboratory_id, $manage_id = false) {
        $statement = $this->db->prepare("
      SELECT
        blank_low_conc_LOD
        ,blank_low_conc_LOQ
        ,blank_only_LOD
        ,blank_only_LOQ
        ,rsd_limit_LOD
        ,rsd_limit_LOQ
        ,peptide
        ,transition
        ,transition_id
        ,lod_loq_units
      FROM lod_loq_comparison
      LEFT JOIN analyte_peptide ON analyte_peptide.analyte_peptide_id = lod_loq_comparison.analyte_peptide_id
      RIGHT JOIN protein ON protein.protein_id = analyte_peptide.protein_id AND protein.approval_status NOT IN (0)
      WHERE lod_loq_comparison.peptide = :peptide
      AND lod_loq_comparison.laboratory_id = :laboratory_id
      AND protein.protein_id = :manage_id

      ORDER BY transition ASC
    ");
        $statement->bindValue(":peptide", $peptide, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
        $statement->bindValue(":manage_id", $manage_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }


    public function get_response_curves_data($peptide, $laboratory_id, $manage_id = false) {
        $statement = $this->db->prepare("
      SELECT
        peptide
        ,transition_id
        ,ROUND(Slope, 2) as Slope
        ,ROUND(Intercept, 2) as Intercept
        ,ROUND(RSquare, 2) as RSquare
      FROM response_curves_data
      LEFT JOIN analyte_peptide ON analyte_peptide.analyte_peptide_id = response_curves_data.analyte_peptide_id
      WHERE response_curves_data.peptide = :peptide
      AND response_curves_data.laboratory_id = :laboratory_id
      AND analyte_peptide.protein_id = :manage_id
      ORDER BY transition DESC
    ");
        $statement->bindValue(":peptide", $peptide, PDO::PARAM_STR);
        $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
        $statement->bindValue(":manage_id", $manage_id, PDO::PARAM_INT);
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    public function get_chromatogram_images($analyte_peptide_id = false, $laboratory_id = false) {

        $data = false;

        if ($analyte_peptide_id) {
            $statement = $this->db->prepare("
        SELECT sequence
             , file_name
             , import_log_id
        FROM panorama_chromatogram_images
        WHERE analyte_peptide_id = :analyte_peptide_id
        AND laboratory_id = :laboratory_id");
            $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
            $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        return $data;
    }

    public function get_images_by_import_log_id($import_log_id = false, $image_type = "chromatogram_images") {
        $data = false;
        $table_name = "panorama_chromatogram_images";

        switch($image_type) {
            case "validation_sample_images":
                $table_name = "panorama_validation_sample_images";
                break;
            case "response_curve_images":
                $table_name = "panorama_response_curve_images";
                break;

            default:
                $table_name = "panorama_chromatogram_images";
                break;
        }


        if ($import_log_id) {
            $statement = $this->db->prepare("
                SELECT sequence
                     , file_name
                     , import_log_id
                FROM {$table_name}
                WHERE import_log_id = :import_log_id
            ");
            $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        return $data;
    }


    public function get_all_chromatogram_images() {
        $data = false;

        $statement = $this->db->prepare("
            SELECT   panorama_chromatogram_images_id
                   , analyte_peptide_id
                   , laboratory_id
                   , sequence as peptide_sequence
                   , file_name
                   , import_log_id
            FROM panorama_chromatogram_images");

        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    public function get_response_curve_images($sequence = false, $analyte_peptide_id = false, $laboratory_id = false) {

        $data = false;

        if ($sequence && $analyte_peptide_id && $laboratory_id) {
            $statement = $this->db->prepare("
        SELECT sequence
             , file_name
             , import_log_id
        FROM panorama_response_curve_images
        WHERE sequence = :sequence
        AND analyte_peptide_id = :analyte_peptide_id
        AND laboratory_id = :laboratory_id");
            $statement->bindValue(":sequence", $sequence, PDO::PARAM_STR);
            $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
            $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        return $data;
    }

    public function get_all_response_curve_images() {

        $data = false;

        $statement = $this->db->prepare("
        SELECT response_curve_images_id
             , analyte_peptide_id
             , laboratory_id
             , sequence as peptide_sequence
             , file_name
             , import_log_id
        FROM panorama_response_curve_images");
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    public function get_validation_sample_image($sequence = false, $analyte_peptide_id = false, $laboratory_id = false) {

        $data = false;

        if ($sequence) {
            $statement = $this->db->prepare("
        SELECT sequence
             , file_name
             , import_log_id
        FROM panorama_validation_sample_images
        WHERE sequence = :sequence
        AND analyte_peptide_id = :analyte_peptide_id
        AND laboratory_id = :laboratory_id");
            $statement->bindValue(":sequence", $sequence, PDO::PARAM_STR);
            $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
            $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);
        }

        return $data;
    }

    public function get_all_validation_sample_images() {

        $data = false;

        $statement = $this->db->prepare("
        SELECT validation_sample_images_id
             , analyte_peptide_id
             , laboratory_id
             , sequence as peptide_sequence
             , file_name
             , import_log_id
        FROM panorama_validation_sample_images");

        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    }

    public function get_validation_sample_images_data($sequence = false, $analyte_peptide_id = false, $laboratory_id = false, $import_log_id = false, $manage_id = false) {
        $data = false;

        if ($sequence) {
            $statement = $this->db->prepare("
        SELECT
          fragment_ion
          ,low_intra_CV
          ,med_intra_CV
          ,high_intra_CV
          ,low_inter_CV
          ,med_inter_CV
          ,high_inter_CV
          ,low_total_CV
          ,med_total_CV
          ,high_total_CV
          ,low_count
          ,med_count
          ,high_count
          ,protein.protein_id as manage_id
        FROM panorama_validation_sample_data
        LEFT JOIN analyte_peptide ON analyte_peptide.analyte_peptide_id = panorama_validation_sample_data.analyte_peptide_id
        LEFT JOIN protein ON protein.protein_id = analyte_peptide.protein_id
        WHERE panorama_validation_sample_data.sequence = :sequence
        AND panorama_validation_sample_data.analyte_peptide_id = :analyte_peptide_id
        AND panorama_validation_sample_data.laboratory_id = :laboratory_id
        AND panorama_validation_sample_data.import_log_id = :import_log_id");

            $statement->bindValue(":sequence", $sequence, PDO::PARAM_STR);
            $statement->bindValue(":analyte_peptide_id", $analyte_peptide_id, PDO::PARAM_INT);
            $statement->bindValue(":laboratory_id", $laboratory_id, PDO::PARAM_INT);
            $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        }

        return $data;
    }

    public function get_selectivity_images($sequence = false, $analyte_peptide_id = false, $laboratory_id = false) {

        $selectivity_image = false;

        if ($sequence) {
            $selectivity_image = SelectivityImages::where([
                ['peptide_sequence', $sequence],
                ['analyte_peptide_id', $analyte_peptide_id],
                ['laboratory_id', $laboratory_id]
            ])->first();
            if ($selectivity_image) {
                $selectivity_image = $selectivity_image->attributesToArray();
            }
        }

        return $selectivity_image;
    }

    public function get_selectivity_summary_data($sequence = false, $analyte_peptide_id = false, $laboratory_id = false) {
        $selectivity_data = [];

        if ($sequence) {
            $cell_lines = SelectivitySummaryData::where([
                ['peptide_sequence', $sequence],
                ['analyte_peptide_id', $analyte_peptide_id],
                ['laboratory_id', $laboratory_id]
            ])->pluck('fragment_ion', 'cell_line');

            $column_names = ['fragment_ion'];
            foreach ($cell_lines as $key => $value) {
                $column_names[] = $key;
            }

            $selectivity_data[] = $column_names;

            $fragment_ions = SelectivitySummaryData::where([
                ['peptide_sequence', $sequence],
                ['analyte_peptide_id', $analyte_peptide_id],
                ['laboratory_id', $laboratory_id]
            ])->pluck('cell_line', 'fragment_ion');

            foreach ($fragment_ions as $key => $value) {

                $estimated_slope = SelectivitySummaryData::where([
                    ['peptide_sequence', $sequence],
                    ['analyte_peptide_id', $analyte_peptide_id],
                    ['laboratory_id', $laboratory_id]
                ])->where('fragment_ion' , '=', $key)->orderBy('cell_line')->pluck('estimated_slope')->all();

                array_unshift($estimated_slope, $key);

                $selectivity_data[] = $estimated_slope;
            }


        }
        return $selectivity_data;
    }

    public function get_selectivity_spike_level_data($sequence = false, $analyte_peptide_id = false, $laboratory_id = false) {
        $selectivity_spike_level_data = [];

        if ($sequence) {
            $cell_lines = SelectivitySpikeLevelData::where([
                ['peptide_sequence', $sequence],
                ['analyte_peptide_id', $analyte_peptide_id],
                ['laboratory_id', $laboratory_id]
            ])->pluck('fragment_ion', 'cell_line');

            $column_names = ['fragment_ion'];
            foreach ($cell_lines as $key => $value) {
                $column_names[] = $key;
            }

            $selectivity_spike_level_data[] = $column_names;

            $fragment_ions = SelectivitySpikeLevelData::where([
                ['peptide_sequence', $sequence],
                ['analyte_peptide_id', $analyte_peptide_id],
                ['laboratory_id', $laboratory_id]
            ])->pluck('cell_line', 'fragment_ion');

            foreach ($fragment_ions as $key => $value) {

                $spike_level = SelectivitySpikeLevelData::where([
                    ['peptide_sequence', $sequence],
                    ['analyte_peptide_id', $analyte_peptide_id],
                    ['laboratory_id', $laboratory_id]
                ])->where('fragment_ion' , '=', $key)->orderBy('cell_line')->pluck('spike_level')->all();

                array_unshift($spike_level, $key);

                $selectivity_spike_level_data[] = $spike_level;
            }

        }
        return $selectivity_spike_level_data;
    }

    public function get_stability_images($sequence = false, $analyte_peptide_id = false, $laboratory_id = false) {

        $stability_image = false;

        if ($sequence) {
            $stability_image = StabilityImages::where([
                ['peptide_sequence', $sequence],
                ['analyte_peptide_id', $analyte_peptide_id],
                ['laboratory_id', $laboratory_id]
            ])->first();
            if ($stability_image) {
                $stability_image = $stability_image->attributesToArray();
            }
        }

        return $stability_image;
    }

    public function get_stability_data($sequence = false, $analyte_peptide_id = false, $laboratory_id = false) {
        $stability_data = false;

        if ($sequence) {
            $stability_data_items = StabilityData::where([
                ['peptide_sequence', $sequence],
                ['analyte_peptide_id', $analyte_peptide_id],
                ['laboratory_id', $laboratory_id]
            ])->get();

            if ($stability_data_items) {
                foreach($stability_data_items as $item) {
                    $stability_data[] = $item->attributesToArray();
                }
            }
        }
        return $stability_data;
    }

    public function get_endogenous_images($sequence = false, $analyte_peptide_id = false, $laboratory_id = false) {

        $endogenous_image = false;

        if ($sequence) {
            $endogenous_image = EndogenousImages::where([
                ['peptide_sequence', $sequence],
                ['analyte_peptide_id', $analyte_peptide_id],
                ['laboratory_id', $laboratory_id]
            ])->first();
            if ($endogenous_image) {
                $endogenous_image = $endogenous_image->attributesToArray();
            }
        }

        return $endogenous_image;
    }

    public function get_endogenous_data($sequence = false, $analyte_peptide_id = false, $laboratory_id = false) {
        $endogenous_data = false;

        if ($sequence) {
            $endogenous_data_items = EndogenousData::where([
                ['peptide_sequence', $sequence],
                ['analyte_peptide_id', $analyte_peptide_id],
                ['laboratory_id', $laboratory_id]
            ])->get();

            if ($endogenous_data_items) {
                foreach($endogenous_data_items as $item) {
                    $endogenous_data[] = $item->attributesToArray();
                }

            }
        }

        return $endogenous_data;
    }

    public function get_uniprot_splice_junctions($uniprot_accession_id = false) {

        $data = false;

        if ($uniprot_accession_id) {
            $statement = $this->db->prepare("
        SELECT *
        FROM uniprot_splice_junctions
        WHERE uniprot_accession_id = :uniprot_accession_id");
            $statement->bindValue(":uniprot_accession_id", $uniprot_accession_id, PDO::PARAM_STR);
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        return $data;
    }

    public function get_uniprot_snps($uniprot_accession_id = false) {

        $data = false;

        if ($uniprot_accession_id) {
            $statement = $this->db->prepare("
        SELECT *
        FROM uniprot_snps
        WHERE uniprot_accession_id = :uniprot_accession_id");
            $statement->bindValue(":uniprot_accession_id", $uniprot_accession_id, PDO::PARAM_STR);
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        return $data;
    }

    public function get_uniprot_isoforms($uniprot_accession_id = false) {

        $data = false;

        if ($uniprot_accession_id) {
            $statement = $this->db->prepare("
		SELECT distinct u.uniprot_isoforms_id,u.id, u.sequence_length,ud.sequence_start,ud.sequence_end,ud.isoform_number,udel.deletion,ui.insertion_start as insertion_start, ui.insertion_end
        	FROM uniprot_isoforms u
        	left join uniprot_isoform_sequence_segments ud on ud.isoform_name = u.id
        	left join uniprot_isoform_insertion_segments ui on ui.isoform_name = u.id
        	left join uniprot_isoform_deletion_segments udel on udel.isoform_name = u.id
        	WHERE u.uniprot_accession_id = :uniprot_accession_id order by ud.isoform_number DESC");


            $statement->bindValue(":uniprot_accession_id", $uniprot_accession_id, PDO::PARAM_STR);
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        return $data;
    }

    public function get_uniprot_species($uniprot_accession_id = false) {

        $data = false;

        if ($uniprot_accession_id) {

            $statement = $this->db->prepare("
		    SELECT CONCAT(u.organism_scientific, ' (', u.organism_common , ')') as species
        	FROM uniprot_species u

        	WHERE u.uniprot_accession_id = :uniprot_accession_id
              AND organism_scientific IS NOT NULL");


            $statement->bindValue(":uniprot_accession_id", $uniprot_accession_id, PDO::PARAM_STR);
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);
        }

        return $data;
    }

    public function get_sop_files($import_log_id = false) {

        $data = false;
        if ($import_log_id) {
            $statement = $this->db->prepare("
        SELECT sop_files.sop_files_id, sop_files.file_name, sop_files.internal_file_name, sop_files.file_type, sop_files.file_size
        FROM sop_files
        LEFT JOIN sop_files_join on sop_files_join.sop_files_id = sop_files.sop_files_id
        WHERE sop_files_join.import_log_id = :import_log_id
        AND sop_files.is_deleted = 0");
            $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);

            // Replace the file extension so the file name extension returns as
            // the same file name extension it will be converted to.
            if ($data) {
                $i = 0;
                foreach ($data as $single_data) {
                    //$file_name_without_extension = preg_replace('/\\.[^.\\s]{3,4}$/', '', $single_data["file_name"]);
                    $data[$i]["file_name"] = $single_data["file_name"]; //$file_name_without_extension.".pdf";
                    $i++;
                }
            }

        }
        return $data;
    }

    public function download_file($sop_files_id = false) {
        $data = false;

        if ($sop_files_id) {
            $statement = $this->db->prepare("
        SELECT
        file_name,
        internal_file_name,
        file_type,
        file_size
        FROM sop_files
        WHERE sop_files_id = :sop_files_id
      ");
            $statement->bindValue(":sop_files_id", $sop_files_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);
        }

        return $data;
    }

    public function get_laboratories($laboratories_id = false) {

        $data = false;
        $and = '';

        if ($laboratories_id) {
            $and = "AND group.group_id = " . (int)$laboratories_id;
        }

        $statement = $this->db->prepare("
      SELECT group_closure_table.descendant as laboratories_id
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

    public function get_laboratory_id_by_abbreviation($laboratory_abbreviation = false) {
        $data = false;
        if ($laboratory_abbreviation) {
            $statement = $this->db->prepare("
        SELECT group_closure_table.descendant as laboratories_id
        FROM group_closure_table
        LEFT JOIN `group` ON group.group_id = group_closure_table.descendant
        WHERE group.abbreviation = :laboratory_abbreviation");
            $statement->bindValue(":laboratory_abbreviation", $laboratory_abbreviation, PDO::PARAM_STR);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            $data = $result['laboratories_id'];
        }
        return $data;
    }

    public function get_laboratory_by_import_log_id($import_log_id = false) {
        $data = false;
        if ($import_log_id) {
            $statement = $this->db->prepare("
        SELECT
            import_log.import_log_id
          , group.group_id as laboratory_id
          , group.name as laboratory_name
          , group.abbreviation as laboratory_abbreviation
          , assay_parameters_new.celllysate_path
          , assay_parameters_new.quantification_units
          , assay_parameters_new.template_version
          , assay_parameters_new.experiment_345
        FROM import_log
        LEFT JOIN `group` ON group.group_id = import_log.laboratory_id
        LEFT JOIN assay_parameters_new ON assay_parameters_new.import_log_id = import_log.import_log_id
        WHERE import_log.import_log_id = :import_log_id
      ");
            $statement->bindValue(":import_log_id", $import_log_id, PDO::PARAM_INT);
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);
        }
        return $data;
    }

    public function get_import_log_ids() {
        $statement = $this->db->prepare("
            SELECT
                import_log.import_log_id
            FROM import_log
        ");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_directory_listing_of_directories($base_directory = false, $add_to_blacklist = '') {
        $data = false;
        if ($base_directory) {
            // Loop through the directory
            if ($handle = opendir($base_directory)) {
                $directories = array();
                $blacklist = array('.', '..', '_test', $add_to_blacklist);
                while (false !== ($file = readdir($handle))) {
                    if (!in_array($file, $blacklist)) {
                        $data[] = $file;
                    }
                }
                closedir($handle);
            }
        }
        return $data;
    }

    public function get_hutch_interlab_sequences($base_directory = false, $lod_loq_comparisons_directory_name = false) {

        $data = false;

        if ($base_directory) {
            // Loop through the directory
            if ($handle = opendir($base_directory)) {
                $directories = array();
                $blacklist = array('.', '..', '_test', 'Broad_Carr', 'SNU_KIST_Kim');
                while (false !== ($file = readdir($handle))) {
                    if (!in_array($file, $blacklist)) {
                        $file_data[] = $file;
                    }
                }
                closedir($handle);
            }
        }
        $csv_laboratories_directory = $file_data[0];

        // Get the laboratory_id by the abbreviation (directory)
        $laboratory_id = $this->get_laboratory_id_by_abbreviation($csv_laboratories_directory);

        $csv_files = $this->get_directory_listing_of_interlab_files($base_directory . '/' . $csv_laboratories_directory . '/' . $lod_loq_comparisons_directory_name);

        foreach ($csv_files as $csv_file) {
            $rows = new SplFileObject($base_directory . '/' . $csv_laboratories_directory . '/' . $lod_loq_comparisons_directory_name . '/' . $csv_file);
            $rows->setFlags(SplFileObject::READ_CSV);

            $i = 0;
            foreach ($rows as $row) {
                // Skip the first line, which are the column names
                if ($i > 0) {

                    // Skip empty lines
                    if ($row[0] != NULL) {

                        // Strip the modified sequence of the string --> [+57]
                        $replacements = array();
                        $patterns = array();
                        $replacements[0] = '';
                        $patterns[0] = '/[\[\+\d+\]]/';
                        $data[] = preg_replace($patterns, $replacements, $row[1]);

                    }
                }
                $i++;
                // if($i > 4) break;
            }
        }

        return $data;
    }

    public function get_directory_listing_of_files($directory = false) {
        $data = false;
        if ($directory) {
            // Loop through the directory
            if ($handle = opendir($directory)) {
                $entries = array();
                while (false !== ($file = readdir($handle))) {
                    if (($file != '.') && ($file != '..')) {
                        $data[] = $file;
                    }
                }
                closedir($handle);
            }
        }
        return $data;
    }

    public function get_directory_listing_of_interlab_files($directory = false) {
        $data = false;
        if ($directory) {
            // Loop through the directory
            if ($handle = opendir($directory)) {
                $entries = array();
                while (false !== ($file = readdir($handle))) {
                    if (($file != '.') && ($file != '..')) {
                        if (stristr($file, 'Interlab') !== FALSE) {
                            $data[] = $file;
                        }

                    }
                }
                closedir($handle);
            }
        }
        return $data;
    }

    public function delete_data_from_tables($table_name = false, $field_name = false, $id = false) {

        // Make sure all variables are true before deleting crap
        if ($table_name && $field_name && $id) {

            $statement = $this->db->prepare("SET foreign_key_checks = 0");
            $statement->execute();

            $statement = $this->db->prepare("
        SET foreign_key_checks = 0;
        DELETE FROM `{$table_name}`
        WHERE `{$field_name}` = :id;
        SET foreign_key_checks = 1;");
            $statement->bindValue(":id", $id, PDO::PARAM_INT);
            $statement->execute();

            $statement = $this->db->prepare("SET foreign_key_checks = 1");
            $statement->execute();

        } else {
            die('!!!!!!!!!!!!!!!!!!!!! SOMETHING RETURNED FALSE !!!!!!!!!!!!!!!!!!!!!');
        }

    }

    public function backup_database($number) {
        $user = $this->final_global_template_vars["db_connection"]["user"];
        $pass = $this->final_global_template_vars["db_connection"]["password"];
        $host = $this->final_global_template_vars["db_connection"]["host"];
        $database = $this->final_global_template_vars["db_connection"]["name"];
        $path_and_file_name = $this->final_global_template_vars["database_backup_path"] . date('Ymd_his') . "_" . $database . "_" . $number . ".sql";
        $mysql_dump = "mysqldump --user={$user} --password={$pass} --host={$host} {$database} > {$path_and_file_name}";
        $var = shell_exec($mysql_dump);
    }

    public function set_status_to_do_not_display($file_name) {

        $not_found = array();
        $rows = new SplFileObject('/mnt/webrepo/fr-s-swpg-cpt-d/' . $file_name);
        $rows->setFlags(SplFileObject::READ_CSV);

        $i = 0;
        foreach ($rows as $row) {
            // Skip the first line, which are the column names
            if ($i > 0) {
                // Skip empty lines
                if ($row[0] != NULL) {

                    if ($row[0]) {

                        $statement = $this->db->prepare("
              SELECT analyte_peptide.protein_id
              FROM analyte_peptide
              LEFT JOIN protein on analyte_peptide.protein_id = protein.protein_id
              LEFT JOIN import_log ON import_log.import_log_id = protein.import_log_id
              LEFT JOIN assay_parameters_new on assay_parameters_new.import_log_id = protein.import_log_id
              LEFT JOIN `group` ON import_log.laboratory_id = group.group_id
              WHERE analyte_peptide.peptide_sequence = :peptide_sequence
              -- AND import_log.laboratory_id = :laboratory_id
            ");
                        $statement->bindValue(":peptide_sequence", $row[0], PDO::PARAM_STR);
                        $statement->bindValue(":laboratory_id", $row[2], PDO::PARAM_INT);
                        $statement->execute();
                        $data = $statement->fetch(PDO::FETCH_ASSOC);

                        if ($data) {
                            // Set the approval_status to 3 in the protein table
                            $statement = $this->db->prepare("
                UPDATE protein
                SET approval_status = 3
                WHERE protein.protein_id = " . (int)$data['protein_id']);
                            $statement->execute();
                        } else {
                            // Collect all of the records not found
                            $not_found[] = $row;
                        }

                    }

                }
            }
            $i++;
        }

        // Create a CSV of all assays not found in the database, therefore approval_status not set to 3
        $fp = fopen('/mnt/webrepo/fr-s-swpg-cpt-d/assays_not_found_' . date('Ymd_his') . '.csv', 'w');
        fputcsv($fp, array('cptac_id', 'peptide_sequence', 'gene_symbol', 'laboratory_id', 'redo'));
        foreach ($not_found as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);

    }

    public function get_all_sequences_on_dev() {
        $statement = $this->db->prepare("SELECT
          protein_id
        , peptide_sequence
      FROM analyte_peptide
      GROUP BY peptide_sequence ASC");
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as $single) {
            $data_array[] = $single["peptide_sequence"];
        }
        return $data_array;
    }

    public function get_all_sequences_on_prod() {
        $statement = $this->db->prepare("SELECT
          cptac.analyte_peptide.protein_id
        , cptac.analyte_peptide.peptide_sequence
      FROM cptac.analyte_peptide
      GROUP BY cptac.analyte_peptide.peptide_sequence ASC");
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as $single) {
            $data_array[] = $single["peptide_sequence"];
        }
        return $data_array;
    }

    public function get_protein_ids_for_records_not_in_prod($peptide_sequences = false) {
        $statement = $this->db->prepare("SELECT
          protein_id
      FROM analyte_peptide
      WHERE peptide_sequence IN(" . $peptide_sequences . ")");
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as $single) {
            $data_array[] = $single["protein_id"];
        }
        return $data_array;
    }

    public function getStatisticsData(){
       $statement = $this->db->prepare("
              select count(*) as total_assays, count(distinct ap.peptide_modified_sequence)
              as unique_peptides, count(distinct p.gene_symbol) as unique_proteins,NOW() as time_stamp from protein p
              join analyte_peptide ap on ap.analyte_peptide_id = p.protein_id
              where p.approval_status = 1");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);

    }

    // For Jeff's CSV
    public function get_fields_for_jeffs_csv() {
        $sql_with_cvs = "SELECT SQL_CALC_FOUND_ROWS
        protein.cptac_id
        , protein.gene_symbol
        , analyte_peptide.peptide_sequence
        , protein.approval_status
        , group.abbreviation as laboratory_abbreviation
        , panorama_validation_sample_data.low_intra_CV
        , panorama_validation_sample_data.med_intra_CV
        , panorama_validation_sample_data.high_intra_CV
        , panorama_validation_sample_data.low_inter_CV
        , panorama_validation_sample_data.med_inter_CV
        , panorama_validation_sample_data.high_inter_CV
        , panorama_validation_sample_data.low_total_CV
        , panorama_validation_sample_data.med_total_CV
        , panorama_validation_sample_data.high_total_CV
        FROM analyte_peptide
        LEFT JOIN assay_parameters on assay_parameters.analyte_peptide_id = analyte_peptide.analyte_peptide_id
        LEFT JOIN `group` ON assay_parameters.laboratories_id = group.group_id
        LEFT JOIN panorama_validation_sample_data on panorama_validation_sample_data.analyte_peptide_id = analyte_peptide.analyte_peptide_id
        LEFT JOIN protein on analyte_peptide.protein_id = protein.protein_id";

        $sql_without_cvs = "SELECT SQL_CALC_FOUND_ROWS
        protein.cptac_id
        , protein.gene_symbol
        , analyte_peptide.peptide_sequence
        , protein.approval_status
        , group.abbreviation as laboratory_abbreviation
        FROM analyte_peptide
        LEFT JOIN assay_parameters on assay_parameters.analyte_peptide_id = analyte_peptide.analyte_peptide_id
        LEFT JOIN `group` ON assay_parameters.laboratories_id = group.group_id
        LEFT JOIN protein on analyte_peptide.protein_id = protein.protein_id";
    }


    public function getPrevNextAssay($current_assay = false) {
        $data = array();
        $select_query = "SELECT analyte_peptide.protein_id,
                     protein.cptac_id
        FROM analyte_peptide
        RIGHT JOIN protein on analyte_peptide.protein_id = protein.protein_id ";

        $sql = $select_query .
            "WHERE analyte_peptide.protein_id < :current_assay
            ORDER BY analyte_peptide.protein_id DESC
            LIMIT 1";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(":current_assay", $current_assay, PDO::PARAM_INT);
        $statement->execute();
        $previous = $statement->fetch(PDO::FETCH_ASSOC);
        $data["previous"] = !empty($previous['protein_id']) ? $previous['cptac_id'] : false;

        $sql = $select_query .
            "WHERE analyte_peptide.protein_id > :current_assay
        ORDER BY analyte_peptide.protein_id ASC
        LIMIT 1";

        $statement = $this->db->prepare($sql);
        $statement->bindValue(":current_assay", $current_assay, PDO::PARAM_INT);
        $statement->execute();
        $next = $statement->fetch(PDO::FETCH_ASSOC);
        $data["next"] = !empty($next['protein_id']) ? $next['cptac_id'] : false;

        // go to first record
        if (!$data['next']) {
            $sql = $select_query .
                "WHERE analyte_peptide.protein_id < :current_assay
            ORDER BY analyte_peptide.protein_id ASC
            LIMIT 1";

            $statement = $this->db->prepare($sql);
            $statement->bindValue(":current_assay", $current_assay, PDO::PARAM_INT);
            $statement->execute();
            $next = $statement->fetch(PDO::FETCH_ASSOC);
            $data["next"] = !empty($next['protein_id']) ? $next['cptac_id'] : false;
        }

        // go to last record
        if (!$data['previous']) {
            $sql = $select_query .
                "WHERE analyte_peptide.protein_id > :current_assay
        ORDER BY analyte_peptide.protein_id DESC
        LIMIT 1";

            $statement = $this->db->prepare($sql);
            $statement->bindValue(":current_assay", $current_assay, PDO::PARAM_INT);
            $statement->execute();
            $previous = $statement->fetch(PDO::FETCH_ASSOC);
            $data["previous"] = !empty($previous['protein_id']) ? $previous['cptac_id'] : false;
        }

        return $data;
    }

}
