<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 3/19/18
 * Time: 1:16 PM
 */

namespace public_upload\models;


use MabeEnum\Enum;

class ExperimentTypeEnum extends Enum {

    const RESPONSE_CURVE = "Response Curve";
    const VALIDATION = "Validation of Repeatability";
    const CHROMATOGRAMS = "Chromatograms";

}