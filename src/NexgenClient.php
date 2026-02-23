<?php

namespace Reliva\Nexgen;

use Illuminate\Support\Facades\Http;
use Reliva\Nexgen\NexgenCreateCollection;
use Reliva\Nexgen\NexgenCreateBilling;

class NexgenClient
{
    /**
     * @var string|null $apiKey
     * The API key used for authenticating requests to the Nexgen API.
     */
    protected $apiKey;

    /**
     * @var string|null $apiSecret
     * The API secret used in combination with the API key for authentication.
     */
    protected $apiSecret;

    /**
     * @var string|null $endpoint
     * The base URL endpoint for the Nexgen API, determined by the environment.
     */
    protected $endpoint;

    /**
     * @var string $environment
     * The environment for API requests. Acceptable values: 'sandbox', 'production', or 'custom'.
     */
    protected $environment;

    /**
     * @var string|null $collectionCode
     * The default collection code used when creating or retrieving billings. Can be overridden per request.
     */
    protected $collectionCode;

    /**
     * @var string|null $callbackUrl
     * The URL where payment status updates (webhooks) from Nexgen will be sent.
     */
    protected $callbackUrl;

    /**
     * @var string|null $redirectUrl
     * The URL to which the user will be redirected after completing the payment process.
     */
    protected $redirectUrl;

    /**
     * Constructor for the NexgenClient class.
     *
     * This constructor initializes a new instance of the NexgenClient which can be used to interact with the Nexgen API
     * for payment gateway operations. It allows the configuration of API credentials and endpoints either directly via 
     * arguments or by reading from Laravel's configuration (config/nexgen.php - which resolves from environment variables).
     *
     * @param string|null $apiKey         The API key to authenticate requests. If not provided, it will use the value from config('nexgen.API_KEY').
     * @param string|null $apiSecret      The API secret corresponding to the API key. If not provided, it will use config('nexgen.API_SECRET').
     * @param string|null $environment    The operating environment for the API calls. Valid values are 'sandbox', 'production', or 'custom'.
     *                                   If not provided, it will use config('nexgen.ENVIRONMENT').
     * @param string|null $collectionCode Default collection code for billing operations; can be overridden per request. 
     *                                   If not provided, will use config('nexgen.COLLECTION_CODE').
     * @param string|null $callbackUrl    The webhook URL for payment status updates. If not provided, will use config('nexgen.CALLBACK_URL').
     * @param string|null $redirectUrl    The URL to redirect the customer after payment. If not provided, will use config('nexgen.REDIRECT_URL').
     *
     * Initialization process:
     * - If arguments are not explicitly provided, fetches values from Laravel's config helper (which reads .env values).
     * - Validates that the required configuration values are set.
     * - Determines the appropriate API endpoint URL based on selected environment (sandbox, production, or custom).
     *
     * @throws \Exception                If the environment is invalid (not one of sandbox, production, or custom).
     * @throws \InvalidArgumentException If required configuration values are missing.
     */
    public function __construct(
        ?string $apiKey = null,
        ?string $apiSecret = null,
        ?string $environment = null,
        ?string $collectionCode = null,
        ?string $callbackUrl = null,
        ?string $redirectUrl = null,
    ) {
        // Set the environment. If not provided, use value from config file.
        $this->environment = $environment ?? config('nexgen.ENVIRONMENT');

        // Set the API key. Give precedence to constructor argument, then config.
        if ($apiKey === null) {
            $this->apiKey = config('nexgen.API_KEY');
        } else {
            $this->apiKey = $apiKey;
        }

        // Set the API secret. Give precedence to constructor argument, then config.
        if ($apiSecret === null) {
            $this->apiSecret = config('nexgen.API_SECRET');
        } else {
            $this->apiSecret = $apiSecret;
        }

        // Set the default collection code for billings (may be null).
        $this->collectionCode = $collectionCode ?? config('nexgen.COLLECTION_CODE');

        // Set the webhook callback URL for payment status updates (may be null).
        $this->callbackUrl = $callbackUrl ?? config('nexgen.CALLBACK_URL');

        // Set the customer redirect URL after payment (may be null).
        $this->redirectUrl = $redirectUrl ?? config('nexgen.REDIRECT_URL');

        // Validate that all required configuration (API keys, secrets, and environment) are available.
        $this->validateConfiguration();

        /**
         * Set the Nexgen API endpoint URL based on the chosen environment:
         * - If 'sandbox', set to the official Nexgen sandbox endpoint.
         * - If 'production', set to the official Nexgen production endpoint.
         * - If 'custom', use the endpoint specified in config('nexgen.ENDPOINT').
         * If the environment is invalid, throw an exception.
         */
        switch ($this->environment) {
            case 'sandbox':
                // Use the staging/sandbox endpoint for safe API testing.
                $this->endpoint = 'https://dash-nexgen-stg.reliva.com.my';
                break;
            case 'production':
                // Use the live production endpoint for real transactions.
                $this->endpoint = 'https://dash-nexgen.reliva.com.my';
                break;
            case 'custom':
                // Use the custom endpoint specified in configuration, typically for special use cases.
                $this->endpoint = config('nexgen.ENDPOINT');
                break;
            default:
                // The specified environment value is not recognized; throw an explicit error.
                throw new \Exception('Invalid environment: ' . $this->environment);
        }
    }

