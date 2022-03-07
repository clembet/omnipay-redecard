<?php namespace Omnipay\RedeCard\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * Pagarme Response
 *
 * This is the response class for all Pagarme requests.
 *
 * @see \Omnipay\Pagarme\Gateway
 */
class Response extends AbstractResponse
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
    public function getTransactionID()
    {
        return $this->get('tid');
    }

    public function getTransactionAuthorizationCode()
    {
        return $this->get('authorizationCode');
    }

    public function getTransactionNSU()
    {
        return $this->get('nsu');
    }

    public function getStatus()
    {
        $status = null;
        if(isset($this->data['authorization']['status']))
            $status = @$this->data['authorization']['status'];
        else
        {
            if(isset($this->data['status']))
                $status = @$this->data['status'];
        }

        return $status;
    }

    public function isPaid()
    {
        $status = @strtolower($this->getStatus());
        return strcmp("approved", $status)==0;
    }

    public function isAuthorized()
    {
        $status = @strtolower($this->getStatus());
        return strcmp("pending", $status)==0;
    }

    public function isPending()
    {
        $status = @strtolower($this->getStatus());
        return strcmp("pending", $status)==0;
    }

    public function isVoided()
    {
        $status = @strtolower($this->getStatus());
        return strcmp("canceled", $status)==0;
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
        return $this->get('returnCode')." - ".$this->get('returnMessage');

    }
}