<?php
function show_assay_details($assay_type = false) {
    $app = \Slim\Slim::getInstance();
    $env = $app->environment();
    global $final_global_template_vars;
    require_once $final_global_template_vars["absolute_path_to_this_module"] . "/models/assays.class.php";
    require_once $_SERVER["PATH_TO_CORE"] . "slim_framework/SWPG/models/XML2Array.php";

    $xml2array = new \swpg\models\XML2Array();
    $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
    $db_resource = $db_conn->get_resource();

    // Local Portal Data
    $assay = new Assay(
        $db_resource
        , $final_global_template_vars["session_key"]
    );

    // Query by gene symbol, uniprot id, or assay id
    $path_parts = explode('/', $env["PATH_INFO"]);
    array_shift($path_parts);
    $gene_symbol = ($path_parts[0] == 'gene') ? $path_parts[1] : false;
    $uniprot_id = ($path_parts[0] == 'uniprot') ? $path_parts[1] : false;
    $assay_id = (!$gene_symbol && !$uniprot_id) ? $path_parts[0] : false;

    // Extract just the id from the "CPTAC-" appended GET variable
    if ($assay_id) {
        preg_match('!\d+!', $assay_id, $matches);
        $assay_id = $matches[0];
    }


    // Next and Previous
    $nextPrev['prevNext'] = $assay->getPrevNextAssay($assay_id);

    // Get details
    $data = $assay->get_details($assay_id, $gene_symbol, $uniprot_id);

    if ($assay_type . "-" . $assay_id !== $data["cptac_id"]) {
        $app->notFound("The Assay with assay type " . $assay_type . " and assay id " . $assay_id . " is not found");
    }

    $data["preview_header"] = false;

    // If get_details() returns false, throw a 404
    if (!$data)
        $app->notFound();

    // If the record is not approved and there no authenticated session, throw a 404
    if (($data["approval_status"] != 1) && (!isset($_SESSION[$final_global_template_vars["session_key"]])))
        $app->notFound();

    // If the record is not approved and there is an authenticated session, set the preview_header variable to true. Otherwise, set it to false.
    $data["preview_header"] = (($data["approval_status"] != 1) && isset($_SESSION[$final_global_template_vars["session_key"]])) ? true : false;


    // Reformat data from UniProt to match the array heiarchy from when it was an API call
    $data["uniprot_api"] = array(
        "uniprot_ac" => $data["uniprot_ac"]
    , "gene_synonym" => $data["uniprot_gene_synonym"]
    , "gene_symbol" => $data["gene"]
    , "hgnc_gene_id" => $data["uniprot_hgnc_gene_id"]
    , "uniprot_kb" => $data["uniprot_kb"]
    , "source_taxon_id" => $data["uniprot_source_taxon_id"]
    , "sequence" => $data["uniprot_sequence"]
    , "sequence_raw" => $data["uniprot_sequence_raw"]
    , "sequence_length" => $data["uniprot_sequence_length"]
    );

    // Get uniprot_splice_junctions
    $data["uniprot_api"]["splice_junctions"] = $assay->get_uniprot_splice_junctions($data["uniprot"]);
    // Get uniprot_snps
    $data["uniprot_api"]["snps"] = $assay->get_uniprot_snps($data["uniprot"]);
    // Get uniprot_isoforms
    $data["uniprot_api"]["isoforms"] = $assay->get_uniprot_isoforms($data["uniprot"]);


    // Get data from Entrez
    $data["entrez_api"] = $assay->get_entrez_gene_data(
        $data["gene"]
        , $final_global_template_vars["entrez_api_url"]
        , $xml2array
    );


    if ($data["preview_header"]) {
        // Get all genes
        $genes['genes'] = $assay->getAllGenes($data['gene']);
    } else {
        $genes['genes'] = $assay->getApprovedGenes($data['gene']);
    }

    $peptide_sequence_array = array();

    // Create an array of peptide starts and ends to send to the $assay->formatSequenceHTML() method (without 'duplicates')
    foreach ($genes['genes'] as $gene_key => $gene_value) {
        $peptide_sequence_array[] = array('id' => $gene_value['manage'], 'cptac_id' => $gene_value['cptac_id'], 'peptide_sequence' => $gene_value['peptide_sequence'], 'peptide_modified_sequence' => $gene_value['peptide_modified_sequence'], 'start' => $gene_value['peptide_start'], 'end' => $gene_value['peptide_end']);
    }
    // Remove duplicate arrays
    $peptide['sequence_array'] = array_map('unserialize', array_unique(array_map('serialize', $peptide_sequence_array)));
    // Reindex the array
    $peptide['sequence_array'] = array_values($peptide['sequence_array']);

    if ($data["preview_header"]) {
        // Create an array of peptide data including laboratory data (with 'duplicates')
        $peptide['sequence_labs_array'] = $assay->getAllPeptideSequences($data['gene']);
    } else {
        $peptide['sequence_labs_array'] = $assay->getApprovedPeptideSequences($data['gene']);
    }


    // Return only unique peptide sequences (TEMPORARY???)
    $input = $genes["genes"];
    $temp = array();
    $keys = array();
    $i = 0;
    foreach ($input as $gene_key => $gene_data) {
        if (!in_array($gene_data['peptide_sequence'], $temp)) {
            $temp[] = $gene_data['peptide_sequence'];
            $keys[$gene_key] = true;
        }
        $i++;
    }
    $genes_result = array_intersect_key($input, $keys);
    $genes['genes_distinct'] = array_values($genes_result);

    // Format the sequence with highlighted peptide starts and ends
    $data["uniprot_api"]['formatted_sequence'] = $assay->formatSequenceHTML($data["uniprot_api"]['sequence'], $peptide['sequence_array'], $data);

    // Scrape the PhosphoSitePlus site for the the embedded SWF
    $phosphosites_graph = false;
    $genes['phosphosites_graph'] = false;
    // TND cache phosphosite pages
    $phosphosites_graph_file = $final_global_template_vars["phosphosite_images_storage_path"] . "/" . $data["uniprot_api"]["uniprot_ac"] . "cache.txt";
    $current_time = time();
    $expire_time = 720 * 60 * 60 * 6;
    $file_time = 0;
    if (file_exists($phosphosites_graph_file)) {
        $file_time = filemtime($phosphosites_graph_file);
    }


    if (file_exists($phosphosites_graph_file) && ($current_time - $expire_time < $file_time)) {
        //echo 'returning from cached file';
        $phosphosites_graph = file_get_contents($phosphosites_graph_file);
    } else {
        $phosphosites_page = $assay->curl("http://www.phosphosite.org/uniprotAccAction.do?id=" . $data["uniprot_api"]["uniprot_ac"]);
        //file_put_contents($file,$phosphosites_page);

        $phosphosites_graph_object = $assay->scrape_between($phosphosites_page, '<object width="970"', '</object>');

        if (!$phosphosites_graph_object) {

            preg_match('/<a href="\/..\/proteinAction.action;jsessionid=\\w{32}\?id=\\d+&amp;showAllSites=true" class="link13HoverRed">human<\/a>/u', $phosphosites_page, $match);
            $result = implode($match);
            preg_match('/id=\\d+&amp;showAllSites=true/u', $result, $match);

            //$proteinId = $assay->scrape_between($phosphosites_page, "<a href=\"/../proteinAction.action?id=", "\" class=\"link13HoverRed\">human</a>");
            $proteinId = implode($match);

            $phosphosites_page = $assay->curl("http://www.phosphosite.org/proteinAction.action?" . $proteinId);
        }

        $phosphosites_graph = $assay->scrape_between($phosphosites_page, '<object width="970" height="300">', '</object>');

        if (!$phosphosites_graph) {
            $phosphosites_graph = $assay->scrape_between($phosphosites_page, '<object width="970" height="200">', '</object>');
            $phosphosites_graph = str_replace('ProteinViewer200.swf', '/assays/library/ProteinViewer200.swf', $phosphosites_graph);
        }

        if (!$phosphosites_graph) {
            $phosphosites_graph = $assay->scrape_between($phosphosites_page, '<object width="970" height="400">', '</object>');
            $phosphosites_graph = str_replace('ProteinViewer400.swf', '/assays/library/ProteinViewer400.swf', $phosphosites_graph);
        }

        if ($phosphosites_graph) {
            $phosphosites_graph = str_replace('ProteinViewer300.swf', '/assays/library/ProteinViewer300.swf', $phosphosites_graph);
            $phosphosites_graph = str_replace('width="970"', 'wmode="transparent" width="855"', $phosphosites_graph);
            file_put_contents($phosphosites_graph_file, $phosphosites_graph);
        }
    }

    if ($phosphosites_graph) {
        $genes['phosphosites_graph'] = $phosphosites_graph;
    }

    // Chromatogram images
    $i = 0;
    foreach ($genes['genes'] as $gene) {
        $genes['genes'][$i]['chromatogram_images'] = false;
        $chromatogram_images = $assay->get_chromatogram_images($gene['analyte_peptide_id'], $gene['laboratories_id']);
        foreach ($chromatogram_images as $chromatogram_image) {
            $genes['genes'][$i]['chromatogram_images'][] = $final_global_template_vars['panorama_images_path'] .
                "/" . $chromatogram_image['import_log_id'] .
                "/" . 'chromatogram_images' .
                "/" . $chromatogram_image['file_name'];
        }
        $i++;
    }
    // Response curve images
    $i = 0;
    foreach ($genes['genes'] as $gene) {
        $genes['genes'][$i]['response_curve_images'] = false;
        $response_curve_images = $assay->get_response_curve_images($gene['peptide_modified_sequence'], $gene['analyte_peptide_id'], $gene['laboratories_id']);
        //$response_curve_images = $assay->get_response_curve_images( $gene['peptide_sequence'], $gene['analyte_peptide_id'], $gene['laboratories_id'] );
        foreach ($response_curve_images as $response_curve_image) {
            $genes['genes'][$i]['response_curve_images'][] = $final_global_template_vars['panorama_images_path'] .
                "/" . $response_curve_image['import_log_id'] .
                "/" . 'response_curve_images' .
                "/" . $response_curve_image['file_name'];
        }
        $i++;
    }
    // Validation sample curve images (Repeatability)
    $i = 0;

    foreach ($genes['genes'] as $gene) {
        $genes['genes'][$i]['validation_sample_image'] = false;
        $validation_sample_image = $assay->get_validation_sample_image($gene['peptide_modified_sequence'], $gene['analyte_peptide_id'], $gene['laboratories_id']);
        //$validation_sample_image = $assay->get_validation_sample_image( $gene['peptide_sequence'], $gene['analyte_peptide_id'], $gene['laboratories_id'] );
        if (!empty($validation_sample_image)) {
            $genes['genes'][$i]['validation_sample_image'] = $final_global_template_vars['panorama_images_path'] .
                "/" . $validation_sample_image['import_log_id'] .
                "/" . 'validation_sample_images' .
                "/" . $validation_sample_image['file_name'];
        }
        $i++;
    }

    // Validation sample tabular data
    $i = 0;
    $validation_ignored_keys = array('fragment_ion', 'low_count', 'med_count', 'high_count');
    foreach ($genes['genes'] as $gene) {

        $genes['genes'][$i]['validation_sample_images_data'] = false;
        $validation_sample_data_array = $assay->get_validation_sample_images_data(
            $gene['peptide_modified_sequence']
            //$gene['peptide_sequence']
            , $gene['analyte_peptide_id']
            , $gene['laboratories_id']
            , $gene['import_log_id']
            , $assay_id
        );

        foreach ($validation_sample_data_array as $validation_sample_data) {

            $manage_id = $validation_sample_data['manage_id'];
            unset($validation_sample_data['manage_id']);

            foreach ($validation_sample_data as $key => $value) {
                // Color values above 20% in red
                if (($value > 20) && !in_array($key, $validation_ignored_keys)) {
                    $new_validation_sample_data[$key] = '<span class=red>' . $value . '</span>';
                } else {
                    $new_validation_sample_data[$key] = $value;
                }
            }
            $genes['genes'][$i]['validation_sample_images_data'][$manage_id][] = $new_validation_sample_data;
        }
        $i++;
    }


    $i = 0;
    foreach ($genes['genes'] as $gene) {
        $genes['genes'][$i]['sop_files'] = $assay->get_sop_files($gene['import_log_id']);
        $i++;
    }

    // IE disclaimer var
    $IE6 = (stristr('MSIE 6', $_SERVER['HTTP_USER_AGENT'])) ? true : false;
    $IE7 = (stristr('MSIE 7', $_SERVER['HTTP_USER_AGENT'])) ? true : false;
    $IE8 = (stristr('MSIE 8', $_SERVER['HTTP_USER_AGENT'])) ? true : false;
    $data['show_disclaimer'] = ($IE6 || $IE7 || $IE8) ? true : false;

    $render_array = array();
    foreach ($data as $key => $value)
        $render_array[$key] = $value;

    $default_render_array = array(
        "page_title" => $data['gene'] . ", CPTAC-" . $data['manage'] . " - CPTAC Assay Portal"
    , "hide_side_nav" => true
    );


    // define data for peptide_sequence
    $gene_peptide_sequence = array();
    foreach ($genes['genes'] as $key => $value) {
        $gene_peptide_sequence['gene_peptide_sequence'][$key] = array(
            "peptide_sequence" => $value['peptide_sequence']
        , "peptide_modified_sequence" => $value['peptide_modified_sequence']
        , "laboratory_abbreviation" => $value['laboratory_abbreviation']
        , "celllysate_path" => $value['celllysate_path']
        , "laboratories_id" => $value['laboratories_id']
        , "import_log_id" => $value['import_log_id']
        , "peptide_standard_purity_types_id" => $value['peptide_standard_purity_types_id']
        , "manage_id" => $value['manage']
        );
    }


    // define data for peptide_sequence
    $distinct_gene_peptide_sequence = array();

    foreach ($genes['genes'] as $key => $value) {
        $distinct_gene_peptide_sequence['distinct_gene_peptide_sequence'][$key] = array("peptide_sequence" => $value['peptide_sequence'], "manage" => $value['manage']);
    }

    // define data for multiplex_panel
    $multiplex = array();
    $multiplex['multiplex'] = $assay->getMultiplexingData($data['multiplex_panel_id']);


    $render_array = array_merge($render_array, $default_render_array, $genes, $peptide, $gene_peptide_sequence, $distinct_gene_peptide_sequence, $nextPrev,$multiplex);

    $app->render(
        'show_assay_details.php'
        , $render_array
    );
}

?>
