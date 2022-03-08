<?php namespace Omnipay\RedeCard\Message;


abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    protected $liveEndpoint = 'https://api.userede.com.br/erede';
    protected $testEndpoint = 'https://sandbox-erede.useredecloud.com.br';
    protected $version = 1;
    protected $requestMethod = 'POST';
    protected $resource = 'transactions';

    public function sendData($data)
    {
        $method = $this->requestMethod;
        $url = $this->getEndpoint();

        $headers = [
            'Authorization' => 'Basic ' . $this->encodeCredentials($this->getMerchantId(), $this->getMerchantKey()),
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'application/json',
        ];

        //print_r([$method, $url, $headers, json_encode($data)]);exit();
        $response = $this->httpClient->request(
            $method,
            $url,
            $headers,
            $this->toJSON($data)
            //http_build_query($data, '', '&')
        );
        //print_r($response);
        //print_r($data);

        if ($response->getStatusCode() != 200 && $response->getStatusCode() != 201 && $response->getStatusCode() != 400) {
            $array = [
                'error' => [
                    'code' => $response->getStatusCode(),
                    'message' => $response->getReasonPhrase()
                ]
            ];

            return $this->response = $this->createResponse($array);
        }

        $json = $response->getBody()->getContents();
        $array = @json_decode($json, true);
        //print_r($array);

        return $this->response = $this->createResponse(@$array);
    }

    protected function setBaseEndpoint($value)
    {
        $this->baseEndpoint = $value;
    }

    public function __get($name)
    {
        return $this->getParameter($name);
    }

    public function encodeCredentials($merchantId, $merchantKey)
    {
        return base64_encode($merchantId . ':' . $merchantKey);
    }

    protected function setRequestMethod($value)
    {
        return $this->requestMethod = $value;
    }

    protected function decode($data)
    {
        return json_decode($data, true);
    }

    public function getEmail()
    {
        return $this->getParameter('email');
    }

    public function setEmail($value)
    {
        return $this->setParameter('email', $value);
    }

    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    public function getMerchantKey()
    {
        return $this->getParameter('merchantKey');
    }

    public function setMerchantKey($value)
    {
        return $this->setParameter('merchantKey', $value);
    }

    public function setOrderId($value)
    {
        return $this->setParameter('order_id', $value);
    }
    public function getOrderId()
    {
        return $this->getParameter('order_id');
    }

    public function setInstallments($value)
    {
        return $this->setParameter('installments', $value);
    }
    public function getInstallments()
    {
        return $this->getParameter('installments');
    }

    public function setSoftDescriptor($value)
    {
        return $this->setParameter('soft_descriptor', $value);
    }
    public function getSoftDescriptor()
    {
        return $this->getParameter('soft_descriptor');
    }

    public function getTransactionID()
    {
        return $this->getParameter('transactionId');
    }

    public function setTransactionID($value)
    {
        return $this->setParameter('transactionId', $value);
    }

    public function getCustomer()
    {
        return $this->getParameter('customer');
    }

    public function setCustomer($value)
    {
        return $this->setParameter('customer', $value);
    }

    public function getCustomerData()
    {
        $card = $this->getCard();
        $customer = $this->getCustomer();

        $data = [
            "Name"=>$customer->getName(),
            "Identity"=>$customer->getDocumentNumber(),
            "IdentityType"=>"CPF",
            "Email"=>$customer->getEmail(),
            "Birthdate"=>$customer->getBirthday('Y-m-d'),// formato ISO
            "IpAddress"=>$this->getClientIp(),
            "Address"=>[
                "Street"=>$customer->getBillingAddress1(),
                "Number"=>$customer->getBillingNumber(),
                "Complement"=>$customer->getBillingAddress2(),
                "ZipCode"=>$customer->getBillingPostcode(),
                "City"=>$customer->getBillingCity(),
                "State"=>$customer->getBillingState(),
                "Country"=>"BRA",
                "District"=>$customer->getBillingDistrict()
            ],
        ];

        if(strcmp(strtolower($this->getPaymentType()), "creditcard")==0)
        {
            $data["DeliveryAddress"]=[
                "Street"=>$card->getShippingAddress1(),
                "Number"=>$card->getShippingNumber(),
                "Complement"=>$card->getShippingAddress2(),
                "ZipCode"=>$card->getShippingPostcode(),
                "City"=>$card->getShippingCity(),
                "State"=>$card->getShippingState(),
                "Country"=>"BRA",
                "District"=>$card->getShippingDistrict()
            ];
        }

        return $data;
    }

    public function getItemData()
    {
        $data = [];
        $items = $this->getItems();

        if ($items) {
            foreach ($items as $n => $item) {
                $item_array = [];
                $item_array['id'] = $n+1;
                $item_array['title'] = $item->getName();
                $item_array['description'] = $item->getName();
                //$item_array['category_id'] = $item->getCategoryId();
                $item_array['quantity'] = (int)$item->getQuantity();
                //$item_array['currency_id'] = $this->getCurrency();
                $item_array['unit_price'] = (double)($this->formatCurrency($item->getPrice()));

                array_push($data, $item_array);
            }
        }

        return $data;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getMethod()
    {
        return $this->requestMethod;
    }

    protected function createResponse($data)
    {
        return $this->response = new Response($this, $data);
    }

    protected function getEndpoint()
    {
        $version = $this->getVersion();
        $endPoint = ($this->getTestMode()?$this->testEndpoint:$this->liveEndpoint);
        return  "{$endPoint}/v{$version}/{$this->getResource()}";
    }

    public function getData()
    {
        $this->validate('merchantId', 'merchantKey');

        return [];
    }

    public function toJSON($data, $options = 0)
    {
        if (version_compare(phpversion(), '5.4.0', '>=') === true) {
            return json_encode($data, $options | 64);
        }
        return str_replace('\\/', '/', json_encode($data, $options));
    }
}
