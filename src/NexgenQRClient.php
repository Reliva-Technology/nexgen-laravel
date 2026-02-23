<?php

namespace Reliva\Nexgen;

use Reliva\Nexgen\NexgenCreateDynamicQR;
use Illuminate\Support\Facades\Http;
use Reliva\Nexgen\NexgenResponse;
use Reliva\Nexgen\NexgenCreateTerminal;

class NexgenQRClient
{

    /**
     * The API key used to authenticate requests to the Nexgen QR API.
     *
     * If not explicitly provided in the constructor, this is loaded from the configuration value:
     *      config('nexgen.QR_API_KEY')
     * This value is required for all API communication and must correspond to the selected environment
     * (production or custom).
     *
     * @var string|null The API key, or null if not set.
     */
    protected $apiKey;

    /**
     * The API secret used alongside the API key for authenticating requests to the Nexgen QR API.
     *
     * If omitted in the constructor, the value is taken from:
     *      config('nexgen.QR_API_SECRET')
     * This secret, paired with the API key, is used as credentials to ensure requests are authorized.
     *
     * @var string|null The API secret, or null if not set.
     */
    protected $apiSecret;

    /**
     * The base endpoint URL for all API requests to the Nexgen QR API.
     *
     * This value is determined based on the configured environment (production or custom).
     * For 'production', it defaults to "https://dash-nexgen.reliva.com.my".
     * For 'custom', it uses the value from config('nexgen.QR_ENDPOINT').
     * This property is set during construction and used as the root URL for all HTTP requests.
     *
     * @var string|null The API endpoint URL, or null if not yet set.
     */
    protected $endpoint;

    /**
     * The environment in which the QR client operates.
     *
     * This determines both which credentials and endpoint are used. Acceptable values are:
     *  - 'production': Uses live production API key, secret, and endpoint.
     *  - 'custom': Uses custom credentials and a custom endpoint as defined in the configuration.
     * The environment is set via the constructor or from config('nexgen.QR_ENVIRONMENT').
     *
     * @var string The current API environment.
     */
    protected $environment;

    /**
     * The terminal code used for QR operations.
     *
     * Each terminal is a registered payment point for QR transactions. This value uniquely identifies
     * which registered payment terminal to use for dynamic QR creation and other requests.
     * If not provided, it is loaded from config('nexgen.QR_TERMINAL_CODE').
     *
     * @var string|null The unique terminal code, or null if not set.
     */
    protected $terminalCode;

    /**
     * The callback URL where asynchronous payment updates will be delivered.
     *
     * When a QR payment is completed or changes status, Nexgen will send an HTTP request to this URL.
     * This property is generally configured per terminal and can be overridden per request, but defaults
     * to config('nexgen.QR_CALLBACK_URL') if not specified at construction.
     *
     * @var string|null The callback (webhook) URL, or null if not defined.
     */
    protected $callbackUrl;

