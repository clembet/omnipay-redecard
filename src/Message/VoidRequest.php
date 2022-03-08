<?php namespace Omnipay\RedeCard\Message;

/**
 *  O Cancelamento é aplicavel a transações do mesmo dia sendo autorizadas ou aprovadas
 *  O Estono é aplicável para transações onde virou o dia, seguindo o processo do adquirente
 * <code>
 *   // Do a refund transaction on the gateway
 *   $transaction = $gateway->void(array(
 *       'transactionId'     => $transactionCode,
 *   ));
 *
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *   }
 * </code>
 */

class VoidRequest extends AbstractRequest   // está dando  erro para vendas com cartao parcelado, não permitindo estornar individualmente o pagamento
{
    protected $resource = 'transactions';
    protected $requestMethod = 'PUT';


    public function getData()
    {
        $this->validate('transactionId', 'amount');
        //$data = parent::getData();
        $data = [
            'amount' => $this->getAmount(),
            'urls' => [
                [
                    "kind"=> "callback",
                    "url"=> $this->getNotifyUrl()
                ]
            ]
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
            "%s/%s/refunds",
            $this->getEndpoint(),
            $this->getTransactionID()
        );

        //print_r([$this->getMethod(), $url, $headers, $data]);exit();
        $httpResponse = $this->httpClient->request($this->getMethod(), $url, $headers, $this->toJSON($data));
        $json = $httpResponse->getBody()->getContents();
        return $this->createResponse(@json_decode($json, true));
    }

    public function getTransactionID()
    {
        return $this->getParameter('transactionId');
    }

    public function setTransactionID($value)
    {
        return $this->setParameter('transactionId', $value);
    }
}
