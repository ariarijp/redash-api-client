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

define('REDASH_URL', 'http://localhost:5000/');
define('REDASH_QUERY_API_KEY', 'YOUR_QUERY_API_KEY');
define('REDASH_USER_API_KEY', 'YOUR_USER_API_KEY');
define('REDASH_QUERY_ID', 1);

// Fetch data without refresh option.
// You can fetch data without User API Key.
$client = new RedashApiClient\Client(REDASH_URL);
$client->fetch(REDASH_QUERY_ID, REDASH_QUERY_API_KEY, false, function (array $row, array $columns) {
    $row = array_map(function ($column) use ($row) {
        return $row[$column];
    }, $columns);

    echo implode("\t", $row).PHP_EOL;
});

// Fetch data with refresh option.
// When you want to fetch data with refresh option, You have to use User API Key.
$client = new RedashApiClient\Client(REDASH_URL, REDASH_USER_API_KEY);
$client->fetch(REDASH_QUERY_ID, null, true, function (array $row, array $columns) {
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
