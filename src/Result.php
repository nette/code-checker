<?php

namespace Nette\CodeChecker;


class Result
{
	public const
		ERROR = 'error',
		FIX = 'fix',
		WARNING = 'warning';

	private $messages = [];


	/**
	 * @param  string
	 * @param  int
	 * @return void
	 */
	public function fix($message, $line = null)
	{
		$this->messages[] = [self::FIX, $message, $line];
	}


	/**
	 * @param  string
	 * @param  int
	 * @return void
	 */
	public function warning($message, $line = null)
	{
		$this->messages[] = [self::WARNING, $message, $line];
	}


	/**
	 * @param  string
	 * @param  int
	 * @return void
	 */
	public function error($message, $line = null)
	{
		$this->messages[] = [self::ERROR, $message, $line];
	}


	/**
	 * @return array
	 */
	public function getMessages()
	{
		return $this->messages;
	}
}
