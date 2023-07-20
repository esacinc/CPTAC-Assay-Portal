<?php
/**
 * @desc Import data from Panorama into CPTAC's Assay Portal database
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 2.0
 * @package cptac
 *
 */

namespace assays_import\controllers;

use assays\models\LabkeyFolder;
use assays\models\LabkeyFolderApi;
use assays_import\models\SelectivityDataImport;
use assays_import\models\SelectivityImagesImport;
use assays_import\models\StabilityDataImport;
use assays_import\models\StabilityImagesImport;
use assays_import\models\EndogenousDataImport;
use assays_import\models\EndogenousImagesImport;

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

use assays_import\models\AssaysImport;
use assays_import\models\importPlotsData;
use assays\models\Assay;
use assays\models\LabkeyApi;
use assays\models\ResponseCurveV2LabkeyApi;
use assays\models\ValidationSampleV2LabkeyApi;

use assays_import\models\ChromatogramsImport;
use assays_import\models\ResponseCurveImagesImport;
use assays_import\models\CurveFitDataImport;
use assays_import\models\LodLoqDataImport;
use assays_import\models\ValidationSampleImagesImport;
use assays_import\models\ValidationSampleDataImport;
use assays_import\models\ImportPanoramaData;

class ImportPanoramaDataController extends Controller {

