<?php

namespace core\models\Google\Recaptcha;

class RecaptchaService {

    public function verifyResponse(string $resp) {
        global $final_global_template_vars;

        $curl_session = curl_init();

        curl_setopt_array($curl_session, [
            CURLOPT_CONNECTTIMEOUT_MS => 5000,
            CURLOPT_DNS_CACHE_TIMEOUT => 0,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => [
                "Accept" => "application/json"
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                "remoteip" => $_SERVER["REMOTE_ADDR"],
                "response" => $resp,
                "secret" => $final_global_template_vars["google_recaptcha_secret"]
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT_MS => 5000,
            CURLOPT_URL => "https://www.google.com/recaptcha/api/siteverify"
        ]);

        $curl_resp_content = curl_exec($curl_session);
        $curl_resp_status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE);
        $error_msgs = [curl_error($curl_session)];

        curl_close($curl_session);

        $success = false;

        if (($curl_resp_status_code === 200) && is_string($curl_resp_content)) {
            $curl_resp_content_object = json_decode($curl_resp_content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $error_msgs = [json_last_error_msg()];
            } else if (!($success = (is_bool($curl_resp_content_object["success"]) && $curl_resp_content_object["success"])) &&
                is_array($curl_resp_content_object["error-codes"])) {
                $error_msgs = $curl_resp_content_object["error-codes"];
            }
        }

        return [
            "error_msgs" => $error_msgs,
            "status_code" => $curl_resp_status_code,
            "success" => $success
        ];
    }

}