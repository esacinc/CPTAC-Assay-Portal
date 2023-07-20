<?php

namespace assays_import\models;
/**
 * Chromatograms Import Class
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */

namespace assays_import\models;

use \PDO;

class ChromatogramsImport {
    private $session_key = "";
    public $db;

    /**
     * Constructor
     *
     * @param object $db_connection The database connection object
     * @param object $import_panorama_data The Import Panorama Data class
     * @param object $labkey The LabkeyApi class
     */

    public function __construct($db_connection = false, $import_panorama_data = false, $labkey = false) {

        if ($db_connection && is_object($db_connection)) {
            $this->db = $db_connection;
        }

        global $final_global_template_vars;
        $this->final_global_template_vars = $final_global_template_vars;

        if ($import_panorama_data && is_object($import_panorama_data)) {
            $this->import_panorama_data = $import_panorama_data;
        }

        if ($labkey && is_object($labkey)) {
            $this->labkey = $labkey;
        }

    }

    /**
     * Run Import
     *
     * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
     * @param array $sequences The sequences array
     * @param string $panorama_authentication_cookie The Panorama authentication cookie value
     * @param bool $delete_old_files_and_data Whether to delete old files and data
     * @param array $lab_data The laboratory data array
     * @return array
     */

    public function run_import(
        $sequences = array()
        , $panorama_authentication_cookie = false
        , $delete_old_files_and_data = false
        , $lab_data = false
        , $import_log_id = false) {

        // Set the $result to an empty array, by default.
        $result = array();
        $i = 0;
        foreach ($sequences as $sequence) {

            /*
             * First, delete any old data and images.
             */

            write_log($import_log_id, 'Importing: ' . $sequence['peptide_modified_sequence']);


            if ($delete_old_files_and_data) {

                // Get the file names.
                $file_names = $this->import_panorama_data->get_images_data(
                    'panorama_chromatogram_images'
                    , $sequence['analyte_peptide_id']
                    , $sequence['laboratories_id']
                );


                // Loop through, delete the files, and delete the corresponding database records.
                foreach ($file_names as $file) {
                    // Files
                    if (file_exists($this->final_global_template_vars["panorama_images_storage_path"] . $file["file_name"])) {
                        unlink($this->final_global_template_vars["panorama_images_storage_path"] . $file["file_name"]);
                        write_log($import_log_id, 'Deleting old image: ' . $this->final_global_template_vars["panorama_images_storage_path"] . $file["file_name"]);
                    }
                    // Data
                    $this->import_panorama_data->delete_data('panorama_chromatogram_images', $file["panorama_chromatogram_images_id"]);
                    write_log($import_log_id, 'Deleting old data: ID#' . $file["panorama_chromatogram_images_id"]);
                }
            }

            /*
             * Process the import logic.
             */


            // Query Panorama for the peptide data.
            $peptide_data = $this->labkey->getPrecursorData(
                $panorama_authentication_cookie
                , "targetedms"
                , "Precursor"
                , $sequence['peptide_modified_sequence']
                , $sequence['laboratory_abbreviation']
                , $sequence['celllysate_path']
                , 'ChromatogramLibrary'
            );


            write_log($import_log_id, 'Query Panorama for the peptide data: ChromatogramLibrary - ' . $sequence['peptide_modified_sequence']);

            if (isset($peptide_data->response) && !empty($peptide_data->response)) {

                $peptide_data = json_decode($peptide_data->response, true);

                if (!empty($peptide_data)) {
                    foreach ($peptide_data['rows'] as $rows_key => $rows_value) {
                        if ($rows_value['RepresentativeDataState'] != 1) {
                            // Remove rows where RepresentativeDataState does not equal 1.
                            unset($peptide_data['rows'][$rows_key]);
                        }
                    }
                }

                if (!empty($peptide_data['rows'])) {

                    write_log($import_log_id, 'Panorama returned rows: ' . count($peptide_data['rows']));


                    // Reindex the array
                    $peptide_data['rows'] = array_values($peptide_data['rows']);


                    // Get chromatograms from Panorama
                    write_log($import_log_id, 'Get images: ChromatogramLibrary - PeptideId/Id: ' . $peptide_data['rows'][0]['PeptideId/Id']);
                    $chromatograms = $this->labkey->getPeptideChromatograms(
                        $panorama_authentication_cookie
                        , $peptide_data['rows'][0]['PeptideId/Id']
                        , $sequence['laboratory_abbreviation']
                        , $sequence['celllysate_path']
                        , 'ChromatogramLibrary'
                    );

                    if (isset($chromatograms->response) && !empty($chromatograms->response)) {

                        $chromatograms = json_decode($chromatograms->response, true);

                        // Log zero row counts.
                        if ($chromatograms['rowCount'] == 0) {

                            write_log($import_log_id, 'Get image FAILED: ChromatogramLibrary: ' . $chromatograms->panoramaUrl);

                            $result["errors"]["row_count_zero"][$i] = $sequence['peptide_sequence'];

                            // Error returned from Panorama.
                            // Log the error to the 'panorama_chromatogram_images_failed' table of the database.
                            $this->import_panorama_data->panorama_chromatogram_images_failed(
                                $lab_data["import_log_id"]
                                , "N/A"
                                , $sequence['analyte_peptide_id']
                                , $sequence['peptide_sequence']
                                , $sequence["peptide_modified_sequence"]
                                , $sequence['laboratory_name']
                                , $sequence['laboratory_abbreviation']
                                , $chromatograms->response
                                , $chromatograms->panoramaUrl
                            );
                        }

                        if ($chromatograms['rowCount'] >= 1) {

                            // Get the chromatogram images...
                            // The main chromatogram image id.
                            $chromatogram_image_id = $chromatograms['rows'][0]['Id'];

                            /*
                             * Process the first chromatogram image.
                             */

                            // Get the chromatogram image, insert into database
                            $chromatogram_image = $this->labkey->getPeptideChromatogramImage(
                                $panorama_authentication_cookie
                                , $chromatogram_image_id
                                , $sequence['peptide_modified_sequence']
                                , $sequence['laboratory_abbreviation']
                                , $sequence['celllysate_path']
                                , 'ChromatogramLibrary');

                            write_log($import_log_id, 'Get the chromatogram image, insert into database: ' . $sequence['peptide_modified_sequence']);

                            if (isset($chromatogram_image->response) && !empty($chromatogram_image->response)) {

                                if (stristr($chromatogram_image->response, 'Error executing command') == false) {

                                    // Returns the path to the file. Just send the filename.
                                    $chromatogram_image_path = explode('/', $chromatogram_image->response);

                                    write_log($import_log_id, 'Chromatogram Image Path: ' . $chromatogram_image->response);

                                    $data["import_log_id"] = $lab_data["import_log_id"];
                                    $data["analyte_peptide_id"] = $sequence['analyte_peptide_id'];
                                    $data["laboratory_id"] = $sequence['laboratories_id'];
                                    $data["file_name"] = $chromatogram_image_path[6];
                                    $data["sequence"] = $sequence['peptide_modified_sequence'];

                                    write_log($import_log_id, 'Saved chromatogram image filename: ' . $data["file_name"]);

                                    // Insert the image data into the database
                                    $this->import_panorama_data->import_chromatogram_images($data);


                                } else {

                                    $result["errors"]["get_image_one_failed"][$i] = $sequence['peptide_sequence'];

                                    write_log($import_log_id, 'Get chromatogram image FAILED: ' . $sequence['peptide_sequence']);

                                    // Error returned from Panorama.
                                    // Log the error to the 'panorama_chromatogram_images_failed' table of the database.
                                    $this->import_panorama_data->panorama_chromatogram_images_failed(
                                        $lab_data["import_log_id"]
                                        , 'medium'
                                        , $sequence['analyte_peptide_id']
                                        , $sequence['peptide_sequence']
                                        , $sequence["peptide_modified_sequence"]
                                        , $sequence['laboratory_name']
                                        , $sequence['laboratory_abbreviation']
                                        , $chromatogram_image->response
                                        , $chromatogram_image->panoramaUrl
                                    );
                                }

                            }


                            /*
                             * Process the other two chromatogram images
                             */

                            // Get the low and high chromatogram info
                            $precursor_chrom_info = $this->labkey->getPrecursorChromInfo(
                                $panorama_authentication_cookie
                                , "targetedms"
                                , "PrecursorChromInfo"
                                , $chromatogram_image_id
                                , $sequence['laboratory_abbreviation']
                                , $sequence['celllysate_path']
                                , 'ChromatogramLibrary'
                            );

                            if (isset($precursor_chrom_info->response) && !empty($precursor_chrom_info->response)) {

                                $precursor_chrom_info = json_decode($precursor_chrom_info->response, true);
                                $precursor_chromatogram_image_ids = array();

                                // Put the low and high precursor chromatogram image ids into an array
                                if ($precursor_chrom_info['rowCount'] > 0) {
                                    foreach ($precursor_chrom_info['rows'] as $precursor_chrom) {
                                        $precursor_chromatogram_image_ids[] = $precursor_chrom['Id'];
                                    }
                                }

                                // Loop through the precursor chromatogram image ids, get the precursor chromatogram images, insert into database
                                $hilo = 0;
                                foreach ($precursor_chromatogram_image_ids as $precursor_chromatogram_image_id) {

                                    $precursor_chromatogram_image = $this->labkey->getPrecursorChromatogramImage(
                                        $panorama_authentication_cookie
                                        , $precursor_chromatogram_image_id
                                        , $sequence['peptide_modified_sequence']
                                        , $sequence['laboratory_abbreviation']
                                        , $sequence['celllysate_path']
                                        , 'ChromatogramLibrary');

                                    if (isset($precursor_chromatogram_image->response) && !empty($precursor_chromatogram_image->response)) {

                                        if (stristr($precursor_chromatogram_image->response, 'Error executing command') == false) {

                                            // Returns the path to the file. Just send the filename.
                                            $precursor_chromatogram_image_path = explode('/', $precursor_chromatogram_image->response);

                                            $data["import_log_id"] = $lab_data["import_log_id"];
                                            $data["analyte_peptide_id"] = $sequence['analyte_peptide_id'];
                                            $data["laboratory_id"] = $sequence['laboratories_id'];
                                            $data["file_name"] = $precursor_chromatogram_image_path[6];
                                            $data["sequence"] = $sequence['peptide_modified_sequence'];

                                            // Insert/update the image data in the database
                                            $this->import_panorama_data->import_chromatogram_images($data);

                                        } else {
                                            // Error returned from Panorama.
                                            // Log the error to the 'panorama_chromatogram_images_failed' table of the database.
                                            // Set the chromatogram type.
                                            $type = ($hilo == 0) ? "low" : "high";

                                            $result["errors"]["get_image_" . $type . "_failed"][$i] = $sequence['peptide_sequence'];

                                            $this->import_panorama_data->panorama_chromatogram_images_failed(
                                                $lab_data["import_log_id"]
                                                , $type
                                                , $sequence['analyte_peptide_id']
                                                , $sequence['peptide_sequence']
                                                , $sequence["peptide_modified_sequence"]
                                                , $sequence['laboratory_name']
                                                , $sequence['laboratory_abbreviation']
                                                , $precursor_chromatogram_image->response
                                                , $precursor_chromatogram_image->panoramaUrl
                                            );
                                        }

                                    }

                                    // Make sure we give Panorama enough time to get get different filenames
                                    usleep(500000);
                                    $hilo++;
                                }

                            }

                        }

                    } else {
                        /*
                         * Nothing returned from the getPeptideChromatograms() API call.
                         * Seems like the Panorama API always returns a $chromatograms->response.
                         */
                        write_log($import_log_id, 'Panorama returned NO peptide data: ' . $sequence['peptide_modified_sequence']);
                    }

                } else {

                    $result["errors"]["get_peptide_empty"][$i] = $sequence['peptide_sequence'];

                    /*
                     * Send an email notification to the portal super administrator if
                     * Panorama doesn't return data for the Precursor query.
                     */
                    $email_subject = "CPTAC Assay Portal: Chromatograms Import: Peptide Row Empty";
                    $headers = $this->final_global_template_vars['message_parts']['headers'];
                    $headers .= 'From: CPTAC Assay Portal <noreply@' . $_SERVER["SERVER_NAME"] . '>' . "\r\n";
                    $body_message = '
                                        <h1>Chromatograms - Get Peptide Row Empty - ' . $lab_data['laboratory_name'] . '</h1>' .
                        $this->final_global_template_vars['message_parts']['body_connector']
                        . '<p><strong>Date/Time:</strong> ' . date('l, F jS, Y \a\t h:i:s A') . '</p>
              <p>Sequence: ' . $sequence['peptide_sequence'] . '</p>
              <p>Precursor query failed out on Panorama. This may just be a data transmission error relating to Panorama\'s API. If this persists, please check Panorama.</p>';
                    $message = $this->final_global_template_vars['message_parts']['body_header'] . $body_message . $this->final_global_template_vars['message_parts']['body_footer'];
                    // Send the email.
                    mail($this->final_global_template_vars["superadmin_email_address"], $email_subject, $message, $headers);

                    /*
                     * Log the error to the 'panorama_chromatogram_images_failed' table of the database.
                     */
                    $this->import_panorama_data->panorama_chromatogram_images_failed(
                        $lab_data["import_log_id"]
                        , "N/A"
                        , $sequence['analyte_peptide_id']
                        , $sequence['peptide_sequence']
                        , $sequence["peptide_modified_sequence"]
                        , $sequence['laboratory_name']
                        , $sequence['laboratory_abbreviation']
                        , 'Precursor query failed out on Panorama. This may just be a data transmission error relating to Panorama\'s API. If this persists, please check Panorama.'
                        , $peptide_data->panoramaUrl
                    );

                }

            }
            $i++;
        }

        return $result;

    }

}
