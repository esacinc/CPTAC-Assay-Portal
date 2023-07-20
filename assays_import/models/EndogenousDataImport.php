<?php
/**
 * Endogenous Data Import Class
 *
 * @author Toan Le - Software Engineer @ ESAC Inc
 * @version 1.0
 * @package cptac
 *
 */

namespace assays_import\models;

use \PDO;

use core\models\Db\EndogenousData;

class EndogenousDataImport {
    private $session_key = "";
    private $import_error_log;
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

        $import_error_log = new ImportErrorLog();

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
                , 'Endogenous'
            );

            if (isset($peptide_data->response) && !empty($peptide_data->response)) {
                $peptide_data = json_decode($peptide_data->response, true);
                if ($peptide_data['rowCount'] > 0) {
                    if (!empty($peptide_data['rows']) && !empty($peptide_data['rows'][0])) {
                        $this_peptide = $peptide_data['rows'][0];
                        // Query Panorama precursor table for the "Charge" using the modified peptide id
                        $peptide_charge = $this->labkey->getPrecursorChargeData(
                            $panorama_authentication_cookie
                            , "targetedms"
                            , "Precursor"
                            , $this_peptide['Id']
                            , $sequence['laboratory_abbreviation']
                            , $sequence['celllysate_path']
                            , 'Endogenous'
                        );
                        if (isset($peptide_charge->response) && !empty($peptide_charge->response)) {
                            $peptide_charge = json_decode($peptide_charge->response, true);
                            $modified_sequence = $this_peptide['PeptideModifiedSequence'];
                            $protein = $this_peptide['PeptideGroupId/Label'];
                            $charge = $peptide_charge['rows'][0]["Charge"];

                            // Query Panorama precursor table for the "curve_type" using the peptide modified sequence
                            $curve_type_data = $this->labkey->getCurveTypeData(
                                $panorama_authentication_cookie
                                , "targetedms"
                                , "Precursor"
                                , $modified_sequence
                                , $sequence['laboratory_abbreviation']
                                , $sequence['celllysate_path']
                                , 'Endogenous'
                            );

                            if (isset($curve_type_data->response) && !empty($curve_type_data->response)) {
                                $curve_type_data = json_decode($curve_type_data->response, true);
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
                                    $endogenous_data = $this->labkey->getPeptideEndogenousData(
                                        $panorama_authentication_cookie
                                        , $modified_sequence
                                        , $protein
                                        , $charge
                                        , $curve_type
                                        , $sequence['laboratory_abbreviation']
                                        , $sequence['celllysate_path']
                                        , 'Endogenous'
                                    );

                                    if (isset($endogenous_data->response) && !empty($endogenous_data->response)) {
                                        if (stristr($endogenous_data->response, 'Error executing command') == false) {
                                            $this_response = json_decode($endogenous_data->response, true);
                                            if ($this_response['html'] == '<div>Unable to display the specified report.</div>') {
                                                $result["errors"]["get_peptide_endogenous_data"][$i] = $sequence['peptide_sequence'];

                                                // Log the failed image retreival in the database
                                                $this->import_panorama_data->panorama_endogenous_data_failed(
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
                                                $csv = array_map("str_getcsv", explode("\n", $endogenous_data->response));
                                                $keys = array_shift($csv);
                                                array_shift($keys);

                                                foreach ($csv as $i => $row) {
                                                    if ($row == false || $row[0] == NULL) {
                                                        unset($csv[$i]);
                                                    } else {
                                                        array_shift($row);
                                                        $key = array_search('all', $row);
                                                        if (strlen($key) > 0) {
                                                            $row[$key] = 'sum';
                                                        }
                                                        $csv[$i] = array_combine($keys, $row);
                                                        $csv[$i]['peptide_sequence'] = $sequence['peptide_sequence'];
                                                        $csv[$i]['analyte_peptide_id'] = $sequence['analyte_peptide_id'];
                                                        $csv[$i]['laboratory_id'] = $sequence['laboratories_id'];
                                                        $csv[$i]['import_log_id'] = $lab_data["import_log_id"];
                                                    }
                                                }
                                                foreach ($csv as $data) {
                                                    $endogenous_data = EndogenousData::where([
                                                        ['peptide_sequence', $data['peptide_sequence']],
                                                        ['analyte_peptide_id', $data['analyte_peptide_id']],
                                                        ['laboratory_id', $data['laboratory_id']],
                                                        ['fragment_ion', $data['fragment_ion']]
                                                    ])->first();
                                                    if (empty($endogenous_data)) {
                                                        $endogenous_data = new EndogenousData();
                                                        $endogenous_data->fill($data);
                                                        $endogenous_data->created_date = date("Y-m-d H:i:s");
                                                    } else {
                                                        $endogenous_data->fill($data);
                                                    }
                                                    $endogenous_data->save();
                                                }
                                            }
                                        } else {
                                            $result["errors"]["get_peptide_endogenous_data"][$i] = $sequence['peptide_sequence'];

                                            // Error returned from Panorama.
                                            // Log the error to the 'panorama_validation_sample_data_failed' table of the database.
                                            $this->import_panorama_data->panorama_endogenous_data_failed(
                                                $lab_data["import_log_id"]
                                                , $sequence['analyte_peptide_id']
                                                , $sequence['peptide_sequence']
                                                , $sequence["peptide_modified_sequence"]
                                                , $sequence['laboratory_name']
                                                , $sequence['laboratory_abbreviation']
                                                , $endogenous_data->response
                                                , $endogenous_data->panoramaUrl
                                            );
                                        }
                                    }
                                }
                            } else {
                                $result["errors"]["get_peptide_endogenous_curve_type"][$i] = $sequence['peptide_sequence'];

                                // Log the failed 'curve type' retrieval in the database
                                $this->import_panorama_data->panorama_endogenous_data_failed(
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
                            $result["errors"]["get_peptide_endogenous_charge"][$i] = $sequence['peptide_sequence'];

                            // Log the failed 'charge' retrieval in the database
                            $this->import_panorama_data->panorama_endogenous_data_failed(
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
                        $result["errors"]["get_peptide_response"][$i] = $sequence['peptide_sequence'];

                        $this->import_panorama_data->panorama_endogenous_data_failed(
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
                }
            } else {
                $result["errors"]["get_peptide_response"][$i] = $sequence['peptide_sequence'];

                // Log the failed peptide retrieval in the database
                $this->import_panorama_data->panorama_endogenous_data_failed(
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
        }
        return $result;
    }
}
