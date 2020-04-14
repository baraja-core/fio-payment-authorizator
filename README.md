Fio bank payment authorizator
=============================

![Integrity check](https://github.com/baraja-core/fio-payment-authorizator/workflows/Integrity%20check/badge.svg)

Simple package for search payments in your bank account by API and authorize new orders.

Install
-------

By Composer:

```shell
composer require baraja-core/fio-payment-authorizator
```

And create service by Neon:

```yaml
services:
    - FioPaymentAuthorizator(%fio.privateKey%)

parameters:
    fio:
        privateKey: xxx
```

Use
---

In presenter use very simply:

```php
/** @var FioPaymentAuthorizator $fio **/
$fio = $this->context->getByType(FioPaymentAuthorizator::class);

// Or simply:

$fio = new FioPaymentAuthorizator('private-key');

dump($fio->process()); // Get last month bank data as TransactionResult.

// Check account and authorize new orders

$unauthorizedVariables = [];

$fio->authOrders(
    $unauthorizedVariables,
    function (Transaction $transaction): void {
        // Do something...
    }
);
```
