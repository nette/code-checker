<?php

/**
 * Source Codes Checker.
 *
 * This file is part of the Nette Framework (http://nette.org)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

require __DIR__ . '/../../Nette-minified/nette.min.php';

use Nette\Utils\Strings;


echo '
CodeChecker version 0.9
-----------------------
';

$options = getopt('d:fl');

if (!$options) { ?>
Usage: php code-checker.phpc [options]

Options:
	-d <path>  folder to scan (optional)
	-f         fixes files
	-l         convert newline characters

<?php
}



class CodeChecker extends Nette\Object
{
	public $tasks = array();

	public $readOnly = FALSE;

	public $accept = array(
		'*.php', '*.phpc', '*.phpt', '*.inc',
		'*.txt', '*.texy', '*.md',
		'*.css', '*.js', '*.json', '*.latte', '*.htm', '*.html', '*.phtml', '*.xml',
		'*.ini', '*.neon',
		'*.sh', '*.bat',
		'*.sql',
		'.htaccess', '.gitignore',
	);

	public $ignore = array(
		'.*', '*.tmp', 'tmp', 'temp', 'log',
	);

	private $file;

	private $error;


	public function run($folder)
	{
		set_time_limit(0);

		if ($this->readOnly) {
			echo "Running in read-only mode\n";
		}

		echo "Scanning folder $folder...\n";

		$counter = 0;
		foreach (Nette\Utils\Finder::findFiles($this->accept)->from($folder)->exclude($this->ignore) as $file)
		{
			echo str_pad(str_repeat('.', $counter++ % 40), 40), "\x0D";

			$orig = $s = file_get_contents($file);
			$this->file = ltrim(str_replace($folder, '', $file), '/\\');
			$this->error = FALSE;

			foreach ($this->tasks as $task) {
				$res = $task($this, $s);
				if ($this->error) {
					continue 2;
				} elseif (is_string($res)) {
					$s = $res;
				}
			}

			if ($s !== $orig && !$this->readOnly) {
				file_put_contents($file, $s);
			}
		}

		echo "\nDone.";
		return !$this->error;
	}


	public function fix($message)
	{
		echo '[' . ($this->readOnly ? 'FOUND' : 'FIX') . "] $this->file   $message\n";
	}


	public function warning($message)
	{
		echo "[WARNING] $this->file   $message\n";
	}


	public function error($message)
	{
		echo "[ERROR] $this->file   $message\n";
		$this->error = TRUE;
	}


	public function is($extension)
	{
		return $extension === pathinfo($this->file, PATHINFO_EXTENSION);
	}

}



$checker = new CodeChecker;
$checker->readOnly = !isset($options['f']);

// control characters checker
$checker->tasks[] = function($checker, $s) {
	if (Strings::match($s, '#[\x00-\x08\x0B\x0C\x0E-\x1F]#')) {
		$checker->error('contains control characters');
	}
};

// BOM remover
$checker->tasks[] = function($checker, $s) {
	if (substr($s, 0, 3) === "\xEF\xBB\xBF") {
		$checker->fix('contains BOM');
		return substr($s, 3);
	}
};

// UTF-8 checker
$checker->tasks[] = function($checker, $s) {
	if (!Strings::checkEncoding($s)) {
		$checker->error('in not valid UTF-8 file');
	}
};

// invalid phpDoc checker
$checker->tasks[] = function($checker, $s) {
	if ($checker->is('php')) {
		foreach (token_get_all($s) as $token) {
			if ($token[0] === T_COMMENT && Strings::match($token[1], '#/\*\s.*@[a-z]#isA')) {
				$checker->warning("missing /** in phpDoc comment on line $token[2]");
			}
		}
	}
};

// invalid doublequoted string checker
$checker->tasks[] = function($checker, $s) {
	if ($checker->is('php')) {
		foreach (token_get_all($s) as $token) {
			if ($token[0] === T_ENCAPSED_AND_WHITESPACE || ($token[0] === T_CONSTANT_ENCAPSED_STRING && $token[1][0] === '"')) {
				$m = Strings::match($token[1], '#^([^\\\\]|\\\\[\\\\nrtvefx0-7\W])*#'); // more strict: '#^([^\\\\]|\\\\[\\\\nrtvef$"x0-7])*#'
				if ($token[1] !== $m[0]) {
					$checker->warning("invalid escape sequence " . substr($token[1], strlen($m[0]), 2) . " in double quoted string on line $token[2]");
				}
			}
		}
	}
};

// newline characters normalizer for the current OS
if (isset($options['l'])) {
	$checker->tasks[] = function($checker, $s) {
		$new = str_replace("\n", PHP_EOL, str_replace(array("\r\n", "\r"), "\n", $s));
		if ($new !== $s) {
			$checker->fix('contains non-system line-endings');
			return $new;
		}
	};
}

// trailing ? > remover
$checker->tasks[] = function($checker, $s) {
	if ($checker->is('php')) {
		$tmp = rtrim($s);
		if (substr($tmp, -2) === '?>') {
			$checker->fix('contains closing PHP tag ?>');
			return substr($tmp, 0, -2);
		}
	}
};

// lint Latte templates
$checker->tasks[] = function($checker, $s) {
	if ($checker->is('latte')) {
		try {
			$template = new Nette\Templating\Template;
			$template->registerFilter(new Nette\Latte\Engine);
			$template->compile($s);
		} catch (Nette\Templating\FilterException $e) {
			$checker->error($e->getMessage() . ($e->sourceLine ? " on line $e->sourceLine" : ''));
		}
	}
};

// lint Neon
$checker->tasks[] = function($checker, $s) {
	if ($checker->is('neon')) {
		try {
			Nette\Utils\Neon::decode($s);
		} catch (Nette\Utils\NeonException $e) {
			$checker->error($e->getMessage());
		}
	}
};

// white-space remover
$checker->tasks[] = function($checker, $s) {
	$new = Strings::replace($s, '#[\t ]+(\r?\n)#', '$1'); // right trim
	if ($checker->is('php')) { // trailing trim
		$eol = preg_match('#\r?\n#', $new, $m) ? $m[0] : PHP_EOL;
		$new = rtrim($new) . $eol;
	} else {
		$new = Strings::replace($new, '#(\r?\n)+\z#', '$1');
	}
	if ($new !== $s) {
		$bytes = strlen($s) - strlen($new);
   		$checker->fix("$bytes bytes of whitespaces");
   		return $new;
   	}
};

$ok = $checker->run(isset($options['d']) ? $options['d'] : getcwd());

exit($ok ? 0 : 1);
