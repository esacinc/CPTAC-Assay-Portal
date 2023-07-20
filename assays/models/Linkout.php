<?php
/**
 * @desc LinkOut: Class to automatically push Assay Portal and Antibody Portal LinkOut data to Pubmed (via cron).
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */

namespace assays\models;

use \PDO;

class Linkout {
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

    public function get_linkout_assays() {
        $statement = $this->db->prepare("SELECT 
        analyte_peptide.peptide_sequence
      , protein.gene_symbol
      , protein.uniprot_gene_synonym
      , protein.uniprot_protein_name
      , protein.cptac_id as url 
      FROM protein
      LEFT JOIN analyte_peptide ON analyte_peptide.protein_id = protein.protein_id
      WHERE protein.approval_status = 1
      GROUP BY protein.gene_symbol");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_linkout_antibodies() {
        $statement = $this->db->prepare("SELECT 
        cpti_antigens.name
      , cpti_antigens.cptc_name
      , cpti_antigens.keywords_abbreviations
      , cpti_antibodies.cpti_species_id
      , cpti_species.label
      , cpti_antibodies.catalog_number as url 
      FROM cpti_antibodies
      LEFT JOIN cpti_antigens ON cpti_antigens.cpti_antigens_id = cpti_antibodies.cpti_antigens_id
      LEFT JOIN cpti_species ON cpti_species.cpti_species_id = cpti_antibodies.cpti_species_id
      WHERE cpti_antibodies.active = 1
      AND cpti_antigens.active = 1
      GROUP BY cptc_name
      ORDER BY cpti_antibodies.catalog_number ASC");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

}