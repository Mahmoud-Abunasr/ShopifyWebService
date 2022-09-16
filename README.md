# ShopifyWebService
A project that demonstrates building a web service using pure PHP and interacting with the Shopify API.
</br></br>
### Usage
1- Clone the repository in the server root folder</br>
2- Edit the `config/config.dev.json` file and set your shopify api_key and access_token in the shopify config sesction</br>
</br></br>
### API Endpoints

| Endpoint | Description | Method |URL  |
| ------------- | ------------- | ------------- | ------------- |
| Get all product  | Get all products from shopify |  `GET`  | `http://your-domain/product/getProducts` |
| Update products quantity  | Update all products variants quantity |  `GET`  | `http://your-domain/product/updateProductsQuantity` |
| Import products  | Import products CSV file and add products to shopify |  `POST`  | `http://your-domain/product/importProducts` |

</br></br>
### Testing Using Postman
Use `postman/TaskRequestCollection.postman_collection.json` for testing the API endpoints
