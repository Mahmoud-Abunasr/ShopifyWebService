<?php
require_once ROOT_PATH . "/src/Core/BaseController.php";
require_once ROOT_PATH . "/src/Core/Config.php";
require_once ROOT_PATH . "/src/Core/Request.php";
require_once ROOT_PATH . "/src/Core/Response.php";
require_once ROOT_PATH . "/src/Api/Services/ShopifyService.php";

/**
 * ProductController contains the product actions.
 */
class ProductController extends BaseController
{
    /**
     * Holds the product fields indices of the products CSV file,
     * Used to extract the product data from the csv file
     */
    private const PRODUCTS_CSV_FILE_FIELD_MAP = [
        "title" => 1,
        "body_html" => 2,
        "vendor" => 3,
        "product_type" => 4,
        "tags" => 5,
        "published" => 6
    ];
    /**
     * Holds the product options fields indices of the products CSV file
     * Used to extract the product options data from the csv file
     */
    private const PRODUCTS_CSV_FILE_OPTIONS_FIELD_MAP = [
        [
            "name" => 7,
            "value" => 8
        ],
        [
            "name" => 9,
            "value" => 10
        ],
        [
            "name" => 11,
            "value" => 12
        ]
    ];
    /**
     * Holds the product image fields indices of the products CSV file
     * Used to extract the product images data from the csv file
     */
    private const PRODUCTS_CSV_FILE_IMAGES_FIELD_MAP = [24];

    /**
     * Receive products csv file, read it and extract products data and send it to shopify
     *
     * @method POST
     * @param  file  $products_csv_file
     * @return Response object
     */
    public function importProducts()
    {
        $response = new Response();
        try {
            $csvFileInfo = Request::getRequest()->getFile("products_csv_file");
            if ($csvFileInfo["type"] != "text/csv") {
                $response->status = false;
                array_push($response->messages, "Invalid file, only csv files are supported");
            } else {
                $filePath = $csvFileInfo["tmp_name"];
                $productList = $this->getProductsFromCsvFile($filePath);
                $shopifyService = new ShopifyService();
                $addedProductsCount = 0;

                foreach ($productList as $product) {
                    $addProductResponse = $shopifyService->addProduct(["product" => $product]);
                    $error = null;
                    if (!$addProductResponse) {
                        $error = "no response received from shopify";
                    } else if (array_key_exists("errors", $addProductResponse)) {
                        $error = json_encode($addProductResponse["errors"]);
                    }
                    if ($error) {
                        array_push($response->messages, sprintf(
                            "Failed to add product: %s, error: %s",
                            $product["title"],
                            $error
                        ));
                    } else {
                        $addedProductsCount++;
                    }
                }

                $response->status = true;
                $response->data = ["addedProductsCount" => $addedProductsCount];
                array_push($response->messages, "Products have been imported successfully");
            }
        } catch (Exception $ex) {
            $response->status = false;
            array_push($response->messages, "Failed to import products");
            error_log("Failed to import products, error: " . $ex->getMessage());
        }

        $this->jsonResponse($response);
    }

    /**
     * Get all products from shopify
     *
     * @method GET
     * @return Response object
     */
    public function getProducts()
    {
        $response = new Response();

        try {
            $shopifyService = new ShopifyService();
            $shopifyProducts = $shopifyService->getProducts();
            $modifiedProductList = $this->removeNullValuesFromProductList($shopifyProducts["products"]);
            $response->status = true;
            $response->data = ["products" => $modifiedProductList];
            array_push($response->messages, "Products have been fetched successfully");
        } catch (Exception $ex) {
            $response->status = false;
            array_push($response->messages, "Failed to get products");
            error_log("Failed to get products, error: " . $ex->getMessage());
        }

        $this->jsonResponse($response);
    }

