<?php
namespace assays\controllers;

use Slim\Http\Request;
use Slim\Http\Response;

use assays\models\Assay;

use core\controllers\Controller;

class AssayDetails extends Controller {

    function show_assay_details (Request $request, Response $response, $args = []) {

        global $final_global_template_vars;

        $xml2array = new \swpg\models\XML2Array();
        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();

        // Local Portal Data
        $assay = new Assay(
            $db_resource
            , $final_global_template_vars["session_key"]
        );

        $assay_id = $args['assay_id'];

        $assay_type = $args['assay_type'];

        // Extract just the id from the "CPTAC-" appended GET variable
        if ($assay_id) {
            preg_match('!\d+!', $assay_id, $matches);
            $assay_id = $matches[0];
        }
        // Next and Previous
        $nextPrev['prevNext'] = $assay->getPrevNextAssay($assay_id);

        // Get details
        $data = $assay->get_details($assay_id, false, false);

        if ($assay_type . "-" . $assay_id !== $data["cptac_id"]) {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }

        $data["preview_header"] = false;

        // If get_details() returns false, throw a 404
        if (!$data)
            throw new \Slim\Exception\NotFoundException($request, $response);

        // If the record is not approved and there no authenticated session, throw a 404
        if (($data["approval_status"] != 1) && (!isset($_SESSION[$final_global_template_vars["session_key"]])))
            throw new \Slim\Exception\NotFoundException($request, $response);

        // If the record is not approved and there is an authenticated session, set the preview_header variable to true. Otherwise, set it to false.
        $data["preview_header"] = (($data["approval_status"] != 1) && isset($_SESSION[$final_global_template_vars["session_key"]])) ? true : false;

        // Reformat uniprot_gene_synonym for display
        $data["uniprot_gene_synonym"] = implode(", ", explode(",", $data["uniprot_gene_synonym"]));

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
            //$this->container->get('logger')->info($data["gene"]);
            $genes['genes'] = $assay->getApprovedGenes($data['gene']);
            //$this->container->get('logger')->info(json_encode($genes["genes"]));
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

        //@@@CAP-94 - fix display exp 3,4 & 5 for pending assays in assay details page
        if($data["additional_approval_status"] == 1) {
            $i = 0;
            foreach ($genes['genes'] as $gene) {
                $genes['genes'][$i]['selectivity_image'] = false;
                $selectivity_image = $assay->get_selectivity_images(
                    $gene['peptide_modified_sequence']
                    , $gene['analyte_peptide_id']
                    , $gene['laboratories_id']);

                if (!empty($selectivity_image)) {
                    $genes['genes'][$i]['selectivity_image'] = $final_global_template_vars['panorama_images_path'] .
                        "/" . $selectivity_image['import_log_id'] .
                        "/" . 'selectivity_images' .
                        "/" . $selectivity_image['file_name'];
                }
                $i++;
            }

            $i = 0;

            foreach ($genes['genes'] as $gene) {
                $selectivity_summary_data = $assay->get_selectivity_summary_data(
                    $gene['peptide_modified_sequence']
                    , $gene['analyte_peptide_id']
                    , $gene['laboratories_id']
                );

                $genes['genes'][$i]['selectivity_summary_data'] = $selectivity_summary_data;

                $i++;
            }


            $i = 0;

            foreach ($genes['genes'] as $gene) {
                $selectivity_spike_level_data = $assay->get_selectivity_spike_level_data(
                    $gene['peptide_modified_sequence']
                    , $gene['analyte_peptide_id']
                    , $gene['laboratories_id']
                );

                $genes['genes'][$i]['selectivity_spike_level_data'] = $selectivity_spike_level_data;

                $i++;
            }


            $i = 0;
            foreach ($genes['genes'] as $gene) {
                $genes['genes'][$i]['stability_image'] = false;
                $stability_image = $assay->get_stability_images(
                    $gene['peptide_modified_sequence']
                    , $gene['analyte_peptide_id']
                    , $gene['laboratories_id']);

                if (!empty($stability_image)) {
                    $genes['genes'][$i]['stability_image'] = $final_global_template_vars['panorama_images_path'] .
                        "/" . $stability_image['import_log_id'] .
                        "/" . 'stability_images' .
                        "/" . $stability_image['file_name'];
                }
                $i++;
            }


            $i = 0;
            //define which data to be loaded from genes array since multipple peptides sometimes returned
            $stability_data_headers = ['fragment_ion', 'control_intra_CV', 'actual_temp_intra_CV', 'frozen_intra_CV', 'FTx1_intra_CV', 'FTx2_intra_CV', 'all_intra_CV', 'all_inter_CV'];
            foreach ($genes['genes'] as $gene) {

                $genes['genes'][$i]['stability_data'] = false;
                $stability_data = $assay->get_stability_data(
                    $gene['peptide_modified_sequence']
                    , $gene['analyte_peptide_id']
                    , $gene['laboratories_id']
                    , $assay_id
                );

                //loop through stability data to format high values
                foreach ($stability_data as $stability_data_result_key => $stability_data_result) {
                    $manage_id = $stability_data_result['analyte_peptide_id'];
                    $new_stability_data_array = [];
                    foreach ($stability_data_result as $key => $value) {
                        if (in_array($key, $stability_data_headers) && $assay_id == $gene['analyte_peptide_id']) {
                            //loop through displayed exp2 data
                            foreach ($genes['genes'][$i]['validation_sample_images_data'][$manage_id] as $exp2_key => $exp2_value) {
                                foreach ($exp2_value as $k => $v) {
                                    //check med_total_CV value
                                    if ($k == 'med_total_CV') {
                                        //check if stability data fragment_ion and exp 2 fragrment_ion are the same
                                        if ($stability_data_result['fragment_ion'] == $exp2_value['fragment_ion']) {
                                            //strip html format from exp2 data value
                                            $exp2_value_text = strip_tags($v, '<span class=red>');
                                            //if exp4 value is greater than exp2 value format html else don't highlight
                                            if ($value > $exp2_value_text) {
                                                //make sure value is not the fragment_ion column
                                                if ($key != 'fragment_ion') {
                                                    $new_stability_data_array[$key] = '<span class=red>' . $value . '</span>';
                                                } else {
                                                    $new_stability_data_array[$key] = $value;
                                                }
                                            } else {
                                                $new_stability_data_array[$key] = $value;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $stability_data[$stability_data_result_key] = $new_stability_data_array;
                }

                $genes['genes'][$i]['stability_data'] = $stability_data;
                $i++;
            }

            $i = 0;
            foreach ($genes['genes'] as $gene) {
                $genes['genes'][$i]['endogenous_image'] = false;
                //$this->container->get('logger')->info($gene['peptide_modified_sequence']);
                $endogenous_image = $assay->get_endogenous_images($gene['peptide_modified_sequence'], $gene['analyte_peptide_id'], $gene['laboratories_id']);
                //$validation_sample_image = $assay->get_validation_sample_image( $gene['peptide_sequence'], $gene['analyte_peptide_id'], $gene['laboratories_id'] );
                if (!empty($endogenous_image)) {
                    $genes['genes'][$i]['endogenous_image'] = $final_global_template_vars['panorama_images_path'] .
                        "/" . $endogenous_image['import_log_id'] .
                        "/" . 'endogenous_images' .
                        "/" . $endogenous_image['file_name'];
                }
                $i++;
            }


            $i = 0;
            foreach ($genes['genes'] as $gene) {
                $genes['genes'][$i]['endogenous_data'] = false;
                $endogenous_data = $assay->get_endogenous_data(
                    $gene['peptide_modified_sequence']
                    , $gene['analyte_peptide_id']
                    , $gene['laboratories_id']
                );
                $new_array = [];
                foreach ($endogenous_data as $index => $endogenous_data_result) {
                    foreach ($endogenous_data_result as $key => $value) {
                        if ($value > 20) {
                            $endogenous_data[$index][$key] = '<span class=red>' . $value . '</span>';
                        }
                    }
                }
                $genes['genes'][$i]['endogenous_data'] = $endogenous_data;
                $i++;
            }
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



        $view = $this->container->get('view');
        $view->render(
            $response
            , 'show_assay_details.twig'
            , $render_array
        );

        return $response;

    }
}
