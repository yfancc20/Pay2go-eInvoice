<?php

namespace Yfancc20\Pay2goInvoice;

class SearchInvoice extends Pay2GoInvoice
{
    /*
     * 折讓發票的 class
     */

    // 設定串接的網址
    protected function setUrl()
    {
        if (!$this->debugMode) { // product mode, 串正式網址
            $this->pay2goUrl = config('pay2goinv.Url_Search');
        } else { // debug mode, 串測試網址
            $this->pay2goUrl = config('pay2goinv.Url_Search_Test');
        }
    }

    // 設定預設值，讀取 config
    protected function setDefault()
    {
        $this->postData = [
            'RespondType' => config('pay2goinv.RespondType'),
            'Version' => config('pay2goinv.Version_Search'),
            'TimeStamp' => time(),
            'SearchType' => '1', // 0:發票&隨機碼 1:訂單號&發票金額
            'MerchantOrderNo' => '',
            'InvoiceNumber' => '',
            'RandomNum' => '',
            'DisplayFlag' => '', // 1: 轉址
            'TotalAmt' => 0,
        ];
    }

    // 設定參數（從訂單）
    public function setData($data)
    {
        $this->postData['InvoiceNumber'] = $data['invoice_no'];
        $this->postData['MerchantOrderNo'] = $data['no'];
        $this->postData['TotalAmt'] = $data['amount'];
        // $this->postData['RxandomNum'] = $data['random_num'];

        return $this->postData;
    }


    // 與其他發票API方式不同，須以 form post 交由 pay2go 轉址
    public function sendURL()
    {
        $this->postData['DisplayFlag'] = '1';

        $postData_ = $this->encrypt($this->postData);

        $transaction_data_array = [
            'MerchantID_' => $this->merchantId,
            'PostData_' => $postData_
        ];

        $transaction_data_str = http_build_query($transaction_data_array);

        // set submit form
        $result = '<form name="Pay2go" id="order_form" method="post" action='.$this->pay2goUrl.'>';
        $result .= '<input type="hidden" name="MerchantID_" value="' . $this->merchantId . '">';
        $result .= '<input type="hidden" name="PostData_" value="' . $postData_ . '">';
        $result .= '</form><script type="text/javascript">document.getElementById(\'order_form\').submit();</script>';

        return $result;
    }

}

?>