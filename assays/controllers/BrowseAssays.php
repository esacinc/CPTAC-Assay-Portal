<?php
namespace assays\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use assays\models\Assay;
use assays\models\Kegg;
use assays\models\WikiPathway;
use core\controllers\Controller;

class BrowseAssays extends Controller {

    function browse_assays(Request $request, Response $response, $args = []) {

        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();
        $assay = new Assay($db_resource, $final_global_template_vars["session_key"]);
        $kegg = new Kegg($db_resource, $final_global_template_vars["session_key"]);
        $wikipathway = new WikiPathway($db_resource);

        // Get all protein species for the checkbox filters
        $protein_species = $assay->getProteinSpecies();
        // Get all assay types
        $assay_types = $assay->getAssayTypes();
        $peptide_standard_purity = $assay->getPeptideStandardPurity();
        $labs = $assay->getLaboratories();
        // Get chromosome numbers
        $chromosome_numbers = $assay->getChromosomeNumbers();
        $assays_with_antibodies = $assay->getAssaysWithAntibodies();
        // Get KEGG pathway info
        $kegg_hierarchy = $kegg->get_kegg_hierarchy("--");
        $flat_kegg_hierarchy = $kegg->flatten_kegg_hierarchy($kegg_hierarchy);
        $flat_kegg_hierarchy = $assay->getKeggEnabledSearchKeywords($flat_kegg_hierarchy);

        $pathways = $wikipathway->find_all_wikipathways_with_categories();

        $assay_statistics = $assay->getStatisticsData();

        $view_params = [
            "page_title" => "The CPTAC Assay Portal"
            , "hide_side_nav" => true
            , "site_logo" => $final_global_template_vars['site_logo']
            , "protein_species" => $protein_species
            , "peptide_standard_purity" => $peptide_standard_purity
            , "assay_types" => $assay_types
            , "labs" => $labs
            , "chromosome_numbers" => $chromosome_numbers
            , "kegg_data" => $flat_kegg_hierarchy
            , "wikipathways" => $pathways
            , "assays_with_antibodies" => $assays_with_antibodies
            , "statistics" => $assay_statistics[0]
        ];

        $wp_id = $request->getParam('wp_id');

        if($wp_id) {
            $pathway = $wikipathway->find_wikipathway_id_by_wp_id($wp_id);
            if($pathway) {
                $view_params['wikipathway_id'] = $pathway['wikipathway_id'];
            }
        }

        // Render
        $view = $this->container->get('view');
        $view->render($response, 'browse_assays.php', $view_params);

        return $response;
    }

}
