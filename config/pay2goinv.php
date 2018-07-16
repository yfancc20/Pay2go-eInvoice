<?php

return [

    'Debug' => env('INVOICE_STORE_DEBUG'),

    // 商店代號
    'MerchantID' => env('INVOICE_STORE_ID'),

    // HashKey
    'HashKey' => env('INVOICE_HashKey'),

    // HashIv
    'HashIV' => env('INVOICE_HashIV'),

    // 回傳格式：JSON / String
    'RespondType' => 'JSON',

    // 開立串接版本：固定 1.4
    'Version_Create' => '1.4',

    // 作廢串接版本：固定 1.0
    'Version_Void' => '1.0',

    // 折讓串接版本：固定 1.3
    'Version_Allow' => '1.3',

    // 查詢串接版本：固定 1.1
    'Version_Search' => '1.1',

    // 開立方式：1=即時 0=等待觸發 3=預約
    'Status_Create' => '1',

    // 開立方式：0=不立即確認 1=立即確認
    'Status_Allow' => '1',

    // 預設買受人：B2B=需要統編 B2C=個人，不需要統編
    'Category' => 'B2C',

    // 課稅別：1=應稅 2=零稅率 3=免稅 9=混合應稅與免稅或零稅率（限收銀機發票無法分辨時使用）
    'TaxType' => '1',

    // 稅率：
    // 1. 課稅別為應稅時，一般稅率請帶 5，特種 稅率請帶入規定的課稅稅率(不含%，例稅率 18%則帶入 18)
    // 2. 課稅別為零稅率、免稅時，稅率請帶入 0
    'TaxRate' => '5',

    // 備註：限 71 字
    'Comment' => '',

    // 串接網址：開立
    'Url_Create' => 'https://inv.pay2go.com/API/invoice_issue',
    'Url_Create_Test' => 'https://cinv.pay2go.com/API/invoice_issue',

    // 作廢
    'Url_Void' => 'https://inv.pay2go.com/API/invoice_invalid',
    'Url_Void_Test' => 'https://cinv.pay2go.com/API/invoice_invalid',

    // 折讓
    'Url_Allow' => 'https://inv.pay2go.com/API/allowance_issue',
    'Url_Allow_Test' => 'https://cinv.pay2go.com/API/allowance_issue',

    // 查詢
    'Url_Search' => 'https://inv.pay2go.com/API/invoice_search',
    'Url_Search_Test' => 'https://cinv.pay2go.com/API/invoice_search'
];
