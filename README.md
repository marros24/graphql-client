# WpjShop GraphQL Client

## Installation
```
composer require wpjshop/graphql-client
```

## Documentation

GraphQL API documentation [here](https://graphql-docs.wpjshop.cz/)

### Instantiate a client
```php
<?php
$client = new \WpjShop\GraphQL\Client('https://bagosport.cz/admin/graphql', 'aa2589b5cbddb845e6dc663a8636740e');
```

### Using the GraphQL Client
Simple use:
```php
<?php
// Returns product data by ID.
$result = $client->product->get(1);
// Returns products collection.
$result = $client->product->list();
// Returns an array with result status and created product.
$result = $client->product->create(['title' => 'New product']);
// Returns an array with result status and updated product.
$result = $client->product->update(1, ['price' => ['priceWithoutVat' => 100]]);

// Returns parameter data by ID.
$result = $client->parameter->get(1);
// ...
```

Usage with custom field selection:
```php
<?php
// Returns an array with data defined by `setSelection` method
$result = $client->product
    ->setSelection(
        [
            'id',
            'variations' => [
                'id',
                'price' => [
                    'withVat',
                    'vat',
                    'currency' => [
                        'code',
                    ],
                ],
            ],
        ]
)->get(1);
```

## GraphQL Client Reference


Each service has at least 4 basic methods:
```php
<?php
$client->{service}->get(int $id); // gets item by ID
$client->{service}->list(int $offset = 0, int $limit = 100); // items collection
$client->{service}->create(array $data); // create new item
$client->{service}->update(int $id, array $data); // updates item by ID
```

Services may have additional methods that are specific to them. For example update of product parameter:
```php
<?php
$client->product->updateParameter(int $productId, int $parameterId, array $values, bool $append = false);
```

You can build custom query using [this library](https://github.com/mghoneimy/php-graphql-client]) that is a part of this client.

```php
<?php
$client->runQuery($query, bool $resultsAsArray = false, array $variables = []);
$client->runRawQuery(string $queryString, $resultsAsArray = false, array $variables = []);
```