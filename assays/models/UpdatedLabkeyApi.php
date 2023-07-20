<?php
namespace assays\models;
use GuzzleHttp\Cookie\FileCookieJar;

/**
 * @desc LabKey/Panorama API for importing data into the CPTAC Assay Portal database
 *
 * @author ESAC's CPTAC Team
 * @version 1.0
 * @package cptac
 *
 */
class UpdatedLabkeyApi {

    protected $email;
    protected $password;
    protected $endpoint;
    protected $responseHeaders;
    protected $client;

    function __construct($labkey_config = false, $panorama_images_path = false, $panorama_images_storage_path = false, $import_log_id = false) {
        $this->panorama_images_path = $panorama_images_path . $import_log_id;
        $this->panorama_images_storage_path = $panorama_images_storage_path . $import_log_id;
        $this->labkey_config = $labkey_config;
        $this->import_log_id = $import_log_id;
        $labkey_login = new \assays\models\LoginApi($this->labkey_config);

        $this->client = $labkey_login->getGuzzleClient();
    }

    /*
     * Get All Peptides
     */

    // https://daily.panoramaweb.org/labkey/query/CPTAC%20Assay%20Portal/JHU_DChan_HZhang_ZZhang/Serum_QExactive_GlycopeptideEnrichedPRM/ResponseCurve/selectRows.api?schemaName=targetedms&query.queryName=Peptide&query.columns=Sequence%2CPeptideModifiedSequence%2CRtCalculatorScore%2CStartIndex%2CEndIndex%2CCalcNeutralMass%2CPeptideGroupId%2FLabel%2CPeptideGroupId%2FRunId%2CPeptideGroupId%2FRunId%2FCreated%2CPeptideGroupId%2FRunId%2FFile%2FFileName%2CPeptideGroupId%2FDescription

