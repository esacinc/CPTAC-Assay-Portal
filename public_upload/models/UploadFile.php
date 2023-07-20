<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 3/19/18
 * Time: 12:43 PM
 */

namespace public_upload\models;


class UploadFile {

    private $experiment_type;
    private $filename;

    public function __construct(ExperimentTypeEnum $experiment_type, string $filename) {
        $this->experiment_type = $experiment_type;
        $this->filename = $filename;
    }

    /**
     * @return ExperimentTypeEnum
     */
    public function getExperimentType(): ExperimentTypeEnum {
        return $this->experiment_type;
    }

    /**
     * @param ExperimentTypeEnum $experiment_type
     */
    public function setExperimentType(ExperimentTypeEnum $experiment_type) {
        $this->experiment_type = $experiment_type;
    }

    /**
     * @return string
     */
    public function getFilename(): string {
        return $this->filename;
    }

    /**
     * @param string $filename
     */
    public function setFilename(string $filename) {
        $this->filename = $filename;
    }

}