    /**
     * NexgenQRClient constructor.
     *
     * This constructor initializes a new instance of the NexgenQRClient, designed to interact with the Nexgen QR API for
     * QR-based payment terminal operations. It allows the user to set API credentials and configuration either through
     * explicit parameters or by falling back to values defined in the application's configuration file (config/nexgen.php),
     * which are typically resolved from environment variables.
     *
     * Initialization steps:
     * 1. Determine the API environment ('production' or 'custom'):
     *    - If the $environment is not provided, default to the value found in config('nexgen.QR_ENVIRONMENT').
     *
     * 2. Set the API key and secret:
     *    - If the $apiKey or $apiSecret are not provided, default to values from config('nexgen.QR_API_KEY') and config('nexgen.QR_API_SECRET'), respectively.
     *    - These credentials are required for authenticating all requests.
     *
     * 3. Set the terminal code:
     *    - The terminal code uniquely identifies a payment terminal for QR operations.
     *    - Use the provided $terminalCode, or fall back to config('nexgen.QR_TERMINAL_CODE').
     *
     * 4. Set the callback URL:
     *    - The callback URL receives asynchronous updates on QR payment status.
     *    - Use the provided $callbackUrl, or config('nexgen.QR_CALLBACK_URL').
     *
     * 5. Validate configuration:
     *    - Ensure that required configuration values (environment, API key, and API secret) are present.
     *    - Throws exceptions with descriptive messages if validation fails.
     *
     * 6. Set the QR API endpoint:
     *    - If environment is 'production', use the official Nexgen QR production endpoint URL.
     *    - If environment is 'custom', use the endpoint specified in config('nexgen.QR_ENDPOINT').
     *    - Throw an exception if an unrecognized environment value is provided.
     *
     * @param string|null $apiKey        The API key for authenticating with the Nexgen QR API. If null, falls back to config.
     * @param string|null $apiSecret     The API secret for authentication. If null, falls back to config.
     * @param string|null $environment   The operating environment: 'production' or 'custom'. If null, defaults to config.
     * @param string|null $terminalCode  Terminal code for QR operations. If null, defaults to config.
     * @param string|null $callbackUrl   Callback URL for asynchronous QR payment updates. If null, defaults to config.
     * 
     * @throws \InvalidArgumentException If required configuration values are missing.
     * @throws \Exception If the environment is invalid (not 'production' or 'custom').
     */
    public function __construct(
        ?string $apiKey = null,
        ?string $apiSecret = null,
        ?string $environment = null,
        ?string $terminalCode = null,
        ?string $callbackUrl = null,
    )
    {
        // Set the instance environment.
        // Use explicit $environment if provided, otherwise fall back to the configuration value.
        $this->environment = $environment ?? config('nexgen.QR_ENVIRONMENT');
        
        // Determine and set the API key.
        // If an API key is provided in the constructor, it takes precedence over the configuration.
        if ($apiKey === null) {
            $this->apiKey = config('nexgen.QR_API_KEY');
        } else {
            $this->apiKey = $apiKey;
        }
        
        // Determine and set the API secret.
        // Use constructor value, or default to the configuration.
        if ($apiSecret === null) {
            $this->apiSecret = config('nexgen.QR_API_SECRET');
        } else {
            $this->apiSecret = $apiSecret;
        }
        
        // Determine and set the terminal code used for this QR client instance.
        $this->terminalCode = $terminalCode ?? config('nexgen.QR_TERMINAL_CODE');
        
        // Determine and set the callback (webhook) URL for asynchronous payment updates.
        $this->callbackUrl = $callbackUrl ?? config('nexgen.QR_CALLBACK_URL');

        // Validate that all required environment and credential values are present.
        // Throws an exception if any are missing or invalid.
        $this->validateConfiguration();

        /**
         * Set the base API endpoint URL based on the resolved environment:
         * - 'production': Uses the official Nexgen QR production endpoint.
         * - 'custom': Uses a user-provided endpoint from configuration.
         * - Unrecognized values: Throws an exception to prevent misconfiguration or accidental misuse of the API.
         */
        switch ($this->environment) {
            case 'production':
                // Set to the live Nexgen QR endpoint for real transactions.
                $this->endpoint = 'https://dash-nexgen.reliva.com.my';
                break;
            case 'custom':
                // Use a user-supplied custom endpoint; required for self-hosted or special cases.
                $this->endpoint = config('nexgen.QR_ENDPOINT');
                break;
            default:
                // Fail fast and loudly if an unrecognized environment is provided.
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

    /**
     * Get the Nexgen QR API endpoint currently in use.
     *
     * @return string|null The endpoint URL to which API requests are made, or null if not set.
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Get the QR environment currently configured.
     *
     * @return string The environment value ('production' or 'custom').
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Get the terminal code configured for QR payments.
     *
     * @return string|null The terminal code identifier, or null if not set.
     */
    public function getTerminalCode()
    {
        return $this->terminalCode;
    }

    /**
     * Get the configured callback/webhook URL for QR payment status updates.
     *
     * @return string|null The callback URL, or null if not set.
     */
    public function getCallbackUrl()
    {
        return $this->callbackUrl;
    }

    /**
     * Get the API key being used for Nexgen QR authentication.
     *
     * @return string|null The configured API key, or null if not set.
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Get the API secret being used for Nexgen QR authentication.
     *
     * @return string|null The configured API secret, or null if not set.
     */
    public function getApiSecret()
    {
        return $this->apiSecret;
    }

    /**
     * Get a summary of all current configuration values used by this QR client.
     *
     * @return array{
     *   api_key: string|null,
     *   api_secret: string|null,
     *   environment: string,
     *   endpoint: string|null,
     *   terminal_code: string|null,
     *   callback_url: string|null
     * }
     */
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


    /**
     * Create a new terminal in the Nexgen system.
     *
     * This method provisions a new terminal by sending its details to the Nexgen API. A terminal acts as a point-of-sale
     * endpoint where QR dynamic payment codes can be generated (for example, for a specific physical location, cash register,
     * or virtual point).
     *
     * The terminal details (name and description) are supplied via a NexgenCreateTerminal object.
     *
     * @see NexgenCreateTerminal.php (lines 5-9)
     *      class NexgenCreateTerminal
     *      {
     *          public String $name;
     *          public String $description;
     *          ...
     *      }
     *      $createTerminal->getName()        // Gets terminal name string
     *      $createTerminal->getDescription() // Gets terminal description string
     * 
     * The created terminal will have an 'active' status by default.
     *
     * @param NexgenCreateTerminal $createTerminal
     *     Value object containing:
     *       - $name (string): The name of the terminal (e.g., "terminal T1")
     *       - $description (string): Description for the terminal (e.g., "terminal T1")
     *     Both fields are required.
     *
     * @return NexgenResponse
     *     On success, returns a NexgenResponse containing the new terminal details, e.g.:
     *     [
     *         "code" => "RLVTBAQA0003",
     *         "name" => "terminal T1",
     *         "description" => "terminal T1",
     *         "status" => "active"
     *     ]
     *     Use $response->isSuccessful() to check status, and $response->getData() for the response array.
     *
     * @example
     *     $newTerminal = new NexgenCreateTerminal('terminal T1', 'terminal T1');
     *     $response = $qrClient->createTerminal($newTerminal);
     *     if ($response->isSuccessful()) {
     *         $data = $response->getData();
     *         echo "Created terminal code: " . $data['code'];
     *     } else {
     *         // Handle error
     *     }
     */
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

    /**
     * Retrieve a list of all created terminals from the Nexgen API.
     *
     * This method fetches an array of terminal objects currently registered under your account.
     * Each terminal in the array will typically contain the following fields:
     *   - "code"        : A unique identifier assigned to the terminal (e.g., "RLVTZCPA0001").
     *   - "name"        : The name given to the terminal (e.g., "terminal T1").
     *   - "description" : The description of the terminal (e.g., "terminal T1").
     *   - "status"      : The status of the terminal ("active" by default).
     *
     * @return NexgenResponse
     *     Returns a NexgenResponse object.
     *     Use $response->isSuccessful() to check the call's success.
     *     On success, $response->getData() will contain an array of terminal data in the format:
     *     [
     *         [
     *             "code" => "RLVTZCPA0001",
     *             "name" => "terminal T1",
     *             "description" => "terminal T1",
     *             "status" => "active"
     *         ],
     *         [
     *             "code" => "RLVTGCFA0002",
     *             "name" => "terminal T1",
     *             "description" => "terminal T1",
     *             "status" => "active"
     *         ],
     *         // ...additional terminals as available
     *     ]
     *     If no terminals exist, the data array will be empty.
     *
     * @example
     *     $response = $qrClient->getTerminalList();
     *     if ($response->isSuccessful()) {
     *         foreach ($response->getData() as $terminal) {
     *             echo "Code: {$terminal['code']} - Name: {$terminal['name']} - Status: {$terminal['status']}\n";
     *         }
     *     } else {
     *         // Handle error
     *         print_r($response->getErrors());
     *     }
     */
    public function getTerminalList()
    {
        return $this->makeGetRequest('api/v1/terminal/get/list');
    }

    /**
     * Retrieve detailed information about a specific terminal from the Nexgen API.
     *
     * If no $terminalCode is provided, the client will use the default terminal code configured in this instance.
     *
     * @param string|null $terminalCode
     *     The unique code identifying the terminal to be fetched. If null or empty, uses $this->terminalCode.
     *
     * @return NexgenResponse
     *     The response object contains detailed terminal data on success.
     *     Use $response->isSuccessful() to check for success.
     *
     * @example
     *     $response = $qrClient->getTerminal('RLVTGCFA0002');
     *     if ($response->isSuccessful()) {
     *         $terminal = $response->getData();
     *         // Sample terminal response:
     *         // [
     *         //     "code" => "RLVTGCFA0002",
     *         //     "name" => "terminal T1",
     *         //     "description" => "terminal T1",
     *         //     "status" => "active"
     *         // ]
     *         echo "Code: {$terminal['code']}, Name: {$terminal['name']}, Status: {$terminal['status']}\n";
     *     } else {
     *         print_r($response->getErrors());
     *     }
     */
    public function getTerminal(?String $terminalCode = null)
    {
        // Use the default terminal code if none is provided
        if (empty($terminalCode)) {
            $terminalCode = $this->terminalCode;
        }
        // Make the GET request to fetch terminal data
        return $this->makeGetRequest('api/v1/terminal/get/data/' . $terminalCode);
    }

    /**
     * Retrieve detailed information about a specific terminal along with its billing data from the Nexgen API.
     *
     * This endpoint returns both the terminal details and all bills associated with the terminal.
     * If no $terminalCode is provided, the client will use the default terminal code configured in this instance.
     *
     * @param string|null $terminalCode
     *     The unique code identifying the terminal to be fetched. If null or empty, uses $this->terminalCode.
     *
     * @return NexgenResponse
     *     The response object contains the terminal details including a list of associated bills.
     *     Use $response->isSuccessful() to check for success and $response->getData() to access the data.
     *
     *     Example response data structure:
     *     [
     *         "code" => "RLVTM7GA0006",
     *         "name" => "Laravel Package",
     *         "description" => "Laravel Package",
     *         "status" => "active",
     *         "bill_list" => [
     *             [
     *                 "code" => "RLVQKCG260223A0007",
     *                 "status" => "expired",
     *                 "amount" => "1.00",
     *                 "payment_description" => "Test Payment",
     *                 "due_date" => "23-02-2026 12:01:00",
     *                 "external_reference_label_1" => null,
     *                 "external_reference_value_1" => null,
     *                 "external_reference_label_2" => null,
     *                 "external_reference_value_2" => null,
     *                 "soundbox_response" => null
     *             ],
     *             [
     *                 "code" => "RLVQS5F260223A0008",
     *                 "status" => "expired",
     *                 "amount" => "1.00",
     *                 "payment_description" => "test data",
     *                 "due_date" => "23-02-2026 12:13:00",
     *                 "external_reference_label_1" => null,
     *                 "external_reference_value_1" => null,
     *                 "external_reference_label_2" => null,
     *                 "external_reference_value_2" => null,
     *                 "soundbox_response" => null
     *             ],
     *             // ... more bills
     *         ]
     *     ]
     *
     * @example
     *     $response = $qrClient->getTerminalDataBilling('RLVTM7GA0006');
     *     if ($response->isSuccessful()) {
     *         $terminal = $response->getData();
     *         echo "Terminal Code: {$terminal['code']}\n";
     *         echo "Name: {$terminal['name']}\n";
     *         echo "Status: {$terminal['status']}\n";
     *         echo "Bills:\n";
     *         foreach ($terminal['bill_list'] as $bill) {
     *             echo "  - Bill Code: {$bill['code']}, Amount: {$bill['amount']}, Status: {$bill['status']}\n";
     *         }
     *     } else {
     *         print_r($response->getErrors());
     *     }
     */
    public function getTerminalDataBilling(?String $terminalCode = null)
    {
        // Use the default terminal code if none is provided
        if (empty($terminalCode)) {
            $terminalCode = $this->terminalCode;
        }
        // Fetch the terminal data including associated bills
        return $this->makeGetRequest('api/v1/terminal/get/data/' . $terminalCode . '/billing');
    }

    /**
     * Create a dynamic DuitNow QR code for a payment on a specified Nexgen terminal.
     *
     * This method generates a dynamic QR code for a one-time payment tied to a particular terminal. The QR code encodes the payment amount,
     * payment description, callback URL, and up to two sets of external references. This QR code supports Malaysian DuitNow QR payments
     * and can be displayed directly to the payer as a base64-encoded PNG string.
     *
     * #### Example Response
     * The API will return a JSON object similar to:
     * {
     *   "code": "RLVQBWH260223A0147",
     *   "status": "unpaid",
     *   "amount": "1.00",
     *   "payment_description": "test payment",
     *   "due_date": "23-02-2026 15:52:00",
     *   "callback_url": "https:\/\/webhook.site\/d6637e28-ff2c-49b3-8be3-e38726fde500",
     *   "qr_code": "iVBORw...5CYII="
     * }
     *
     * - `qr_code`: This field contains a base64-encoded PNG image representing the generated QR code.
     *   You can render the QR code in an HTML page with:
     *   <img src="data:image/png;base64,{qr_code}" />
     *
     * @param NexgenCreateDynamicQR $createDynamicQR
     *     - fieldAmount (string): The payment amount. Required, e.g., "1.00"
     *     - fieldPaymentDescription (string): Description for the payment. Required.
     *     - fieldCallbackUrl (string|null): URL to receive payment status updates. Optional; if null, uses client's default.
     *     - fieldExternalReferenceLabel1 (string|null): Optional external reference label 1.
     *     - fieldExternalReferenceValue1 (string|null): Optional external reference value 1.
     *     - fieldExternalReferenceLabel2 (string|null): Optional external reference label 2.
     *     - fieldExternalReferenceValue2 (string|null): Optional external reference value 2.
     * @param String|null $terminalCode
     *     The terminal code to generate the QR for. If null, uses the default configured in the client.
     *
     * @return NexgenResponse
     *     Response object:
     *     - isSuccessful(): bool - True if QR was created successfully.
     *     - getData(): array|null - On success, array with keys: code, status, amount, payment_description, due_date, callback_url, qr_code.
     *     - getErrors(): array|null - On failure, contains error details.
     */
    public function createDynamicQR(NexgenCreateDynamicQR $createDynamicQR, ?String $terminalCode = null)
    {
        // Extract fields from the NexgenCreateDynamicQR value object.
        $amount = $createDynamicQR->getFieldAmount(); // Payment amount (string, required)
        $paymentDescription = $createDynamicQR->getFieldPaymentDescription(); // Payment description (string, required)
        $callbackUrl = $createDynamicQR->getFieldCallbackUrl(); // Callback URL (string|null, optional)
        $externalReferenceLabel1 = $createDynamicQR->getFieldExternalReferenceLabel1(); // Optional string
        $externalReferenceValue1 = $createDynamicQR->getFieldExternalReferenceValue1(); // Optional string
        $externalReferenceLabel2 = $createDynamicQR->getFieldExternalReferenceLabel2(); // Optional string
        $externalReferenceValue2 = $createDynamicQR->getFieldExternalReferenceValue2(); // Optional string

        // Use the default terminal code if not explicitly provided.
        if (empty($terminalCode)) {
            $terminalCode = $this->terminalCode;
        }

        // Use the default callback URL if not explicitly provided.
        if (empty($callbackUrl)) {
            $callbackUrl = $this->callbackUrl;
        }

        // Assemble the request payload. Include optional reference fields if they are not null.
        $dynamicQRRequest = [
            'fieldAmount' => $amount,
            'fieldPaymentDescription' => $paymentDescription,
            'fieldCallbackUrl' => $callbackUrl,
            ...array_filter([
                'fieldExternalReferenceLabel1' => $externalReferenceLabel1,
                'fieldExternalReferenceValue1' => $externalReferenceValue1,
                'fieldExternalReferenceLabel2' => $externalReferenceLabel2,
                'fieldExternalReferenceValue2' => $externalReferenceValue2,
            ], function ($value) {
                return !is_null($value);
            }),
        ];

        // Post the QR request to the Nexgen API and return the NexgenResponse object.
        // On success, the response will contain all details of the payment and a base64 PNG string for "qr_code".
        return $this->makePostRequest('api/v1/qr/create/' . $terminalCode, $dynamicQRRequest);
    }

    /**
     * Retrieve and return the data associated with a given QR code from the Nexgen API.
     *
     * This method will perform a GET request to the Nexgen QR API to obtain full information about
     * the specified QR code. If no terminal code is explicitly provided, the default terminal code
     * configured in the client instance will be used.
     *
     * A successful request returns a response object containing all details for the specified QR code.
     * 
     * Example successful response:
     * 
     * {
     *     "code": "RLVQXBP250801A0001",
     *     "amount": "1.00",
     *     "status": "paid",
     *     "payment_description": "Terminal QR Payment 3",
     *     "due_date": "01-08-2025 01:00:00",
     *     "external_reference_label_1": null,
     *     "external_reference_value_1": null,
     *     "external_reference_label_2": null,
     *     "external_reference_value_2": null,
     *     "callback_url": "https:\/\/webhook.site\/6246a18e-2028-42ff-91c9-4e34c2d3ee29",
     *     "soundbox_response": "Your recevied payment 1.00 has been successfully processed."
     * }
     *
     * @param String $qrCode
     *     The QR code identifier whose data you wish to fetch.
     * @param String|null $terminalCode
     *     Optional terminal code. If null, uses the client's default terminal code.
     *
     * @return NexgenResponse
     *     - isSuccessful(): bool - True if QR data was returned.
     *     - getData(): array|null - Contains all QR code payment fields (see example above).
     *     - getErrors(): array|null - If request failed, details about error(s).
     */
    public function getQRData(String $qrCode, ?String $terminalCode = null)
    {
        // Use the default terminal code if not provided
        if (empty($terminalCode)) {
            $terminalCode = $this->terminalCode;
        }
        // Make a GET request to the API to fetch all data about the given QR code
        return $this->makeGetRequest('api/v1/qr/get/data/' . $terminalCode . '/' . $qrCode);
    }

}

