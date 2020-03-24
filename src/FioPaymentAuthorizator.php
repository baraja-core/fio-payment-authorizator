<?php

declare(strict_types=1);

namespace Baraja\FioPaymentAuthorizator;


use Nette\Caching\Cache;
use Nette\Caching\IStorage;

final class FioPaymentAuthorizator
{

	/** @var string */
	private $privateKey;

	/** @var Cache|null */
	private $cache;


	/**
	 * @param string $privateKey
	 * @param IStorage|null $storage
	 */
	public function __construct(string $privateKey, ?IStorage $storage = null)
	{
		$this->privateKey = $privateKey;
		if ($storage !== null) {
			$this->cache = new Cache($storage, 'fio-payment-authorizator');
		}
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
	 * @param callable $callback with first argument of type Transaction.
	 * @param string $currency
	 * @param float $tolerance
	 */
	public function authOrders(array $unauthorizedVariables, callable $callback, string $currency = 'CZK', float $tolerance = 1.0): void
	{
		$transactions = $this->process()->getTransactions();
		$variables = array_keys($unauthorizedVariables);

		$process = static function (float $price, Transaction $transaction) use ($callback, $currency, $tolerance): void {
			if ($transaction->getCurrency() !== $currency) { // Fix different currencies
				$price = Helpers::convertCurrency($transaction->getCurrency(), $currency, $price);
			}
			if ($transaction->getPrice() - $price >= -$tolerance) { // Is price in tolerance?
				$callback($transaction);
			}
		};

		foreach ($transactions as $transaction) {
			foreach ($variables as $currentVariable) {
				if ($transaction->isVariableSymbol((int) $currentVariable) === true) {
					$process((float) $unauthorizedVariables[(int) $currentVariable], $transaction);
					break;
				}
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
			return $staticCache[$url] = $cache;
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
}
