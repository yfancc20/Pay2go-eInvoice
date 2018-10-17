<?php

namespace Yfancc20\Pay2goInvoice;

class AllowInvoice extends Pay2GoInvoice
{
    /*
     * 折讓發票的 class
     */
    protected $taxRate;

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
            'ItemCount' => 1, // 多商品以 | 隔開（string）
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

        $this->taxRate = config('pay2goinv.TaxRate') * 0.01 + 1;
    }

    // 設定參數（從訂單）
    public function setData($data)
    {
        $this->setDataByFields($data);

        // 營業，代未稅額
        $notTaxAmt = round($data['TotalAmt'] / $this->taxRate); // 未稅金額 -> 除以稅率
        $this->postData['ItemTaxAmt'] = $data['TotalAmt'] - $notTaxAmt;
        $this->postData['ItemPrice'] = $notTaxAmt;
        $this->postData['ItemAmt'] = $this->postData['ItemCount'] * $notTaxAmt;
        $this->postData['TotalAmt'] = $data['TotalAmt'];
        $this->postData['BuyerEmail'] = $data['BuyerEmail'];

        return $this->postData;
    }

}

?>
