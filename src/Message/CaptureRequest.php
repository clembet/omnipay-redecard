<?php namespace Omnipay\RedeCard\Message;


class CaptureRequest extends AbstractRequest
{
    protected $resource = 'transactions';
    protected $requestMethod = 'PUT';


    public function getData()
    {
        $this->validate('transactionId', 'amount');
        //$data = parent::getData();
        $data = [
            'amount' => (int)($this->getAmount()*100.0)
        ];

        return $data;
    }

    public function sendData($data)
    {
        $this->validate('transactionId', 'amount');

        $url = $this->getEndpoint();
        $data = $this->getData();

        $headers = [
            'Authorization' => 'Basic ' . $this->encodeCredentials($this->getMerchantId(), $this->getMerchantKey()),
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'application/json',
        ];

        $url = sprintf(
            "%s/%s",
            $this->getEndpoint(),
            $this->getTransactionID()
        );

        //print_r([$this->getMethod(), $url, $headers, $data]);exit();
        $httpResponse = $this->httpClient->request($this->getMethod(), $url, $headers, $this->toJSON($data));
        $json = $httpResponse->getBody()->getContents();
        return $this->createResponse(@json_decode($json, true));
    }
}
