<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 10/12/18
 * Time: 9:39 PM
 */

namespace assays\models;


class LabkeyFolderApi {

    protected  $create_folder_params = [
        "X-LABKEY-CSRF" => ""
        , "X-ONUNAUTHORIZED" => "UNAUTHORIZED"
        , "hasLoaded" => "true"
        , "name" => ""
        , "checkbox-1014-inputEl" => "on"
        , "title" => ""
        , "folderType" => "Template"
        , "templateSourceId" => "81fd5f22-3c3e-1035-9105-13c9abcb5ae1"
        , "templateWriterTypes" => [  "Folder type and active modules"
                                    , "Full-text search settings"
                                    , "Webpart properties and layout"
                                    , "Container specific module properties"
                                    , "Queries"
                                    , "Grid Views"
                                    , "Reports and Charts"
                                    , "External schema definitions"
                                    , "Lists"
                                    , "Wikis and their attachments"
                                    , "Missing value indicators"
                                    , "Notification settings"
                                    , "Study"
                                    ]
        , "templateIncludeSubfolders" => "on"
    ];

    protected  $set_permission_params = [
        "X-LABKEY-CSRF" => ""
        , "X-ONUNAUTHORIZED" => "UNAUTHORIZED"
        , "permissionType" => "Inherit"
    ];

    protected  $data_pipeline_params = [
        "X-LABKEY-CSRF" => ""
        , "X-ONUNAUTHORIZED" => "UNAUTHORIZED"
        , "file" => ""
    ];

    function __construct($labkey_config, $cookies) {
        $this->labkey_config = $labkey_config;
        $this->cookies = $cookies;
    }

    public function checkFolderExists(LabkeyFolder $labkey_folder) {
        if($labkey_folder->getParent() == false || $labkey_folder->getFolder() == false) {
            return false;
        }

        $project_name = rawurlencode($this->labkey_config['project_name']);
        $action = LabkeyFolderActionEnum::PROJECT;
        $referer_url = $this->labkey_config['server']
            . $project_name
            . "/" . $labkey_folder->getParent()
            . "/" . $labkey_folder->getFolder()
            . "/";

        $url = $referer_url . $action;
        return $this->checkItemExists($url);
    }

    public function checkFileExists(LabkeyFolder $labkey_folder) {

        var_dump($labkey_folder, true);

        $project_name = rawurlencode($this->labkey_config['project_name']);
        $nav = $project_name
            . "/" . $labkey_folder->getParent()
            . "/" . $labkey_folder->getFolder()
            . "/" . $labkey_folder->getExperiment()
            . "/%40files"
            . "/" . $labkey_folder->getFilename();

        $url = $this->labkey_config['webdav'] . $nav;

        var_dump($url);

        return $this->checkItemExists($url);
    }

