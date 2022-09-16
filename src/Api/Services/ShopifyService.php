<?php

/**
 * ShopifyService is an interface to interact with the shopify admin API.
 */
class ShopifyService
{
    /**
     * Holds the shopify api configuration like the api_key, access_token, etc..
     * @var array
     */
    private $shopifyConfig = null;

    /**
     * Create a new ShopifyService instance.
     * @return void
     */
    public function __construct()
    {
        /**
         * Load the shopify configuration according to the run environment [development|production]
         */
        $config = Config::getConfig();
        $this->shopifyConfig = $config["shopify"];
    }

    /**
     * Send request to the shopify admin api.
     *
     * @param  string  $endpoint: target endpoint, eg: products, locations, etc.
     * @param  string  $method: request method, eg: Request::GET, Request::POST, etc.
     * @param  array  $data: post data object (optional).
     * @return array
     */
    private function sendRequest($endpoint, $method, $data = null)
    {
        $url = sprintf(
            "https://%s:%s@%s.%s/%s",
            $this->shopifyConfig["apiKey"],
            $this->shopifyConfig["apiPassword"],
            $this->shopifyConfig["storeName"],
            $this->shopifyConfig["domain"],
            $endpoint
        );
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($method == Request::POST || $method == Request::PUT) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json'
            ));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $errorMsg = curl_error($ch);
            throw new Exception($errorMsg);
        }
        curl_close($ch);
        return json_decode($response, true);
    }

    /**
     * Get products from the shopify admin api.
     *
     * @return array
     */
    public function getProducts()
    {
        return $this->sendRequest(
            $this->shopifyConfig["apiEndpoints"]["products"],
            Request::GET
        );
    }

    /**
     * Add a product to shopify.
     *
     * @param array $productData
     * @return array
     */
    public function addProduct($productData)
    {
        return $this->sendRequest(
            $this->shopifyConfig["apiEndpoints"]["products"],
            Request::POST,
            $productData
        );
    }

    /**
     * Get locations from the shopify admin api.
     *
     * @return array
     */
    public function getLocations()
    {
        return $this->sendRequest(
            $this->shopifyConfig["apiEndpoints"]["locations"],
            Request::GET
        );
    }

    /**
     * Update product variants.
     *
     * @param array $inventoryData
     * @return array
     */
    public function updateInventory($inventoryData)
    {
        return $this->sendRequest(
            $this->shopifyConfig["apiEndpoints"]["inventory_levels"],
            Request::POST,
            $inventoryData
        );
    }
}
