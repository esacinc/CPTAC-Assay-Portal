<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 10/17/18
 * Time: 2:02 PM
 */

namespace assays\models;


use MabeEnum\Enum;

class LabkeyFolderActionEnum extends Enum {

    const CREATE = "admin-createFolder.view";
    const SET_PERMISSION = "admin-setFolderPermissions.view";
    const PROJECT = "project-begin.view";
    const PIPELINE = "targetedms-skylineDocUpload.view?path=.%2F";
    const UPLOAD = "";

}