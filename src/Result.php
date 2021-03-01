<?php

declare(strict_types=1);

namespace Nette\CodeChecker;


class Result
{
	public const
		ERROR = 'error',
		FIX = 'fix',
		WARNING = 'warning';

	private array $messages = [];


	public function fix(string $message, ?int $line = null): void
	{
		$this->messages[] = [self::FIX, $message, $line];
	}


	public function warning(string $message, ?int $line = null): void
	{
		$this->messages[] = [self::WARNING, $message, $line];
	}


	public function error(string $message, ?int $line = null): void
	{
		$this->messages[] = [self::ERROR, $message, $line];
	}


	public function getMessages(): array
	{
		return $this->messages;
	}
}
