<?php

namespace Yfancc20\Pay2goInvoice;

class CreateInvoice extends Pay2GoInvoice
{
    /*
     * 開立發票的 class
     */
    protected $taxRate;

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
            'ItemCount' => 1, // 多商品以 | 隔開（string）
            'ItemUnit' => '個',
            'ItemPrice' => 0,
            'ItemAmt' => 0,
            'ItemTaxType' => '',
            'Status' => config('pay2goinv.Status_Create'),
            'CreateStatusTime' => '',
            'Comment' => config('pay2goinv.Comment')
        ];

        $this->taxRate = $this->postData['TaxRate'] * 0.01 + 1;
    }

    // 設定參數（overwrite原本的 setData()）
    public function setData($data)
    {
        // 可接受 array / object
        $this->setDataByFields($data);

        // 檢查傳出資料
        $this->checkPostData($data);
        
        return $this->postData;
    }

    /*
     * 填寫 postData 的各條件審核
     * 有些欄位有相依性，條件來自 pay2go 官方 API 文件
     */
    private function checkPostData($data)
    {

        // B2B、B2C 欄位條件
        if ($data['BuyerType'] == 2) {
            // B2B，有統編
            $this->postData['BuyerName'] = $data['BuyerName'];
            $this->postData['BuyerUBN'] = (string) $data['BuyerUBN'];
            $this->postData['Category'] = 'B2B';
            // 載具類別、愛心代碼欄位只適用 B2C
            $this->postData['CarrierType'] = '';
            $this->postData['LoveCode'] = '';

            $this->postData['PrintFlag'] = 'Y'; // B2B 必填 Y
            // 未稅價
            $this->postData['ItemPrice'] = (string) round($data['ItemPrice'] / $this->taxRate);
            $this->postData['ItemAmt'] = (string) round($data['ItemPrice'] / $this->taxRate);

        } else {
            // B2C，買受人個人
            $this->postData['BuyerName'] = (string) $data['BuyerName'];
            $this->postData['BuyerUBN'] = '';
            $this->postData['Category'] = 'B2C';
            $this->postData['PrintFlag'] = 'N';

            // 載具類別有提供時，LoveCode 必為空值
            if ($data['BuyerType'] == 0) {
                $this->postData['LoveCode'] = '';
                $this->postData['CarrierType'] = (string) $data['CarrierType'];

                // 載具編號
                switch ($this->postData['CarrierType']) {
                    case '0': // 手機條碼
                        $this->postData['CarrierNum'] = rawurlencode($data['CarrierNum']);
                        break;

                    case '1': // 自然人
                        $this->postData['CarrierNum'] = rawurlencode($data['CarrierNum']);
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
            } else if ($data['BuyerType'] == 1){ // 捐贈
                $this->postData['CarrierType'] = ''; // 捐贈時，載具類別為空值
                $this->postData['LoveCode'] = $data['LoveCode'];
            }

            // 含稅價
            $this->postData['ItemPrice'] = (string) $data['ItemPrice'];
            $this->postData['ItemAmt'] = $this->postData['ItemCount'] * $this->postData['ItemPrice'];

        }

        // 零稅率、免稅的稅率 = 0
        if ($this->postData['TaxType'] == '2' || $this->postData['TaxType'] == '3') {
            $this->postData['TaxRate'] = 0;
        }

        // 混合應稅、零稅、免稅要提供銷售額
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
            $amt = round($data['ItemPrice'] / $this->taxRate);
        }

        // 算稅額、發票金額
        $taxAmt = $data['ItemPrice'] - $amt;
        $totalAmt = $amt + $taxAmt;

        // 共同欄位
        $this->postData['Amt'] = $amt;
        $this->postData['TaxAmt'] = $taxAmt;
        $this->postData['TotalAmt'] = $totalAmt;
    }
}

?>