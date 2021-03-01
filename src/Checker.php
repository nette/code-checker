<?php

declare(strict_types=1);

namespace Nette\CodeChecker;

use Nette\CommandLine\Console;
use Nette\Utils\Finder;


class Checker
{
	public bool $readOnly = false;

	public bool $showProgress = false;

	public array $accept = [
		'*.php', '*.phpt', '*.inc',
		'*.txt', '*.texy', '*.md',
		'*.css', '*.less', '*.sass', '*.scss', '*.js', '*.json', '*.latte', '*.htm', '*.html', '*.phtml', '*.xml',
		'*.ini', '*.neon', '*.yml',
		'*.sh', '*.bat',
		'*.sql',
		'.htaccess', '.gitignore',
	];

	public array $ignore = [
		'.git', '.svn', '.idea', '*.tmp', 'tmp', 'temp', 'log', 'vendor', 'node_modules', 'bower_components',
		'*.min.js', 'package.json', 'package-lock.json',
	];

	private array $tasks = [];

	private string $relativePath;

	private Console $console;


	public function run($paths): bool
	{
		$this->console = new Console;

		if ($this->readOnly) {
			echo "Running in read-only mode\n";
		}

		echo "Scanning {$this->console->color('white', implode(', ', $paths))}\n";

		$iterator = new \AppendIterator;
		foreach ($paths as $path) {
			$iterator->append(
				is_file($path)
				? new \ArrayIterator([$path])
				: Finder::findFiles($this->accept)
					->exclude($this->ignore)
					->from($path)
					->exclude($this->ignore)
					->getIterator(),
			);
		}

		$counter = 0;
		$success = true;
		foreach ($iterator as $file) {
			if ($this->showProgress) {
				echo str_pad(str_repeat('.', $counter++ % 40), 40), "\x0D";
			}
			$this->relativePath = is_string($file)
				? $file
				: $iterator->getSubPathName();
			$success = $this->processFile((string) $file) && $success;
		}

		if ($this->showProgress) {
			echo str_pad('', 40), "\x0D";
		}

		echo "Done.\n";
		return $success;
	}


	public function addTask(callable $task, ?string $pattern = null): void
	{
		$this->tasks[] = [$task, $pattern];
	}


	private function processFile(string $file): bool
	{
		$error = false;
		$origContents = $lastContents = file_get_contents($file);

		foreach ($this->tasks as $task) {
			[$handler, $pattern] = $task;
			if ($pattern && !$this->matchFileName($pattern, basename($file))) {
				continue;
			}

			$result = new Result;
			$contents = $lastContents;
			$handler($contents, $result);

			foreach ($result->getMessages() as $result) {
				[$type, $message, $line] = $result;
				if ($type === Result::ERROR) {
					$this->write('ERROR', $message, $line, 'red');
					$error = true;

				} elseif ($type === Result::WARNING) {
					$this->write('WARNING', $message, $line, 'yellow');

				} elseif ($type === Result::FIX) {
					$this->write($this->readOnly ? 'FOUND' : 'FIX', $message, $line, 'aqua');
					$error = $error || $this->readOnly;
				}
			}

			if (!$error) {
				$lastContents = $contents;
			}
		}

		if ($lastContents !== $origContents && !$this->readOnly) {
			file_put_contents($file, $lastContents);
		}
		return !$error;
	}


	private function matchFileName(string $pattern, string $name): bool
	{
		$neg = substr($pattern, 0, 1) === '!';
		foreach (explode(',', ltrim($pattern, '!')) as $part) {
			if (fnmatch($part, $name, FNM_CASEFOLD)) {
				return !$neg;
			}
		}

		return $neg;
	}


	private function write(string $type, string $message, ?int $line, string $color): void
	{
		$base = basename($this->relativePath);
		echo $this->console->color($color, str_pad("[$type]", 10)),
			$base === $this->relativePath ? '' : $this->console->color('silver', dirname($this->relativePath) . DIRECTORY_SEPARATOR),
			$this->console->color('white', $base . ($line ? ':' . $line : '')), '    ',
			$this->console->color($color, $message), "\n";
	}
}
