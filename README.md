# redash-api-client

[re:dash](http://redash.io/) results API client for PHP.

## Installaton

```bash
$ composer require ariarijp/redash-api-client
```

## Usage

```php
<?php

require __DIR__.'/vendor/autoload.php';

define('REDASH_URL', 'http://localhost:9001/');
define('REDASH_API_KEY', 'API_KEY_FOR_QUERY');
define('REDASH_QUERY_ID', 1);

$client = new RedashApiClient\Client(REDASH_URL);
$client->getResults(REDASH_QUERY_ID, REDASH_API_KEY, function ($row, $columns) {
    $row = array_map(function ($column) use ($row) {
        return $row[$column];
    }, $columns);

    echo implode("\t", $row).PHP_EOL;
});
```

## License

MIT

## Author

[ariarijp](https://github.com/ariarijp)
