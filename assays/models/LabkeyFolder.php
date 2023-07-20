<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 10/17/18
 * Time: 1:43 PM
 */

namespace assays\models;


class LabkeyFolder {

    const EXPERIMENTS = [
        "Chromatograms" => "ChromatogramLibrary",
        "Response Curve" => "ResponseCurve",
        "Validation of Repeatability" => "ValidationSamples"
    ];

    protected $project;
    protected $parent;
    protected $folder;
    protected $experiment;
    protected $file;
    protected $filename;
    protected $import_log_id;
    protected $submission_id;

    function __construct($parent, $folder) {
        $this->parent = $parent;
        $this->folder = $folder;
    }

    /**
     * @return mixed
     */
    public function getProject() {
        return $this->project;
    }

    /**
     * @param mixed $project
     */
    public function setProject($project) {
        $this->project = $project;
    }

    /**
     * @return mixed
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent) {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getFolder() {
        return $this->folder;
    }

    /**
     * @param mixed $folder
     */
    public function setFolder($folder) {
        $this->folder = $folder;
    }

    /**
     * @return mixed
     */
    public function getExperiment() {
        return $this->experiment;
    }

    /**
     * @param mixed $experiment
     */
    public function setExperiment($experiment) {
        $this->experiment = $experiment;
    }

    /**
     * @return mixed
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file) {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * @param mixed $filename
     */
    public function setFilename($filename) {
        $this->filename = $filename;
    }

    /**
     * @return mixed
     */
    public function getImportLogId() {
        return $this->import_log_id;
    }

    /**
     * @param mixed $import_log_id
     */
    public function setImportLogId($import_log_id) {
        $this->import_log_id = $import_log_id;
    }

    /**
     * @return mixed
     */
    public function getSubmissionId() {
        return $this->submission_id;
    }

    /**
     * @param mixed $submission_id
     */
    public function setSubmissionId($submission_id) {
        $this->submission_id = $submission_id;
    }

}