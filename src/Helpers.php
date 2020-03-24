<?php

declare(strict_types=1);

namespace Baraja\FioPaymentAuthorizator;


final class Helpers
{

	/** @throws \Error */
	final public function __construct()
	{
		throw new \Error('Class ' . get_class($this) . ' is static and cannot be instantiated.');
	}


	/**
	 * @param string $currentCurrency
	 * @param string $expectedCurrency
	 * @param float $price
	 * @return float
	 */
	public static function convertCurrency(string $currentCurrency, string $expectedCurrency, float $price): float
	{
		// TODO: Reserved for future use.

		return $price;
	}
}