<?php

namespace Yfancc20\Pay2goInvoice;

use Yfancc20\Pay2goInvoice\CreateInvoice;
use Yfancc20\Pay2goInvoice\VoidInvoice;
use Yfancc20\Pay2goInvoice\AllowInvoice;
use Yfancc20\Pay2goInvoice\SearchInvoice;

class Invoice
{
    /*
     * There are four methods: Create / Void / Allow / Search
     */

    // 開立
    public function create($data)
    {
        $pay2goInv = new CreateInvoice();
        $pay2goInv->setData($data);
        $pay2goInv->addComment($data['comment']);

        return $pay2goInv->send();
    }


    // 作廢
    public function void($data)
    {
        $pay2goInv = new VoidInvoice();
        $pay2goInv->setData($data);

        return $pay2goInv->send();
    }

    // 折讓
    public function allow($data)
    {
        $pay2goInv = new AllowInvoice();
        $pay2goInv->setData($data);

        return $pay2goInv->send();
    }

    // 查詢
    public function search($data)
    {
        $pay2goInv = new SearchInvoice();
        $pay2goInv->setData($data);

        return $pay2goInv->sendURL();
    }
}