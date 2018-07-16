<?php

namespace Yfancc20\Pay2goInvoice;

class CreateInvoice extends Pay2GoInvoice
{
    /*
     * 開立發票的 class
     */

    // 設定串接的網址
    protected function setUrl()
    {
        if (!$this->debugMode) { // product mode, 串正式網址
            $this->pay2goUrl = config('pay2goinv.Url_Create');
        } else { // debug mode, 串測試網址
            $this->pay2goUrl = config('pay2goinv.Url_Create_Test');
        }
    }

    // 設定預設值，讀取 config
    protected function setDefault()
    {
        $this->postData = [
            'RespondType' => config('pay2goinv.RespondType'),
            'Version' => config('pay2goinv.Version_Create'),
            'TimeStamp' => time(), // 需要為 time() 格式
            'TransNum' => '',
            'MerchantOrderNo' => '',
            'BuyerName' => '',
            'BuyerUBN' => '',
            'BuyerAddress' => '',
            'BuyerEmail' => '',
            'Category' => config('pay2goinv.Category'),
            'TaxType' => config('pay2goinv.TaxType'),
            'TaxRate' => config('pay2goinv.TaxRate'),
            'CustomeClearance' => '',
            'Amt' => 0, // 未稅額
            'AmtSales' => 0,
            'AmtZero' => 0,
            'AmtFree' => 0,
            'TaxAmt' => 0,
            'TotalAmt' => 0,
            'CarrierType' => '',
            'CarrierNum' => '',
            'LoveCode' => '',
            'PrintFlag' => 'Y',
            'ItemName' => '',
            'ItemCount' => 0, // 多商品以 | 隔開（string）
            'ItemUnit' => '',
            'ItemPrice' => 0,
            'ItemAmt' => 0,
            'ItemTaxType' => '',
            'Status' => config('pay2goinv.Status_Create'),
            'CreateStatusTime' => '',
            'Comment' => config('pay2goinv.Comment')
        ];
    }

    // 設定參數（從訂單）
    public function setData($data)
    {
        // 可接受 array / object
        if (is_array($data)) {
            $this->checkPostData($data);
        } else if (is_object($data)) {
            $this->checkPostData((array) $data);
        }

        return $this->postData;
    }

    // 設定參數：加在 comment 後的字
    public function addComment($str)
    {
        $this->postData['Comment'] .= $str;
    }

    /*
     * 填寫 postData 的各條件審核
     * 有些欄位有相依性，條件來自 pay2go 官方 API 文件
     */
    private function checkPostData($data)
    {
        // B2B、B2C 欄位條件
        if ($data['buyer_type'] == 2) {
            // B2B，有統編
            $this->postData['BuyerName'] = ' ';
            $this->postData['BuyerUBN'] = (string) $data['company_number'];
            $this->postData['Category'] = 'B2B';
            // 載具類別、愛心代碼欄位只適用 B2C
            $this->postData['CarrierType'] = '';
            $this->postData['LoveCode'] = '';

            $this->postData['PrintFlag'] = 'Y'; // B2B 必填 Y
            // 未稅價
            $this->postData['ItemPrice'] = (string) round($data['amount'] / 1.05);
            $this->postData['ItemAmt'] = (string) round($data['amount'] / 1.05);

        } else {
            // B2C，買受人個人
            $this->postData['BuyerName'] = (string) $data['buyer_name'];
            $this->postData['Category'] = 'B2C';
            $this->postData['PrintFlag'] = 'N';

            // 載具類別有提供時，LoveCode 必為空值
            if ($data['buyer_type'] == 0) {
                $this->postData['LoveCode'] = '';
                $this->postData['CarrierType'] = (string) $data['carrier_type'];

                // 載具編號
                switch ($this->postData['CarrierType']) {
                    case '0': // 手機條碼
                        $this->postData['CarrierNum'] = rawurlencode($data['carrier_num']);
                        break;

                    case '1': // 自然人
                        $this->postData['CarrierNum'] = rawurlencode($data['carrier_num']);
                        break;

                    case '2': // 智付寶載具
                        $this->postData['CarrierNum'] = rawurlencode($data['buyer_email']);
                        break;

                    default:
                        $this->postData['CarrierNum'] = '';
                        // 載具類別、愛心碼皆為空時，索取發票必 Y
                        $this->postData['PrintFlag'] = 'Y';
                        break;
                }
            } else if ($data['buyer_type'] == 1){ // 捐贈
                $this->postData['CarrierType'] = '';
                $this->postData['LoveCode'] = $data['love_code'];
            }

            // 含稅價
            $this->postData['ItemPrice'] = (string) $data['amount'];
            $this->postData['ItemAmt'] = (string) $data['amount'];

        }

        // 零稅率、免稅的稅率 = 0
        if ($this->postData['TaxType'] == '2' || $this->postData['TaxType'] == '3') {
            $this->postData['TaxRate'] = 0;
        }

        // 混合應睡、零稅、免稅要提供銷售額
        // if ($this->postData['TaxType'] == '2' || $this->postData['TaxType'] == '3' || $this->postData['TaxType'] == '9') {
        //     // $this->postData['AmtSales'] = ;
        //     // $this->postData['AmtZero'] = ;
        //     // $this->postData['AmtFree'] = ;
        // }

        // 課稅別為零稅率，需要帶海關標記
        // if ($this->postData['TaxType'] == '2') {
        //     $this->postData['CustomsClearance'] = 1 or 2;
        // }

        // 課稅別為混合應稅
        if ($this->postData['TaxType'] == '9') {
            // 銷售額總計
            $amt = $this->postData['AmtSales'] + $this->postData['AmtZero'] + $this->postData['AmtFree'];
            // $this->postData['ItemTaxType'] = ;
        } else {
            $amt = round($data['amount'] / 1.05);
        }

        // 算稅額、發票金額
        $taxAmt = $data['amount'] - $amt;
        $totalAmt = $amt + $taxAmt;

        // 共同欄位
        $this->postData['BuyerAddress'] = (string) isset($data['buyer_address']) ? $data['buyer_address'] : '';
        $this->postData['BuyerEmail'] = (string) isset($data['buyer_email']) ? $data['buyer_email'] : '';
        $this->postData['MerchantOrderNo'] = (string) $data['no'];
        $this->postData['Amt'] = $amt;
        $this->postData['TaxAmt'] = $taxAmt;
        $this->postData['TotalAmt'] = $totalAmt;
        $this->postData['ItemName'] = (string) $data['desc'];
        $this->postData['ItemCount'] = '1';
        $this->postData['ItemUnit'] = '個';

    }
}

?>