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
    protected $pay2goInv = null;

    // 開立
    public function create($data)
    {
        $this->pay2goInv = new CreateInvoice();
        $this->pay2goInv->setData($data);

        return $this->pay2goInv->send();
    }

    // 作廢
    public function void($data)
    {
        $this->pay2goInv = new VoidInvoice();
        $this->pay2goInv->setData($data);

        return $this->pay2goInv->send();
    }

    // 折讓
    public function allow($data)
    {
        $this->pay2goInv = new AllowInvoice();
        $this->pay2goInv->setData($data);

        return $this->pay2goInv->send();
    }

    // 查詢
    public function search($data)
    {
        $this->pay2goInv = new SearchInvoice();
        $this->pay2goInv->setData($data);

        return $this->pay2goInv->sendURL();
    }

    public function getPostData()
    {
        if ($this->pay2goInv) {
            return $this->pay2goInv->getData();
        } else {
            return '[ getData() ]: The instance has not been set yet.';
        }
    }

    public function getRawResult()
    {
        if ($this->pay2goInv) {
            return $this->pay2goInv->getRawResult();
        } else {
            return '[ getRawResult() ]: The instance has not benn set yet.';
        }
    }
}