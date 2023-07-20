<?php
namespace public_upload\controllers;

use public_upload\models\ExperimentTypeEnum;
use public_upload\models\FileLikeObject;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

use core\controllers\Controller;
use public_upload\models\UploadFile;
use public_upload\models\PublicUpload;

class UploadFiles extends Controller {

    function upload_files(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["public_upload_db_connection"]);
        $db_resource = $db_conn->get_resource();
        $session_key = $final_global_template_vars["session_key"];
        $public_upload = new PublicUpload($db_resource, $session_key);

        $directory = $_SERVER["DOCUMENT_ROOT"] . '/upload';
        $import_log_id = $_SESSION[$session_key]['import_log_id'];

        //$logger = $this->container->get('logger');

        //$logger->info('import log id: ' . $import_log_id);

        $upload_folder = $directory . "/" . $import_log_id;

        $this->createFileImportFolder($upload_folder);

        $uploadedFiles = $request->getUploadedFiles();

        foreach ($uploadedFiles as $uploadedFile) {

            //$this->logger->info("Error code: " . $uploadedFile->getError());

            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $filename = $this->moveUploadedFile($upload_folder, $uploadedFile);

                $upload_file = new UploadFile(ExperimentTypeEnum::get($request->getParam("experiment")), $filename);

                $public_upload->insert_public_upload_file($import_log_id, $upload_file);
            }
        }

        return $response->withJson([]);
    }

    function get_public_upload_files(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["public_upload_db_connection"]);
        $db_resource = $db_conn->get_resource();
        $session_key = $final_global_template_vars["session_key"];
        $public_upload = new PublicUpload($db_resource, $session_key);

        $directory = $_SERVER["DOCUMENT_ROOT"] . '/upload';
        $import_log_id = $_SESSION[$session_key]['import_log_id'];

        //$logger = $this->container->get('logger');

        //$logger->info('import log id: ' . $import_log_id);

        $import_log_id = 63;

        $upload_folder = $directory . "/" . $import_log_id;

        $files = $public_upload->get_public_upload_files($import_log_id);
        $fileLikeObjects = [];

        foreach($files as $file) {

            $fileLikeObject = new FileLikeObject($file['file_name']
                , $file['experiment_type']
                , $file['last_modified']
                , $file['size']);
            $fileLikeObjects[] = $fileLikeObject;
        }

        return $response->withJson($fileLikeObjects);
    }

    function check_public_upload_files(Request $request, Response $response, $args = []) {
        global $final_global_template_vars;

        $db_conn = new \swpg\models\db($final_global_template_vars["public_upload_db_connection"]);
        $db_resource = $db_conn->get_resource();
        $session_key = $final_global_template_vars["session_key"];
        $public_upload = new PublicUpload($db_resource, $session_key);

        $directory = $_SERVER["DOCUMENT_ROOT"] . '/upload';
        $import_log_id = $_SESSION[$session_key]['import_log_id'];

        //$logger = $this->container->get('logger');

        //$logger->info('import log id: ' . $import_log_id);

        $upload_folder = $directory . "/" . $import_log_id;

        $data = $public_upload->get_public_upload_files($import_log_id);

        //$logger->info('files: ' . var_export($data, true) . " count: " . count($data));

        $status = "true";


/*        if(count($data) < 3) {
           $status = false;
        }

        foreach($data as $item) {
            if($item['file_name']) {
                if (!file_exists($upload_folder . "/" . $item['file_name'])) {
                    $status = false;
                }
            }
        }*/

        return $response->withJson(["status" => $status]);
    }

    /**
     * create file import folder if it doesn't exist
     */
    function createFileImportFolder($directory) {
        if (!file_exists($directory)) {
            mkdir($directory, 0755);
        }
    }

    /**
     * Moves the uploaded file to the upload directory and assigns it a unique name
     * to avoid overwriting an existing uploaded file.
     *
     * @param string $directory directory to which the file is moved
     * @param UploadedFile $uploaded file uploaded file to move
     * @return string filename of moved file
     */
    function moveUploadedFile($directory, UploadedFile $uploadedFile) {
        $basename = pathinfo($uploadedFile->getClientFilename(), PATHINFO_BASENAME);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $basename);

        return $basename;
    }

}

?>
