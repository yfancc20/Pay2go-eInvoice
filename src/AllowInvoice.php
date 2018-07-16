<?php

namespace Yfancc20\Pay2goInvoice;

class AllowInvoice extends Pay2GoInvoice
{
    /*
     * 折讓發票的 class
     */

    // 設定串接的網址
    protected function setUrl()
    {
        if (!$this->debugMode) { // product mode, 串正式網址
            $this->pay2goUrl = config('pay2goinv.Url_Allow');
        } else { // debug mode, 串測試網址
            $this->pay2goUrl = config('pay2goinv.Url_Allow_Test');
        }
    }

    // 設定預設值，讀取 config
    protected function setDefault()
    {
        $this->postData = [
            'RespondType' => config('pay2goinv.RespondType'),
            'Version' => config('pay2goinv.Version_Create'),
            'TimeStamp' => time(), // 需要為 time() 格式
            'InvoiceNo' => '',
            'MerchantOrderNo' => '',
            'ItemName' => '',
            'ItemCount' => 0, // 多商品以 | 隔開（string）
            'ItemUnit' => '',
            'ItemPrice' => 0,
            'ItemAmt' => 0,
            'TaxTypeForMixed' => '',
            'ItemTaxType' => '',
            'ItemTaxAmt' => 0,
            'TotalAmt' => 0,
            'BuyerEmail' => '',
            'Status' => config('pay2goinv.Status_Allow'),
        ];
    }

    // 設定參數（從訂單）
    public function setData($data)
    {
        $this->postData['InvoiceNo'] = $data['invoice_no'];
        $this->postData['MerchantOrderNo'] = $data['no'];
        $this->postData['ItemName'] = $data['desc'];
        $this->postData['ItemCount'] = 1;
        $this->postData['ItemUnit'] = '個';

        // 營業，代未稅額
        $taxRate = 1 + (config('pay2goinv.TaxRate') / 100); // 1.05
        $notTaxAmt = round($data['amount'] / $taxRate); // 未稅金額: /1.05
        $this->postData['ItemTaxAmt'] = $data['amount'] - $notTaxAmt;
        $this->postData['ItemPrice'] = $notTaxAmt;
        $this->postData['ItemAmt'] = $this->postData['ItemCount'] * $notTaxAmt;
        $this->postData['TotalAmt'] = $data['amount'];
        $this->postData['BuyerEmail'] = $data['buyer_email'];

        return $this->postData;
    }

}

?>
