<?php namespace Omnipay\RedeCard\Message;

class PurchaseRequest extends AuthorizeRequest
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
        // faz o registro do cliente, se não houver especificado

        $data = parent::getData();
        $data["capture"] = true;
        //$this->getNotifyUrl()  // verificar se no painel é especificado uma url para notificação

        return $data;
    }
}
