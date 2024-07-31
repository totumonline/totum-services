<?php

use services\config\Conf;

$dirs = explode('/', $_SERVER['REQUEST_URI']);

require_once '../vendor/autoload.php';

/*Проверяем входящие*/

$inputData = json_decode(file_get_contents('php://input'), true);


if (($dirs[1] ?? false) === 'connectChecker') {
    $error = '';
    if (empty($inputData['number'])) {
        $error = 'Number is empty';
    } elseif (empty($inputData['key'])) {
        $error = 'Key is empty';
    } else {
        $Conf = Conf::init($_SERVER['HTTP_HOST']);
        try {
            $settings=$Conf->getSettings($inputData['number'], $inputData['key']);

        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }
    }
    $answer = [];
    if ($error) {
        $answer['error'] = $error;
    } else {
        $answer['number'] = 'OK';
    }

    echo json_encode($answer, JSON_UNESCAPED_UNICODE);
    die;
}

if (empty($inputData['number']) || empty($inputData['key']) || is_array($inputData['number']) || is_array($inputData['key'])) {

    die('Check service connect settings');
}

if (empty($inputData['hash'])) {
    die('Hash is empty');
}

/*Предварительные проверки*/
switch ($dirs[1] ?? false) {
    case 'xlsx':
    case 'docx':
        if (key_exists('file', $inputData['data'])) {
            if (empty($inputData['data']['file'])) {
                die('Empty file');
            }
        } elseif (empty($inputData['data']['template'])) {
            die('Empty template');
        }

        break;
    case 'pdf':
        if (empty($inputData['data']['file'])) {
            die('Empty file');
        }
        if (!in_array($inputData['data']['type'], ['html', 'xlsx', 'docx'])) {
            die('Input file type not allowed. Only: html, docx, xlsx');
        }
        break;
    default:
        die('Not correct or not active service type');
}

$Conf = Conf::init($_SERVER['HTTP_HOST']);

/*Проверка авторизации*/

try {
    $accountData=$Conf->getSettings($inputData['number'], $inputData['key']);

} catch (\Exception $exception) {
    die('Account parameters are not correct or account is not active ');
}
$service = $dirs[1] ?? false;

switch ($service) {
    case 'xlsx':
    case 'docx':
    case 'pdf':

        $serviceData = json_encode([
                'number' => $inputData['number'],
                'back_url' => $accountData['back_url'],
                'answer_key_path' => '',
                'checkSSl' => $accountData['check_back_url_certificate'] === 'true',
                'data' => $inputData['data'],
                'hash' => $inputData['hash'],
                'host' => $_SERVER['HTTP_HOST'],
            ], JSON_UNESCAPED_UNICODE);

        $file = tempnam('../tmp_files', 'data_' . substr($inputData['hash'], 0, 16));
        file_put_contents($file, $serviceData);
        echo ($answer = `cd ../services && ./{$service} < {$file}`);

        $inputDataForLog = $inputData;
        if ($inputDataForLog['data']['file']??false){
            $inputDataForLog['data']['file'] = '--';
        }
        if ($inputDataForLog['data']['filestring']??false){
            $inputDataForLog['data']['filestring'] = '--';
        }


        if($answer){
            $inputData["answer"] = $answer;
        }
        $Conf->log($inputData['number'], inputData: $inputDataForLog);
        break;
}

echo 'true';

//var_dump();


