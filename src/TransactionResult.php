<?php

declare(strict_types=1);

namespace Baraja\FioPaymentAuthorizator;


final class TransactionResult
{
	/** @var array<int, Transaction> */
	private array $transactions = [];

	private int $accountId;

	private int $bankId;

	private string $currency;

	private string $iban;

	private string $bic;

	private float $openingBalance;

	private float $closingBalance;

	private \DateTimeInterface $dateStart;

	private \DateTimeInterface $dateEnd;

	private int $idFrom;

	private int $idTo;


	public function __construct(string $payload)
	{
		$lines = explode("\n", $payload);

		if (isset($lines[10]) === false) {
			throw new \InvalidArgumentException(sprintf("Fio transaction data file is broken. File must define some variables.\n\n%s", $payload));
		}

		// Meta information parser
		$lineParser = static fn(string $haystack): string => explode(';', $haystack, 2)[1] ?? '';

		$this->accountId = (int) $lineParser($lines[0]);
		$this->bankId = (int) $lineParser($lines[1]);
		$this->currency = strtoupper($lineParser($lines[2]));
		$this->iban = $lineParser($lines[3]);
		$this->bic = $lineParser($lines[4]);
		$this->openingBalance = (float) str_replace(',', '.', $lineParser($lines[5]));
		$this->closingBalance = (float) str_replace(',', '.', $lineParser($lines[6]));
		$this->dateStart = new \DateTimeImmutable($lineParser($lines[7]));
		$this->dateEnd = new \DateTimeImmutable($lineParser($lines[8]));
		$this->idFrom = (int) $lineParser($lines[9]);
		$this->idTo = (int) $lineParser($lines[10]);

		for ($i = 13; isset($lines[$i]); $i++) { // Transactions
			$this->transactions[] = new Transaction($lines[$i]);
		}
	}


	/**
	 * @return array<int, Transaction>
	 */
	public function getTransactions(): array
	{
		return $this->transactions;
	}


	public function getAccountId(): int
	{
		return $this->accountId;
	}


	public function getBankId(): int
	{
		return $this->bankId;
	}


	public function getCurrency(): string
	{
		return $this->currency;
	}


	public function getIban(): string
	{
		return $this->iban;
	}


	public function getBic(): string
	{
		return $this->bic;
	}


	public function getOpeningBalance(): float
	{
		return $this->openingBalance;
	}


	public function getClosingBalance(): float
	{
		return $this->closingBalance;
	}


	public function getDateStart(): \DateTimeInterface
	{
		return $this->dateStart;
	}


	public function getDateEnd(): \DateTimeInterface
	{
		return $this->dateEnd;
	}


	public function getIdFrom(): int
	{
		return $this->idFrom;
	}


	public function getIdTo(): int
	{
		return $this->idTo;
	}
}
