<?php

declare(strict_types=1);

namespace Baraja\FioPaymentAuthorizator;


use Nette\SmartObject;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

final class TransactionResult
{
	use SmartObject;

	/** @var Transaction[] */
	private $transactions = [];

	/** @var int */
	private $accountId;

	/** @var int */
	private $bankId;

	/** @var string */
	private $currency;

	/** @var string */
	private $iban;

	/** @var string */
	private $bic;

	/** @var float */
	private $openingBalance;

	/** @var float */
	private $closingBalance;

	/** @var \DateTime */
	private $dateStart;

	/** @var \DateTime */
	private $dateEnd;

	/** @var int */
	private $idFrom;

	/** @var int */
	private $idTo;


	/**
	 * @param string $data
	 */
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

		// Transactions
		for ($i = 13; isset($parser[$i]); $i++) {
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


	/**
	 * @return int
	 */
	public function getAccountId(): int
	{
		return $this->accountId;
	}


	/**
	 * @return int
	 */
	public function getBankId(): int
	{
		return $this->bankId;
	}


	/**
	 * @return string
	 */
	public function getCurrency(): string
	{
		return $this->currency;
	}


	/**
	 * @return string
	 */
	public function getIban(): string
	{
		return $this->iban;
	}


	/**
	 * @return string
	 */
	public function getBic(): string
	{
		return $this->bic;
	}


	/**
	 * @return float
	 */
	public function getOpeningBalance(): float
	{
		return $this->openingBalance;
	}


	/**
	 * @return float
	 */
	public function getClosingBalance(): float
	{
		return $this->closingBalance;
	}


	/**
	 * @return \DateTime
	 */
	public function getDateStart(): \DateTime
	{
		return $this->dateStart;
	}


	/**
	 * @return \DateTime
	 */
	public function getDateEnd(): \DateTime
	{
		return $this->dateEnd;
	}


	/**
	 * @return int
	 */
	public function getIdFrom(): int
	{
		return $this->idFrom;
	}


	/**
	 * @return int
	 */
	public function getIdTo(): int
	{
		return $this->idTo;
	}
}