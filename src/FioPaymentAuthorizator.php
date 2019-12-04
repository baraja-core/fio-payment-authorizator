<?php

declare(strict_types=1);

namespace Baraja;


use Nette\Caching\Cache;
use Nette\Caching\IStorage;

class FioPaymentAuthorizator
{

	/**
	 * @var string
	 */
	private $privateKey;

	/**
	 * @var Cache|null
	 */
	private $cache;

	/**
	 * @param string $privateKey
	 * @param IStorage|null $IStorage
	 */
	public function __construct(string $privateKey, ?IStorage $IStorage = null)
	{
		$this->privateKey = $privateKey;
		$this->cache = $IStorage === null ? null : new Cache($IStorage, 'fio-payment-authorizator');
	}

	/**
	 * @return TransactionResult
	 */
	public function process(): TransactionResult
	{
		return new TransactionResult($this->loadData());
	}

	/**
	 * Check list of unauthorized variable symbols, compare with read bank account list and authorize paid records.
	 * For valid transaction user record must match price exactly or in given tolerance (default is +/- 1 CZK).
	 *
	 * Example:
	 *    [19010017 => 250]
	 *    Variable: 19010017
	 *    Price: 250 CZK, accept <249, 251>
	 *
	 * @param int[]|float[] $unauthorizedVariables -> key is variable, value is expected price.
	 * @param callable(Transaction $transaction) $callback
	 * @param string $currency
	 * @param float $tolerance
	 */
	public function authOrders(array $unauthorizedVariables, callable $callback, string $currency = 'CZK', float $tolerance = 1): void
	{
		$transactions = $this->process()->getTransactions();
		$variables = array_keys($unauthorizedVariables);

		$process = static function (float $price, Transaction $transaction) use ($callback, $currency, $tolerance): void {
			if ($transaction->getCurrency() !== $currency) { // Fix different currencies
				$price = $this->convertCurrency($transaction->getCurrency(), $currency, $price);
			}
			if ($transaction->getPrice() - $price >= -$tolerance) { // Is price in tolerance?
				$callback($transaction);
			}
		};

		foreach ($transactions as $transaction) {
			$variable = null;
			foreach ($variables as $currentVariable) {
				if ($transaction->isVariableSymbol((int) $currentVariable) === true) {
					$variable = (int) $currentVariable;
					break;
				}
			}

			if ($variable !== null) {
				$process((float) $unauthorizedVariables[$variable], $transaction);
			}
		}
	}

	/**
	 * @return string
	 */
	private function loadData(): string
	{
		static $staticCache = [];

		$year = (int) date('Y');
		if (($month = (int) date('m') - 1) === 0) {
			$year--;
			$month = 12;
		}

		$url = 'https://www.fio.cz/ib_api/rest/periods/' . $this->privateKey
			. '/' . $year . '-' . $month . '-01/' . date('Y-m-d')
			. '/transactions.csv';

		if (isset($staticCache[$url]) === true) {
			return $staticCache[$url];
		}

		if ($this->cache !== null && ($cache = $this->cache->load($url)) !== null) {
			$staticCache[$url] = $cache;

			return $cache;
		}

		if (($data = file_get_contents($url)) === false) {
			FioPaymentException::emptyResponse($url);
		}

		$staticCache[$url] = $data;
		if ($this->cache !== null) {
			$this->cache->save($url, $data, [
				Cache::EXPIRE => '15 minutes',
				Cache::TAGS => ['fio', 'bank', 'payment'],
			]);
		}

		return $data;
	}

	/**
	 * @param string $currentCurrency
	 * @param string $expectedCurrency
	 * @param float $price
	 * @return float
	 */
	private function convertCurrency(string $currentCurrency, string $expectedCurrency, float $price): float
	{
		// TODO: Reserved for future use.

		return $price;
	}

}