    /**
     * Validate that required environment variables are set based on the selected environment.
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validateConfiguration()
    {
        // Check if environment is set
        if (empty($this->environment)) {
            throw new \InvalidArgumentException(
                'NexgenClient requires NEXGEN_ENVIRONMENT to be set. ' .
                'Please set NEXGEN_ENVIRONMENT in your .env file or pass it to the constructor.'
            );
        }

        // Check if API key and secret are set
        if (empty($this->apiKey) || empty($this->apiSecret)) {
            $missingVars = [];
            
            if ($this->environment === 'sandbox') {
                if (empty($this->apiKey)) {
                    $missingVars[] = 'NEXGEN_SANDBOX_API_KEY';
                }
                if (empty($this->apiSecret)) {
                    $missingVars[] = 'NEXGEN_SANDBOX_API_SECRET';
                }
                
                throw new \InvalidArgumentException(
                    'NexgenClient requires the following environment variables to be set when using "sandbox" environment: ' .
                    implode(', ', $missingVars) . '. ' .
                    'Please set these in your .env file or pass them to the constructor.'
                );
            } else {
                // production or custom environment
                if (empty($this->apiKey)) {
                    $missingVars[] = 'NEXGEN_PROD_API_KEY';
                }
                if (empty($this->apiSecret)) {
                    $missingVars[] = 'NEXGEN_PROD_API_SECRET';
                }
                
                throw new \InvalidArgumentException(
                    'NexgenClient requires the following environment variables to be set when using "' . $this->environment . '" environment: ' .
                    implode(', ', $missingVars) . '. ' .
                    'Please set these in your .env file or pass them to the constructor.'
                );
            }
        }
    }

    /**
     * Returns the API endpoint URL that the NexgenClient instance will use to communicate with the Nexgen API.
     *
     * The endpoint is determined based on the selected environment and configuration, and is typically the base URL
     * for all API requests made by this client instance.
     *
     * @return string|null The API endpoint URL as a string, or null if it is not set.
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Returns the currently configured environment for this NexgenClient.
     *
     * The environment dictates which API credentials, API endpoints, and other settings will be used.
     * Common values include 'sandbox', 'production', or 'custom'.
     *
     * @return string The environment value as a string.
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Checks if the NexgenClient is running in sandbox mode.
     *
     * Sandbox mode utilizes test credentials and endpoints for safe testing of API requests.
     * 
     * @return bool True if the environment is set to 'sandbox'; false otherwise.
     */
    public function isSandbox()
    {
        return $this->environment === 'sandbox';
    }

    /**
     * Returns the currently configured API key used for authenticating API requests.
     *
     * The key may correspond to the sandbox or production environment based on the configuration.
     * 
     * @return string The API key as a string.
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Returns the API secret used for securing API requests.
     *
     * The secret is paired with the API key and environment.
     *
     * @return string The API secret as a string.
     */
    public function getApiSecret()
    {
        return $this->apiSecret;
    }

    /**
     * Returns the default collection code, if one is configured.
     *
     * The collection code is used to group bills together in the Nexgen platform.
     *
     * @return string|null The collection code, or null if not set.
     */
    public function getCollectionCode()
    {
        return $this->collectionCode;
    }