    /**
     * Update all the products variants quantity
     *
     * @method GET
     * @return Response object
     */
    public function updateProductsQuantity()
    {
        $response = new Response();

        try {
            $shopifyService = new ShopifyService();
            $shopifyLocations = $shopifyService->getLocations();
            $shopifyProducts = $shopifyService->getProducts();
            $location = $shopifyLocations["locations"][0];
            $quantity = 50;
            $updatedVariantsCount = 0;
            foreach ($shopifyProducts["products"] as $product) {
                foreach ($product["variants"] as $variant) {
                    $updateInventoryResponse = $shopifyService->updateInventory([
                        "location_id" => $location["id"],
                        "inventory_item_id" => $variant["inventory_item_id"],
                        "available_adjustment" => $quantity
                    ]);

                    $error = null;
                    if (!$updateInventoryResponse) {
                        $error = "no response received from shopify";
                    } else if (array_key_exists("errors", $updateInventoryResponse)) {
                        $error = json_encode($updateInventoryResponse["errors"]);
                    }
                    if ($error) {
                        array_push($response->messages, sprintf(
                            "Failed to update variant, id: %s, product_id: %s, error: %s",
                            $variant["inventory_item_id"],
                            $product["id"],
                            $error
                        ));
                    } else {
                        $updatedVariantsCount++;
                    }
                }
            }

            $response->status = true;
            $response->data = ["updatedVariantsCount" => $updatedVariantsCount];
            array_push($response->messages, "Variants have been updated successfully");
        } catch (Exception $ex) {
            $response->status = false;
            array_push($response->messages, "Failed to update variants");
            error_log("Failed to update variants, error: " . $ex->getMessage());
        }

        $this->jsonResponse($response);
    }

    /**
     * Read the products data from csv file
     *
     * @param $filePath: products csv file path
     * @return array
     */
    private function getProductsFromCsvFile($filePath)
    {
        $productList = [];
        $csvFile = fopen($filePath, 'r');
        $csvHeader = fgetcsv($csvFile);
        while (($product = fgetcsv($csvFile)) !== false) {
            $shopifyProduct = $this->buildShopifyProductDataObject($product);
            array_push($productList, $shopifyProduct);
        }
        fclose($csvFile);
        return $productList;
    }

    /**
     * Convert the csv product data array to the shopify product json structure
     *
     * @param $productCsvData
     * @return array
     */
    private function buildShopifyProductDataObject($productCsvData)
    {
        $shopifyProductObject = [];
        $options = [];
        $images = [];

        foreach (self::PRODUCTS_CSV_FILE_FIELD_MAP as $key => $value) {
            $fieldValue = $productCsvData[$value];
            if (!$fieldValue)
                continue;
            $shopifyProductObject[$key] = $fieldValue;
        }

        foreach (self::PRODUCTS_CSV_FILE_OPTIONS_FIELD_MAP as $option) {
            $optionName = $productCsvData[$option["name"]];
            $optionValue = $productCsvData[$option["value"]];
            if (!$optionName || !$optionValue)
                continue;
            array_push($options, [
                "name" => $optionName,
                "values" => [
                    $optionValue
                ]
            ]);
        }

        foreach (self::PRODUCTS_CSV_FILE_IMAGES_FIELD_MAP as $image) {
            $imageValue = $productCsvData[$image];
            if (!$imageValue)
                continue;
            array_push($images, [
                "src" => $imageValue
            ]);
        }

        if (sizeof($option) > 0)
            $shopifyProductObject["options"] = $options;

        if (sizeof($images) > 0)
            $shopifyProductObject["images"] = $images;

        return $shopifyProductObject;
    }

    /**
     * Remove the keys of null values from the product data array
     *
     * @param $productList
     * @return array
     */
    private function removeNullValuesFromProductList($productList)
    {
        $resultProductList = [];
        foreach ($productList as $product) {
            $removeNullValuesResult = $this->removeNullValuesFromArray($product);
            $hasNullValues = $removeNullValuesResult["hasNullValue"];
            $resultProduct = $removeNullValuesResult["result"];
            if ($hasNullValues)
                $resultProduct["title"] = $resultProduct["title"] . " nullable";
            array_push($resultProductList, $resultProduct);
        }

        return $resultProductList;
    }

    /**
     * Recursion function to remove the keys of null values from the nested arrays
     *
     * @param array $array
     * @return array
     */
    private function removeNullValuesFromArray($array)
    {
        $hasNullValue = false;
        $resultArray = [];

        foreach ($array as $key => $value) {
            if ($value == null || $value == "" || $value == "N/A") {
                $hasNullValue = true;
            } else {
                if (is_array($value) && array() !== $array) {
                    $removeNullValuesResult = $this->removeNullValuesFromArray($value);
                    if (!$hasNullValue)
                        $hasNullValue = $removeNullValuesResult["hasNullValue"];
                    $resultArray[$key] = $removeNullValuesResult["result"];
                } else {
                    $resultArray[$key] = $value;
                }
            }
        }

        return ["hasNullValue" => $hasNullValue, "result" => $resultArray];
    }
}
