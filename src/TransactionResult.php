<?php

declare(strict_types=1);

namespace Baraja\FioPaymentAuthorizator;


use Nette\Utils\DateTime;
use Nette\Utils\Strings;

final class TransactionResult
{

	/** @var Transaction[] */
	private array $transactions = [];

	private int $accountId;

	private int $bankId;

	private string $currency;

	private string $iban;

	private string $bic;

	private float $openingBalance;

	private float $closingBalance;

	private \DateTime $dateStart;

	private \DateTime $dateEnd;

	private int $idFrom;

	private int $idTo;


	public function __construct(string $data)
	{
		$parser = explode("\n", Strings::normalize($data));

		if (isset($parser[10]) === false) {
			FioPaymentException::transactionDataAreBroken($data);
		}

		// Meta information parser
		$line = static function (string $line): string {
			return explode(';', $line, 2)[1] ?? '';
		};

		$this->accountId = (int) $line($parser[0]);
		$this->bankId = (int) $line($parser[1]);
		$this->currency = strtoupper($line($parser[2]));
		$this->iban = $line($parser[3]);
		$this->bic = $line($parser[4]);
		$this->openingBalance = (float) str_replace(',', '.', $line($parser[5]));
		$this->closingBalance = (float) str_replace(',', '.', $line($parser[6]));
		$this->dateStart = DateTime::from($line($parser[7]));
		$this->dateEnd = DateTime::from($line($parser[8]));
		$this->idFrom = (int) $line($parser[9]);
		$this->idTo = (int) $line($parser[10]);

		for ($i = 13; isset($parser[$i]); $i++) { // Transactions
			$this->transactions[] = new Transaction($parser[$i]);
		}
	}


	/**
	 * @return Transaction[]
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


	public function getDateStart(): \DateTime
	{
		return $this->dateStart;
	}


	public function getDateEnd(): \DateTime
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
