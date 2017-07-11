<?php

namespace Nette\CodeChecker;

use Nette\Utils\Finder;


class Checker
{
	/** @var bool */
	public $readOnly = false;

	/** @var bool */
	public $showProgress = false;

	/** @var bool */
	public $useColors;

	public $accept = [
		'*.php', '*.phpt', '*.inc',
		'*.txt', '*.texy', '*.md',
		'*.css', '*.less', '*.sass', '*.scss', '*.js', '*.json', '*.latte', '*.htm', '*.html', '*.phtml', '*.xml',
		'*.ini', '*.neon', '*.yml',
		'*.sh', '*.bat',
		'*.sql',
		'.htaccess', '.gitignore',
	];

	public $ignore = [
		'.git', '.svn', '.idea', '*.tmp', 'tmp', 'temp', 'log', 'vendor', 'node_modules', 'bower_components',
		'*.min.js', 'package.json', 'package-lock.json',
	];

	private $tasks = [];

	/** @var string */
	private $relativePath;


	public function run($path)
	{
		$this->useColors = PHP_SAPI === 'cli' && ((function_exists('posix_isatty') && posix_isatty(STDOUT))
				|| getenv('ConEmuANSI') === 'ON' || getenv('ANSICON') !== false || getenv('term') === 'xterm-256color');

		if ($this->readOnly) {
			echo "Running in read-only mode\n";
		}

		echo "Scanning {$this->color('white', $path)}\n";

		$counter = 0;
		$success = true;
		$files = is_file($path)
			? [$path]
			: Finder::findFiles($this->accept)->exclude($this->ignore)->from($path)->exclude($this->ignore);

		foreach ($files as $file) {
			if ($this->showProgress) {
				echo str_pad(str_repeat('.', $counter++ % 40), 40), "\x0D";
			}
			$this->relativePath = ltrim(substr($file, strlen($path)), '/\\');
			$success = $this->processFile($file) && $success;
		}

		if ($this->showProgress) {
			echo str_pad('', 40), "\x0D";
		}

		echo "Done.\n";
		return $success;
	}


	public function addTask(callable $task, $pattern = null)
	{
		$this->tasks[] = [$task, $pattern];
	}


	/**
	 * @param  string
	 * @return bool
	 */
	private function processFile($file)
	{
		$error = false;
		$origContents = $lastContents = file_get_contents($file);

		foreach ($this->tasks as $task) {
			list($handler, $pattern) = $task;
			if ($pattern && !$this->matchFileName($pattern, basename($file))) {
				continue;
			}

			$result = new Result;
			$contents = $lastContents;
			$handler($contents, $result);

			foreach ($result->getMessages() as $result) {
				list($type, $message, $line) = $result;
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


	private function matchFileName($pattern, $name)
	{
		$neg = substr($pattern, 0, 1) === '!';
		foreach (explode(',', ltrim($pattern, '!')) as $part) {
			if (fnmatch($part, $name, FNM_CASEFOLD)) {
				return !$neg;
			}
		}
		return $neg;
	}


	private function write($type, $message, $line, $color)
	{
		$base = basename($this->relativePath);
		echo $this->color($color, str_pad("[$type]", 10)),
			$base === $this->relativePath ? '' : $this->color('silver', dirname($this->relativePath) . DIRECTORY_SEPARATOR),
			$this->color('white', $base . ($line ? ':' . $line : '')), '    ',
			$this->color($color, $message), "\n";
	}


	private function color($color = null, $s = null)
	{
		static $colors = [
			'black' => '0;30', 'gray' => '1;30', 'silver' => '0;37', 'white' => '1;37',
			'navy' => '0;34', 'blue' => '1;34', 'green' => '0;32', 'lime' => '1;32',
			'teal' => '0;36', 'aqua' => '1;36', 'maroon' => '0;31', 'red' => '1;31',
			'purple' => '0;35', 'fuchsia' => '1;35', 'olive' => '0;33', 'yellow' => '1;33',
			null => '0',
		];
		if ($this->useColors) {
			$c = explode('/', $color);
			$s = "\033[" . ($c[0] ? $colors[$c[0]] : '')
				. (empty($c[1]) ? '' : ';4' . substr($colors[$c[1]], -1))
				. 'm' . $s . ($s === null ? '' : "\033[0m");
		}
		return $s;
	}
}