    public function getAllPeptides(
        $panorama_authentication_cookie = false
        , $schemaName
        , $queryName
        , $queryColumns
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {


        $data = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/selectRows.api'
            , array(
                'schemaName' => $schemaName
            , 'query.queryName' => $queryName
            , 'query.columns' => $queryColumns
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
        );

        return $data;
    }

    /*
     * Get Peptide
     */

    public function getPeptide(
        $panorama_authentication_cookie = false
        , $schemaName
        , $queryName
        , $peptideSequence
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $data = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/selectRows.api'
            , array(
                'schemaName' => $schemaName
            , 'query.queryName' => $queryName
            , 'query.PeptideModifiedSequence~eq' => $peptideSequence
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
        );

        return $data;
    }

    /*
     * Get Peptide Data
     */

    public function getPeptideData(
        $panorama_authentication_cookie = false
        , $schemaName
        , $queryName
        , $peptideId
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        return $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/selectRows.api'
            , array(
                'schemaName' => $schemaName
            , 'query.queryName' => $queryName
            , 'query.Id~eq' => $peptideId
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
        );
    }

    /*
     * Get Precursor Data
     */

    public function getPrecursorData(
        $panorama_authentication_cookie = false
        , $schemaName
        , $queryName
        , $sequence
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        return $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/selectRows.api'
            , array(
                'schemaName' => $schemaName
            , 'query.queryName' => $queryName
            , 'query.PeptideId/PeptideModifiedSequence~eq' => $sequence
            , 'query.columns' => 'RepresentativeDataState,PeptideId/Sequence,PeptideId/PeptideModifiedSequence,ModifiedSequence,IsotopeLabelId/Name,PeptideId/Id,PeptideId/PeptideGroupId/Label,PeptideId/PeptideGroupId/Id'
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
        );
    }

    /*
     * Get Precursor Charge Data
     */

    public function getPrecursorChargeData(
        $panorama_authentication_cookie = false
        , $schemaName
        , $queryName
        , $modifiedPeptideId
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        return $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/selectRows.api'
            , array(
                'schemaName' => $schemaName
            , 'query.queryName' => $queryName
            , 'query.PeptideId~eq' => $modifiedPeptideId
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
        );

    }

    /*
     * Get Modification Type
     */

    public function getModificationType(
        $panorama_authentication_cookie = false
        , $schemaName
        , $queryName
        , $peptideModifiedSequence
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $data = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/selectRows.api'
            , array(
                'schemaName' => $schemaName
            , 'query.queryName' => $queryName
            , 'query.PeptideId/PeptideModifiedSequence~eq' => $peptideModifiedSequence
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
        );

        return $data;
    }

    /*
     * Get the Site of Modification
     */

    // https://daily.panoramaweb.org/labkey/query/CPTAC%20Assay%20Portal/JHU_DChan_HZhang_ZZhang/Serum_QExactive_GlycopeptideEnrichedPRM/ResponseCurve/getQuery.view?query.columns=PeptideId,PeptideId/PeptideModifiedSequence,PeptideId/Sequence,StructuralModId/Name,IndexAA,MassDiff&schemaName=targetedms&query.queryName=PeptideStructuralModification&query.PeptideId~eq=181120

    public function getSiteOfModification(
        $panorama_authentication_cookie = false
        , $schemaName
        , $queryName
        , $peptideId
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $data = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/selectRows.api'
            , array(
                'schemaName' => $schemaName
            , 'query.queryName' => $queryName
            , 'query.PeptideId~eq' => $peptideId
            , 'query.columns' => 'IndexAA'
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
        );

        return $data;
    }

    /*
     * Get Precursor Cromatogram Information
     */

    public function getPrecursorChromInfo(
        $panorama_authentication_cookie = false
        , $schemaName
        , $queryName
        , $PeptideChromInfoId
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        return $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/selectRows.api'
            , array(
                'schemaName' => $schemaName
            , 'query.queryName' => $queryName
            , 'query.PeptideChromInfoId~eq' => $PeptideChromInfoId
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
        );
    }

    /*
     * Get Peptide Chromatograms
     */

    public function getPeptideChromatograms(
        $panorama_authentication_cookie = false
        , $peptideId = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        return $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/selectRows.api'
            , array(
                'schemaName' => 'targetedms'
            , 'query.queryName' => 'peptidechrominfo'
            , 'query.PeptideId~eq' => $peptideId
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
        );
    }

    /*
     * Get Peptide Chromatogram Image
     */

    public function getPeptideChromatogramImage(
        $panorama_authentication_cookie = false
        , $chromatogram_id = false
        , $peptide_sequence = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $returnObj = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/peptideChromatogramChart.view'
            , array(
                'id' => $chromatogram_id
            , 'chartWidth' => '600'
            , 'chartHeight' => '600'
            , 'syncY' => 'false'
            , 'syncX' => 'false'
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // plot_type
            , false // protein
            , false // csv
        );

        $chromatogram_type = 'peptide';
        if($returnObj->error == false) {
            $result = $this->downloadChromatogramImages(
                $returnObj->response
                , $chromatogram_type
                , $returnObj->parameters
                , $peptide_sequence
                , $laboratory_abbreviation);
            if($result == false) {
                $returnObj->error = true;
            }
            $returnObj->response = $result;
        }

        return $returnObj;
    }

    /*
     * Get Precursor Chromatogram Image
     */

    public function getPrecursorChromatogramImage(
        $panorama_authentication_cookie = false
        , $precursor_chromatogram_id = false
        , $peptide_sequence = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $returnObj = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/precursorChromatogramChart.view'
            , array(
                'id' => $precursor_chromatogram_id
            , 'chartWidth' => '600'
            , 'chartHeight' => '600'
            , 'syncY' => 'false'
            , 'syncX' => 'false'
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // plot_type
            , false // protein
            , false // csv
        );

        $chromatogram_type = 'precursor';
        if($returnObj->error == false) {
            $result = $this->downloadChromatogramImages(
                $returnObj->response
                , $chromatogram_type
                , $returnObj->parameters
                , $peptide_sequence
                , $laboratory_abbreviation);
            if($result == false) {
                $returnObj->error = true;
            }
            $returnObj->response = $result;
        }

        return $returnObj;

    }

    /*
     * Get Peptide Response Curve Image
     */

    public function getPeptideResponseCurveImage(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $plot_type = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false
        , $peptideType = false) {


        $returnObj = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/getWebPart.view'
            , array(
                'webpart.name' => 'Report'
            , 'webpart.frame' => 'none'
            , 'schemaName' => 'targetedms'
            , 'queryName' => 'ResponseCurveQuery'
            , 'reportName' => 'ResponseCurve'
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            , 'plotType' => $plot_type
            , 'showSection' => 'response_curve_png'
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , $plot_type
            , false // protein
            , false // csv
            , $peptideType
        );

        $report_name = "response_curve";

        if($returnObj->error == false) {

            $result = $this->downloadPlotImage(
                $returnObj->response
                , 'GET'
                , $peptide_sequence
                , $laboratory_abbreviation
                , $report_name
                , $plot_type
                , $panorama_authentication_cookie);

            if($result == false) {
                $returnObj->error = true;
            }
            $returnObj->response = $result;

        }

        return $returnObj;
    }

    /*
    * Reset Peptide Response Curve Image
    */

    public function buildResetPeptideResponseCurveUrls(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false
        , $peptideType = false) {

        $data = array();

        $data[0] = $this->buildUrl(
            $panorama_authentication_cookie
            , 'GET'
            , '/executeQuery.view'
            , array(
                'schemaName' => 'targetedms'
            , 'query.queryName' => 'ResponseCurveQuery'
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // protein
            , false // csv
            , $peptideType
        );

        $data[1] = $this->buildUrl(
            $panorama_authentication_cookie
            , 'GET'
            , '/executeQuery.view'
            , array(
                'schemaName' => 'targetedms'
            , 'query.queryName' => 'ResponseCurveQuery'
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            , 'query.reportId' => 'module:AssayPortal/reports/schemas/targetedms/ResponseCurveQuery/ResponseCurve.r'
            , 'reportId' => 'module:AssayPortal/reports/schemas/targetedms/ResponseCurveQuery/ResponseCurve.r'
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // protein
            , false // csv
            , $peptideType
        );

        return $data;
    }

    public function buildResetPeptideResponseCurveAnalysisUrls(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false
        , $peptideType = false) {

        $data = array();

        $data[0] = $this->buildUrl(
            $panorama_authentication_cookie
            , 'GET'
            , '/executeQuery.view'
            , array(
                'schemaName' => 'targetedms'
            , 'query.queryName' => 'ResponseCurveAnalysis'
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // protein
            , false // csv
            , $peptideType
        );

        $data[1] = $this->buildUrl(
            $panorama_authentication_cookie
            , 'GET'
            , '/executeQuery.view'
            , array(
                'schemaName' => 'targetedms'
            , 'query.queryName' => 'ResponseCurveAnalysis'
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            , 'query.reportId' => 'module:AssayPortal/reports/schemas/targetedms/ResponseCurveAnalysis/ResponseCurveAnalysis.r'
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // protein
            , false // csv
            , $peptideType
        );

        return $data;
    }

    /*
     * Get Peptide Validation Sample Image (Repeatability)
     */

    public function getPeptideValidationSampleImage(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $protein = false
        , $charge = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $returnObj = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/getWebPart.view'
            , array(
                'webpart.name' => 'Report'
            , 'webpart.frame' => 'none'
            , 'schemaName' => 'targetedms'
            , 'queryName' => 'QCAnalysisQuery'
            , 'query.Protein~eq' => $protein
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            , 'query.PrecursorCharge~eq' => $charge
            , 'curve_type' => 'reverse'
            , 'reportName' => 'web_portal_QC'
            , 'showSection' => 'QC_plot_png'
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // plot_type
            , $protein
            , false // csv
        );

        $report_name = "validation_sample";

        if($returnObj->error == false) {

            $result = $this->downloadPlotImage(
                $returnObj->response
                , 'GET'
                , $peptide_sequence
                , $laboratory_abbreviation
                , $report_name
                , false
                , $panorama_authentication_cookie);

            if($result == false) {
                $returnObj->error = true;
            }
            $returnObj->response = $result;

        }

        return $returnObj;

    }

    /*
     * Get Peptide Validation Sample Data (Repeatability)
     */

    public function getPeptideValidationSampleData(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $protein = false
        , $charge = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $parameters = [
            'webpart.name' => 'Report'
        , 'webpart.frame' => 'none'
        , 'schemaName' => 'targetedms'
        , 'queryName' => 'QCAnalysisQuery'
        , 'query.Protein~eq' => $protein
        , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
        , 'query.PrecursorCharge~eq' => $charge
        , 'curve_type' => 'reverse'
        , 'reportName' => 'web_portal_QC'
        , 'showSection' => 'CV_results.csv'
        ];

        $returnObj = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/getWebPart.view'
            , $parameters
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // plot_type
            , $protein
            , true // csv
        );

        if($returnObj->error == false) {

            $result = $this->downloadCsv(
                $returnObj->response
                , 'GET'
                , $parameters
                , $panorama_authentication_cookie);

            if($result == false) {
                $returnObj->error = true;
            }
            $returnObj->response = $result;

        }

        return $returnObj;

    }

    /*
     * Get Peptide Endogenous Image
     */
    public function getPeptideEndogenousImage(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $protein = false
        , $charge = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $returnObj = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/getWebPart.view'
            , array(
                'webpart.name' => 'Report'
            , 'webpart.frame' => 'none'
            , 'schemaName' => 'targetedms'
            , 'queryName' => 'QCAnalysisQuery'
            , 'query.Protein~eq' => $protein
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            , 'query.PrecursorCharge~eq' => $charge
            , 'curve_type' => 'reverse'
            , 'reportName' => 'Endogenous'
            , 'showSection' => 'QC_plot_png'
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // plot_type
            , $protein
            , false // csv
        );

        $report_name = "endogenous";

        if($returnObj->error == false) {

            $result = $this->downloadPlotImage(
                $returnObj->response
                , 'GET'
                , $peptide_sequence
                , $laboratory_abbreviation
                , $report_name
                , false
                , $panorama_authentication_cookie);

            if($result == false) {
                $returnObj->error = true;
            }
            $returnObj->response = $result;

        }

        return $returnObj;
    }

    /*
     * Get Peptide Endogenous Data
     */

    public function getPeptideEndogenousData(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $protein = false
        , $charge = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $parameters = [
            'webpart.name' => 'Report'
        , 'webpart.frame' => 'none'
        , 'schemaName' => 'targetedms'
        , 'queryName' => 'QCAnalysisQuery'
        , 'query.Protein~eq' => $protein
        , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
        , 'query.PrecursorCharge~eq' => $charge
        , 'curve_type' => 'reverse'
        , 'reportName' => 'Endogenous'
        , 'showSection' => 'CV_results.csv'
        ];

        $returnObj = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/getWebPart.view'
            , $parameters
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // plot_type
            , $protein
            , true // csv
        );

        if($returnObj->error == false) {

            $result = $this->downloadCsv(
                $returnObj->response
                , 'GET'
                , $parameters
                , $panorama_authentication_cookie);

            if($result == false) {
                $returnObj->error = true;
            }
            $returnObj->response = $result;

        }

        return $returnObj;
    }

    /*
     * Get Peptide Selectivity Image
     */
    public function getPeptideSelectivityImage(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $protein = false
        , $charge = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $parameters = [
              'webpart.name' => 'Report'
            , 'webpart.frame' => 'none'
            , 'schemaName' => 'targetedms'
            , 'queryName' => 'Selectivity'
            , 'query.Protein~eq' => $protein
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            , 'query.PrecursorCharge~eq' => $charge
            , 'curve_type' => 'forward'
            , 'reportName' => 'Selectivity'
            , 'showSection' => 'QC_plot_png'
        ];

        $returnObj = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/getWebPart.view'
            , $parameters
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // plot_type
            , $protein
            , false // csv
        );

        $report_name = "selectivity";

        if($returnObj->error == false) {

            $result = $this->downloadPlotImage(
                $returnObj->response
                , 'GET'
                , $peptide_sequence
                , $laboratory_abbreviation
                , $report_name
                , false
                , $panorama_authentication_cookie);

            if($result == false) {
                $returnObj->error = true;
            }
            $returnObj->response = $result;

        }

        return $returnObj;
    }

    /*
     * Get Peptide Selectivity Data
     */
    public function getPeptideSelectivityData(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $protein = false
        , $charge = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $parameters = [
              'webpart.name' => 'Report'
            , 'webpart.frame' => 'none'
            , 'schemaName' => 'targetedms'
            , 'queryName' => 'QCAnalysisQuery'
            , 'query.Protein~eq' => $protein
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            , 'query.PrecursorCharge~eq' => $charge
            , 'curve_type' => 'reverse'
            , 'reportName' => 'Selectivity'
            , 'showSection' => 'summary_table2.csv'

        ];

        $returnObj = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/getWebPart.view'
            , $parameters
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // plot_type
            , $protein
            , true // csv
        );

        if($returnObj->error == false) {

            $result = $this->downloadCsv(
                $returnObj->response
                , 'GET'
                , $parameters
                , $panorama_authentication_cookie);

            if($result == false) {
                $returnObj->error = true;
            }
            $returnObj->response = $result;

        }

        return $returnObj;
    }

    /*
     * Get Peptide Selectivity Data
     */
    public function getPeptideSelectivitySpikeLevelData(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $protein = false
        , $charge = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $parameters = [
              'webpart.name' => 'Report'
            , 'webpart.frame' => 'none'
            , 'schemaName' => 'targetedms'
            , 'queryName' => 'QCAnalysisQuery'
            , 'query.Protein~eq' => $protein
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            , 'query.PrecursorCharge~eq' => $charge
            , 'curve_type' => 'reverse'
            , 'reportName' => 'Selectivity'
            , 'showSection' => 'values_for_spike_levels.csv'
        ];

        $returnObj = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/getWebPart.view'
            , $parameters
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // plot_type
            , $protein
            , true // csv
        );

        if($returnObj->error == false) {

            $result = $this->downloadCsv(
                $returnObj->response
                , 'GET'
                , $parameters
                , $panorama_authentication_cookie);

            if($result == false) {
                $returnObj->error = true;
            }
            $returnObj->response = $result;

        }

        return $returnObj;
    }

