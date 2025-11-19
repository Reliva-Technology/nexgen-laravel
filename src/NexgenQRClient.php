<?php

namespace Reliva\Nexgen;

use Reliva\Nexgen\NexgenCreateDynamicQR;
use Illuminate\Support\Facades\Http;
use Reliva\Nexgen\NexgenResponse;
use Reliva\Nexgen\NexgenCreateTerminal;

class NexgenQRClient
{

    /**
     * The API key for authentication.
     *
     * @var string|null
     */
    protected $apiKey;

    /**
     * The API secret for authentication.
     *
     * @var string|null
     */
    protected $apiSecret;

    /**
     * The API endpoint URL.
     *
     * @var string|null
     */
    protected $endpoint;

    /**
     * The environment (production / custom).
     *
     * @var string
     */
    protected $environment;

    /**
     * The terminal code.
     *
     * @var string|null
     */
    protected $terminalCode;

    /**
     * The callback URL.
     *
     * @var string|null
     */
    protected $callbackUrl;

    public function __construct(
        ?String $apiKey = null,
        ?String $apiSecret = null,
        ?String $environment = null,
        ?String $terminalCode = null,
        ?String $callbackUrl = null,
    )
    {
        $this->apiKey = $apiKey ?? config('nexgen.API_KEY');
        $this->apiSecret = $apiSecret ?? config('nexgen.API_SECRET');
        $this->environment = $environment ?? config('nexgen.QR_ENVIRONMENT');
        $this->terminalCode = $terminalCode ?? config('nexgen.QR_TERMINAL_CODE');
        $this->callbackUrl = $callbackUrl ?? config('nexgen.QR_CALLBACK_URL');

        // Set endpoint based on environment
        // if environment is production, use the production endpoint
        // if environment is custom, use the custom endpoint
        switch ($this->environment) {
            case 'production':
                $this->endpoint = 'https://dash-nexgen.reliva.com.my';
                break;
            case 'custom':
                $this->endpoint = config('nexgen.QR_ENDPOINT');
                break;
            default:
                throw new \Exception('Invalid environment: ' . $this->environment);
        }
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function getTerminalCode()
    {
        return $this->terminalCode;
    }

    public function getCallbackUrl()
    {
        return $this->callbackUrl;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function getApiSecret()
    {
        return $this->apiSecret;
    }

    public function getConfig()
    {
        return [
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
            'environment' => $this->environment,
            'endpoint' => $this->endpoint,
            'terminal_code' => $this->terminalCode,
            'callback_url' => $this->callbackUrl,
        ];
    }


    private function makeGetRequest(String $urlPath, array $params = [])
    {

        $url = rtrim($this->endpoint, '/') . '/' . ltrim($urlPath, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params) . '&ApiSecret=' . $this->apiSecret;
        } else {
            $url .= '?ApiSecret=' . $this->apiSecret;
        }

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'ApiKey' => $this->apiKey,
        ])->get($url);
        

        return new NexgenResponse($response->successful(), $response->json());
    }

    private function makePostRequest(String $urlPath, array $data = [], array $params = [])
    {
        $url = rtrim($this->endpoint, '/') . '/' . ltrim($urlPath, '/');


        if (!empty($params)) {
            $url .= '?' . http_build_query($params) . '&ApiSecret=' . $this->apiSecret;
        } else {
            $url .= '?ApiSecret=' . $this->apiSecret;
        }


        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'ApiKey' => $this->apiKey,
        ])->post($url, $data);

        return new NexgenResponse($response->successful(), $response->json());
    }


    public function createTerminal(NexgenCreateTerminal $createTerminal)
    {
        $name = $createTerminal->getName();
        $description = $createTerminal->getDescription();

        return $this->makePostRequest('api/v1/terminal/create', [
            'name' => $name,
            'description' => $description,
            'status' => 'active',
        ]);
    }

    public function getTerminalList()
    {
        return $this->makeGetRequest('api/v1/terminal/get/list');
    }

    public function getTerminal(?String $terminalCode = null)
    {
        if (empty($terminalCode)) {
            $terminalCode = $this->terminalCode;
        }
        return $this->makeGetRequest('api/v1/terminal/get/data/' . $terminalCode);
    }

    public function getTerminalDataBilling(?String $terminalCode = null)
    {
        if (empty($terminalCode)) {
            $terminalCode = $this->terminalCode;
        }
        return $this->makeGetRequest('api/v1/terminal/get/data/' . $terminalCode . '/billing');
    }

    public function createDynamicQR(NexgenCreateDynamicQR $createDynamicQR, ?String $terminalCode = null){
        $amount = $createDynamicQR->getFieldAmount();
        $paymentDescription = $createDynamicQR->getFieldPaymentDescription();
        $callbackUrl = $createDynamicQR->getFieldCallbackUrl();
        $externalReferenceLabel1 = $createDynamicQR->getFieldExternalReferenceLabel1();
        $externalReferenceValue1 = $createDynamicQR->getFieldExternalReferenceValue1();
        $externalReferenceLabel2 = $createDynamicQR->getFieldExternalReferenceLabel2();
        $externalReferenceValue2 = $createDynamicQR->getFieldExternalReferenceValue2();

        if (empty($terminalCode)) {
            $terminalCode = $this->terminalCode;
        }

        if (empty($callbackUrl)) {
            $callbackUrl = $this->callbackUrl;
        }

        $dynamicQRRequest = [
            'amount' => $amount,
            'paymentDescription' => $paymentDescription,
            'callbackUrl' => $callbackUrl,
                // Optional external references, only include if not null
                ...array_filter([
                    'externalReferenceLabel1' => $externalReferenceLabel1,
                    'externalReferenceValue1' => $externalReferenceValue1,
                    'externalReferenceLabel2' => $externalReferenceLabel2,
                    'externalReferenceValue2' => $externalReferenceValue2,
                ], function ($value) {
                    return !is_null($value);
                }),
        ];
        
        return $this->makePostRequest('api/v1/qr/create/' . $terminalCode, $dynamicQRRequest);
    }

    public function getQRData(String $qr_code, ?String $terminalCode = null)
    {
        if (empty($terminalCode)) {
            $terminalCode = $this->terminalCode;
        }
        return $this->makeGetRequest('api/v1/qr/get/data/' . $terminalCode . '/' . $qr_code);
    }
}

