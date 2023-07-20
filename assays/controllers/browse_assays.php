<?php
function browse_assays() {
    $app = \Slim\Slim::getInstance();
    global $final_global_template_vars;
    require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php";
    require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/kegg.class.php";
    // Yes, we're requiring Wordpress resources here... for the top navigation menu.

    $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
    $db_resource = $db_conn->get_resource();
    $assay = new Assay($db_resource, $final_global_template_vars["session_key"]);
    $kegg = new Kegg($db_resource, $final_global_template_vars["session_key"]);

    // Get all protein species for the checkbox filters
    $protein_species = $assay->getProteinSpecies();
    // Get all assay types
    $assay_types = $assay->getAssayTypes();
    $assay_panels = $assay->getAssayPanels();
    $peptide_standard_purity = $assay->getPeptideStandardPurity();
    $labs = $assay->getLaboratories();
    // Get chromosome numbers
    $chromosome_numbers = $assay->getChromosomeNumbers();
    // Get KEGG pathway info
    $kegg_hierarchy = $kegg->get_kegg_hierarchy("--");
    $flat_kegg_hierarchy = $kegg->flatten_kegg_hierarchy($kegg_hierarchy);
    $flat_kegg_hierarchy = $assay->getKeggEnabledSearchKeywords($flat_kegg_hierarchy);
    // Get statistics info
    $statistics = $assay->getStatisticsData();

    // Render
    $app->render('browse_assays.php', array(
        "page_title" => "The CPTAC Assay Portal"
    , "hide_side_nav" => true
    , "protein_species" => $protein_species
    , "peptide_standard_purity" => $peptide_standard_purity
    , "assay_types" => $assay_types
    , "assay_panels" => $assay_panels
    , "labs" => $labs
    , "chromosome_numbers" => $chromosome_numbers
    , "kegg_data" => $flat_kegg_hierarchy
    , "menu" => $menu
    , "statistics" => $statistics
    ));
}

?>
