<?php

declare(strict_types=1);

namespace Baraja;


use Nette\SmartObject;
use Nette\Utils\DateTime;

final class Transaction
{

	use SmartObject;

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var \DateTime
	 */
	private $date;

	/**
	 * @var float
	 */
	private $price;

	/**
	 * @sample CZK
	 * @var string
	 */
	private $currency;

	/**
	 * @var string|null
	 */
	private $toAccount;

	/**
	 * @var string|null
	 */
	private $toAccountName;

	/**
	 * @var int|null
	 */
	private $toBankCode;

	/**
	 * @var string|null
	 */
	private $toBankName;

	/**
	 * @var int|null
	 */
	private $constantSymbol;

	/**
	 * @var int|null
	 */
	private $variableSymbol;

	/**
	 * @var int|null
	 */
	private $specificSymbol;

	/**
	 * @var string|null
	 */
	private $userNotice;

	/**
	 * @var string|null
	 */
	private $toMessage;

	/**
	 * @var string|null
	 */
	private $type;

	/**
	 * @var string|null
	 */
	private $sender;

	/**
	 * @var string|null
	 */
	private $message;

	/**
	 * @var string|null
	 */
	private $comment;

	/**
	 * @var string|null
	 */
	private $bic;

	/**
	 * @var int|null
	 */
	private $idTransaction;

	/**
	 * @param string $line
	 */
	public function __construct(string $line)
	{
		$parser = explode(';', $line);

		$this->id = ((int) ($parser[0] ?? null)) ? : null;
		$this->date = DateTime::from($parser[1] ?? null);
		$this->price = ((float) str_replace(',', '.', $parser[2] ?? '0')) ? : null;
		$this->currency = trim($parser[3] ?? '', '"') ? : null;
		$this->toAccount = trim($parser[4] ?? '', '"') ? : null;
		$this->toAccountName = trim($parser[5] ?? '', '"') ? : null;
		$this->toBankCode = ((int) ($parser[6] ?? null)) ? : null;
		$this->toBankName = trim($parser[7] ?? '', '"') ? : null;
		$this->constantSymbol = ((int) ($parser[8] ?? null)) ? : null;
		$this->variableSymbol = ((int) ($parser[9] ?? null)) ? : null;
		$this->specificSymbol = ((int) ($parser[10] ?? null)) ? : null;
		$this->userNotice = trim($parser[11] ?? '', '"') ? : null;
		$this->toMessage = trim($parser[12] ?? '', '"') ? : null;
		$this->type = trim($parser[13] ?? '', '"') ? : null;
		$this->sender = trim($parser[14] ?? '', '"') ? : null;
		$this->message = trim($parser[15] ?? '', '"') ? : null;
		$this->comment = trim($parser[16] ?? '', '"') ? : null;
		$this->bic = trim($parser[17] ?? '', '"') ? : null;
		$this->idTransaction = ((int) ($parser[18] ?? null)) ? : null;
	}

	/**
	 * @param int $variableSymbol
	 * @return bool
	 */
	public function isVariableSymbol(int $variableSymbol): bool
	{
		return $this->variableSymbol === $variableSymbol || $this->isContainVariableSymbolInMessage($variableSymbol);
	}

	/**
	 * @param int $variableSymbol
	 * @return bool
	 */
	public function isContainVariableSymbolInMessage(int $variableSymbol): bool
	{
		$haystack = $this->userNotice . ' ' . $this->toMessage . ' ' . $this->message . ' ' . $this->comment;

		return strpos($haystack, (string) $variableSymbol) !== false;
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return \DateTime
	 */
	public function getDate(): \DateTime
	{
		return $this->date;
	}

	/**
	 * @return float
	 */
	public function getPrice(): float
	{
		return $this->price;
	}

	/**
	 * @return string
	 */
	public function getCurrency(): string
	{
		return $this->currency;
	}

	/**
	 * @return string|null
	 */
	public function getToAccount(): ?string
	{
		return $this->toAccount;
	}

	/**
	 * @return string|null
	 */
	public function getToAccountName(): ?string
	{
		return $this->toAccountName;
	}

	/**
	 * @return int|null
	 */
	public function getToBankCode(): ?int
	{
		return $this->toBankCode;
	}

	/**
	 * @return string|null
	 */
	public function getToBankName(): ?string
	{
		return $this->toBankName;
	}

	/**
	 * @return int|null
	 */
	public function getConstantSymbol(): ?int
	{
		return $this->constantSymbol;
	}

	/**
	 * @return int|null
	 */
	public function getVariableSymbol(): ?int
	{
		return $this->variableSymbol;
	}

	/**
	 * @return int|null
	 */
	public function getSpecificSymbol(): ?int
	{
		return $this->specificSymbol;
	}

	/**
	 * @return string|null
	 */
	public function getUserNotice(): ?string
	{
		return $this->userNotice;
	}

	/**
	 * @return string|null
	 */
	public function getToMessage(): ?string
	{
		return $this->toMessage;
	}

	/**
	 * @return string|null
	 */
	public function getType(): ?string
	{
		return $this->type;
	}

	/**
	 * @return string|null
	 */
	public function getSender(): ?string
	{
		return $this->sender;
	}

	/**
	 * @return string|null
	 */
	public function getMessage(): ?string
	{
		return $this->message;
	}

	/**
	 * @return string|null
	 */
	public function getComment(): ?string
	{
		return $this->comment;
	}

	/**
	 * @return string|null
	 */
	public function getBic(): ?string
	{
		return $this->bic;
	}

	/**
	 * @return int|null
	 */
	public function getIdTransaction(): ?int
	{
		return $this->idTransaction;
	}

}
