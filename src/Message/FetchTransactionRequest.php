<?php namespace Omnipay\RedeCard\Message;

class FetchTransactionRequest extends AbstractRequest
{
    protected $resource = 'transactions';
    protected $requestMethod = 'GET';

    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */
    public function getData()
    {
        return parent::getData();
    }

    public function sendData($data)
    {
        $this->validate('transactionId');

        //$url = $this->getEndpoint();

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

        $httpResponse = $this->httpClient->request($this->getMethod(), $url, $headers);
        $json = $httpResponse->getBody()->getContents();
        return $this->createResponse(@json_decode($json, true));
    }
}
