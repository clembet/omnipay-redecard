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
                "amount"=> (int)($this->getAmount()*100.0),
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
                "Amount"=>(int)($this->getAmount()*100.0),
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
}
