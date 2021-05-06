<?php

declare(strict_types=1);

namespace Baraja\FioPaymentAuthorizator;


use Baraja\BankTransferAuthorizator\BaseAuthorizator;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\Utils\Callback;

final class FioPaymentAuthorizator extends BaseAuthorizator
{
	private ?Cache $cache = null;


	public function __construct(
		private string $privateKey,
		?Storage $storage = null
	) {
		if ($privateKey === '') {
			throw new FioPaymentException('Private key can not be empty.');
		}
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
		if ($this->cache !== null) {
			$cache = $this->cache->load($url);
			if ($cache !== null) {
				return $staticCache[$url] = $cache;
			}
		}

		$data = $this->safeDownload($url);
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
	 * Removes control characters, normalizes line breaks to `\n`, removes leading and trailing blank lines,
	 * trims end spaces on lines, normalizes UTF-8 to the normal form of NFC.
	 */
	private function normalize(string $s): string
	{
		$s = trim($s);
		// convert to compressed normal form (NFC)
		if (class_exists('Normalizer', false)) {
			$n = \Normalizer::normalize($s, \Normalizer::FORM_C);
			if ($n !== false) {
				$s = (string) $n;
			}
		}

		$s = str_replace(["\r\n", "\r"], "\n", $s);

		// remove control characters; leave \t + \n
		$s = (string) preg_replace('#[\x00-\x08\x0B-\x1F\x7F-\x9F]+#u', '', $s);

		// right trim
		$s = (string) preg_replace('#[\t ]+$#m', '', $s);

		// leading and trailing blank lines
		return trim($s, "\n");
	}


	private function safeDownload(string $url): string
	{
		$data = (string) Callback::invokeSafe(
			'file_get_contents',
			[$url],
			fn(string $message) => throw new FioPaymentException(
				'Can not download data from URL "' . $url . '".'
				. "\n" . 'Reported error: ' . $message,
			),
		);
		$data = $this->normalize($data);
		if ($data === '') {
			throw new FioPaymentException(
				'Fio payment API response is empty, URL "' . $url . '" given. Is your API key valid?',
			);
		}
		if (str_contains($data, '<status>error</status>')) {
			throw new FioPaymentException(
				'The external API service is currently down.'
				. "\n\n" . 'Original report:'
				. "\n\n" . $data,
			);
		}

		return $data;
	}
}
