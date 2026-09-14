<?php declare(strict_types=1);

namespace Nette\CodeChecker;

use Nette\CommandLine\{Ansi, Console};
use Nette\Utils\Finder;


class Checker
{
	public bool $readOnly = false;

	public bool $showProgress = false;

	/** @var list<string> */
	public array $accept = [
		'*.php', '*.phpt', '*.inc',
		'*.txt', '*.texy', '*.md',
		'*.css', '*.less', '*.sass', '*.scss', '*.js', '*.mjs', '*.cjs', '*.ts', '*.json', '*.latte', '*.htm', '*.html', '*.phtml', '*.xml',
		'*.ini', '*.neon', '*.yml',
		'*.sh', '*.bat',
		'*.sql',
		'.htaccess', '.gitignore',
	];

	/** @var list<string> */
	public array $ignore = [
		'.git', '.svn', '.idea', '.claude', '*.tmp', 'tmp', 'temp', 'log', 'vendor', 'node_modules', 'bower_components',
		'*.min.js', 'package.json', 'package-lock.json',
	];

	/** @var list<array{callable(string, Result): void, ?string}> */
	private array $tasks = [];

	private string $relativePath;


	public function __construct(
		private Console $console,
	) {
	}


	/** @param list<string> $paths */
	public function run(array $paths): bool
	{
		if ($this->readOnly) {
			$this->console->writeLine('Running in read-only mode');
		}

		$this->console->writeLine('Scanning ' . $this->console->color('white', implode(', ', $paths)));

		$finder = new Finder;
		foreach ($paths as $path) {
			if (is_file($path)) {
				$finder->append($path);
			} else {
				$finder->append()
					->files($this->accept)
					->exclude($this->ignore)
					->from($path)
					->exclude($this->ignore);
			}
		}

		$counter = 0;
		$success = true;
		foreach ($finder as $file) {
			if ($this->showProgress) {
				$this->console->setStatus(str_repeat('.', $counter++ % 40 + 1));
			}
			$this->relativePath = $file->getRelativePathname();
			$success = $this->processFile((string) $file) && $success;
		}

		$this->console->writeLine('Done.'); // erases the progress by itself
		return $success;
	}


	/** @param callable(string, Result): void $task */
	public function addTask(callable $task, ?string $pattern = null): void
	{
		$this->tasks[] = [$task, $pattern];
	}


	private function processFile(string $file): bool
	{
		$error = false;
		$origContents = $lastContents = (string) file_get_contents($file);

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
				if ($type === Result::Error) {
					$this->write('ERROR', $message, $line, 'red');
					$error = true;

				} elseif ($type === Result::Warning) {
					$this->write('WARNING', $message, $line, 'yellow');

				} elseif ($type === Result::Fix) {
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
		$neg = str_starts_with($pattern, '!');
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
		$this->console->writeLine(
			$this->console->color($color, str_pad("[$type]", 10))
			. ($base === $this->relativePath ? '' : $this->console->color('silver', dirname($this->relativePath) . DIRECTORY_SEPARATOR))
			. $this->console->color('white', $base . ($line ? ':' . $line : '')) . '    '
			// a message quoting the checked file may carry escape sequences of its own
			. $this->console->color($color, Ansi::strip($message)),
		);
	}
}
