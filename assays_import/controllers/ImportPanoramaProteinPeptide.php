<?php
/**
 * @desc Import Assays: controller for importing protein and peptide data from Panorama, UniProt, and Entrez Gene.
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 2.0
 * @package cptac
 *
 */

namespace assays_import\controllers;

use \PDO;
// Tweak some PHP configurations
ini_set('memory_limit', '1024M'); // 1 GB
ini_set('max_execution_time', 66000); // 10 hours

use Slim\Http\Request;
use Slim\Http\Response;

use core\controllers\Controller;

use assays_import\models\AssaysImport;
use assays_import\models\ImportPanoramaData;
use assays_import\models\ImportUniprotData;
use assays_import\models\ImportEntrezGenomicContext;
use assays\models\Assay;
use assays\models\LabkeyApi;
use assays\models\Kegg;

use swpg\models\XML2Array;


class ImportPanoramaProteinPeptide extends Controller {

    function import_panorama_protein_peptide(Request $request, Response $response, $args = []) {

        global $final_global_template_vars;




        //require_once $_SERVER["PATH_TO_CORE"] . '/library/functions/functions.php';


        $db_conn = new \swpg\models\db($final_global_template_vars["db_connection"]);
        $db_resource = $db_conn->get_resource();

        $xml2array = new \swpg\models\XML2Array();
        $assay = new Assay($db_resource);
        $import = new ImportPanoramaData($db_resource);
        $import_uniprot_data = new ImportUniprotData($db_resource);
        $import_entrez = new ImportEntrezGenomicContext($db_resource);

        $this->logger->info("start import protein peptide");

        $kegg = new Kegg($db_resource);
        $labkey = new LabkeyApi(
            $final_global_template_vars["labkey_config"]
            , $final_global_template_vars["panorama_images_path"]
            , $final_global_template_vars["panorama_images_storage_path"]
            , $request->getParam("import_log_id")
        );
        $user_account = new \user_account\models\UserAccountDao($db_resource);




        // Set test mode, which stops actual imports from executing.
        $execute['test_mode'] = false;

        // Panorama query columns
        $panorama_query_columns = array(
            'Sequence'
        , 'PeptideModifiedSequence'
        , 'RtCalculatorScore'
        , 'StartIndex'
        , 'EndIndex'
        , 'CalcNeutralMass'
        , 'PeptideGroupId/Label'
        , 'PeptideGroupId/RunId'
        , 'PeptideGroupId/RunId/Created'
        , 'PeptideGroupId/RunId/File/FileName'
        , 'PeptideGroupId/Description'
        , 'PeptideGroupId/Accession'
        , 'PeptideGroupId/Note'
        );

        /*
         * Check the import_log_id. Throw a 404 if it's not a valid ID.
         */

        // Get the laboratory metadata via the import_log_id GET variable.
        $get = $assay->get_laboratory_by_import_log_id($request->getParam("import_log_id"));


        write_log($get["import_log_id"], 'Import Panorama - all protein peptides');

        // If get_laboratories() returns false, throw a 404
        if (!$get) throw new \Slim\Exception\NotFoundException($request, $response);

        /*
         * Backup the database.
         */

        //$assay->backup_database("0");

        /*
         * Log the import execution details to the database.
         */


        $log_data["import_log_id"] = (int)$get["import_log_id"];
        $log_data["laboratory_id"] = (int)$get["laboratory_id"];
        $log_data["executed_by_user_id"] = (int)$request->getParam("account_id");
        $log_data["import_executed_status"] = true;
        //$imports_executed_log_id = $import->insert_executed_imports($log_data);



        //write_log($get["import_log_id"], 'Import logs updated');
        $imports_executed_log_id = $request->getParam("imports_executed_log_id");


        /*
         * Send an email to the site admin and end-user to notify that an import has been executed.
         */

        // Get the user's data, since we have no $_SESSION data at this point.
        $user_data = $user_account->get_user_account_info($log_data["executed_by_user_id"], false);

        //@@@CAP-61 - fix assay import blockers
        /*
        $test_import_subject = ($request->getParam("test_import") == 1) ? '[TEST RUN]' : '';
        $email_subject = "CPTAC Assay Portal: Import Started " . $test_import_subject . ", " . date('F j, Y h:i:s A') . " - " . $get['laboratory_name'];
        $headers = $final_global_template_vars['message_parts']['headers'];
        $headers .= 'From: CPTAC Assay Portal <noreply@' . $_SERVER["SERVER_NAME"] . '>' . "\r\n";
        $test_import_notification = ($request->getParam("test_import") == 1) ? '<span style="color:red;">[TEST RUN]</span>' : '';
        $body_message = '
    <h1>CPTAC Assay Portal: Import Started - ' . $get['laboratory_name'] . '</h1>' .
            $final_global_template_vars['message_parts']['body_connector']
            . '<p><strong>Date/Time:</strong> ' . date('l, F jS, Y \a\t h:i:s A') . '</p>
    <p>' . $test_import_notification . ' An import has been executed by the ' . $get['laboratory_name'] . ' laboratory.</p>
  ';
        $message = $final_global_template_vars['message_parts']['body_header'] . $body_message . $final_global_template_vars['message_parts']['body_footer'];

        // Send the email
        mail($final_global_template_vars["superadmin_email_address"] . ", " . $user_data["email"], $email_subject, $message, $headers);

        write_log($get["import_log_id"], 'Admin notification sent');
        */



        // If we're not in test mode, go ahead and execute.
        if (!$execute['test_mode']) {

            /*
             * Get the Panorama authentication cookie file.
             */


            $panorama_authentication_cookie = $labkey->get_panorama_authentication_cookie($get["import_log_id"]);


            write_log($get["import_log_id"], $panorama_authentication_cookie);

            /*
             * Query Panorama for all of the peptides.
             */

            write_log($get["import_log_id"], 'Getting all protein peptides');

            $returned_peptide_data = $labkey->getAllPeptides(
                $panorama_authentication_cookie
                , "targetedms"
                , "Peptide"
                , implode(',', $panorama_query_columns)
                , $get['laboratory_abbreviation']
                , $get['celllysate_path']
                , 'ResponseCurve'
            );


            $peptide_data = false;
            if (isset($returned_peptide_data->response) && !empty($returned_peptide_data->response)) {
                $peptide_data = json_decode($returned_peptide_data->response, true);
            }


            // record this data for future examination
            $sql = "DELETE FROM import_initial_start_records WHERE import_log_id = :import_log_id";
            $statement = $db_resource->prepare($sql);
            $statement->bindValue(":import_log_id", $get["import_log_id"], PDO::PARAM_INT);
            $statement->execute();

            $sql = "INSERT INTO import_initial_start_records
              (import_log_id,run_by_user_id,recieved_data,records_count)
              VALUES
              (:import_log_id,:run_by_user_id,:recieved_data,:records_count)";
            $statement = $db_resource->prepare($sql);
            $statement->bindValue(":import_log_id", $get["import_log_id"], PDO::PARAM_INT);
            $statement->bindValue(":run_by_user_id", $request->getParam("account_id"), PDO::PARAM_INT);
            $statement->bindValue(":recieved_data", json_encode($peptide_data['rows']), PDO::PARAM_STR);
            $statement->bindValue(":records_count", $peptide_data['rowCount'], PDO::PARAM_STR);
            $statement->execute();


            write_log($get["import_log_id"], 'Received total peptides count: ' . $peptide_data['rowCount']);


            // If Panorama does not respond to this first query, display a message and email the super admin and end-user.
            if (!$peptide_data) {
                //@@@CAP-61 - fix assay import blockers
                /*
                mail(
                    $final_global_template_vars["superadmin_email_address"] . ", " . $user_data["email"]
                    , "CPTAC Import: Panorama, No Response - " . $get['laboratory_abbreviation']
                    , date('l F jS, Y h:i:s A') . "\n\nNo response from Panorama for an import executed by the '" . $get['laboratory_name'] . "' laboratory."
                );
                echo 'Panorama did not respond. Please try again. If this persists, please <a href="mailto:' . $final_global_template_vars["superadmin_email_address"] . '">contact the site administrator</a>.';
                */
                write_log($get["import_log_id"], 'Panorama did not respond. Please try again.');

                die();

            }


            // If Panorama returns no data for this first query, display a message and email the super admin and end-user.
            if ($peptide_data["rowCount"] == 0) {
                //@@CAP-61 fix assay import blockers
                /*
                mail(
                    $final_global_template_vars["superadmin_email_address"] . ", " . $user_data["email"]
                    , "CPTAC Import: Panorama, No Data - " . $get['laboratory_abbreviation']
                    , date('l F jS, Y h:i:s A') . "\n\nNo data returned from Panorama for an import executed by the '" . $get['laboratory_name'] . "' laboratory. Please make sure Response Curve data is present out on Panorama. If this persists, please contact the site administrator at: " . $final_global_template_vars["superadmin_email_address"] . "."
                );

                echo 'No data returned from Panorama. Please make sure Response Curve data is present out on Panorama. If this persists, please <a href="mailto:' . $final_global_template_vars["superadmin_email_address"] . '">contact the site administrator</a>.';
                */
                write_log($get["import_log_id"], 'No data returned from Panorama. Please make sure Response Curve data is present out on Panorama.');

                die();
            }


            // If Panorama does respond, process.
            if ($peptide_data["rowCount"] > 0) {

                $i = 0;

                write_log($get["import_log_id"], 'Preparing to pull data.');

                // $peptide_data["rows"] = $peptide_data["rowCount"];

                /*
                 * If this is a test run, limit the record count to 5.
                 */


                if ($request->getParam("test_import") == 1) {
                    $subtract_amount = ($peptide_data["rowCount"] - 5);
                    $peptide_data["rows"] = array_splice($peptide_data["rows"], $subtract_amount);
                }

                ##############################

                // $subtract_amount = ($peptide_data["rowCount"]-1);
                // $peptide_data["rows"] = array_splice($peptide_data["rows"], $subtract_amount);

                ##############################

                $data = array();
                foreach ($peptide_data["rows"] as $row) {

                    // Parse the metadata for the id to query UniProt with.

                    // Get the UniProt ID.
                    $data[$i]["uniprot_lookup_id"] = false;

                    // Check to see if there is a "tr" value.
                    preg_match("/tr\|(\w*)/", $row["PeptideGroupId/Label"], $swiss_prot_label_matches_tr);

                    // Check to see if there is a "sp" value.
                    preg_match("/sp\|(\w*)/", $row["PeptideGroupId/Label"], $swiss_prot_label_matches_sp);

                    //Check to see if there is a note value
                    preg_match("/sp\|(\w*)/", $row["PeptideGroupId/Note"], $swiss_prot_note_matches_sp);

                    //Check to see if there is an accession value
                    $accession_id = $row["PeptideGroupId/Accession"];


                    // // Check to see if there is a ENSEMBL value.
                    // preg_match("/\|ENSEMBL:(\w*)/", $row["PeptideGroupId/Description"], $ensembl_matches);

                    // Check to see if there is a TREMBL value.
                    preg_match("/\|TREMBL:(\w*)/", $row["PeptideGroupId/Description"], $trembl_matches);

                    // Check to see if there is a SWISS-PROT value.
                    preg_match("/\|SWISS-PROT:(\w*)/", $row["PeptideGroupId/Description"], $swiss_prot_matches);

                    if (isset($swiss_prot_label_matches_tr) && !empty($swiss_prot_label_matches_tr)) {
                        $data[$i]["uniprot_lookup_id"] = $swiss_prot_label_matches_tr[1];
                    }

                    if (isset($swiss_prot_label_matches_sp) && !empty($swiss_prot_label_matches_sp)) {
                        $data[$i]["uniprot_lookup_id"] = $swiss_prot_label_matches_sp[1];
                    }
                    // if(isset($ensembl_matches) && !empty($ensembl_matches)) {
                    //   $data[$i]["uniprot_lookup_id"] = $ensembl_matches[1];
                    // }

                    if (isset($trembl_matches) && !empty($trembl_matches)) {
                        $data[$i]["uniprot_lookup_id"] = $trembl_matches[1];
                    }

                    if (isset($swiss_prot_matches) && !empty($swiss_prot_matches)) {
                        $data[$i]["uniprot_lookup_id"] = $swiss_prot_matches[1];
                    }

                    if (isset($swiss_prot_note_matches_sp) && !empty($swiss_prot_note_matches_sp)) {
                        $data[$i]["uniprot_lookup_id"] = $swiss_prot_note_matches_sp[1];
                    }

                    if (isset($accession_id) && !empty($accession_id)) {
                        $data[$i]["uniprot_lookup_id"] = $accession_id;
                    }


                    if (empty($data[$i]["uniprot_lookup_id"])) {

                        // // Check to see if there is a TREMBL value.
                        // preg_match("/\|TREMBL:(\w*)/", $row["PeptideGroupId/Description"], $trembl_matches);

                        //  // Check to see if there is a SWISS-PROT value.
                        // preg_match("/\|SWISS-PROT:(\w*)/", $row["PeptideGroupId/Description"], $swiss_prot_matches);


                    }

                    write_log($get["import_log_id"], 'Getting Uniprot data for: ' . $row["Sequence"]);
                    //hack to get uniprot id for problem uniprot ids

                    if ($row["Sequence"] == "GLSTSLPDLDSEPWIEVK") {
                        write_log($get["import_log_id"], 'Inside uniprot hack for: ' . $row["Sequence"]);
                        $data[$i]["uniprot_lookup_id"] = "Q659C4";
                    }
                    if ($row["Sequence"] == "TESEVPPRPASPK") {
                        write_log($get["import_log_id"], 'Inside uniprot hack for: ' . $row["Sequence"]);
                        $data[$i]["uniprot_lookup_id"] = "Q9Y3L3";
                    }
                    if ($row["Sequence"] == "GPASQFYITPSTSLSPR") {
                        write_log($get["import_log_id"], 'Inside uniprot hack for: ' . $row["Sequence"]);
                        $data[$i]["uniprot_lookup_id"] = "P49750";
                    }

                    // Get the gene symbol from UniProt.
                    $uniprot = $assay->get_assay_by_uniprot_api(
                        $get["import_log_id"]
                        , $data[$i]["uniprot_lookup_id"]
                        , $final_global_template_vars["uniprot_protein_api_url"]
                        , $xml2array
                        , $final_global_template_vars["uniprot_regions_array"]
                        , $row["Sequence"]
                        , $row["PeptideGroupId/Description"]
                        , $row["PeptideGroupId/Label"]
                    );

                    $uniprot_result = ($uniprot) ? "SUCCESS" : '<span class="import-error">FAIL</span>';


                    write_log($get["import_log_id"], 'Uniprot data: ' . $uniprot_result);

                    $data[$i]["import_log_id"] = $get["import_log_id"];

                    if ($uniprot) {

                        $data[$i]["gene_symbol"] = $uniprot["gene_symbol"];

                        // Get the modification_type from Panorama.
                        $data[$i]["modification_type"] = "unmodified";
                        // Set the "site_of_modification_peptide" to N/A first, then populate if data is returned by Panorama.
                        $data[$i]["site_of_modification_peptide"] = "N/A";

                        $peptide_modification_type = $labkey->getModificationType(
                            $panorama_authentication_cookie
                            , "targetedms"
                            , "ModificationType"
                            , $row["PeptideModifiedSequence"]
                            , $get['laboratory_abbreviation']
                            , $get['celllysate_path']
                            , 'ResponseCurve'
                        );

                        $peptide_modification_type_result = ($peptide_modification_type) ? "SUCCESS" : '<span class="import-error">FAIL</span>';
                        write_log($get["import_log_id"], 'Getting modification type: ' . $peptide_modification_type_result);


                        if (isset($peptide_modification_type->response) && !empty($peptide_modification_type->response)) {
                            $modification_type = json_decode($peptide_modification_type->response, true);
                            //dump ($modification_type);
                            $modification_type['rowCount'] = isset($modification_type['rowCount']) ? $modification_type['rowCount'] : false;
                            if ($modification_type['rowCount'] > 0) {

                                $modification_rows = array_sort($modification_type['rows'], "IndexAA", $order = SORT_ASC);

                                $data[$i]["modification_type"] = "";
                                $data[$i]["site_of_modification_peptide"] = "";

                                foreach ($modification_rows as $key => $value) {
                                    if (!empty($data[$i]["modification_type"])) {
                                        $data[$i]["modification_type"] = $data[$i]["modification_type"] . ", ";
                                    }

                                    if (!empty($data[$i]["site_of_modification_peptide"])) {
                                        $data[$i]["site_of_modification_peptide"] = $data[$i]["site_of_modification_peptide"] . ", ";
                                    }

                                    if (isset($modification_rows[$key]['modificationType'])) {
                                        $data[$i]["modification_type"] = $data[$i]["modification_type"] . $modification_rows[$key]["modificationType"];
                                    } else if (isset($modification_rows[$key]["StructuralModId/name"])) {
                                        $data[$i]["modification_type"] = $data[$i]["modification_type"] . $modification_rows[$key]["StructuralModId/name"];
                                    }

                                    if (isset($modification_rows[$key]["IndexAA"])) {
                                        $data[$i]["site_of_modification_peptide"] = $data[$i]["site_of_modification_peptide"] . ($modification_rows[$key]["IndexAA"] + 1);
                                    }
                                }
                            }
                        }

                        // Build out the rest of the data array.
                        $data[$i]["import_log_id"] = $get["import_log_id"];
                        $data[$i]["peptide_molecular_weight"] = $row["CalcNeutralMass"];
                        $data[$i]["peptide_sequence"] = $row["Sequence"];
                        $data[$i]["peptide_modified_sequence"] = $row["PeptideModifiedSequence"];
                        $data[$i]["hydrophobicity"] = $row["RtCalculatorScore"];
                        $data[$i]["panorama_peptide_url"] = $row["_labkeyurl_Sequence"];
                        $data[$i]["panorama_protein_url"] = $row["_labkeyurl_PeptideGroupId/Label"];
                        $data[$i]["peptide_start"] = $row["StartIndex"];
                        $data[$i]["peptide_end"] = $row["EndIndex"];
                        $data[$i]["panorama_created_date"] = $row["PeptideGroupId/RunId/Created"];

                    } else {

                        $data[$i]["import_log_id"] = $get["import_log_id"];
                        $data[$i]["peptide_molecular_weight"] = $row["CalcNeutralMass"];
                        $data[$i]["peptide_sequence"] = $row["Sequence"];
                        $data[$i]["peptide_modified_sequence"] = $row["PeptideModifiedSequence"];
                        $data[$i]["hydrophobicity"] = $row["RtCalculatorScore"];
                        $data[$i]["panorama_peptide_url"] = $row["_labkeyurl_Sequence"];
                        $data[$i]["panorama_protein_url"] = $row["_labkeyurl_PeptideGroupId/Label"];
                        $data[$i]["peptide_start"] = $row["StartIndex"];
                        $data[$i]["peptide_end"] = $row["EndIndex"];
                        $data[$i]["panorama_created_date"] = $row["PeptideGroupId/RunId/Created"];


                    }

                    $i++;

                }

                /*
                 * Loop through the data array and perform inserts.
                 */

                // let's just insert protiens and peptides

                write_log($get["import_log_id"], 'Creating initial local records');

                $this_insert_row_count = 0;
                foreach ($data as $key) {

                    write_log($get["import_log_id"], 'Saving: ' . $key['peptide_modified_sequence']);

                    $import->do_initial_inserts($key);
                    ++$this_insert_row_count;

                    if ($request->getParam("test_import") == 1) {
                        if ($this_insert_row_count >= 5)
                            break;
                    }
                }

                write_log($get["import_log_id"], 'Initial DB write complete');
                write_log($get["import_log_id"], 'Preparing to import asset data');

                $this_record_count = 0;

                foreach ($data as $key) {

                    if ($key["uniprot_lookup_id"] && isset($key["peptide_sequence"])) {
                        /*
                        * Create the initial records in the protein and analyte_peptide tables.
                        */

                        //$import->create_initial_records( $key );
                        write_log($get["import_log_id"], 'Getting data for: ' . $key["peptide_sequence"]);

                        /*
                        * Get data from UniProt using the uniprot_lookup_id.
                        */

                        $uniprot_data = $assay->get_assay_by_uniprot_api(
                            $get["import_log_id"]
                            , $key["uniprot_lookup_id"]
                            , $final_global_template_vars["uniprot_protein_api_url"]
                            , $xml2array
                            , $final_global_template_vars["uniprot_regions_array"]
                            , $key["peptide_sequence"]
                        );


                        write_log($get["import_log_id"], 'Getting Uniprot data for: ' . $key["peptide_sequence"]);


                        /*
                         * Get data from Entrez Gene: chromosome_start, chromosome_stop, and chromosome_number.
                         */


                        //@@@CAP-61 - fix assay import blockers
                        /*
                        if (isset($uniprot_data['gene_symbol']) && ($uniprot_data['gene_symbol'] !== FALSE)) {

                            $import_entrez->import_entrez_gene_data(
                                $uniprot_data['gene_symbol']
                                , $final_global_template_vars["entrez_api_url"]
                                , $xml2array
                                , $user_data["email"]
                            );
                        }
                        */

                        $this_gene_symbol = !empty($uniprot_data['gene_symbol']) ? $uniprot_data['gene_symbol'] : '<span class="import-error">GENE SYMBOL MISSING</span>';
                        write_log($get["import_log_id"], 'Getting Entrez Gene data for: ' . $this_gene_symbol);
                        //usleep(500000);

                        /*
                         * Update the peptide_standard_label_type field in the database.
                         */

                        // Query Panorama for the peptide_standard_label_type data.

                        $peptide_standard_label_type = $labkey->getPeptideIsotopeLabelModifications(
                            $panorama_authentication_cookie
                            , $key["peptide_modified_sequence"]
                            , $get['laboratory_abbreviation']
                            , $get['celllysate_path']
                            , 'ResponseCurve');


                        if (isset($peptide_standard_label_type->response) && !empty($peptide_standard_label_type->response)) {
                            // Format into a PHP array.
                            $peptide_standard_label_type = json_decode($peptide_standard_label_type->response, true);

                            // Update query.
                            if (isset($peptide_standard_label_type["rows"][0]["IsotopeModification"]) && !empty($peptide_standard_label_type["rows"][0]["IsotopeModification"])) {
                                $import->import_peptide_standard_label_type($key["peptide_sequence"], $peptide_standard_label_type["rows"][0]["IsotopeModification"]);
                            }

                        }


                        if (isset($uniprot_data['protein_name'])) {
                            /*
                             * Update the protein table in the database with data from UniProt.
                             */
                            $import_uniprot_data->import_uniprot_data($uniprot_data);

                            /*
                             * Insert splice junctions uniprot_data.
                             */
                            $import_uniprot_data->import_uniprot_splice_junctions($uniprot_data["splice_junctions"], $uniprot_data["uniprot_ac"]);

                            /*
                             * Insert SNPs data.
                             */
                            $import_uniprot_data->import_uniprot_snps($uniprot_data["snps"], $uniprot_data["uniprot_ac"]);

                            /*
                             * Insert isoforms data.
                             */
                            $import_uniprot_data->import_uniprot_isoforms($uniprot_data["isoforms"], $uniprot_data["uniprot_ac"]);
                        }


                    } else {
                        // If UniProt lookup is unsuccessful, send an error report to the CPTAC Assay Portal Admin and the end-user.
                        $peptide_sequence = isset($key["peptide_sequence"]) ? $key["peptide_sequence"] : "'unknown sequence'";
                        mail(
                            $final_global_template_vars["superadmin_email_address"] . "," . $user_data["email"]
                            , "CPTAC Import: UniProt lookup unsuccessful for '" . $peptide_sequence . "'"
                            , date('l F jS, Y h:i:s A') . "\n\nExecuted by: " . $get['laboratory_name'] . "\n\nError message: UniProt lookup unsuccessful for '" . $peptide_sequence . "'"
                        );
                    }

                }

            }


            /*
             * Check for missing UniProt data and attempt to import again.
             */

/*            write_log($get["import_log_id"], 'Running Uniprot fix');

            $url = "https://" . $_SERVER["SERVER_NAME"] . "/assays_import/fix_uniprot_import/" . $get["import_log_id"];

            $this->logger->info("uniprot fix " . $url);

            $ch = curl_init($url);
            curl_setopt($ch, CURLINFO_HEADER_OUT, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            curl_close($ch);*/


            /*
             * Delete the Panorama cookie file from the filesystem.
             */

            if (is_file($panorama_authentication_cookie)) {
                unlink($panorama_authentication_cookie);
            }

        } // Test mode ends.

        /*
         * Send an email to the site admin to notify that the executed import has finished.
         */

        //CAP-61 - fix assay import blockers
        /*
        $email_subject = "CPTAC Assay Portal: Main Import Finished, " . date('F j, Y h:i:s A') . " - " . $get['laboratory_name'];
        $headers = $final_global_template_vars['message_parts']['headers'];
        $headers .= 'From: CPTAC Assay Portal <noreply@' . $_SERVER["SERVER_NAME"] . '>' . "\r\n";
        $body_message = '
    <h1>CPTAC Assay Portal: Main Import Finished - ' . $get['laboratory_name'] . '</h1>' .
            $final_global_template_vars['message_parts']['body_connector']
            . '<p><strong>Date/Time:</strong> ' . date('l, F jS, Y \a\t h:i:s A') . '</p>
    <p>An import executed by the "' . $get['laboratory_name'] . '" laboratory has finished. The Panorama images and data import has been executed.</p>
  ';
        $message = $final_global_template_vars['message_parts']['body_header'] . $body_message . $final_global_template_vars['message_parts']['body_footer'];

        // Send the email
        mail($final_global_template_vars["superadmin_email_address"], $email_subject, $message, $headers);
        */

        /*
         * Import From Panorama Into Portal Tables:
         *
         * panorama_chromatogram_images
         * panorama_response_curve_images
         * panorama_validation_sample_images
         * panorama_validation_sample_data
         * lod_loq_comparison
         * response_curves_data
         *
         */

        $test_import = ($request->getParam("test_import") == 1) ? "&test_import=1" : "";


/*        $url = "https://" . $_SERVER["SERVER_NAME"] . "/assays_import/import_panorama_data/?import_log_id=" . $get["import_log_id"]
            . "&imports_executed_log_id=" . $imports_executed_log_id
            . "&account_id=" . $log_data["executed_by_user_id"]
            . $test_import
            . "&uniquehash=" . uniqid();

        $this->logger->info("import panorama data " . $url);


        $ch = curl_init($url);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        curl_close($ch);*/


        $data = [
            "imports_executed_log_id" => $imports_executed_log_id
        ];

        return $response->withJson($data);

        /*
         * Output Message
         */

        // echo "\n\n".'Import ended on '.date('l jS \of F Y h:i:s A').'. Panorama data has been successfully imported.'."\n\n";
    }

}
