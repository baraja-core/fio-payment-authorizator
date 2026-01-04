<div align='center'>
  <picture>
    <source media='(prefers-color-scheme: dark)' srcset='https://cdn.brj.app/images/brj-logo/logo-regular.png'>
    <img src='https://cdn.brj.app/images/brj-logo/logo-dark.png' alt='BRJ logo'>
  </picture>
  <br>
  <a href="https://brj.app">BRJ organisation</a>
</div>
<hr>

# Fio Bank Payment Authorizator

![Integrity check](https://github.com/baraja-core/fio-payment-authorizator/workflows/Integrity%20check/badge.svg)

A PHP library for seamless integration with Fio Bank's API, enabling automatic retrieval of bank transactions and authorization of orders based on variable symbols. Perfect for e-commerce platforms, billing systems, and any application requiring automated payment verification.

## :sparkles: Key Features

- **Automatic Transaction Retrieval** - Fetches transactions from the last month via Fio Bank's REST API
- **Variable Symbol Matching** - Authorizes orders by matching variable symbols in transactions
- **Smart Symbol Detection** - Searches for variable symbols in message fields when not found in the dedicated field
- **Built-in Caching** - Optional Nette Cache integration to reduce API calls (15-minute cache expiration)
- **CSV Parsing** - Efficiently parses Fio Bank's CSV response format
- **Extensible Architecture** - Implements `BaseAuthorizator` interface for consistent behavior across different bank integrations
- **Type-Safe** - Fully typed PHP 8.1+ codebase with strict types

## :building_construction: Architecture

The library follows a clean, layered architecture with clear separation of concerns:

```
┌─────────────────────────────────────────────────────────────────────┐
│                        Your Application                             │
└─────────────────────────────────────────────────────────────────────┘
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    FioPaymentAuthorizator                           │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  • Manages API communication                                 │   │
│  │  • Handles caching layer                                     │   │
│  │  • Provides authOrders() method for bulk authorization       │   │
│  └─────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────┘
                                   │
                    ┌──────────────┴──────────────┐
                    ▼                              ▼
┌───────────────────────────────┐  ┌───────────────────────────────┐
│      TransactionResult        │  │      FioPaymentException      │
│  ┌─────────────────────────┐ │  │  ┌─────────────────────────┐  │
│  │ • Account metadata      │ │  │  │ • API errors            │  │
│  │ • IBAN, BIC             │ │  │  │ • Empty responses       │  │
│  │ • Balance information   │ │  │  │ • Service unavailable   │  │
│  │ • Date range            │ │  │  └─────────────────────────┘  │
│  │ • Transaction list      │ │  └───────────────────────────────┘
│  └─────────────────────────┘ │
└───────────────────────────────┘
                │
                ▼
┌───────────────────────────────┐
│         Transaction           │
│  ┌─────────────────────────┐ │
│  │ • Transaction ID        │ │
│  │ • Date, Price, Currency │ │
│  │ • Variable/Constant/    │ │
│  │   Specific symbols      │ │
│  │ • Account details       │ │
│  │ • Messages & comments   │ │
│  └─────────────────────────┘ │
└───────────────────────────────┘
```

## :jigsaw: Components

### FioPaymentAuthorizator

The main entry point of the library. Extends `BaseAuthorizator` from the `baraja-core/bank-transaction-authorizator` package.

**Responsibilities:**
- Constructs API URLs with the provided private key
- Downloads transaction data from Fio Bank's REST API
- Manages static and persistent caching
- Normalizes and validates API responses
- Provides the `authOrders()` method for bulk authorization (inherited from BaseAuthorizator)

### TransactionResult

A value object representing the complete API response, containing:

| Property | Type | Description |
|----------|------|-------------|
| `accountId` | `int` | Bank account number |
| `bankId` | `int` | Bank identifier (routing number) |
| `currency` | `string` | Account currency (e.g., CZK, EUR) |
| `iban` | `string` | International Bank Account Number |
| `bic` | `string` | Bank Identifier Code (SWIFT) |
| `openingBalance` | `float` | Balance at the start of the period |
| `closingBalance` | `float` | Balance at the end of the period |
| `dateStart` | `DateTimeInterface` | Start of the transaction period |
| `dateEnd` | `DateTimeInterface` | End of the transaction period |
| `idFrom` | `int` | First transaction ID in the response |
| `idTo` | `int` | Last transaction ID in the response |
| `transactions` | `Transaction[]` | Array of transaction objects |

### Transaction

Represents a single bank transaction with comprehensive details:

| Property | Type | Description |
|----------|------|-------------|
| `id` | `int` | Unique transaction identifier |
| `date` | `DateTimeInterface` | Transaction date |
| `price` | `float` | Transaction amount (positive for incoming, negative for outgoing) |
| `currency` | `string` | Transaction currency |
| `toAccount` | `?string` | Counterparty account number |
| `toAccountName` | `?string` | Counterparty account name |
| `toBankCode` | `?int` | Counterparty bank code |
| `toBankName` | `?string` | Counterparty bank name |
| `constantSymbol` | `?int` | Constant symbol (KS) |
| `variableSymbol` | `?int` | Variable symbol (VS) |
| `specificSymbol` | `?int` | Specific symbol (SS) |
| `userNotice` | `?string` | User notice/reference |
| `toMessage` | `?string` | Message for recipient |
| `type` | `?string` | Transaction type |
| `sender` | `?string` | Sender identification |
| `message` | `?string` | General message |
| `comment` | `?string` | Additional comment |
| `bic` | `?string` | Counterparty BIC/SWIFT code |
| `idTransaction` | `?int` | Alternative transaction ID |

**Key Methods:**
- `isVariableSymbol(int $vs): bool` - Checks if transaction matches a variable symbol
- `isContainVariableSymbolInMessage(int|string $vs): bool` - Searches for VS in message fields

### FioPaymentException

Custom exception class for handling Fio-specific errors:
- Empty API responses
- Invalid API keys
- Service unavailability
- Malformed data

## :package: Installation

It's best to use [Composer](https://getcomposer.org) for installation, and you can also find the package on
[Packagist](https://packagist.org/packages/baraja-core/fio-payment-authorizator) and
[GitHub](https://github.com/baraja-core/fio-payment-authorizator).

To install, simply use the command:

```shell
$ composer require baraja-core/fio-payment-authorizator
```

You can use the package manually by creating an instance of the internal classes, or register a DIC extension to link the services directly to the Nette Framework.

### Requirements

- PHP 8.1 or higher
- `baraja-core/bank-transaction-authorizator` ^2.0 (installed automatically)
- Optional: `nette/caching` for persistent caching support

## :key: Obtaining API Key

1. Log in to your [Fio Bank Internet Banking](https://ib.fio.cz/)
2. Navigate to **Settings** > **API**
3. Generate a new API token with read permissions
4. Copy the token - this is your `privateKey`

> **Security Note:** Never commit your API key to version control. Use environment variables or a secure configuration management system.

## :rocket: Basic Usage

### Standalone Usage

```php
use Baraja\FioPaymentAuthorizator\FioPaymentAuthorizator;
use Baraja\FioPaymentAuthorizator\Transaction;

// Create instance with your API key
$fio = new FioPaymentAuthorizator('your-private-api-key');

// Get all transactions from the last month
$result = $fio->process();

// Access account information
echo 'Account: ' . $result->getIban() . PHP_EOL;
echo 'Currency: ' . $result->getCurrency() . PHP_EOL;
echo 'Opening Balance: ' . $result->getOpeningBalance() . PHP_EOL;
echo 'Closing Balance: ' . $result->getClosingBalance() . PHP_EOL;

// Iterate through transactions
foreach ($result->getTransactions() as $transaction) {
    echo sprintf(
        "[%s] %s %s - VS: %s\n",
        $transaction->getDate()->format('Y-m-d'),
        $transaction->getPrice(),
        $transaction->getCurrency(),
        $transaction->getVariableSymbol() ?? 'N/A'
    );
}
```

### Order Authorization

The library can automatically authorize orders based on variable symbols:

```php
// Array of variable symbols from unpaid orders
$unauthorizedVariables = [123456, 789012, 345678];

// Authorize orders - the callback is called for each matched transaction
$fio->authOrders(
    $unauthorizedVariables,
    function (Transaction $transaction): void {
        // Mark order as paid in your system
        $orderId = $transaction->getVariableSymbol();

        // Example: Update order status in database
        $this->orderRepository->markAsPaid($orderId, [
            'transactionId' => $transaction->getId(),
            'amount' => $transaction->getPrice(),
            'paidAt' => $transaction->getDate(),
        ]);
    }
);
```

### Using with Nette Framework

Register the service in your `config.neon`:

```yaml
parameters:
    fio:
        privateKey: %env.FIO_API_KEY%

services:
    - Baraja\FioPaymentAuthorizator\FioPaymentAuthorizator(%fio.privateKey%)
```

Then inject it in your presenter or service:

```php
final class PaymentPresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private FioPaymentAuthorizator $fio,
    ) {
    }

    public function actionAuthorize(): void
    {
        $result = $this->fio->process();
        // Process transactions...
    }
}
```

### Enabling Caching

To reduce API calls and improve performance, enable caching by providing a Nette Cache storage:

```yaml
services:
    - Baraja\FioPaymentAuthorizator\FioPaymentAuthorizator(
        privateKey: %fio.privateKey%
        storage: @cacheStorage
    )
```

Or manually:

```php
use Nette\Caching\Storages\FileStorage;

$storage = new FileStorage('/path/to/cache');
$fio = new FioPaymentAuthorizator('your-private-api-key', $storage);
```

The cache expires after 15 minutes and is tagged with `fio`, `bank`, and `payment` for easy invalidation.

## :mag: Advanced Usage

### Checking Variable Symbol in Messages

Some payment systems (especially international transfers) may include the variable symbol in the message field instead of the dedicated VS field. The library handles this automatically:

```php
$transaction = $result->getTransactions()[0];

// This checks both the VS field AND message fields
if ($transaction->isVariableSymbol(123456)) {
    echo 'Variable symbol found!';
}

// Check only in message fields
if ($transaction->isContainVariableSymbolInMessage(123456)) {
    echo 'Found in message content';
}
```

### Filtering Transactions

```php
$transactions = $fio->getTransactions();

// Filter incoming payments only
$incoming = array_filter(
    $transactions,
    fn(Transaction $t) => $t->getPrice() > 0
);

// Filter by date range
$startDate = new DateTime('2024-01-01');
$filtered = array_filter(
    $transactions,
    fn(Transaction $t) => $t->getDate() >= $startDate
);

// Filter by specific bank
$fioBankCode = 2010;
$fioTransfers = array_filter(
    $transactions,
    fn(Transaction $t) => $t->getToBankCode() === $fioBankCode
);
```

### Error Handling

```php
use Baraja\FioPaymentAuthorizator\FioPaymentException;

try {
    $result = $fio->process();
} catch (FioPaymentException $e) {
    // Handle Fio-specific errors
    if (str_contains($e->getMessage(), 'API key')) {
        // Invalid API key
        $this->logger->error('Invalid Fio API key');
    } elseif (str_contains($e->getMessage(), 'down')) {
        // Service temporarily unavailable
        $this->logger->warning('Fio API temporarily unavailable');
    }
}
```

## :warning: API Rate Limits

Fio Bank API has rate limiting in place:
- **30 seconds** minimum interval between requests
- The built-in caching helps respect this limit
- Consider implementing a queue system for high-traffic applications

## :books: Related Packages

- [`baraja-core/bank-transaction-authorizator`](https://github.com/baraja-core/bank-transaction-authorizator) - Base package providing the `BaseAuthorizator` interface
- Works with other bank authorizators following the same interface

## :busts_in_silhouette: Author

**Jan Barasek** - [https://baraja.cz](https://baraja.cz)

## :page_facing_up: License

`baraja-core/fio-payment-authorizator` is licensed under the MIT license. See the [LICENSE](https://github.com/baraja-core/fio-payment-authorizator/blob/master/LICENSE) file for more details.
