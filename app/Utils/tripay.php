<?php 

namespace App\Utils;
class tripay {
    protected $apiKey;
    protected $privateKey;
    protected $merchantCode;
    protected $amount;

    protected $invoice;

    public function __construct() {
        $this->apiKey = env('TRIPAY_API_KEY');
        $this->privateKey = env('TRIPAY_PRIVATE_KEY');
        $this->merchantCode = env('TRIPAY_MERCHANT_CODE');

    }

    public function getSignature(): string
    {
        return hash_hmac('sha256', $this->merchantCode . $this->invoice.$this->amount, $this->privateKey);

    }

    public function setSignature($invoice, $amount){
        $this->invoice = $invoice;
        $this->amount = $amount;
    }
}