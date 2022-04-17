<?php

declare(strict_types=1);

namespace Baraja\FioPaymentAuthorizator;


final class Transaction implements \Baraja\BankTransferAuthorizator\Transaction
{
	private int $id;

	private \DateTimeInterface $date;

	private float $price;

	private string $currency;

	private ?string $toAccount;

	private ?string $toAccountName;

	private ?int $toBankCode;

	private ?string $toBankName;

	private ?int $constantSymbol;

	private ?int $variableSymbol;

	private ?int $specificSymbol;

	private ?string $userNotice;

	private ?string $toMessage;

	private ?string $type;

	private ?string $sender;

	private ?string $message;

	private ?string $comment;

	private ?string $bic;

	private ?int $idTransaction;


	public function __construct(string $line, string $defaultCurrency = 'CZK')
	{
		$part = explode(';', $line);

		if (isset($part[0]) && $part[0] !== '') {
			$this->id = (int) $part[0];
		} else {
			throw new \InvalidArgumentException(sprintf("Transaction identifier is not defined.\n\nLine: %s", $line));
		}

		$this->date = new \DateTimeImmutable($part[1] ?? 'now');
		$this->price = ((float) str_replace(',', '.', $part[2] ?? '0')) ?: 0;
		$this->currency = strtoupper(trim($part[3] ?? '', '"')) ?: $defaultCurrency;
		$this->toAccount = trim($part[4] ?? '', '"') ?: null;
		$this->toAccountName = trim($part[5] ?? '', '"') ?: null;
		$this->toBankCode = ((int) ($part[6] ?? null)) ?: null;
		$this->toBankName = trim($part[7] ?? '', '"') ?: null;
		$this->constantSymbol = ((int) ($part[8] ?? null)) ?: null;
		$this->variableSymbol = ((int) ($part[9] ?? null)) ?: null;
		$this->specificSymbol = ((int) ($part[10] ?? null)) ?: null;
		$this->userNotice = trim($part[11] ?? '', '"') ?: null;
		$this->toMessage = trim($part[12] ?? '', '"') ?: null;
		$this->type = trim($part[13] ?? '', '"') ?: null;
		$this->sender = trim($part[14] ?? '', '"') ?: null;
		$this->message = trim($part[15] ?? '', '"') ?: null;
		$this->comment = trim($part[16] ?? '', '"') ?: null;
		$this->bic = trim($part[17] ?? '', '"') ?: null;
		$this->idTransaction = ((int) ($part[18] ?? null)) ?: null;
	}


	public function isVariableSymbol(int $vs): bool
	{
		return $this->variableSymbol === $vs || $this->isContainVariableSymbolInMessage($vs);
	}


	public function isContainVariableSymbolInMessage(int|string $vs): bool
	{
		return str_contains(sprintf(
			'%s %s %s %s',
			$this->userNotice,
			$this->toMessage,
			$this->message,
			$this->comment,
		), (string) $vs);
	}


	public function getId(): int
	{
		return $this->id;
	}


	public function getDate(): \DateTimeInterface
	{
		return $this->date;
	}


	public function getPrice(): float
	{
		return $this->price;
	}


	public function getCurrency(): string
	{
		return $this->currency;
	}


	public function getToAccount(): ?string
	{
		return $this->toAccount;
	}


	public function getToAccountName(): ?string
	{
		return $this->toAccountName;
	}


	public function getToBankCode(): ?int
	{
		return $this->toBankCode;
	}


	public function getToBankName(): ?string
	{
		return $this->toBankName;
	}


	public function getConstantSymbol(): ?int
	{
		return $this->constantSymbol;
	}


	public function getVariableSymbol(): ?int
	{
		return $this->variableSymbol;
	}


	public function getSpecificSymbol(): ?int
	{
		return $this->specificSymbol;
	}


	public function getUserNotice(): ?string
	{
		return $this->userNotice;
	}


	public function getToMessage(): ?string
	{
		return $this->toMessage;
	}


	public function getType(): ?string
	{
		return $this->type;
	}


	public function getSender(): ?string
	{
		return $this->sender;
	}


	public function getMessage(): ?string
	{
		return $this->message;
	}


	public function getComment(): ?string
	{
		return $this->comment;
	}


	public function getBic(): ?string
	{
		return $this->bic;
	}


	public function getIdTransaction(): ?int
	{
		return $this->idTransaction;
	}
}
