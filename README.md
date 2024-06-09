<div align='center'>
  <picture>
    <source media='(prefers-color-scheme: dark)' srcset='https://cdn.brj.app/images/brj-logo/logo-regular.png'>
    <img src='https://cdn.brj.app/images/brj-logo/logo-dark.png' alt='BRJ logo'>
  </picture>
  <br>
  <a href="https://brj.app">BRJ organisation</a>
</div>
<hr>
Fio bank payment authorizator
=============================

![Integrity check](https://github.com/baraja-core/fio-payment-authorizator/workflows/Integrity%20check/badge.svg)

Simple package for search payments in your bank account by API and authorize new orders.

📦 Installation
---------------

It's best to use [Composer](https://getcomposer.org) for installation, and you can also find the package on
[Packagist](https://packagist.org/packages/baraja-core/fio-payment-authorizator) and
[GitHub](https://github.com/baraja-core/fio-payment-authorizator).

To install, simply use the command:

```
$ composer require baraja-core/fio-payment-authorizator
```

You can use the package manually by creating an instance of the internal classes, or register a DIC extension to link the services directly to the Nette Framework.

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

📄 License
-----------

`baraja-core/fio-payment-authorizator` is licensed under the MIT license. See the [LICENSE](https://github.com/baraja-core/template/blob/master/LICENSE) file for more details.
