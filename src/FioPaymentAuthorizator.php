<?php

declare(strict_types=1);

namespace Baraja;


use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Utils\Validators;

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
	 * @param int[] $unauthorizedVariables
	 * @param callable(Transaction $transaction) $callback
	 */
	public function authOrders(array $unauthorizedVariables, callable $callback): void
	{
		assert(Validators::everyIs($unauthorizedVariables, 'int'));
		$transactions = $this->process();

		foreach ($transactions->getTransactions() as $transaction) {
			if (\in_array($transaction->getVariableSymbol(), $unauthorizedVariables, true) === false) {
				$callback($transaction);
			}
		}
	}

	/**
	 * @return string
	 */
	private function loadData(): string
	{
		$year = (int) date('Y');
		if (($month = (int) date('m') - 1) === 0) {
			$year--;
			$month = 12;
		}

		$url = 'https://www.fio.cz/ib_api/rest/periods/' . $this->privateKey
			. '/' . $year . '-' . $month . '-01/' . date('Y-m-d')
			. '/transactions.csv';

		if ($this->cache !== null && ($cache = $this->cache->load($url)) !== null) {
			return $cache;
		}

		$data = file_get_contents($url);

		if ($this->cache !== null) {
			$this->cache->save($url, $data, [
				Cache::EXPIRE => '15 minutes',
				Cache::TAGS => ['fio', 'bank', 'payment'],
			]);
		}

		return $data;
	}

}