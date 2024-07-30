<?php

namespace services\common;

use services\config\Conf;

class ConfigParent
{

    protected $dir = '../';
    protected $settings;
    protected $host;


    public function __construct($host = null)
    {
        $this->host = $this->host ?? $host;
    }

    public function getSettings(string $number, string $key): array
    {
        $settings = $this->settings ?? $this->settings = $this->parseSettingsFile($this->dir . 'services_list');
        if (!key_exists($number, $settings)) {
            throw new \Exception("Settings for number $number not found");
        }
        $set = $settings[$number];
        if ($set["key"] != $key) {
            throw new \Exception("Key for number $number is not correct");
        }

        return $set;
    }

    /**
     * @param string $filepath
     * @return array
     * @throws \Exception
     */
    protected function parseSettingsFile(string $filepath): array
    {
        $settings = [];
        foreach (file($filepath) ?: throw new \Exception("Settings file not accessible") as $i => $line) {
            $line = trim($line);
            if (!strlen($line) || $line[0] === '#') {
                continue;
            }

            $_set = json_decode($line, true);
            if (is_null($_set)) {
                throw new \Exception("Line " . ($i + 1) . " is not correct json");
            }
            $_set2 = $_set;
            unset($_set2["number"]);
            $_set2 = array_intersect_key($_set2, ["key" => null, "back_url" => null, "check_back_url_certificate" => null]);
            if (count($_set2) != 3) {
                throw new \Exception("Settings in $line " . ($i + 1) . " don't contain all params");
            }
            $settings[$_set["number"]] = $_set2;
        }
        return $settings;
    }

    public function getDir()
    {
        return $this->dir;
    }

    public function billing($accountNumber, $serviceType, $back_url_answer, $http_response_header, $error = null, $withPDF = false, $comment = null, $extraData = [])
    {

    }

    public function sendAnswer(string $back_url, bool $checkSsl, string $hash, ?string $error, ?string $resultFile, ?string $answer_key_path): array
    {
        /*!!!!!!*/
        if (empty($resultFile) || !filesize($resultFile)) {
            $Data['data']['error'] = $error ?? 'Generation error';
        } else {
            rename($resultFile, str_replace('../tmp_files', '../http/fls', $resultFile));
            $Data['data']['link'] = 'https://' . $this->host . '/' . preg_replace('/^(.*)\/([^\/]+)$/',
                    '$2',
                    $resultFile);
        }
        $Data['hash'] = $hash;


        $context = stream_context_create(
            [
                'http' => [
                    'ignore_errors' => true,
                    'header' => "Content-type: application/json\r\nUser-Agent: TOTUM\r\nConnection: Close\r\n\r\n",
                    'method' => 'POST',
                    'content' => json_encode($Data),
                    'timeout' => 5
                ],
                'ssl' => [
                    'verify_peer' => $checkSsl,
                    'verify_peer_name' => $checkSsl,
                ]
            ]
        );

        $back_url_answer = file_get_contents($back_url . '/ServicesAnswer' . ($answer_key_path ? '/' . $answer_key_path : ''),
            false,
            $context);

        if ($back_url_answer === false) {
            $back_url_answer = error_get_last();
        }

        return [$back_url_answer, $http_response_header];
    }

    public
    function create_tmp_file($dir, $prefix, $extension, $accountNumber, $cutHash)
    {
        $i = 0;
        while (true) {
            $filename = $dir . '/' . $prefix . '_' . $accountNumber . '_' . $cutHash . ($i ? '_' . $i : '') . '.' . $extension;
            if (!is_file($filename)) {
                file_put_contents($filename, '');
                return $filename;
            }

            $i++;
        }
    }

    public function log($number, $error = null, array $inputData = array())
    {
        if ($error) {
            $error = 'ERROR: ' . $error;
        }
        fwrite(fopen('../services_log', 'a'), date("H:i ") . $number . ' ' . ($error ?: json_encode($inputData, JSON_UNESCAPED_UNICODE)));

    }
}