    public function checkItemExists(string $url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
        curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body
        curl_setopt($ch, CURLOPT_TIMEOUT,10);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);


        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getRequestHeader());
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_VERBOSE, 1);


        $output = curl_exec($ch);

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpcode == 200) {
            return true;
        }

        return false;
    }

    public function createFolderIfNotExist(LabkeyFolder $labkey_folder) {
        $status = $this->checkFolderExists($labkey_folder);

        if ($status) {
            return $status;
        } else {
            $this->createFolder($labkey_folder);
        }

        return $this->checkFolderExists($labkey_folder);
    }

    public function createFolder(LabkeyFolder $labkey_folder) {
        if($labkey_folder->getParent() == false || $labkey_folder->getFolder() == false) {
            return false;
        }

        $folder_params = $this->create_folder_params;

        $project_name = rawurlencode($this->labkey_config['project_name']);
        $action = LabkeyFolderActionEnum::CREATE;
        $referer_url = $this->labkey_config['server'] . $project_name . "/" . $labkey_folder->getParent() . "/";
        $url = $referer_url . $action;

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($ch, CURLOPT_POST, true);


        $folder_params["X-LABKEY-CSRF"] = $this->cookies["X-LABKEY-CSRF"];
        $folder_params["name"] = $labkey_folder->getFolder();
        curl_setopt($ch, CURLOPT_POSTFIELDS, LabkeyApi::buildUrlQuery($folder_params));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);


        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getRequestHeader());
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        LabkeyApi::setCurlOpt($ch);

        curl_exec($ch);

        curl_close($ch);

        return $this->checkFolderExists($labkey_folder);
    }

    public function setPermission(LabkeyFolder $labkey_folder) {
        if($labkey_folder->getParent() == false || $labkey_folder->getFolder() == false) {
            return false;
        }

        if($this->checkFolderExists($labkey_folder) == false) {
            return false;
        }

        $folder_params = $this->set_permission_params;

        $project_name = rawurlencode($this->labkey_config['project_name']);
        $action = LabkeyFolderActionEnum::SET_PERMISSION;
        $referer_url = $this->labkey_config['server']
            . $project_name
            . "/" . $labkey_folder->getParent()
            . "/" . $labkey_folder->getFolder()
            . "/";
        $url = $referer_url . $action;


        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($ch, CURLOPT_POST, true);


        $folder_params["X-LABKEY-CSRF"] = $this->cookies["X-LABKEY-CSRF"];
        curl_setopt($ch, CURLOPT_POSTFIELDS, LabkeyApi::buildUrlQuery($folder_params));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getRequestHeader());
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        LabkeyApi::setCurlOpt($ch);

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
    }

    public function uploadFile(LabkeyFolder $labkey_folder) {

        if (!$this->checkFolderExists($labkey_folder)) {
            return false;
        }

        if ($this->checkFileExists($labkey_folder)) {
            return false;
        }

        $project_name = rawurlencode($this->labkey_config['project_name']);
        $nav = $project_name
            . "/" . $labkey_folder->getParent()
            . "/" . $labkey_folder->getFolder()
            . "/" . $labkey_folder->getExperiment()
            . "/%40files"
            . "/" . basename($labkey_folder->getFilename());
        $url = $this->labkey_config['webdav'] . $nav;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_PUT, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getRequestHeader());

        $fh_res = fopen($labkey_folder->getFile(), 'r');

        curl_setopt($ch, CURLOPT_INFILE, $fh_res);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($labkey_folder->getFile()));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE); // --data-binary
        curl_setopt($ch, CURLOPT_HEADER, 1);

        LabkeyApi::setCurlOpt($ch);

        curl_exec ($ch);

        fclose($fh_res);
        curl_close($ch);

        return $this->checkFileExists($labkey_folder);
    }

    public function containsItems(LabkeyFolder $labkey_folder) {

        if (!$this->checkFolderExists($labkey_folder)) {
            return false;
        }

        $project_name = rawurlencode($this->labkey_config['project_name']);
        $nav = $project_name
            . "/" . $labkey_folder->getParent()
            . "/" . $labkey_folder->getFolder()
            . "/" . $labkey_folder->getExperiment()
            . "/%40files";
        $url = $this->labkey_config['webdav'] . $nav;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PROPFIND');

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getRequestHeader());

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE); // --data-binary
        curl_setopt($ch, CURLOPT_HEADER, 1);

        LabkeyApi::setCurlOpt($ch);

        $response = curl_exec ($ch);

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        curl_close($ch);

        if($httpcode == 207) {

            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);

            //var_dump($body);
            $xml = simplexml_load_string($body);

            if($xml->response) {
                return true;
            }
        }

        return false;

    }

    public function listFiles(LabkeyFolder $labkey_folder) {

        if (!$this->checkFolderExists($labkey_folder)) {
            return false;
        }

        $project_name = rawurlencode($this->labkey_config['project_name']);
        $nav = $project_name
            . "/" . $labkey_folder->getParent()
            . "/" . $labkey_folder->getFolder()
            . "/" . $labkey_folder->getExperiment()
            . "/%40files";
        $url = $this->labkey_config['webdav'] . $nav;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PROPFIND');

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getRequestHeader());

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE); // --data-binary
        curl_setopt($ch, CURLOPT_HEADER, 1);

        LabkeyApi::setCurlOpt($ch);

        $response = curl_exec ($ch);

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        curl_close($ch);

        if($httpcode == 207) {

            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);

            //var_dump($body);
            $xml = simplexml_load_string($body);

            if($xml->response) {
                return $xml;
            }
        }

        return false;

    }

    public function executeDataPipeline(LabkeyFolder $labkey_folder) {
        if(    $labkey_folder->getParent() == false
            || $labkey_folder->getFolder() == false
            || $labkey_folder->getExperiment() == false
            || $labkey_folder->getFilename() == false) {
            return false;
        }

        if ($this->checkFileExists($labkey_folder) == false) {
            return false;
        }

        $folder_params = $this->data_pipeline_params;
        $folder_params['file'] = $labkey_folder->getFilename();

        $project_name = rawurlencode($this->labkey_config['project_name']);
        $nav = $project_name
            . "/" . $labkey_folder->getParent()
            . "/" . $labkey_folder->getFolder()
            . "/" . $labkey_folder->getExperiment()
            . "/";

        $action = LabkeyFolderActionEnum::PIPELINE;
        $referer_url = $this->labkey_config['server'] . $nav;
        $url = $referer_url . $action;


        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($ch, CURLOPT_POST, true);


        $folder_params["X-LABKEY-CSRF"] = $this->cookies["X-LABKEY-CSRF"];
        curl_setopt($ch, CURLOPT_POSTFIELDS, LabkeyApi::buildUrlQuery($folder_params));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);


        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getRequestHeader());
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        LabkeyApi::setCurlOpt($ch);

        curl_exec($ch);

        curl_close($ch);

        return true; //executed data pipeline job
    }

    private function getRequestHeader() {
        $request_headers = array();

        $request_headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8';
        $request_headers[] = 'Accept-Encoding: gzip, deflate';
        $request_headers[] = 'Accept-Language: en-US,en;q=0.9,ja;q=0.8,vi;q=0.7';
        $request_headers[] = 'Cache-Control: max-age=0';
        $request_headers[] = 'Connection: keep-alive';
        $request_headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $request_headers[] = 'Cookie: ' . LoginApi::getCookiesAsString($this->cookies);
        $request_headers[] = 'Origin: ' . $this->labkey_config['server_raw'];
        $request_headers[] = 'Referer: ' . $this->labkey_config['server'];
        $request_headers[] = 'Upgrade-Insecure-Requests: 1';
        $request_headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36';

        return $request_headers;
    }

}