<?php
namespace ierusalim\GitHubWebhook;

class Handler
{
    private $secret;
    private $webhookCall;
    private $data;
    private $event;
    private $delivery;

    public function __construct($secret, $webhookCall)
    {
        if(!is_callable($webhookCall)) {
            throw new \Exception("WebhookCall must be callable",500);
        }
        $this->secret = $secret;
        $this->webhookCall = $webhookCall;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getDelivery()
    {
        return $this->delivery;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getSecret()
    {
        return $this->secret;
    }

    public function handle()
    {
        if (!$this->validate()) {
            return false;
        }
        return call_user_func(
            $this->webhookCall,[
                'event'=>$this->event,
                'delivery'=>$this->delivery,
                'data'=>$this->data
            ]
        );
    }

    public function validate()
    {
        $signature = @$_SERVER['HTTP_X_HUB_SIGNATURE'];
        $event = @$_SERVER['HTTP_X_GITHUB_EVENT'];
        $delivery = @$_SERVER['HTTP_X_GITHUB_DELIVERY'];

        if (!isset($signature, $event, $delivery)) {
            return false;
        }

        $payload = file_get_contents('php://input');

        // Check if the payload is json or urlencoded.
        if (strpos($payload, 'payload=') === 0) {
            $payload = substr(urldecode($payload), 8);
        }

        if (!$this->validateSignature($signature, $payload)) {
            return false;
        }

        $this->data = json_decode($payload,true);
        $this->event = $event;
        $this->delivery = $delivery;
        return true;
    }

    protected function validateSignature($gitHubSignatureHeader, $payload)
    {
        list ($algo, $gitHubSignature) = explode("=", $gitHubSignatureHeader);

        if ($algo !== 'sha1') {
            // see https://developer.github.com/webhooks/securing/
            return false;
        }

        $payloadHash = hash_hmac($algo, $payload, $this->secret);
        return ($payloadHash === $gitHubSignature);
    }
}
