<?php namespace Omnipay\RedeCard\Message;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\ItemBag;

class AuthorizeRequest extends AbstractRequest
{
    protected $resource = 'transactions';
    protected $requestMethod = 'POST';
    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */

    public function getData()
    {
        $this->validate('customer');

        $data = $this->getDataCreditCard();

        return $data;
    }

    public function getDataCreditCard()
    {
        $this->validate('card');
        $card = $this->getCard();

        $data = [
                "capture"=> false,
                "kind"=> "credit",
                "reference"=> $this->getOrderId(), //TODO: verificar se o tamanho máximo do pedido_num tem tamanho máximo 16
                "amount"=> $this->getAmount(),
                "cardholderName"=> $card->getName(),
                "cardNumber"=> $card->getNumber(),
                "expirationMonth"=> $card->getExpiryMonth(),
                "expirationYear"=> $card->getExpiryYear(),
                "securityCode"=> $card->getCvv(),
                "softDescriptor"=> $this->getSoftDescriptor(),
                "subscription"=> false,
                "origin"=> 1,
                "distributorAffiliation"=> 0, // Número de filiação do distribuidor (PV). Quantidade Máxima de Dígitos (9). // TODO: verificar se é necessário pegar essa informação no painel do rede
                //"brandTid"=> "string" //Só é utilizado quando para uma recorrência (subscription> true).
                "antifraudRequired" => false, // se colocar true, então complementar com os dados abaixo

                /*em Consumer information [
                    name
                    email
                    phone: 
                    type:2,
                    ddd: 81,
                    number: numero do telefone,
                    cpf:,
                ]
                
                em Shipping information [
                    address:
                    number:,
                    complement,
                    zipCode,
                    neighborhood,
                    city,
                    state,
                ]

                em itens[ 
                    type: 1 // 1= produto fisico, 2 = produto digital, 3 = serviços, 4 = aéreas
                    description
                    quantity
                    amount
                ]

                em device: [
                    ip: Customer IP checker
                    sessionId: Rede fingerprint Session ID PS: For more information, check out the item  “3.Device            Fingerprint”.  <script type="application/javascript" src="https://fingerprint.userede.com.br/b.js"></script>
                ]*/

        ];
        if($this->getInstallments()>1)
            $data["installments"]= $this->getInstallments();

        /*$data = [
            "MerchantOrderId"=>$this->getOrderId(),
            "Customer"=>$this->getCustomerData(),
            "Payment"=>[
                "Provider"=>$this->getTestMode()?"Simulado":$this->getPaymentProvider(), // https://braspag.github.io/manual/braspag-pagador#lista-de-providers
                "Type"=>"CreditCard",
                "Amount"=>$this->getAmount(),
                "Currency"=>"BRL",
                "Country"=>"BRA",
                "Installments"=>$this->getInstallments(),
                "Interest"=>"ByMerchant",
                "Capture"=>false, // true faz a captura, e false é apenas autorização sem lançar na fatura precisando capturar depois
                "Authenticate"=>false,
                "Recurrent"=>false,
                "SoftDescriptor"=>$this->getSoftDescriptor(),
                "DoSplit"=>false,
                "CreditCard"=>[
                    "CardNumber"=>$card->getNumber(),
                    "Holder"=>$card->getName(),
                    "ExpirationDate"=>sprintf("%02d/%04d", $card->getExpiryMonth(), $card->getExpiryYear()),
                    "SecurityCode"=>$card->getCvv(),
                    "Brand"=>$card->getBrand(),
                    "SaveCard"=>"false",
                    "Alias"=>"",
                ],
            ]
        ];*/

        return $data;
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
}
