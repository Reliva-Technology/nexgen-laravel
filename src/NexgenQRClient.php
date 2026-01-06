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
        $this->environment = $environment ?? config('nexgen.QR_ENVIRONMENT');
        
        // Determine API key and secret based on QR environment if not explicitly provided
        if ($apiKey === null) {
            $this->apiKey = config('nexgen.QR_API_KEY');
        } else {
            $this->apiKey = $apiKey;
        }
        
        if ($apiSecret === null) {
            $this->apiSecret = config('nexgen.QR_API_SECRET');
        } else {
            $this->apiSecret = $apiSecret;
        }
        
        $this->terminalCode = $terminalCode ?? config('nexgen.QR_TERMINAL_CODE');
        $this->callbackUrl = $callbackUrl ?? config('nexgen.QR_CALLBACK_URL');

        // Validate QR environment and required API keys
        $this->validateConfiguration();

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

    /**
     * Validate that required environment variables are set based on the selected QR environment.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validateConfiguration()
    {
        // Check if QR environment is set
        if (empty($this->environment)) {
            throw new \InvalidArgumentException(
                'NexgenQRClient requires NEXGEN_QR_ENVIRONMENT to be set. ' .
                'Please set NEXGEN_QR_ENVIRONMENT in your .env file or pass it to the constructor.'
            );
        }

        // Check if API key and secret are set
        if (empty($this->apiKey) || empty($this->apiSecret)) {
            $missingVars = [];
            
            if (empty($this->apiKey)) {
                $missingVars[] = 'NEXGEN_QR_PROD_API_KEY';
            }
            if (empty($this->apiSecret)) {
                $missingVars[] = 'NEXGEN_QR_PROD_API_SECRET';
            }
            
            throw new \InvalidArgumentException(
                'NexgenQRClient requires the following environment variables to be set when using "' . $this->environment . '" environment: ' .
                implode(', ', $missingVars) . '. ' .
                'Please set these in your .env file or pass them to the constructor.'
            );
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
            'fieldName' => $name,
            'fieldDescription' => $description,
            'fieldStatus' => 'active',
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
            'fieldAmount' => $amount,
            'fieldPaymentDescription' => $paymentDescription,
            'fieldCallbackUrl' => $callbackUrl,
                // Optional external references, only include if not null
                ...array_filter([
                    'fieldExternalReferenceLabel1' => $externalReferenceLabel1,
                    'fieldExternalReferenceValue1' => $externalReferenceValue1,
                    'fieldExternalReferenceLabel2' => $externalReferenceLabel2,
                    'fieldExternalReferenceValue2' => $externalReferenceValue2,
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

