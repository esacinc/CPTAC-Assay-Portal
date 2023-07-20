<?php
/**
 * Validation Sample Images Import Class
 *
 * @author The Advanced Biomedical Computing Center (ABCC) - SWPG - halusagn
 * @version 1.0
 * @package cptac
 *
 */

namespace assays_import\models;

use \PDO;

class ValidationSampleDataImport
{
    private $session_key = "";
    public $db;

    /**
     * Constructor
     *
     * @param object $db_connection The database connection object
     * @param object $import_panorama_data The Import Panorama Data class
     * @param object $labkey The LabkeyApi class
     */

    public function __construct($db_connection = false, $import_panorama_data = false, $labkey = false)
    {

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
        , $lab_data = false)
    {

        // Set the $result to an empty array, by default.
        $result = array();
        $i = 0;
        foreach ($sequences as $sequence) {

            // Query Panorama peptide table for the data (modified_sequence and protein) using the peptide sequence
            $peptide_data = $this->labkey->getPeptide(
                $panorama_authentication_cookie
                , "targetedms"
                , "Peptide"
                , $sequence['peptide_sequence']
                , $sequence['laboratory_abbreviation']
                , $sequence['celllysate_path']
                , 'ValidationSamples'
            );


            if (isset($peptide_data->response) && !empty($peptide_data->response)) {


                $peptide_data = json_decode($peptide_data->response, true);

                if ($peptide_data['rowCount'] > 0) {

                    if (!empty($peptide_data['rows']) && !empty($peptide_data['rows'][0])) {

                        // Query Panorama precursor table for the "Charge" using the modified peptide id
                        $peptide_charge = $this->labkey->getPrecursorChargeData(
                            $panorama_authentication_cookie
                            , "targetedms"
                            , "Precursor"
                            , $peptide_data['rows'][0]['Id']
                            , $sequence['laboratory_abbreviation']
                            , $sequence['celllysate_path']
                            , 'ValidationSamples'
                        );

                        if (isset($peptide_charge->response) && !empty($peptide_charge->response)) {

                            $peptide_charge = json_decode($peptide_charge->response, true);
                            // Simplify some variables
                            $modified_sequence = $peptide_data['rows'][0]['PeptideModifiedSequence'];
                            $protein = $peptide_data['rows'][0]['PeptideGroupId/Label'];
                            $charge = $peptide_charge['rows'][0]["Charge"];

                            // Query Panorama precursor table for the "curve_type" using the peptide modified sequence
                            $curve_type_data = $this->labkey->getCurveTypeData(
                                $panorama_authentication_cookie
                                , "targetedms"
                                , "Precursor"
                                , $modified_sequence
                                , $sequence['laboratory_abbreviation']
                                , $sequence['celllysate_path']
                                , 'ValidationSamples'
                            );

                            if (isset($curve_type_data->response) && !empty($curve_type_data->response)) {
                                // Test the url to query the returned json data
                                // https://panoramaweb.org/query/CPTAC%20Assay%20Portal/Koomen/RSLCnano_Quantiva_MRM3/ValidationSamples/selectRows.api?schemaName=targetedms&query.queryName=Precursor&query.PeptideModifiedSequence%7eq=SLLSGLLK
                                $curve_type_data = json_decode($curve_type_data->response, true);
                                // https://panoramaweb.org/query/CPTAC%20Assay%20Portal/Koomen/RSLCnano_Quantiva_MRM3/ValidationSamples/selectRows.api?schemaName=targetedms&query.queryName=Precursor&query.PeptideModifiedSequence%7eq=SLLSGLLK&query.columns=PeptideId%2FPeptideGroupId%2FLabel%2CPeptideId%2FPeptideModifiedSequence%2CIsotopeLabelId%2FName%2CIsotopeLabelId%2FStandard

                                //Simplify some variables
                                $curve_type_data = $curve_type_data['rows'];
                                $isotope_standard_status = false;
                                $isotope_standard_label = false;
                                foreach ($curve_type_data as $curve_type_value) {
                                    $isotope_standard = $curve_type_value['IsotopeLabelId/Standard'];
                                    $isotope_label = $curve_type_value['IsotopeLabelId/Name'];
                                    if ($isotope_standard) {
                                        $isotope_standard_status = $isotope_standard;
                                        $isotope_standard_label = $isotope_label;
                                        break;
                                    }
                                }
                                if ($isotope_standard_status) {
                                    if ($isotope_standard_label == 'heavy') {
                                        $curve_type = 'forward';
                                    } else {
                                        $curve_type = 'reverse';
                                    }

                                    // Query Panorama for validation sample data using the modified sequence, protein, charge and curve type
                                    $validation_sample_data = $this->labkey->getPeptideValidationSampleData(
                                        $panorama_authentication_cookie
                                        , $modified_sequence
                                        , $protein
                                        , $charge
                                        , $curve_type
                                        , $sequence['laboratory_abbreviation']
                                        , $sequence['celllysate_path']
                                        , 'ValidationSamples'
                                    );


                                    if (isset($validation_sample_data->response) && !empty($validation_sample_data->response)) {


                                        if (stristr($validation_sample_data->response, 'Error executing command') == false) {

                                            $this_response = json_decode($validation_sample_data->response, true);


                                            if ($this_response['html'] == '<div>Unable to display the specified report.</div>') {

                                                $result["errors"]["get_peptide_validation_sample_data"][$i] = $sequence['peptide_sequence'];

                                                // Log the failed image retreival in the database
                                                $this->import_panorama_data->panorama_validation_sample_data_failed(
                                                    $lab_data["import_log_id"]
                                                    , $sequence['analyte_peptide_id']
                                                    , $sequence['peptide_sequence']
                                                    , $sequence["peptide_modified_sequence"]
                                                    , $sequence['laboratory_name']
                                                    , $sequence['laboratory_abbreviation']
                                                    , $this_response['html']
                                                    , $this_response["panoramaUrl"]
                                                );
                                            } else {
                                                // Create the array for the database insert
                                                $file_arrays = array();
                                                $file = explode("\n", $validation_sample_data->response);
                                                array_pop($file);
                                                $a = 0;
                                                foreach ($file as $file_value) {
                                                    // Skip the first line, which are the column names
                                                    if ($a > 0) {

                                                        $file_array = explode(',', $file_value);
                                                        // Change the value, '"all"' to '"sum"'
                                                        if (array_search('"all"', $file_array)) {
                                                            $key = array_search('"all"', $file_array);
                                                            $file_array[$key] = '"sum"';
                                                        }
                                                        array_shift($file_array);
                                                        array_push($file_array, $sequence['peptide_sequence']);
                                                        array_push($file_array, $sequence['analyte_peptide_id']);
                                                        array_push($file_array, $sequence['laboratories_id']);
                                                        array_push($file_array, $lab_data["import_log_id"]);
                                                        $file_arrays[] = $file_array;
                                                    }
                                                    $a++;
                                                }

                                                // Insert file data into the database
                                                $this->import_panorama_data->import_validation_sample_images_data($file_arrays);
                                            }

                                        } else {

                                            $result["errors"]["get_peptide_validation_sample_data"][$i] = $sequence['peptide_sequence'];

                                            // Error returned from Panorama.
                                            // Log the error to the 'panorama_validation_sample_data_failed' table of the database.
                                            $this->import_panorama_data->panorama_validation_sample_data_failed(
                                                $lab_data["import_log_id"]
                                                , $sequence['analyte_peptide_id']
                                                , $sequence['peptide_sequence']
                                                , $sequence["peptide_modified_sequence"]
                                                , $sequence['laboratory_name']
                                                , $sequence['laboratory_abbreviation']
                                                , $validation_sample_data->response
                                                , $validation_sample_data->panoramaUrl
                                            );
                                        }
                                    }

                                } else {
                                    $result["errors"]["get_peptide_validation_sample_internal_standard"][$i] = $sequence['peptide_sequence'];

                                    // Log the failed 'internal standard' retrieval in the database
                                    $this->import_panorama_data->panorama_validation_sample_data_failed(
                                        $lab_data["import_log_id"]
                                        , $sequence['analyte_peptide_id']
                                        , $sequence['peptide_sequence']
                                        , $sequence["peptide_modified_sequence"]
                                        , $sequence['laboratory_name']
                                        , $sequence['laboratory_abbreviation']
                                        , 'Failed to retrieve internal standard'
                                        , false
                                    );
                                }
                            } else {
                                $result["errors"]["get_peptide_validation_sample_curve_type"][$i] = $sequence['peptide_sequence'];

                                // Log the failed 'curve type' retreival in the database
                                $this->import_panorama_data->panorama_validation_sample_data_failed(
                                    $lab_data["import_log_id"]
                                    , $sequence['analyte_peptide_id']
                                    , $sequence['peptide_sequence']
                                    , $sequence["peptide_modified_sequence"]
                                    , $sequence['laboratory_name']
                                    , $sequence['laboratory_abbreviation']
                                    , 'Failed to retrieve curve type'
                                    , false
                                );
                            }
                        } else {
                            $result["errors"]["get_peptide_validation_sample_charge"][$i] = $sequence['peptide_sequence'];

                            // Log the failed 'charge' retreival in the database
                            $this->import_panorama_data->panorama_validation_sample_data_failed(
                                $lab_data["import_log_id"]
                                , $sequence['analyte_peptide_id']
                                , $sequence['peptide_sequence']
                                , $sequence["peptide_modified_sequence"]
                                , $sequence['laboratory_name']
                                , $sequence['laboratory_abbreviation']
                                , 'Failed to retrieve charge'
                                , false
                            );
                        }
                    } else {

                        $result["errors"]["get_peptide_rows"][$i] = $sequence['peptide_sequence'];

                        // Log the failed peptide retreival in the database
                        $this->import_panorama_data->panorama_validation_sample_data_failed(
                            $lab_data["import_log_id"]
                            , $sequence['analyte_peptide_id']
                            , $sequence['peptide_sequence']
                            , $sequence["peptide_modified_sequence"]
                            , $sequence['laboratory_name']
                            , $sequence['laboratory_abbreviation']
                            , 'Unable to get peptide data (no rows returned)'
                            , false
                        );
                    }

                }
            } else {

                $result["errors"]["get_peptide_response"][$i] = $sequence['peptide_sequence'];

                // Log the failed peptide retreival in the database
                $this->import_panorama_data->panorama_validation_sample_data_failed(
                    $lab_data["import_log_id"]
                    , $sequence['analyte_peptide_id']
                    , $sequence['peptide_sequence']
                    , $sequence["peptide_modified_sequence"]
                    , $sequence['laboratory_name']
                    , $sequence['laboratory_abbreviation']
                    , 'Unable to get peptide data (no response returned)'
                    , false
                );
            }
            // Make sure we give Panorama enough time
            usleep(500000);

            $i++;
            // if($i > 14) break;
        }
        return $result;
    }
}
