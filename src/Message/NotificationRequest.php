<?php namespace Omnipay\RedeCard\Message;

/*
https://developer.userede.com.br/e-rede#url-notificacoes

A URL pode ser informada na própria API ou acessando o portal da Rede em: menu e.Rede > Configurações > URL de notificações. Ressaltamos que caso a URL seja informada nos 2 canais, a prioridade do envio das notificações será sempre na que foi informada na API.

 */
class NotificationRequest extends AbstractRequest //TODO: refazer
{
    protected $resource = 'notifications';
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

    public function getNotificationType()
    {
        return $this->getParameter('notificationType');
    }

    public function setNotificationType($value)
    {
        return $this->setParameter('notificationType', $value);
    }

    public function setNotificationCode($value)
    {
        return $this->setParameter('notificationCode', $value);
    }

    public function getNotificationCode()
    {
        return $this->getParameter('notificationCode');
    }

    public function sendData($data)
    {
        $this->validate('notificationCode');

        $url = sprintf(
            '%s/%s?%s',
            $this->getEndpoint(),
            $this->getNotificationCode(),
            http_build_query($data, '', '&')
        );

        //print $url."\n\n";
        $httpResponse = $this->httpClient->request($this->getMethod(), $url, ['Content-Type' => 'application/x-www-form-urlencoded']);
        $xml          = @simplexml_load_string($httpResponse->getBody()->getContents(), 'SimpleXMLElement', LIBXML_NOCDATA);

        return $this->createResponse(@$this->xml2array($xml));
    }
}
