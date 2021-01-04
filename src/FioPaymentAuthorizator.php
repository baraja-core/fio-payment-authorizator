<?php

declare(strict_types=1);

namespace Baraja\FioPaymentAuthorizator;


use Baraja\BankTransferAuthorizator\BaseAuthorizator;
use Nette\Caching\Cache;
use Nette\Caching\Storage;

final class FioPaymentAuthorizator extends BaseAuthorizator
{
	private string $privateKey;

	private ?Cache $cache = null;


	public function __construct(string $privateKey, ?Storage $storage = null)
	{
		$this->privateKey = $privateKey;
		if ($storage !== null) {
			$this->cache = new Cache($storage, 'fio-payment-authorizator');
		}
	}


	public function process(): TransactionResult
	{
		return new TransactionResult($this->loadData());
	}


	/**
	 * @return Transaction[]
	 */
	public function getTransactions(): array
	{
		return $this->process()->getTransactions();
	}


	public function getDefaultCurrency(): string
	{
		return $this->process()->getCurrency();
	}


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
		if (($data = trim((string) @file_get_contents($url))) === '') {
			throw new FioPaymentException('Fio payment API response is empty, URL "' . $url . '" given. Is your API key valid?');
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
