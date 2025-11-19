<?php

namespace Reliva\Nexgen;

use Illuminate\Support\Facades\Http;
use Reliva\Nexgen\NexgenCreateCollection;
use Reliva\Nexgen\Enum\NexgenCollectionStatus;
use Reliva\Nexgen\NexgenCreateBilling;

class NexgenClient
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
     * The environment (sandbox / production / custom).
     *
     * @var string
     */
    protected $environment;

    /**
     * The collection code.
     *
     * @var string|null
     */
    protected $collectionCode;

    /**
     * The callback URL.
     *
     * @var string|null
     */
    protected $callbackUrl;

    /**
     * The redirect URL.
     *
     * @var string|null
     */
    protected $redirectUrl;

    /**
     * Create a new Nexgen client instance.
     *
     * @param string $apiKey - The API key.
     * @param string $apiSecret - The API secret.
     * @param String $environment - The environment.
     * @param string $collectionCode - The code of the collection.
     * @param string $callbackUrl - The callback URL.
     * @param string $redirectUrl - The redirect URL.
     * 
     * @return void
     * 
     */
    public function __construct(
        ?String $apiKey = null,
        ?String $apiSecret = null,
        ?String $environment = null,
        ?String $collectionCode = null,
        ?String $callbackUrl = null,
        ?String $redirectUrl = null,

    ) {
        $this->apiKey = $apiKey ?? config('nexgen.API_KEY');
        $this->apiSecret = $apiSecret ?? config('nexgen.API_SECRET');
        $this->environment = $environment ?? config('nexgen.ENVIRONMENT');
        $this->collectionCode = $collectionCode ?? config('nexgen.COLLECTION_CODE');
        $this->callbackUrl = $callbackUrl ?? config('nexgen.CALLBACK_URL');
        $this->redirectUrl = $redirectUrl ?? config('nexgen.REDIRECT_URL');

        // Set endpoint based on environment
        // if environment is sandbox, use the sandbox endpoint
        // if environment is production, use the production endpoint
        // if environment is custom, use the custom endpoint
        switch ($this->environment) {
            case 'sandbox':
                $this->endpoint = 'https://dash-nexgen-stg.reliva.com.my';
                break;
            case 'production':
                $this->endpoint = 'https://dash-nexgen.reliva.com.my';
                break;
            case 'custom':
                $this->endpoint = config('nexgen.ENDPOINT');
                break;
            default:
                throw new \Exception('Invalid environment: ' . $this->environment);
        }
    }

    /**
     * Get the API endpoint URL.
     *
     * @return string|null
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Get the current environment.
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Check if the client is in sandbox mode.
     *
     * @return bool
     */
    public function isSandbox()
    {
        return $this->environment === 'sandbox';
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function getApiSecret()
    {
        return $this->apiSecret;
    }

    public function getCollectionCode()
    {
        return $this->collectionCode;
    }

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
     * Create a new collection. A Collection is a group of related bills that helps in organizing and managing different types of payments. For instance, you can create a 'Service' Collection for bills related to service charges payments. Each bill must be associated with a Collection, enabling better organization and easier tracking of payments under specific categories.
     *
     * @param string $name - The name of the collection. Must be between 5 and 50 characters long, and can only contain letters, digits, hyphens, and spaces.
     * @param string $description - The description of the collection. Must be between 5 and 50 characters long, and can only contain letters, digits, hyphens, and spaces.
     * @return NexgenResponse 
     * 
     */
    public function createCollection(NexgenCreateCollection $createCollection)
    {
        $name = $createCollection->getName();
        $description = $createCollection->getDescription();

        // name and description requirements: Be required (it cannot be empty). Have a minimum length of 5 characters and a maximum length of 50 characters. Match the regular expression ^[A-Za-z0-9- ]+$, which allows only letters (A-Z, a-z), digits (0-9), hyphens (-), and spaces, with no other characters allowed.
        if (empty($name) || strlen($name) < 5 || strlen($name) > 50 || !preg_match('/^[A-Za-z0-9- ]+$/', $name)) {
            return new NexgenResponse(false, ['error' => 'Name is required and must be between 5 and 50 characters long, and can only contain letters, digits, hyphens, and spaces.']);
        }
        if (empty($description) || strlen($description) < 5 || strlen($description) > 50 || !preg_match('/^[A-Za-z0-9- ]+$/', $description)) {
            return new NexgenResponse(false, ['error' => 'Description is required and must be between 5 and 50 characters long, and can only contain letters, digits, hyphens, and spaces.']);
        }

        return $this->makePostRequest('api/v1/collection/create', [
            'fieldName' => $name,
            'fieldDescription' => $description,
            'fieldStatus' => 'active',
        ]);
    }

    /**
     * Get the list of collections. The Get Collection List API endpoint allows you to retrieve a list (array) of all the collections associated with your account. Each collection represents a group of bills, such as Subscription Fees, Rental Payments, or Utility Bills, enabling you to manage and categorize multiple transactions effectively.
     *
     * @return NexgenResponse  
     */
    public function getCollectionList()
    {

        return $this->makeGetRequest('api/v1/collection/get/list');
    }

    /**
     * Get the data of a collection. The Get Collection Data API endpoint allows you to retrieve detailed information for a specific collection. A Collection is a group of bills organized under a single category, such as Service Fees. This endpoint provides all relevant data for a specific collection by using its unique identifier.
     *
     * @param string|null $collectionCode - The code of the collection. If not provided, the collection code will be the one set in the configuration.
     * @return NexgenResponse
     */
    public function getCollection(?String $collectionCode = null)
    {

        if (empty($collectionCode)) {
            $collectionCode = $this->collectionCode;
        }

        return $this->makeGetRequest('api/v1/collection/get/data/' . $collectionCode);
    }



    /**
     * Get the billing data of a collection. The Get Collection Data with Billing List API endpoint allows you to retrieve detailed information for a specific collection along with a list of all bills associated with that collection. A Collection groups related bills, such as Service Fees or Subscription Payments, making it easier to manage multiple transactions.
     *
     * @param string|null $collectionCode - The code of the collection. If not provided, the collection code will be the one set in the configuration.
     * @return NexgenResponse
     */
    public function getCollectionDataBilling(?String $collectionCode = null)
    {
        if (empty($collectionCode)) {
            $collectionCode = $this->collectionCode;
        }
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
     * Create a new billing. The Billing allows businesses to create and manage bills for their customers through an API, making it easy to automate invoicing and payment collection. This API is designed to generate bills with detailed information such as amounts, due dates, and customer details. The Billing API is a versatile solution for handling payments for services, subscriptions, product purchases, and more.
     *
     * @param NexgenCreateBilling $createBilling - The billing to create.
     * @param string|null $collectionCode - The code of the collection. If not provided, the collection code will be the one set in the configuration.
     * @return NexgenResponse
     */
    public function createBilling(

        NexgenCreateBilling $createBilling,
        ?String $collectionCode = null

    ) {
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



        if (empty($collectionCode)) {
            $collectionCode = $this->getCollectionCode();
        }

        if (empty($fieldRedirectUrl)) {
            $fieldRedirectUrl = $this->redirectUrl;
        }
        if (empty($fieldCallbackUrl)) {
            $fieldCallbackUrl = $this->callbackUrl;
        }

        $billRequest = [
            'fieldName' => $fieldName,
            'fieldEmail' => $fieldEmail,
            'fieldPhone' => $fieldPhone,
            'fieldAmount' => $fieldAmount,
            'fieldPaymentDescription' => $fieldPaymentDescription,
            'fieldRedirectUrl' => $fieldRedirectUrl,
            'fieldCallbackUrl' => $fieldCallbackUrl,
            ...array_filter([
                'fieldDueDate' => $fieldDueDate,
                'fieldExternalReferenceLabel1' => $fieldExternalReferenceLabel1,
                'fieldExternalReferenceValue1' => $fieldExternalReferenceValue1,
                'fieldExternalReferenceLabel2' => $fieldExternalReferenceLabel2,
                'fieldExternalReferenceValue2' => $fieldExternalReferenceValue2,
                'fieldExternalReferenceLabel3' => $fieldExternalReferenceLabel3,
                'fieldExternalReferenceValue3' => $fieldExternalReferenceValue3,
                'fieldExternalReferenceLabel4' => $fieldExternalReferenceLabel4,
                'fieldExternalReferenceValue4' => $fieldExternalReferenceValue4,
            ], function ($value) {
                return !is_null($value);
            }),
        ];

        return $this->makePostRequest('api/v1/billing/create/' . $collectionCode, $billRequest);
    }

    /**
     * Get the data of a billing. The Get Billing Data API endpoint allows you to retrieve detailed information for a specific billing. A Billing is a payment request created for a customer, containing details like amount, due date, and payment description. This endpoint provides all relevant data for a specific billing by using its unique identifier.
     *
     * @param string $billingId - The id of the billing.
     * @param string|null $collectionCode - The code of the collection. If not provided, the collection code will be the one set in the configuration.
     * @return NexgenResponse
     */
    public function getBillingData(String $billingId, ?String $collectionCode = null)
    {
        if (empty($collectionCode)) {
            $collectionCode = $this->collectionCode;
        }
        return $this->makeGetRequest('api/v1/billing/get/data/' . $collectionCode . '/' . $billingId);
    }
}