    /**
     * Returns an array of all the important configuration values for this client instance.
     *
     * This includes API credentials, environment, endpoint URL, collection code,
     * callback URL, and redirect URL. Useful for debugging or for consumers of this class.
     *
     * @return array An associative array of client configuration values.
     */
    public function getConfig()
    {
        return [
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
            'environment' => $this->environment,
            'endpoint' => $this->endpoint,
            'collection_code' => $this->collectionCode,
            'callback_url' => $this->callbackUrl,
            'redirect_url' => $this->redirectUrl,
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

    private function makePutRequest(String $urlPath, array $data = [], array $params = [])
    {
        $url = rtrim($this->endpoint, '/') . '/' . ltrim($urlPath, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params) . '&ApiSecret=' . $this->apiSecret;
        }
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'ApiKey' => $this->apiKey,
        ])->put($url, $data);

        return new NexgenResponse($response->successful(), $response->json());
    }


    /**
     * Create a new collection by sending a request to the Nexgen API.
     *
     * A Collection in Nexgen is a logical grouping of related bills. Creating collections helps in organizing and managing different types
     * of payments. For example, you can create a "Service" collection for bills related to service charges. Every bill must be linked with
     * a collection, providing better payment organization, management, and reporting.
     *
     * This method uses the details provided via the NexgenCreateCollection value object and validates the input before
     * making the API call.
     *
     * @param NexgenCreateCollection $createCollection
     *     An object encapsulating the details of the collection to be created, such as the name and description.
     *     - getName(): Returns the name of the new collection.
     *     - getDescription(): Returns the description of the collection.
     *
     *     Name and description validation requirements:
     *     - Must not be empty (required fields).
     *     - Must be at least 5 characters and at most 50 characters in length.
     *     - Must match the pattern /^[A-Za-z0-9- ]+$/ (only alphanumeric characters, spaces, and hyphens).
     *     - Any violation will return a failed NexgenResponse with a descriptive error.
     *
     * Example usage:
     *     $createCollection = new NexgenCreateCollection('Maintenance Fees', 'All billings for maintenance services');
     *     $response = $nexgenClient->createCollection($createCollection);
     *
     * @return NexgenResponse
     *     Returns a NexgenResponse object indicating the success (true/false) and the response data from the Nexgen API,
     *     or a validation error if the request is not suitable.
     */
    public function createCollection(NexgenCreateCollection $createCollection)
    {
        // Get the name and description from the value object
        $name = $createCollection->getName();
        $description = $createCollection->getDescription();

        /**
         * Validate the collection name:
         * - Must not be empty
         * - Length must be between 5 and 50 characters (inclusive)
         * - Must only contain alphanumeric characters (A-Z, a-z, 0-9), hyphens (-), and spaces
         * If validation fails, return a NexgenResponse indicating the validation issue.
         */
        if (empty($name) || strlen($name) < 5 || strlen($name) > 50 || !preg_match('/^[A-Za-z0-9- ]+$/', $name)) {
            return new NexgenResponse(false, [
                'error' => 'Name is required and must be between 5 and 50 characters long, and can only contain letters, digits, hyphens, and spaces.'
            ]);
        }

        /**
         * Validate the collection description:
         * - Must not be empty
         * - Length must be between 5 and 50 characters (inclusive)
         * - Must only contain alphanumeric characters (A-Z, a-z, 0-9), hyphens (-), and spaces
         * If validation fails, return a NexgenResponse indicating the validation issue.
         */
        if (empty($description) || strlen($description) < 5 || strlen($description) > 50 || !preg_match('/^[A-Za-z0-9- ]+$/', $description)) {
            return new NexgenResponse(false, [
                'error' => 'Description is required and must be between 5 and 50 characters long, and can only contain letters, digits, hyphens, and spaces.'
            ]);
        }

        /**
         * Compose and send the request to create the collection.
         * - fieldName: Name of the collection (validated above)
         * - fieldDescription: Description of the collection (validated above)
         * - fieldStatus: Set to 'active' by default so the collection is enabled immediately after creation
         *
         * Returns the NexgenResponse from the API.
         */
        return $this->makePostRequest('api/v1/collection/create', [
            'fieldName' => $name,
            'fieldDescription' => $description,
            'fieldStatus' => 'active',
        ]);
    }

    /**
     * Retrieve a list of all collections associated with the current Nexgen account.
     *
     * This method calls the Nexgen API's "Get Collection List" endpoint, which returns an array of
     * all collection objects created under your account. Each collection represents a group of bills,
     * such as "Subscription Fees," "Rental Payments," or "Utility Bills", and helps you categorize
     * multiple transactions for easier management.
     *
     * The response contains an array of collection details, where each collection has:
     *   - code:        The unique identifier string for the collection (e.g., "STGCW9VA0045").
     *   - name:        The display name of the collection (e.g., "Test 1").
     *   - description: A textual description of the collection (e.g., "Test 1").
     *   - status:      The status of the collection ("active" or "inactive").
     *
     * Sample response data structure:
     * [
     *     {
     *         "code": "STGCW9VA0045",
     *         "name": "Test 1",
     *         "description": "Test 1",
     *         "status": "active"
     *     },
     *     {
     *         "code": "STGCWDLA0047",
     *         "name": "test name",
     *         "description": "test Description",
     *         "status": "active"
     *     },
     *     // ...
     * ]
     *
     * Usage Example:
     *     $response = $nexgenClient->getCollectionList();
     *     if ($response->isSuccessful()) {
     *         $collections = $response->getData(); // array of collections as shown above
     *     } else {
     *         // Handle error: $error = $response->getError();
     *     }
     *
     * @return NexgenResponse Returns a NexgenResponse containing an array of all
     *                       collection details as described above, or error details if the request fails.
     */
    public function getCollectionList()
    {
        // Send a GET request to the Nexgen API endpoint for listing all collections tied to this account.
        // No additional parameters are required; the method relies on credentials initialized in the client.
        // The endpoint responds with an array of collection objects (see sample structure in the docblock above).
        return $this->makeGetRequest('api/v1/collection/get/list');
    }

    /**
     * Retrieve detailed data for a specific collection from the Nexgen API.
     *
     * This method calls the "Get Collection Data" endpoint to return all data associated with a particular collection. 
     * A collection is a logical grouping of bills under a common category, such as "Service Fees" or "Membership Dues."
     * You can specify a collection code directly, or the method will use the default code from configuration if none is given.
     *
     * The API responds with an object describing the requested collection. This includes the unique collection code, 
     * the collection's name, an optional description, and the current status ("active" or "inactive").
     *
     * Example response data structure:
     *
     * {
     *     "code": "STGCOYEA0061",
     *     "name": "WP Test",
     *     "description": "wp test",
     *     "status": "active"
     * }
     *
     * Usage example:
     *     $response = $nexgenClient->getCollection('STGCOYEA0061');
     *     if ($response->isSuccessful()) {
     *         $collection = $response->getData(); // Returns the collection as described above
     *     } else {
     *         // Handle error, get details via $response->getError()
     *     }
     *
     * @param string|null $collectionCode The collection code to query. If null, uses the default from configuration.
     * @return NexgenResponse NexgenResponse containing the collection data or error details if the request fails.
     */
    public function getCollection(?string $collectionCode = null)
    {
        if (empty($collectionCode)) {
            $collectionCode = $this->collectionCode;
        }

        return $this->makeGetRequest('api/v1/collection/get/data/' . $collectionCode);
    }



    /**
     * Retrieve the detailed data for a specific collection along with all bills associated with that collection.
     *
     * This method sends a GET request to the Nexgen API "Get Collection Data with Billing List" endpoint.
     * You can use this to obtain both collection metadata and a list of all billings under that collection.
     *
     * Full Example: Retrieving billing data for a collection
     *
     * Example usage:
     *   $client = new NexgenClient('sandbox_dummy_api_key', 'sandbox_dummy_api_secret', 'sandbox');
     *   // 'STGCOYEA0061' is the fake collection code used for the demonstration
     *   $response = $client->getCollectionDataBilling('STGCOYEA0061');
     *   if ($response->isSuccessful()) {
     *       $data = $response->getData();
     *       print_r($data);
     *   } else {
     *       // Handle errors: $response->getError()
     *   }
     *
     * Example response:
     * {
     *   "code": "STGCOYEA0061",
     *   "name": "WP Test",
     *   "description": "wp test",
     *   "status": "active",
     *   "bill_list": [
     *     {
     *       "code": "STGBFEU251124AGIU2",
     *       "status": "paid",
     *       "amount": "11.00",
     *       "payment_description": "Nexgen Testing Payment",
     *       "due_date": "25-11-2025 13:40:00",
     *       "payer_name": "ASDKJH SDJNCDS",
     *       "payer_email": "dev-email@wpengine.local",
     *       "payer_phone": "0126442616",
     *       "external_reference_label_1": "Order ID",
     *       "external_reference_value_1": "85",
     *       "external_reference_label_2": null,
     *       "external_reference_value_2": null,
     *       "external_reference_label_3": null,
     *       "external_reference_value_3": null,
     *       "external_reference_label_4": null,
     *       "external_reference_value_4": null,
     *       "redirect_url": "http://localhost:10008/?order_id=85",
     *       "callback_url": "http://localhost:10008/?order_id=85",
     *       "payment_url": "https://dash-nexgen-stg.reliva.com.my/p/b/STGBFEU251124AGIU2/1",
     *       "payment_method_accepted": "DuitNow Online Banking/Wallet",
     *       "payment_method_detail": {
     *         "transaction_date": "24-11-2025 00:00:00",
     *         "transaction_id": "20251124M001051186100001016",
     *         "reference_id": "STGTPWOBW1ACNBP412",
     *         "customer_bank": "ACFBMYK1|PYN Bank A",
     *         "customer_bank_type": "RET"
     *       }
     *     }
     *     // ... more bills 
     *   ]
     * }
     *
     * @param string|null $collectionCode The code of the collection to fetch data for.
     *                                   If null, uses the default collection code specified in configuration.
     * @return NexgenResponse Returns an object containing collection details and the billing list, or error information.
     */
    public function getCollectionDataBilling(?string $collectionCode = null)
    {
        // Use default from config if none provided
        if (empty($collectionCode)) {
            $collectionCode = $this->collectionCode;
        }

        // Send GET request to retrieve collection data and associated billing list
        return $this->makeGetRequest('api/v1/collection/get/data/' . $collectionCode . '/billing');
    }

    /**
     * Switch the status of a collection. The Switch Status for Collection Data API endpoint allows you to change the status of a specific collection. The status could be updated from "active" to "inactive" or vice versa, depending on your needs. This operation helps in managing collections by activating or deactivating them.
     *
     * @param string $collectionCode - The code of the collection.
     * @param string $status - The status to switch to. Must be either 'active' or 'inactive'.
     * @return NexgenResponse
     */

    // public function switchStatusCollectionData(String $collectionCode, NexgenCollectionStatus $status)
    // {

    //     return $this->makePutRequest('api/v1/collection/switch/status/data/' . $collectionCode, [], ['fieldStatus' => $status]);
    // }


    /**
     * Create a new billing (invoice) for a customer using the Nexgen API.
     *
     * This method issues a billing request to the Nexgen system and returns
     * a payment URL for your customer to make payment.
     * You can create billings for products, services, subscriptions, and more.
     *
     * The following customer and billing details are required:
     * - Name (full name of the payer)
     * - Email (payer's email)
     * - Phone (payer's phone)
     * - Amount (how much to charge, as string, decimals allowed)
     * - Payment Description (purpose for billing/invoice)
     * - Optionally: Due date (in 'DD-MM-YYYY HH:mm:ss' format)
     *
     * You may also attach up to four external reference label/value pairs (all optional),
     * which can be used for custom metadata ("label_1" ... "label_4", "value_1" ... "value_4").
     *
     * - fieldExternalReferenceLabel1, fieldExternalReferenceValue1 (Optional)
     * - fieldExternalReferenceLabel2, fieldExternalReferenceValue2 (Optional)
     * - fieldExternalReferenceLabel3, fieldExternalReferenceValue3 (Optional)
     * - fieldExternalReferenceLabel4, fieldExternalReferenceValue4 (Optional)
     *
     * The redirectUrl (where the customer returns after payment) and callbackUrl (where Nexgen
     * sends payment status webhooks) will be set from the billing object or use the client's defaults.
     *
     * @param NexgenCreateBilling $createBilling  Details of the billing to create (see above).
     * @param string|null $collectionCode        Optional: code for the collection or group to assign this billing to.
     * @return NexgenResponse                    API response (see below).
     *
     * --- Example ---
     *
     * $billing = new NexgenCreateBilling(
     *     name: 'Raja',
     *     email: 'jhanarthananraja@gmail.com',
     *     phone: '60126442616',
     *     amount: '2.60',
     *     paymentDescription: 'Membership Fee',
     *     dueDate: '24-02-2026 14:47:00', // Optional
     *     externalReferenceLabel1: 'Student ID',         // Optional
     *     externalReferenceValue1: 'U1234567',           // Optional
     *     externalReferenceLabel2: 'Department',         // Optional
     *     externalReferenceValue2: 'Physics',            // Optional
     *     externalReferenceLabel3: 'Sem',                // Optional
     *     externalReferenceValue3: '8',                  // Optional
     *     externalReferenceLabel4: 'Notes',              // Optional
     *     externalReferenceValue4: 'Early registration', // Optional
     *     redirectUrl: 'https://your-domain.com/thankyou', // Optional
     *     callbackUrl: 'https://webhook.site/d6637e28-ff2c-49b3-8be3-e38726fde500' // Optional
     * );
     *
     * $response = $client->createBilling($billing);
     *
     * --- Sample (fake) response ---
     *
     * {
     *   "code": "STGBSYZ260223ACTP9",
     *   "status": "unpaid",
     *   "amount": "2.60",
     *   "payment_description": "Membership Fee",
     *   "due_date": "24-02-2026 14:47:00",
     *   "payer_name": "Raja",
     *   "payer_email": "jhanarthananraja@gmail.com",
     *   "payer_phone": "60126442616",
     *   "external_references": {
     *       "label_1": "Student ID",
     *       "value_1": "U1234567",
     *       "label_2": "Department",
     *       "value_2": "Physics",
     *       "label_3": "Sem",
     *       "value_3": "8",
     *       "label_4": "Notes",
     *       "value_4": "Early registration"
     *   },
     *   "redirect_url": "https://your-domain.com/thankyou",
     *   "callback_url": "https://webhook.site/d6637e28-ff2c-49b3-8be3-e38726fde500",
     *   "payment_url": "https://dash-nexgen-stg.reliva.com.my/p/b/STGBSYZ260223ACTP9/1"
     * }
     */
    public function createBilling(
        NexgenCreateBilling $createBilling,
        ?string $collectionCode = null
    ) {
        // Extract all billing and customer fields from the request object.
        $fieldName = $createBilling->getFieldName();
        $fieldEmail = $createBilling->getFieldEmail();
        $fieldPhone = $createBilling->getFieldPhone();
        $fieldAmount = $createBilling->getFieldAmount();
        $fieldPaymentDescription = $createBilling->getFieldPaymentDescription();
        $fieldDueDate = $createBilling->getFieldDueDate();
        $fieldRedirectUrl = $createBilling->getFieldRedirectUrl();
        $fieldCallbackUrl = $createBilling->getFieldCallbackUrl();
        $fieldExternalReferenceLabel1 = $createBilling->getFieldExternalReferenceLabel1();
        $fieldExternalReferenceValue1 = $createBilling->getFieldExternalReferenceValue1();
        $fieldExternalReferenceLabel2 = $createBilling->getFieldExternalReferenceLabel2();
        $fieldExternalReferenceValue2 = $createBilling->getFieldExternalReferenceValue2();
        $fieldExternalReferenceLabel3 = $createBilling->getFieldExternalReferenceLabel3();
        $fieldExternalReferenceValue3 = $createBilling->getFieldExternalReferenceValue3();
        $fieldExternalReferenceLabel4 = $createBilling->getFieldExternalReferenceLabel4();
        $fieldExternalReferenceValue4 = $createBilling->getFieldExternalReferenceValue4();

        // Use default collection code if not provided.
        if (empty($collectionCode)) {
            $collectionCode = $this->getCollectionCode();
        }

        // Use defaults for redirect and callback URLs if not in billing.
        if (empty($fieldRedirectUrl)) {
            $fieldRedirectUrl = $this->redirectUrl;
        }
        if (empty($fieldCallbackUrl)) {
            $fieldCallbackUrl = $this->callbackUrl;
        }

        // Prepare the request payload for Nexgen.
        $billRequest = [
            'fieldName' => $fieldName, // Payer's full name (required)
            'fieldEmail' => $fieldEmail, // Payer's email (required)
            'fieldPhone' => $fieldPhone, // Payer's phone (required)
            'fieldAmount' => $fieldAmount, // Amount as string, use decimal for cents (required)
            'fieldPaymentDescription' => $fieldPaymentDescription, // Invoice or billing description (required)
            'fieldRedirectUrl' => $fieldRedirectUrl, // URL to redirect payer after payment (required)
            'fieldCallbackUrl' => $fieldCallbackUrl, // Webhook/callback upon payment status change (required)
            // Optional fields only included if provided. All 4 external references are optional.
            ...array_filter([
                'fieldDueDate' => $fieldDueDate, // Due date (optional)
                'fieldExternalReferenceLabel1' => $fieldExternalReferenceLabel1, // Optional
                'fieldExternalReferenceValue1' => $fieldExternalReferenceValue1, // Optional
                'fieldExternalReferenceLabel2' => $fieldExternalReferenceLabel2, // Optional
                'fieldExternalReferenceValue2' => $fieldExternalReferenceValue2, // Optional
                'fieldExternalReferenceLabel3' => $fieldExternalReferenceLabel3, // Optional
                'fieldExternalReferenceValue3' => $fieldExternalReferenceValue3, // Optional
                'fieldExternalReferenceLabel4' => $fieldExternalReferenceLabel4, // Optional
                'fieldExternalReferenceValue4' => $fieldExternalReferenceValue4, // Optional
            ], function ($value) {
                return !is_null($value);
            }),
        ];

        // Send the API request to create the billing and return the API response.
        return $this->makePostRequest('api/v1/billing/create/' . $collectionCode, $billRequest);
    }

    /**
     * Retrieve detailed data for a specific billing from the Nexgen API.
     *
     * This method fetches all available details for a single billing ("payment request") using the billing's unique ID and the code of the collection it belongs to.
     * Billing details include amount, status, payer information, due date, payment URLs, references, and payment results.
     *
     * If a specific `$collectionCode` is not provided, the client's configured collection code is used by default.
     *
     * @param string $billingId
     *     The unique identifier of the bill (payment request) you wish to fetch.
     *     This is the code assigned to the bill when it was created (e.g., "STGBFEU251124AGIU2").
     *
     * @param string|null $collectionCode
     *     The collection code grouping the billing. If omitted or null, uses the default from the client configuration.
     *
     * @return NexgenResponse
     *     Returns a NexgenResponse object with the structure:
     *         - isSuccessful(): bool
     *         - getData(): array|null
     *           If successful, the data array contains keys and sample values like:
     *           [
     *               "code" => "STGBFEU251124AGIU2",
     *               "status" => "paid",
     *               "transaction_status" => "completed",
     *               "amount" => "11.00",
     *               "payment_description" => "Nexgen Testing Payment",
     *               "due_date" => "25-11-2025 13:40:00",
     *               "payer_name" => "ASDKJH SDJNCDS",
     *               "payer_email" => "dev-email@wpengine.local",
     *               "payer_phone" => "0126442616",
     *               "external_reference_label_1" => "Order ID",
     *               "external_reference_value_1" => "85",
     *               "external_reference_label_2" => null,
     *               "external_reference_value_2" => null,
     *               "external_reference_label_3" => null,
     *               "external_reference_value_3" => null,
     *               "external_reference_label_4" => null,
     *               "external_reference_value_4" => null,
     *               "redirect_url" => "http://localhost:10008/?order_id=85",
     *               "callback_url" => "http://localhost:10008/?order_id=85",
     *               "payment_url" => "https://dash-nexgen-stg.reliva.com.my/p/b/STGBFEU251124AGIU2/1",
     *               "payment_method_accepted" => "DuitNow Online Banking/Wallet",
     *               "payment_method_detail" => [
     *                   "transaction_date" => "24-11-2025 00:00:00",
     *                   "transaction_id" => "20251124M001051186100001016",
     *                   "reference_id" => "STGTPWOBW1ACNBP412",
     *                   "customer_bank" => "ACFBMYK1|PYN Bank A",
     *                   "customer_bank_type" => "RET",
     *               ]
     *           ]
     *         - If unsuccessful, error details are available from NexgenResponse.
     *
     * Example usage:
     *     $response = $client->getBillingData('STGBFEU251124AGIU2');
     *     if ($response->isSuccessful()) {
     *         $billing = $response->getData();
     *         echo $billing['status']; // e.g., "paid"
     *     } else {
     *         // Handle error
     *     }
     */
    public function getBillingData(string $billingId, ?string $collectionCode = null)
    {
        // If collection code not provided, default to client's configuration value.
        if (empty($collectionCode)) {
            $collectionCode = $this->collectionCode;
        }

        // Build API endpoint path for fetching billing data.
        $endpoint = 'api/v1/billing/get/data/' . $collectionCode . '/' . $billingId;

        // Execute GET request to Nexgen API to retrieve detailed billing information.
        return $this->makeGetRequest($endpoint);
    }
}
