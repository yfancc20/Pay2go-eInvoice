<?php

namespace Yfancc20\Pay2goInvoice;

class Pay2GoInvoice
{
    /*
     * 官方 API 文件：
     *     https://inv.pay2go.com/Invoice_index/download
     */

    protected $pay2goUrl; // 串接網址

    protected $merchantId;
    protected $hashKey;
    protected $hashIv;

    protected $debugMode = false;
    protected $postData;
    protected $rawResult;


    public function __construct()
    {
        $this->merchantId = config('pay2goinv.MerchantID');
        $this->hashKey = config('pay2goinv.HashKey');
        $this->hashIv = config('pay2goinv.HashIV');

        // default settings
        $this->debugMode = config('pay2goinv.Debug');
        $this->postData = [];
        $this->setDefault();
        $this->setUrl();
    }

    // 取得欲傳送資料
    public function getData()
    {
        return $this->postData;
    }

    // 作業送出
    public function send()
    {
        return $this->sendPay2Go($this->pay2goUrl);
    }

    // 加密、送至 Pay2Go 串接網址，所有功能送出前會經過此 function
    protected function sendPay2Go($url)
    {
        $postData_ = $this->encrypt($this->postData);

        $transaction_data_array = [
            'MerchantID_' => $this->merchantId,
            'PostData_' => $postData_
        ];

        $transaction_data_str = http_build_query($transaction_data_array);
        $result = $this->curl_work($url, $transaction_data_str);
        $this->rawResult = $result;

        $response = $this->checkResponse($result);

        // $response['Status'], $response['Message'] ...
        return $response;
    }

    // 加密函式
    protected function encrypt($data)
    {
        /*
         * 使用 openssl_encrypt
         */
        $dataStr = http_build_query($data);
        $dataEncrypt = trim(bin2hex(openssl_encrypt($this->addpadding($dataStr), 'aes-256-cbc', $this->hashKey,OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $this->hashIv)));

        return $dataEncrypt;
    }

    // 加 padding
    protected function addpadding($string, $blocksize = 32)
    {
        $len = strlen($string);
        $pad = $blocksize - ($len % $blocksize);
        $string .= str_repeat(chr($pad), $pad);

        return $string;
    }

    // curl post
    protected function curl_work($url = '', $parameter = '')
    {
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'Google Bot',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_POST => '1',
            CURLOPT_POSTFIELDS => $parameter,
        );

        $ch = curl_init();
        curl_setopt_array($ch, $curl_options);
        $result = curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_errno($ch);
        curl_close($ch);

        $return_info = array(
            'url' => $url,
            'sent_parameter' => $parameter,
            'http_status' => $retcode,
            'curl_error_no' => $curl_error,
            'web_info' => $result,
        );

        return $return_info;
    }

    // 失敗要回報
    protected function checkResponse($result)
    {
        switch ($this->postData['RespondType']) {
            case 'JSON':
                $response = json_decode($result['web_info'], true);
                if (!empty($response['Result'])) {
                    // Result 是以 json 格式，取出來 push 進 response
                    $resultData = json_decode($response['Result'], true);
                    foreach ($resultData as $key => $value) {
                        $response[$key] = $value;
                    }
                }
                break;
            case 'String':
                parse_str($result['web_info'], $response);
                break;
            default:
                $response = $result['web_info'];
                break;
        }

        return $response;
    }

    // 設定postData參數
    public function setData($data)
    {
        $this->setDataByFields($data);

        return $this->postData;
    }

    // 根據傳入data逐一設定postData欄位
    public function setDataByFields($data)
    {
        foreach ($data as $key => $value) {
            $this->postData[$key] = $value;
        }
    }

    // 回傳原始結果
    public function getRawResult()
    {
        return $this->rawResult;
    }
}