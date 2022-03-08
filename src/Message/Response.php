<?php namespace Omnipay\RedeCard\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * Pagarme Response
 *
 * This is the response class for all Pagarme requests.
 *
 * @see \Omnipay\Pagarme\Gateway
 */
class Response extends AbstractResponse // TODO: validar essa estrutura
{
    private $validCodes = ['00'];
    /**
     * Is the transaction successful?
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return in_array($this->getCode(), $this->validCodes);
    }

    private function get($key, $default = null)
    {
        return isset($this->data[$key])
               ? $this->data[$key]
               : (isset($this->data['authorization'][$key])
                  ? $this->data['authorization'][$key]
                  : $default);

    }

    /**
     * Get the transaction reference.
     *
     * @return string|null
     */
    public function getTransactionReference()
    {
        return $this->get('tid');
    }

    public function getTransactionAuthorizationCode()
    {
        return $this->get('tid');
    }

    public function getStatus()
    {
        $status = null;
        if(isset($this->data['Payment']['Status']))
            $status = @$this->data['Payment']['Status'];
        else
        {
            if(isset($this->data['Status']))
                $status = @$this->data['Status'];
        }

        return $status;
    }

    public function isPaid()
    {
        $status = $this->getStatus();
        return $status==2;
    }

    public function isAuthorized()
    {
        $status = $this->getStatus();
        return $status==1;
    }

    public function isPending()
    {
        $status = $this->getStatus();
        return $status==12;
    }

    public function isVoided()
    {
        $status = $this->getStatus();
        return ($status==10||$status==11);
    }

    public function getCode()
    {
        return $this->get('returnCode');
    }

    /**
     * Get the error message from the response.
     *
     * Returns null if the request was successful.
     *
     * @return string|null
     */
    public function getMessage()
    {
        return $this->get('returnCode')." - ".$this->get('returnMessage');;

    }
}