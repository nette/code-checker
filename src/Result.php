<?php

declare(strict_types=1);

namespace Nette\CodeChecker;


class Result
{
	public const
		Error = 'error',
		Fix = 'fix',
		Warning = 'warning';

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


	public function getMessages(): array
	{
		return $this->messages;
	}
}
