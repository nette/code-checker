<?php declare(strict_types=1);

namespace Nette\CodeChecker;


class Result
{
	public const
		Error = 'error',
		Fix = 'fix',
		Warning = 'warning';

	/** @var list<array{string, string, ?int}> */
	private array $messages = [];


	public function fix(string $message, ?int $line = null): void
	{
		$this->messages[] = [self::Fix, $message, $line];
	}


	public function warning(string $message, ?int $line = null): void
	{
		$this->messages[] = [self::Warning, $message, $line];
	}


	public function error(string $message, ?int $line = null): void
	{
		$this->messages[] = [self::Error, $message, $line];
	}


	/** @return list<array{string, string, ?int}> */
	public function getMessages(): array
	{
		return $this->messages;
	}
}
