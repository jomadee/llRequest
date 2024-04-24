## Package Documentation: llRequest

### Overview
`llRequest` is a versatile PHP library for making SOAP and HTTP API requests. It simplifies the process of interacting with remote services by providing an easy-to-use interface for sending requests and handling responses.

### Installation
To use `llRequest`, you can include it in your project via Composer. Add the following line to your `composer.json` file:
```json
"require": {
    "ll/request": "^1.0"
}
```
Then run `composer install` to install the package.

### Examples

#### SOAP Request
```php
use ll\Request\Request;

// Set SOAP headers
$headers = [
    'login' => 'xxxx',
    'password' => 'xxxx',
    'trace' => 1,
    'exception' => 0
];

// Make a SOAP request
Request::soap('http://xxxxx/service.svc?wsdl')
    ->headers($headers)
    ->ProductGet(['idProduct' => 4])
    ->done(function ($response) {
        echo '<pre>' , __LINE__ , ": " , print_r($response, true) , '</pre>';
    });
```

#### HTTP/API GET Request
```php
use ll\Request\Request;

// Make an HTTP GET request
Request::http('http://xxxxx.xxx/xxx/xxx')
    ->headers([
        'Content-Type' => 'application/json'
    ])
    ->get()
    ->done(function ($response) {
        echo '<pre>' , __LINE__ , ": " , print_r($response->meta(), true) , '</pre>';
        $decodedResponse = json_decode($response);
        echo '<pre>' , __LINE__ , ": " , print_r($decodedResponse, true) , '</pre>';
    });
```

#### HTTP/API POST Request
```php
use ll\Request\Request;

// Make an HTTP POST request
Request::http('http://xxxxx.xxx/xxx/xxx')
    ->headers([
        'Content-Type' => 'application/json'
    ])
    ->post(json_encode(['name' => 'tintim']))
    ->done(function ($response) {
        echo '<pre>' , __LINE__ , ": " , print_r($response->meta(), true) , '</pre>';
        $decodedResponse = json_decode($response);
        echo '<pre>' , __LINE__ , ": " , print_r($decodedResponse, true) , '</pre>';
    });
```

### Usage
- **SOAP Request:** Use `Request::soap($wsdlUrl)` to initiate a SOAP request. You can set headers with `->headers($headersArray)` and make method calls like `->MethodName($paramsArray)`.
- **HTTP/API Request:** Use `Request::http($url)` to initiate an HTTP request. Set headers with `->headers($headersArray)` and specify the request type (`->get()`, `->post($data)`, etc.). Handle the response using the `->done()` method.

### Notes
- Ensure that the required dependencies are installed via Composer (`ll/request`).
- Replace placeholder values (`xxxx`, `http://xxxxx.xxx/xxx/xxx`, etc.) with actual URLs, credentials, and data relevant to your use case.
- Handle response data within the `->done()` callback function, processing it as needed for your application.

For more information and detailed usage instructions, refer to the official `llRequest` documentation or source repository.