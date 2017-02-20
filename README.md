# llRequest

exemplo de uso soap:

```php
 $setHeaders =
            array(
                'login' => 'xxxx',
                'password' => 'xxxx',
                'trace' => 1,
                'exception' => 0
            );

        Request::soap('http://xxxxx/service.svc?wsdl')
            ->headers($setHeaders)
            ->ProductGet(array('idProduct' => 4))
        ->done(function ($r){
              echo '<pre>' , __LINE__ , ": " , print_r($r, true) , '</pre>';
        });
```

exemplo http/api:

```php
        Request::http('http://xxxxx.xxx/xxx/xxx')
            ->headers(array(
                'Content-Type' => 'application/json'
            ))
            ->get()
            ->done(function ($r) {
                echo '<pre>' , __LINE__ , ": " , print_r($r->meta(), true) , '</pre>';
                $r = json_decode($r);
                echo '<pre>' , __LINE__ , ": " , print_r($r, true) , '</pre>';
            });
```