    function import_panorama_data(Request $request, Response $response, $args = []) {

        // Tweak some PHP configurations
        ini_set('memory_limit', '2048M'); // 2 GB
        ini_set('max_execution_time', 36000); // 10 hours

        global $final_global_template_vars;

        $labkey_config = $final_global_template_vars["labkey_config"];

        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();

        $assay = new Assay($db_resource);
        $import_panorama_data = new ImportPanoramaData($db_resource);
        $plots = new importPlotsData($db_resource);

        $labkey_config = $final_global_template_vars["labkey_config"];

        $labkey = new LabkeyApi(
            $labkey_config
            , $final_global_template_vars["panorama_images_path"]
            , $final_global_template_vars["panorama_images_storage_path"]
            , $request->getParam('import_log_id')
        );

        $labkey_login = new \assays\models\LoginApi($labkey_config);

        $cookies = $labkey_login->getCookies();

        $folderApi = new LabkeyFolderApi($labkey_config, $cookies);

        //@@@CAP-61 - fix assay import issues
        $labkey_folder = new LabkeyFolder($lab_data['laboratory_abbreviation'],$lab_data['celllysate_path']);

        $user_account = new \user_account\models\UserAccountDao($db_resource);

        $get = $request->getParams();


        $import_log_id = $get["import_log_id"];


        /*
         * Check the import_log_id. Throw a 404 if it's not a valid ID.
         */

        // Get the laboratory metadata via the import_log_id GET variable.
        $lab_data = $assay->get_laboratory_by_import_log_id($get["import_log_id"]);

        // If get_laboratories() returns false, throw a 404
        if (!$lab_data) throw new \Slim\Exception\NotFoundException($request, $response);


        write_log($import_log_id, 'Verified import ID');

        $labkey_folder->setParent($lab_data['laboratory_abbreviation']);
        $labkey_folder->setFolder($lab_data['celllysate_path']);
        //$labkey_folder->setFolder("test_folder");
        //$labkey_folder->setExperiment("ChromatogramLibrary");

        /*
         * Backup the database.
         */
        //$assay->backup_database("2");
        //write_log($import_log_id, 'Database backed-up');

        /*
         * Send an email to the site admin to notify that an import has been executed.
         */

        // Get the user's data, since we have no $_SESSION data at this point (for later in the script).
        $user_data = $user_account->get_user_account_info((int)$request->getParam("account_id"), false);

        $email_addresses = array(
            $final_global_template_vars["superadmin_email_address"]
        );

        $reimport_text = "";
        if (isset($get["run_missed_images"]) && ($get["run_missed_images"] == "true")) {
            array_push($email_addresses, $user_data["email"]);
            $reimport_text = "Re-";
        }

        $to = implode(", ", $email_addresses);

        $email_subject = "CPTAC Assay Portal: Images " . $reimport_text . "Import Started, " . date('F j, Y h:i:s A') . " - " . $lab_data['laboratory_name'];
        $headers = $final_global_template_vars['message_parts']['headers'];
        $headers .= 'From: CPTAC Assay Portal <noreply@' . $_SERVER["SERVER_NAME"] . '>' . "\r\n";
        $body_message = '
    <h1>CPTAC Assay Portal: Images ' . $reimport_text . 'Import Started - ' . $lab_data['laboratory_name'] . '</h1>' .
            $final_global_template_vars['message_parts']['body_connector']
            . '<p><strong>Date/Time:</strong> ' . date('l, F jS, Y \a\t h:i:s A') . '</p>
    <p>Panorama images and data ' . strtolower($reimport_text) . 'import has been executed by ' . $lab_data['laboratory_name'] . '.</p>
  ';
        $message = $final_global_template_vars['message_parts']['body_header'] . $body_message . $final_global_template_vars['message_parts']['body_footer'];

        // Send the email
        mail($to, $email_subject, $message, $headers);

        write_log($import_log_id, 'Administrator email notifications sent');

        /*
         * Configuration
         */

        // Just check and run on one sequence? (string - sequence | false)
        $peptide_sequence = false;
        // Just check and run on an array of existing IDs (array - ids | false)
        $peptide_ids = false;
        // $peptide_ids = array(102, 103, 225, 268, 333, 606, 629, 644, 656, 549, 540, 868);
        // Delete old files and data during import? (boolean)
        $delete_old_files_and_data = false;

        /*
         * Configuration Ends
         */

        // Run one sequence, an array of sequences from existing IDs, or run all
        if (isset($get["peptide_sequence"]) && isset($get["run_missed_images"]) && ($get["run_missed_images"] == "true")) {
            // Get one sequence's data from the database.
            $sequences = $assay->getPeptideSequenceBySequence($peptide_sequence);

            write_log($import_log_id, 'Single sequence retrieved from db:' . $peptide_sequence);

        } else if (isset($get["run_missed_images"]) && ($get["run_missed_images"] == "true")) {
            // Update the reimport_in_progress field in the imports_executed_log table, setting it to true.
            $log_data["imports_executed_log_id"] = (int)$get["imports_executed_log_id"];
            $log_data["laboratory_id"] = $lab_data["laboratory_id"];
            $log_data["executed_by_user_id"] = (int)$get["account_id"];
            $log_data["reimport_in_progress"] = 1;
            $import_panorama_data->update_reimport_in_progress($log_data);
            // Get sequences from an array of analyte peptide IDs.
            // Get all sequences for a lab, to pass it to the next method, check_for_missed_images().
            $all_sequences = $assay->getPeptideSequences($lab_data["import_log_id"]);
            // Get all of the problematic images.
            $missed_images = $import_panorama_data->check_for_missed_images(
                $lab_data["laboratory_id"]
                , $lab_data["import_log_id"]
                , $all_sequences
            );
            // Get all of the problematic images data.
            $missed_images_data = $import_panorama_data->check_for_missed_images_data(
                $lab_data["laboratory_id"]
                , $lab_data["import_log_id"]
                , $all_sequences
            );
            // Merge the problematic images and problematic images data arrays.
            $sequences = array_merge($missed_images, $missed_images_data);

            write_log($import_log_id, 'FOUND: Import run missed images');


        } else {
            // Get all sequence data from the database
            $sequences = $assay->getPeptideSequences($lab_data["import_log_id"]);

            write_log($import_log_id, 'All sequenced retrieved from local db');

        }


        /*
         * If this is a test run, limit the record count to 5.
         */

        if ($request->getParam("test_import") == 1) {
            $sequences_count = count($sequences);
            $subtract_amount = ($sequences_count - 5);
            $sequences = array_splice($sequences, $subtract_amount);
        }

        // Set test mode, which stops actual imports from executing.
        $execute['test_mode'] = false;
        $execute['experiment_345'] = $lab_data['experiment_345'];

        // Select which imports to execute (boolean).
        $execute['import_chromatogram_images'] = !isset($get["run_missed_images"]);
        $execute['import_response_curve_images'] = !isset($get["run_missed_images"]);
        $execute['import_validation_sample_images'] = !isset($get["run_missed_images"]);
        $execute['import_validation_sample_tabular_data'] = !isset($get["run_missed_images"]);
        $execute['import_lod_loq_data'] = !isset($get["run_missed_images"]);
        $execute['import_curve_fit_data'] = !isset($get["run_missed_images"]);

        // Select which missed data checks to execute (boolean).
        $execute['missed_chromatogram_images'] = !empty($sequences["chromatograms"]);
        $execute['missed_response_curve_images'] = !empty($sequences["response_curves"]);
        $execute['missed_validation_sample_images'] = !empty($sequences["validation_samples"]);
        $execute['missed_validation_sample_tabular_data'] = !empty($sequences["validation_samples_data"]);
        $execute['missed_lod_loq_data'] = !empty($sequences["lod_loq_data"]);
        $execute['missed_curve_fit_data'] = !empty($sequences["response_curves_data"]);


        // Manual override.
        // $execute['import_chromatogram_images'] = false;
        // $execute['import_response_curve_images'] = false;
        // $execute['import_validation_sample_images'] = false;
        // $execute['import_validation_sample_tabular_data'] = false;
        // $execute['import_lod_loq_data'] = false;
        // $execute['import_curve_fit_data'] = false;

        // If we're not in test mode, go ahead and execute.
        if (!$execute['test_mode']) {

            /*
             * Get the Panorama authentication cookie file.
             */

            $panorama_authentication_cookie = $labkey->get_panorama_authentication_cookie($import_log_id);

            /*
             *************************************
             * Process chromatogram images
             *************************************
             */

            if ($execute['import_chromatogram_images'] || $execute['missed_chromatogram_images']) {

                // Purge the error logs.
                $import_panorama_data->purge_error_logs("panorama_chromatogram_images_failed", $lab_data["import_log_id"]);

                // Run missed chromatogram images.

                write_log($import_log_id, 'Getting all missed chromatogram images');

                $all_sequences_preserved = array();
                if (isset($sequences["chromatograms"]) && !empty($sequences["chromatograms"])) {
                    $all_sequences_preserved = $sequences;
                    $sequences = $sequences["chromatograms"];
                }

                // Instantiate.
                $chromatograms_import = new ChromatogramsImport($db_resource, $import_panorama_data, $labkey);

                // Run the import.
                $chromatograms_result = $chromatograms_import->run_import(
                    $sequences
                    , $panorama_authentication_cookie
                    , $delete_old_files_and_data
                    , $lab_data
                    , $import_log_id
                );

                write_log($import_log_id, 'Getting chromatogram images complete');


                // Restore the $sequences array for the next import process.
                if (isset($sequences["chromatograms"]) && !empty($sequences["chromatograms"])) {
                    $sequences = $all_sequences_preserved;
                }

            }

            $template_version = $lab_data['template_version'];

            /*
             *************************************
             * Process response curve images
             *************************************
             */

            if ($execute['import_response_curve_images'] || $execute['missed_response_curve_images']) {

                // Purge the error logs.
                $import_panorama_data->purge_error_logs("panorama_response_curve_images_failed", $lab_data["import_log_id"]);

                // Run missed response curve images
                $all_sequences_preserved = array();
                if (isset($sequences["response_curves"]) && !empty($sequences["response_curves"])) {
                    $all_sequences_preserved = $sequences;
                    $sequences = $sequences["response_curves"];
                }

                // Instantiate.
                //@@@CAP-61 - fix assay import blockers
                //$response_curve_images_import = new ResponseCurveImagesImport($db_resource, $import_panorama_data, $labkey);

                //if ($template_version == 2) {
                    $response_curve_v2_labkey = new ResponseCurveV2LabkeyApi(
                        $final_global_template_vars["labkey_config"]
                        , $final_global_template_vars["panorama_images_path"]
                        , $final_global_template_vars["panorama_images_storage_path"]
                        , $request->getParam('import_log_id')
                    );
                    $response_curve_images_import = new ResponseCurveImagesImport($db_resource, $import_panorama_data, $response_curve_v2_labkey);
                //}

                // Run the import.
                $response_curve_images_result = $response_curve_images_import->run_import(
                    $sequences
                    , $panorama_authentication_cookie
                    , $delete_old_files_and_data
                    , $lab_data
                );


                write_log($import_log_id, 'response_curve_images import');


                if (isset($sequences["response_curves"]) && !empty($sequences["response_curves"])) {
                    $sequences = $all_sequences_preserved;
                }

            }

            /*
             *************************************
             * Process validation sample images
             *************************************
             */


            if ($execute['import_validation_sample_images'] || $execute['missed_validation_sample_images']) {

                // Purge the error logs.
                $import_panorama_data->purge_error_logs("panorama_validation_sample_images_failed", $lab_data["import_log_id"]);

                // Run validation sample images
                $all_sequences_preserved = array();
                if (isset($sequences["validation_samples"]) && !empty($sequences["validation_samples"])) {
                    $all_sequences_preserved = $sequences;
                    $sequences = $sequences["validation_samples"];
                }

                // Instantiate.
                //@@@CAP-61 - fix assay import blockers
                //$validation_sample_images = new ValidationSampleImagesImport($db_resource, $import_panorama_data, $labkey);

                //if ($template_version == 2) {
                    $validation_sample_v2_labkey = new ValidationSampleV2LabkeyApi(
                        $final_global_template_vars["labkey_config"]
                        , $final_global_template_vars["panorama_images_path"]
                        , $final_global_template_vars["panorama_images_storage_path"]
                        , $request->getParam('import_log_id')
                    );
                    $validation_sample_images = new ValidationSampleImagesImport($db_resource, $import_panorama_data, $validation_sample_v2_labkey);
                //}

                // Run the import.
                $validation_sample_images_result = $validation_sample_images->run_import(
                    $sequences
                    , $panorama_authentication_cookie
                    , $delete_old_files_and_data
                    , $lab_data
                );

                write_log($import_log_id, 'validation_sample_images import');

                if (isset($sequences["validation_samples"]) && !empty($sequences["validation_samples"])) {
                    $sequences = $all_sequences_preserved;
                }

            }

            //die('stop here');


            /*
             *************************************
             * Process validation sample data
             *************************************
             */

            if ($execute['import_validation_sample_tabular_data'] || $execute['missed_validation_sample_tabular_data']) {

                // Purge the error logs.
                $import_panorama_data->purge_error_logs("panorama_validation_sample_data_failed", $lab_data["import_log_id"]);

                // Run validation sample images data
                $all_sequences_preserved = array();
                if (isset($sequences["validation_samples_data"]) && !empty($sequences["validation_samples_data"])) {
                    $all_sequences_preserved = $sequences;
                    $sequences = $sequences["validation_samples_data"];
                }

                // Instantiate.
                //@@@CAP-61 - fix assay import blockers
                //$validation_sample_data = new ValidationSampleDataImport($db_resource, $import_panorama_data, $labkey);

                //if ($template_version == 2) {
                    $validation_sample_v2_labkey = new ValidationSampleV2LabkeyApi(
                        $final_global_template_vars["labkey_config"]
                        , $final_global_template_vars["panorama_images_path"]
                        , $final_global_template_vars["panorama_images_storage_path"]
                        , $request->getParam('import_log_id')
                        );
                    $validation_sample_data = new ValidationSampleDataImport($db_resource, $import_panorama_data, $validation_sample_v2_labkey);
                //}

                // Run the import.
                $validation_sample_data_result = $validation_sample_data->run_import(
                    $sequences
                    , $panorama_authentication_cookie
                    , $delete_old_files_and_data
                    , $lab_data
                );

                write_log($import_log_id, 'validation_sample_data import');

                if (isset($sequences["validation_samples_data"]) && !empty($sequences["validation_samples_data"])) {
                    $sequences = $all_sequences_preserved;
                }

            }


            /*
             *************************************
             * Process LOD/LOQ data
             *************************************
             */

            if ($execute['import_lod_loq_data'] || $execute['missed_lod_loq_data']) {
                write_log($import_log_id, 'inside controller lod_loq_data before run_import');

                // Purge the error logs.
                $import_panorama_data->purge_error_logs("lod_loq_comparison_data_failed", $lab_data["import_log_id"]);

                // Run lod loq data
                $all_sequences_preserved = array();
                if (isset($sequences["lod_loq_data"]) && !empty($sequences["lod_loq_data"])) {
                    $all_sequences_preserved = $sequences;
                    $sequences = $sequences["lod_loq_data"];
                }

                // Instantiate.
                //@@@CAP-61 fix assay import blockers
                //$lod_loq_data = new LodLoqDataImport($db_resource, $import_panorama_data, $labkey, $plots);

                //if ($template_version == 2) {
                    $response_curve_v2_labkey = new ResponseCurveV2LabkeyApi(
                        $final_global_template_vars["labkey_config"]
                        , $final_global_template_vars["panorama_images_path"]
                        , $final_global_template_vars["panorama_images_storage_path"]
                        , $request->getParam('import_log_id')
                    );
                    $lod_loq_data = new LodLoqDataImport($db_resource, $import_panorama_data, $response_curve_v2_labkey, $plots);
                //}

                // Run the import.
                $lod_loq_data_result = $lod_loq_data->run_import(
                    $sequences
                    , $panorama_authentication_cookie
                    , $delete_old_files_and_data
                    , $lab_data
                );

                write_log($import_log_id, 'controller after lod_loq_data run_import');
                //write_log($import_log_id,'controller lod_loq_data import id: '.$import_log_id);
                //write_log($import_log_id,'controller lod_loq_data import lab_data_log_id: '.$lab_data["import_log_id"]);
                //write_log($import_log_id,'controller lod_loq_data import sequences: '.$sequences['lod_loq_data']);

                if (isset($sequences["lod_loq_data"]) && !empty($sequences["lod_loq_data"])) {
                    $sequences = $all_sequences_preserved;
                }

            }

            /*
             *************************************************
             * Process Curve Fit data (response curves)
             *************************************************
             */

            if ($execute['import_curve_fit_data'] || $execute['missed_curve_fit_data']) {
                write_log($import_log_id, 'controller curve fit import');

                // Purge the error logs.
                $import_panorama_data->purge_error_logs("response_curves_data_failed", $lab_data["import_log_id"]);

                // Run curve fit data (validation samples)
                $all_sequences_preserved = array();
                if (isset($sequences["response_curves_data"]) && !empty($sequences["response_curves_data"])) {
                    $all_sequences_preserved = $sequences;
                    $sequences = $sequences["response_curves_data"];
                }

                // Instantiate.
                //@@CAP-61 - fix assay import blockers
                //$curve_fit_data = new CurveFitDataImport($db_resource, $import_panorama_data, $labkey, $plots);

                //if ($template_version == 2) {
                    $response_curve_v2_labkey = new ResponseCurveV2LabkeyApi(
                        $final_global_template_vars["labkey_config"]
                        , $final_global_template_vars["panorama_images_path"]
                        , $final_global_template_vars["panorama_images_storage_path"]
                        , $request->getParam('import_log_id')
                    );
                    $curve_fit_data = new CurveFitDataImport($db_resource, $import_panorama_data, $response_curve_v2_labkey, $plots);
                //}

                // Run the import.
                $curve_fit_data_result = $curve_fit_data->run_import(
                    $sequences
                    , $panorama_authentication_cookie
                    , $delete_old_files_and_data
                    , $lab_data
                );

                //write_log($import_log_id,'controller curve fit import');
                //write_log($import_log_id,'controller curve fit import id: '.$import_log_id);
                //write_log($import_log_id,'controller curve fit import lab_data_log_id: '.$lab_data["import_log_id"]);
                //write_log($import_log_id,'controller curve import sequences data: '.$sequences['response_curves_data']);


                write_log($import_log_id, 'End Curve Fit Data Import');

            }

            /*
             *************************************
             * Process experiment 3, 4 and 5
             *************************************
             */

            $execute['import_experiment_345'] = ($execute["experiment_345"] == 1);

            if ($execute['import_experiment_345']) {
                write_log($import_log_id, 'Import experiment 3, 4 and 5');

                $selectivity_labkey = new \assays\models\SelectivityLabkeyApi(
                    $final_global_template_vars["labkey_config"]
                    , $final_global_template_vars["panorama_images_path"]
                    , $final_global_template_vars["panorama_images_storage_path"]
                    , $import_log_id
                );

                // Instantiate.
                $selectivity_image_import = new SelectivityImagesImport($db_resource, $import_panorama_data, $selectivity_labkey);

                // Run the import.
                $selectivity_image_result = $selectivity_image_import->run_import(
                    $sequences
                    , $panorama_authentication_cookie
                    , $delete_old_files_and_data
                    , $lab_data
                );

                // Instantiate.
                $selectivity_data_import = new SelectivityDataImport($db_resource, $import_panorama_data, $selectivity_labkey);

                // Run the import.
                $selectivity_data_result = $selectivity_data_import->run_import(
                    $sequences
                    , $panorama_authentication_cookie
                    , $delete_old_files_and_data
                    , $lab_data
                );


                $stability_labkey = new \assays\models\StabilityLabkeyApi(
                    $final_global_template_vars["labkey_config"]
                    , $final_global_template_vars["panorama_images_path"]
                    , $final_global_template_vars["panorama_images_storage_path"]
                    , $import_log_id
                );

                // Instantiate.
                $stability_image_import = new StabilityImagesImport($db_resource, $import_panorama_data, $stability_labkey);

                // Run the import.
                $stability_image_import = $stability_image_import->run_import(
                    $sequences
                    , $panorama_authentication_cookie
                    , $delete_old_files_and_data
                    , $lab_data
                );

                // Instantiate.
                $stability_data_import = new StabilityDataImport($db_resource, $import_panorama_data, $stability_labkey);

                // Run the import.
                $stability_data_result = $stability_data_import->run_import(
                    $sequences
                    , $panorama_authentication_cookie
                    , $delete_old_files_and_data
                    , $lab_data
                );

                $endogenous_labkey = new \assays\models\EndogenousLabkeyApi(
                    $final_global_template_vars["labkey_config"]
                    , $final_global_template_vars["panorama_images_path"]
                    , $final_global_template_vars["panorama_images_storage_path"]
                    , $import_log_id
                );

                // Instantiate.
                $endogenous_image_import = new EndogenousImagesImport($db_resource, $import_panorama_data, $endogenous_labkey);

                // Run the import.
                $endogenous_image_import = $endogenous_image_import->run_import(
                    $sequences
                    , $panorama_authentication_cookie
                    , $delete_old_files_and_data
                    , $lab_data
                );

                // Instantiate.
                $endogenous_data_import = new EndogenousDataImport($db_resource, $import_panorama_data, $endogenous_labkey);

                // Run the import.
                $endogenous_data_result = $endogenous_data_import->run_import(
                    $sequences
                    , $panorama_authentication_cookie
                    , $delete_old_files_and_data
                    , $lab_data
                );
            }



            // Delete Panorama's authentication cookie.
            if (is_file($panorama_authentication_cookie)) {
                unlink($panorama_authentication_cookie);
            }

            write_log($import_log_id, 'End Import');
        } // Test mode ends

        // Record the import_end_date in the imports_executed_log table.
        if (isset($get["imports_executed_log_id"]) && !isset($get["run_missed_images"])) {
            $import_panorama_data->update_executed_import_end_date($get["imports_executed_log_id"]);

            write_log($import_log_id, 'Update import set the new end date for import executed log id ' . $get["imports_executed_log_id"]);

        }

        // Update the reimport_in_progress field in the imports_executed_log table, setting it to false.
        if (isset($get["imports_executed_log_id"]) && isset($get["run_missed_images"])) {
            $log_data["imports_executed_log_id"] = (int)$get["imports_executed_log_id"];
            $log_data["laboratory_id"] = $lab_data["laboratory_id"];
            $log_data["executed_by_user_id"] = (int)$get["account_id"];
            $log_data["reimport_in_progress"] = 0;
            $import_panorama_data->update_reimport_in_progress($log_data);

            write_log($import_log_id, 'Update re-import in progress');
        }

        /*
        * Send an email to the site admin and end-user to notify that the import has finished.
        */
        //@@CAP-61 - fix assay import blockers
        /*
        $test_import_subject = ($request->getParam("test_import") == 1) ? '[TEST RUN]' : '';
        $email_subject = "CPTAC Assay Portal: " . $reimport_text . "Import Finished " . $test_import_subject . ", " . date('F j, Y h:i:s A') . " - " . $lab_data['laboratory_name'];
        $headers = $final_global_template_vars['message_parts']['headers'];
        $headers .= 'From: CPTAC Assay Portal <noreply@' . $_SERVER["SERVER_NAME"] . '>' . "\r\n";
        $test_import_notification = ($request->getParam("test_import") == 1) ? '<span style="color:red;">[TEST RUN]</span>' : '';
        $body_message = '
    <h1>CPTAC Assay Portal: ' . $reimport_text . 'Import Finished - ' . $lab_data['laboratory_name'] . '</h1>' .
            $final_global_template_vars['message_parts']['body_connector']
            . '<p><strong>Date/Time:</strong> ' . date('l, F jS, Y \a\t h:i:s A') . '</p>
    <p>' . $test_import_notification . ' An import executed by ' . $lab_data['laboratory_name'] . ' has finished.</p>
    <p><a href="https://' . $_SERVER["SERVER_NAME"] . '/assays_import/">Review the imported assays</a></p>
    ';
        $message = $final_global_template_vars['message_parts']['body_header'] . $body_message . $final_global_template_vars['message_parts']['body_footer'];

        // Send the email
        mail($final_global_template_vars["superadmin_email_address"] . ", " . $user_data["email"], $email_subject, $message, $headers);

        write_log($import_log_id, 'Administrator completed email sent');
        */
        write_log($import_log_id, 'IMPORT COMPLETE - ' . $lab_data['laboratory_name'] . ' ' . date('l, F jS, Y \a\t h:i:s A'));

        return $response->withHeader('Content-Type', 'text/plain')->write("Done with Panorama images and data import - " . date('l, F jS, Y \a\t h:i:s A'));

    }

}