    /*
     * Get Peptide Stability Image
     */
    public function getPeptideStabilityImage(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $protein = false
        , $charge = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $parameters = [
              'webpart.name' => 'Report'
            , 'webpart.frame' => 'none'
            , 'schemaName' => 'targetedms'
            , 'queryName' => 'QCAnalysisQuery'
            , 'query.Protein~eq' => $protein
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            , 'query.PrecursorCharge~eq' => $charge
            , 'curve_type' => 'reverse'
            , 'reportName' => 'Stability'
            , 'showSection' => 'QC_plot_png'
        ];

        $returnObj = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/getWebPart.view'
            , $parameters
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // plot_type
            , $protein
            , false // csv
        );

        $report_name = "stabillity";

        if($returnObj->error == false) {

            $result = $this->downloadPlotImage(
                $returnObj->response
                , 'GET'
                , $peptide_sequence
                , $laboratory_abbreviation
                , $report_name
                , false
                , $panorama_authentication_cookie);

            if($result == false) {
                $returnObj->error = true;
            }
            $returnObj->response = $result;

        }

        return $returnObj;
    }

    /*
     * Get Peptide Stability Data
     */
    public function getPeptideStabilityData(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $protein = false
        , $charge = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $parameters = [
              'webpart.name' => 'Report'
            , 'webpart.frame' => 'none'
            , 'schemaName' => 'targetedms'
            , 'queryName' => 'QCAnalysisQuery'
            , 'query.Protein~eq' => $protein
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            , 'query.PrecursorCharge~eq' => $charge
            , 'curve_type' => 'reverse'
            , 'reportName' => 'Stability'
            , 'showSection' => 'CV_results.csv'
        ];

        $returnObj = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/getWebPart.view'
            , $parameters
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // plot_type
            , $protein
            , true // csv
        );

        if($returnObj->error == false) {

            $result = $this->downloadCsv(
                $returnObj->response
                , 'GET'
                , $parameters
                , $panorama_authentication_cookie);

            if($result == false) {
                $returnObj->error = true;
            }
            $returnObj->response = $result;

        }

        return $returnObj;
    }

    /*
     * Get Peptide Isotope Label Modifications
     */

    public function getPeptideIsotopeLabelModifications(
        $panorama_authentication_cookie = false
        , $modified_peptide_sequence = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $data = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/getQuery.view'
            , array(
                'schemaName' => 'targetedms'
            , 'query.queryName' => 'PeptideIsotopeLabelModifications'
            , 'query.PeptideModifiedSequence~eq' => $modified_peptide_sequence
            )
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $modified_peptide_sequence
            , false // plot_type
            , false // protein
            , false // csv
        );

        return $data;
    }

    /*
     * Get LOD/LOQ Data
     */

    // https://daily.panoramaweb.org/labkey/project/CPTAC%20Assay%20Portal/FHCRC_Paulovich/CellLysate_5500QTRAP_directMRM/ResponseCurve/getWebPart.view?webpart.name=Report&webpart.frame=none&schemaName=targetedms&queryName=ResponseCurveAnalysis&query.PeptideModifiedSequence~eq=AEPEDHYFLLTEPPLNTPENR&reportName=ResponseCurveAnalysis&showSection=LODCTable.csv

    public function getLodLoqData(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {
        write_log($this->import_log_id, "inside getLodLoqData cookie: " . $panorama_authentication_cookie);
        write_log($this->import_log_id, 'inside getLodLoqData celllysate: ' . $celllysate_path);
        write_log($this->import_log_id, 'inside getLodLoqData library ' . $library);
        write_log($this->import_log_id, 'inside getLodLoqData peptide sequence' . $peptide_sequence);
        write_log($this->import_log_id, 'inside getLodLoqData lab_abbreviation ' . $laboratory_abbreviation);

        $parameters = [
              'webpart.name' => 'Report'
            , 'webpart.frame' => 'none'
            , 'schemaName' => 'targetedms'
            , 'queryName' => 'ResponseCurveAnalysis'
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            , 'reportName' => 'ResponseCurveAnalysis'
            , 'showSection' => 'LODCTable.csv'
        ];

        $returnObj = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/getWebPart.view'
            , $parameters
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // plot_type
            , false // protein
            , true // csv
        );

        if($returnObj->error == false) {

            $result = $this->downloadCsv(
                $returnObj->response
                , 'GET'
                , $parameters
                , $panorama_authentication_cookie);

            if($result == false) {
                $returnObj->error = true;
            }
            $returnObj->response = $result;

        }

        return $returnObj;

    }

    /*
     * Get Curve Fit Data
     */

    // https://daily.panoramaweb.org/labkey/project/CPTAC%20Assay%20Portal/FHCRC_Paulovich/CellLysate_5500QTRAP_directMRM/ResponseCurve/getWebPart.view?webpart.name=Report&webpart.frame=none&schemaName=targetedms&queryName=ResponseCurveAnalysis&query.PeptideModifiedSequence~eq=AEPEDHYFLLTEPPLNTPENR&reportName=ResponseCurveAnalysis&showSection=fitTable.csv

    public function getCurveFitData(
        $panorama_authentication_cookie = false
        , $peptide_sequence = false
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false) {

        $parameters = [
              'webpart.name' => 'Report'
            , 'webpart.frame' => 'none'
            , 'schemaName' => 'targetedms'
            , 'queryName' => 'ResponseCurveAnalysis'
            , 'query.PeptideModifiedSequence~eq' => $peptide_sequence
            , 'reportName' => 'ResponseCurveAnalysis'
            , 'showSection' => 'fitTable.csv'
        ];

        $returnObj = $this->_doCall(
            $panorama_authentication_cookie
            , 'GET'
            , '/getWebPart.view'
            , $parameters
            , $laboratory_abbreviation
            , $celllysate_path
            , $library
            , $peptide_sequence
            , false // plot_type
            , false // protein
            , true // csv
        );

        if($returnObj->error == false) {

            $result = $this->downloadCsv(
                $returnObj->response
                , 'GET'
                , $parameters
                , $panorama_authentication_cookie);

            if($result == false) {
                $returnObj->error = true;
            }
            $returnObj->response = $result;

        }

        return $returnObj;
    }

    /*
     * Get Panorama Authentication Cookie
     */

    public function get_panorama_authentication_cookie($import_log_id = false) {

        $cookie_hash = date("YmdHis") . "_" . uniqid();

        $url = $this->labkey_config["server_raw"] . '/labkey/home/login-login.view'; //set cookie to assay portal view
        $cookie = $this->panorama_images_storage_path . "cookie" . $cookie_hash . ".txt"; //cookie file without possible syntax issues

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_USERPWD, $this->labkey_config['email'] . ":" . $this->labkey_config['password']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ch = $this->setCurlOpt($ch);
        $auth_result = curl_exec($ch);

        curl_close($ch);

        $log_result = ($auth_result) ? "SUCCESS" : '<span class="import-error">FAIL</span>';
        $fail_message = (!$auth_result) ? json_encode($auth_result) : false;
        write_log($import_log_id, 'API server authentication attempt: ' . $log_result . " " . $fail_message);
        write_log($import_log_id, 'email: ' . $this->labkey_config['email'] . " " . $this->labkey_config['password']);
        write_log($import_log_id, 'cookie url: ' . $url);

        return $cookie;
    }

    protected function buildUrl(
        $panorama_authentication_cookie
        , $httpMethod
        , $path
        , $parameters = null
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false
        , $peptide_sequence = false
        , $plot_type = false
        , $protein = false
        , $csv = false
        , $peptideType = false
    ) {
        $query_name = false;

        // Panorama throws around two naming conventions for the query name key.
        // Pin it down and normalize the variable.
        if (isset($parameters['query.queryName']) || isset($parameters['queryName'])) {
            if (isset($parameters['query.queryName'])) {
                $query_name = $parameters['query.queryName'];
            }
            if (isset($parameters['queryName'])) {
                $query_name = $parameters['queryName'];
            }
        }

        switch ($query_name) {
            case false: // images ??
                $url = $this->labkey_config['targetedms_query_path'] . $laboratory_abbreviation . '/' . $celllysate_path . '/' . $library . $path;
                break;

            case 'peptidechrominfo': // validation sample images
                $url = $this->labkey_config['query_endpoint_live'] . $laboratory_abbreviation . '/' . $celllysate_path . '/' . $library . $path;
                break;

            case 'ResponseCurveQuery': // response curve images
                if ($path == '/executeQuery.view') {
                    $url = $this->labkey_config['query_endpoint_live'] . $laboratory_abbreviation . '/' . $celllysate_path . '/' . $library . $path;
                } else {
                    $url = $this->labkey_config['project_endpoint_live'] . $laboratory_abbreviation . '/' . $celllysate_path . '/' . $library . $path;
                }
                break;

            case 'Selectivity':
            case 'Stability':
            case 'Endogenous':
            case 'QCAnalysisQuery': // validation sample images and data
            case 'ResponseCurveAnalysis': // lod/loq and curve fit data
                if ($path == '/executeQuery.view') {
                    $url = $this->labkey_config['query_endpoint_live'] . $laboratory_abbreviation . '/' . $celllysate_path . '/' . $library . $path;
                } else {
                    $url = $this->labkey_config['project_endpoint_live'] . $laboratory_abbreviation . '/' . $celllysate_path . '/' . $library . $path;
                }
                break;

            default: // regular
                $url = $this->labkey_config['query_endpoint_live'] . $laboratory_abbreviation . '/' . $celllysate_path . '/' . $library . $path;
                break;
        }

        // insert peptideType AFER sequence
        if ($peptideType) {
            //$parameters = array_merge($parameters, array('peptideType' => $peptideType) );
            $final_parameters = array();
            foreach ($parameters as $param_key => $param_value) {
                if ($param_key == 'query.PeptideModifiedSequence~eq') {
                    $final_parameters[$param_key] = $param_value;
                    $final_parameters['peptideType'] = $peptideType;
                } else {
                    $final_parameters[$param_key] = $param_value;
                }
            }
            $parameters = $final_parameters;
        }

        if ($httpMethod != 'POST') {
            $url = (is_null($parameters) || count($parameters) == 0) ? $url : $url . '?' . http_build_query($parameters);
        }

        return $url;

    }

    /*
     * Execute cURL
     */

    protected function _doCall(
        $panorama_authentication_cookie
        , $httpMethod
        , $path
        , $parameters = null
        , $laboratory_abbreviation = false
        , $celllysate_path = false
        , $library = false
        , $peptide_sequence = false
        , $plot_type = false
        , $protein = false
        , $csv = false
        , $peptideType = false
    ) {

        $query_name = false;
        $get_url = false;
        $cookie_hash = date("YmdHis") . "_" . uniqid();
        $this->responseHeaders = array();
        write_log($this->import_log_id, "inside _doCall cookie: " . $panorama_authentication_cookie);
        write_log($this->import_log_id, "inside _doCall httpMethod: " . $httpMethod);
        write_log($this->import_log_id, "inside _doCall path: " . $path);
        write_log($this->import_log_id, "inside _doCall parameters: " . $parameters);
        //write_log($this->import_log_id,"inside _doCall parameters members: ".print_r($parameters));
        write_log($this->import_log_id, "inside _doCall lab: " . $laboratory_abbreviation);
        write_log($this->import_log_id, "inside _doCall celllysate_path: " . $celllysate_path);
        write_log($this->import_log_id, "inside _doCall library: " . $library);
        write_log($this->import_log_id, "inside _doCall peptide: " . $peptide_sequence);
        write_log($this->import_log_id, "inside _doCall plot_type: " . $plot_type);
        write_log($this->import_log_id, "inside _doCall protein: " . $protein);
        write_log($this->import_log_id, "inside _doCall csv: " . $csv);
        write_log($this->import_log_id, "inside _doCall peptideType: " . $peptideType);


        $cookie_jar = tempnam($this->panorama_images_storage_path . "/cookie" . $cookie_hash . ".txt");
        // If an authentication cookie was not provided, generate one now
        if (!$panorama_authentication_cookie) {
            write_log($this->import_log_id, "no authentication cookie provided.");


            $ch = curl_init($this->labkey_config["server_raw"] . '/labkey/project/CPTAC%20Assay%20Portal/begin.view?');


            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpMethod);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
            curl_setopt($ch, CURLOPT_USERPWD, $this->labkey_config['email'] . ":" . $this->labkey_config['password']);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $ch = $this->setCurlOpt($ch);
            $auth_result = curl_exec($ch);

            curl_close($ch);


            $panorama_authentication_cookie = $cookie_jar;
        }


        // Panorama throws around two naming conventions for the query name key.
        // Pin it down and normalize the variable.
        if (isset($parameters['query.queryName']) || isset($parameters['queryName'])) {
            if (isset($parameters['query.queryName'])) {
                $query_name = $parameters['query.queryName'];
            }
            if (isset($parameters['queryName'])) {
                $query_name = $parameters['queryName'];
            }
        }

        $log_query_name = $query_name;
        switch ($query_name) {
            case false: // images ??

                $log_query_name = 'Image';

                $url = $this->labkey_config['targetedms_query_path'] . $laboratory_abbreviation . '/' . $celllysate_path . '/' . $library . $path;
                break;
            case 'peptidechrominfo': // validation sample images
                $url = $this->labkey_config['query_endpoint_live'] . $laboratory_abbreviation . '/' . $celllysate_path . '/' . $library . $path;
                break;
            case 'ResponseCurveQuery': // response curve images
                if ($path == '/viewScriptReport.view') {
                    $url = $this->labkey_config['report_endpoint_live'] . $laboratory_abbreviation . '/' . $celllysate_path . '/' . $library . $path;
                } else {
                    $url = $this->labkey_config['project_endpoint_live'] . $laboratory_abbreviation . '/' . $celllysate_path . '/' . $library . $path;
                }
                break;

            case 'Selectivity':
            case 'Stability':
            case 'Endogenous':
            case 'QCAnalysisQuery': // validation sample images and data
            case 'ResponseCurveAnalysis': // lod/loq and curve fit data
                if ($path == '/viewScriptReport.view') {
                    $url = $this->labkey_config['report_endpoint_live'] . $laboratory_abbreviation . '/' . $celllysate_path . '/' . $library . $path;
                } else {
                    $url = $this->labkey_config['project_endpoint_live'] . $laboratory_abbreviation . '/' . $celllysate_path . '/' . $library . $path;
                }
                write_log($this->import_log_id, "inside query switch(queryname) " . $url);
                break;
            default: // regular
                $url = $this->labkey_config['query_endpoint_live'] . $laboratory_abbreviation . '/' . $celllysate_path . '/' . $library . $path;
                break;
        }

        // insert peptideType AFER sequence
        if ($peptideType) {
            //$parameters = array_merge($parameters, array('peptideType' => $peptideType) );
            $final_parameters = array();
            foreach ($parameters as $param_key => $param_value) {
                if ($param_key == 'query.PeptideModifiedSequence~eq') {
                    $final_parameters[$param_key] = $param_value;
                    $final_parameters['peptideType'] = $peptideType;
                } else {
                    $final_parameters[$param_key] = $param_value;
                }
            }
            $parameters = $final_parameters;
        }

        write_log($this->import_log_id, '_doCall: ' . $peptide_sequence . ' - Query Name: ' . $log_query_name . ' - Library: ' . $library . "\nURL: " . $url . '?' . http_build_query($parameters));

        if ($httpMethod != 'POST') {
            $get_url = (is_null($parameters) || count($parameters) == 0) ? $url : $url . '?' . http_build_query($parameters);
            $curl = curl_init($get_url);
            if ($httpMethod != 'GET') {
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $httpMethod);
            }
        } else {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_POST, true);
            if (!is_null($parameters)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($parameters));
            }
        }



        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
        curl_setopt($curl, CURLOPT_COOKIEFILE, $panorama_authentication_cookie);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $curl = $this->setCurlOpt($curl);
        session_write_close();
        //$result = curl_exec($curl);

        //print_r(var_export($result, true));

        $result = $this->client->get($get_url)->getBody()->getContents();

        //print_r(var_export($test, true));

        $log_result = ($result) ? "SUCCESS" : '<span class="import-error">FAIL</span>';

        $log_result_fail = false;

        if (!$result) {
            write_log($this->import_log_id, "RESULT - {$peptide_sequence}: " . $result);
        }


        write_log($this->import_log_id, "_doCall Result - {$peptide_sequence}: " . $log_result . $log_result_fail);

        $returnObj = new \stdClass();

        $returnObj->error = false;
        if ($result === false) {
            $returnObj->error = true;
        } else {
            $returnObj->httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            //if (($returnObj->httpCode < 200) || $returnObj->httpCode >= 400) {
            //    $returnObj->error = true;
            //}
            // Return the result.
            $returnObj->response = $result;
            // Return the executed Panorama API URL.
            $returnObj->panoramaUrl = $url . '?' . http_build_query($parameters);
            $returnObj->parameters = $parameters;
            $returnObj->contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
            $returnObj->requestHeaders = preg_split('/(\\n|\\r){1,2}/', curl_getinfo($curl, CURLINFO_HEADER_OUT));

            $returnObj->responseHeaders = $this->responseHeaders;
        }

        curl_close($curl);

        return $returnObj;
    }

    public static function setCurlOpt($curl) {
        if (isset($curl)) {
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $arrayCiphers = array(
                'DHE-RSA-AES256-SHA',
                'DHE-DSS-AES256-SHA',
                'AES256-SHA:KRB5-DES-CBC3-MD5',
                'KRB5-DES-CBC3-SHA',
                'EDH-RSA-DES-CBC3-SHA',
                'EDH-DSS-DES-CBC3-SHA',
                'DES-CBC3-SHA:DES-CBC3-MD5',
                'DHE-RSA-AES128-SHA',
                'DHE-DSS-AES128-SHA',
                'AES128-SHA:RC2-CBC-MD5',
                'KRB5-RC4-MD5:KRB5-RC4-SHA',
                'RC4-SHA:RC4-MD5:RC4-MD5',
                'KRB5-DES-CBC-MD5',
                'KRB5-DES-CBC-SHA',
                'EDH-RSA-DES-CBC-SHA',
                'EDH-DSS-DES-CBC-SHA:DES-CBC-SHA',
                'DES-CBC-MD5:EXP-KRB5-RC2-CBC-MD5',
                'EXP-KRB5-DES-CBC-MD5',
                'EXP-KRB5-RC2-CBC-SHA',
                'EXP-KRB5-DES-CBC-SHA',
                'EXP-EDH-RSA-DES-CBC-SHA',
                'EXP-EDH-DSS-DES-CBC-SHA',
                'EXP-DES-CBC-SHA',
                'EXP-RC2-CBC-MD5',
                'EXP-RC2-CBC-MD5',
                'EXP-KRB5-RC4-MD5',
                'EXP-KRB5-RC4-SHA',
                'EXP-RC4-MD5:EXP-RC4-MD5'
            );

            curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, implode(':', $arrayCiphers));
        }
        return $curl;
    }

    public static function buildUrlQuery($params) {
        $query = "";
        foreach ($params as $key => $value) {

            $item_query = "";

            if (is_array($value)) {
                foreach($value as $item) {
                    if (empty($item_query)) {
                        $item_query = $key . "=" . rawurlencode($item);
                    } else {
                        $item_query = $item_query . "&" .$key . "=" . rawurlencode($item);
                    }
                }
            } else {
                $item_query = $key . "=" . rawurlencode($value);
            }

            if (empty($query)) {
                $query = $item_query;
            } else {
                $query = $query . "&" . $item_query;
            }
        }
        return $query;
    }

    protected function downloadCsv($input, $httpMethod, $parameters, $panorama_authentication_cookie) {

        $returned_data_array = json_decode($input, true);

        if (stristr($returned_data_array["html"], 'Error executing command') == false) {

            preg_match('/<a href="(.+)">/', $returned_data_array["html"], $matches);

            if (!empty($matches)) {
                $url_string = str_replace('&amp;', '&', $matches[1]);
                $url = $this->labkey_config["server_raw"] . $url_string;

                print_r("\n" . $url);

                $result = $this->client->get($url)->getBody()->getContents();

                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $httpMethod);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_COOKIEFILE, $panorama_authentication_cookie);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                $curl = $this->setCurlOpt($curl);
                session_write_close();
                //$result = curl_exec($curl);
                curl_close($curl);

                write_log($this->import_log_id, "Image Saved: " . $parameters['reportName']);
            }

        } else {
            // Return the error so it can be logged into the database.
            $result = $returned_data_array["html"];
            write_log($this->import_log_id, '<span class="import-error">Image Error</span>: ' . $parameters['reportName'] . ' - ' . $result);
        }

        return $result;

    }

    protected function downloadPlotImage(
        $input
        , $httpMethod
        , $peptide_sequence
        , $laboratory_abbreviation
        , $report_name
        , $plot_type
        , $panorama_authentication_cookie) {

        $returned_data_array = json_decode($input, true);
        $response = $returned_data_array["html"];

        if (stristr($returned_data_array["html"], 'Error executing command') == false) {

            preg_match('/< *img[^>]*src *= *["\']?([^"\']*)/i', $returned_data_array["html"], $matches);


            if (!empty($matches)) {
                $url_string = str_replace('&amp;', '&', $matches[1]);
                $url = $this->labkey_config["server_raw"] . $url_string;

                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $httpMethod);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_COOKIEFILE, $panorama_authentication_cookie);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                $curl = $this->setCurlOpt($curl);
                session_write_close();
                //$response = curl_exec($curl);

                $response = $this->client->get($url)->getBody()->getContents();

                curl_close($curl);

                if ($plot_type) {
                    $filename = $peptide_sequence . "_" . $report_name . "_" . "_" . $plot_type . "_" . $laboratory_abbreviation;
                } else {
                    $filename = $peptide_sequence . "_" . $report_name . "_" . $laboratory_abbreviation;
                }


                $panorama_images_storage_folder = $this->panorama_images_storage_path .
                    "/" . $report_name . '_images';
                mkdir($panorama_images_storage_folder, 0755, true);

                $panorama_images_storage_path = $panorama_images_storage_folder .
                    "/" . $filename . '.png';
                $panorama_image_path = $this->panorama_images_path .
                    "/" . $report_name . '_images' .
                    "/" . $filename . '.png';
                file_put_contents($panorama_images_storage_path, $response);
                $response = $panorama_image_path;

                write_log($this->import_log_id, "Image Saved: " . $report_name . $response);

            }

        } else {
            // Return the error so it can be logged into the database.

            write_log($this->import_log_id, '<span class="import-error">Image Error</span>: ' . $response);
        }

        return $response;

    }

    private function downloadChromatogramImages($input, $chromatogram_type, $parameters, $peptide_sequence, $laboratory_abbreviation) {
        $filename = $peptide_sequence . "_" . $chromatogram_type . "_chrom_id_" . $parameters["id"] . "_" . $laboratory_abbreviation;

        $panorama_images_storage_folder = $this->panorama_images_storage_path .
            "/" . 'chromatogram_images';
        mkdir($panorama_images_storage_folder, 0755, true);

        $panorama_images_storage_path = $panorama_images_storage_folder .
            "/" . $filename . '.png';
        $panorama_image_path = $this->panorama_images_path .
            "/" . 'chromatogram_images' .
            "/" . $filename . '.png';
        file_put_contents($panorama_images_storage_path, $input);
        $response = $panorama_image_path;

        write_log($this->import_log_id, "Chromatogram Image Saved: " . $response);

        return $response;
    }

}

?>
