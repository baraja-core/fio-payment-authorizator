<?php

declare(strict_types=1);

namespace Baraja;


final class FioPaymentException extends \RuntimeException
{

	/**
	 * @param string $url
	 */
	public static function emptyResponse(string $url): void
	{
		throw new self('Fio payment API response is empty, URL "' . $url . '" given.');
	}

	/**
	 * @param string $data
	 */
	public static function transactionDataAreBroken(string $data): void
	{
		throw new self('Fio transaction data file is broken. File must define some variables.' . "\n\n" . $data);
	}

}