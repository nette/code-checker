<?php

/**
 * Source Codes Checker.
 *
 * This file is part of the Nette Framework (https://nette.org)
 */

use Nette\Utils\Strings;
use Nette\CommandLine\Parser;

if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo('Install packages using `composer update`');
	exit(1);
}

set_exception_handler(function ($e) {
	echo "Error: {$e->getMessage()}\n";
	die(2);
});

set_error_handler(function ($severity, $message, $file, $line) {
	if (($severity & error_reporting()) === $severity) {
		throw new ErrorException($message, 0, $severity, $file, $line);
	}
	return FALSE;
});


echo '
CodeChecker version 2.8
-----------------------
';

$cmd = new Parser(<<<XX
Usage:
    php code-checker.php [options]

Options:
    -d <path>             Folder or file to scan (default: current directory)
    -i | --ignore <mask>  Files to ignore
    -f | --fix            Fixes files
    -l | --eol            Convert newline characters
    --short-arrays        Enforces PHP 5.4 short array syntax
    --strict-types        Checks whether PHP 7.0 directive strict_types is enabled


XX
, [
	'-d' => [Parser::REALPATH => TRUE, Parser::VALUE => getcwd()],
	'--ignore' => [Parser::REPEATABLE => TRUE],
]);

$options = $cmd->parse();
if ($cmd->isEmpty()) {
	$cmd->help();
}



class CodeChecker extends Nette\Object
{
	public $tasks = [];

	public $readOnly = FALSE;

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
		'*.min.js', 'package.json',
	];

	private $file;

	private $error;


	public function run($path)
	{
		set_time_limit(0);

		$this->useColors = PHP_SAPI === 'cli' && ((function_exists('posix_isatty') && posix_isatty(STDOUT))
				|| getenv('ConEmuANSI') === 'ON' || getenv('ANSICON') !== FALSE || getenv('term') === 'xterm-256color');

		if ($this->readOnly) {
			echo "Running in read-only mode\n";
		}

		echo "Scanning {$this->color('white', $path)}\n";

		$counter = 0;
		$success = TRUE;
		$files = is_file($path)
			? [$path]
			: Nette\Utils\Finder::findFiles($this->accept)->exclude($this->ignore)->from($path)->exclude($this->ignore);

		foreach ($files as $file)
		{
			echo str_pad(str_repeat('.', $counter++ % 40), 40), "\x0D";

			$orig = $s = file_get_contents($file);
			$this->file = ltrim(substr($file, strlen($path)), '/\\');
			$this->error = FALSE;

			foreach ($this->tasks as $task) {
				$res = $task->bindTo($this)->__invoke($s);
				if ($this->error) {
					$success = FALSE;
					break;
				} elseif (is_string($res)) {
					$s = $res;
				}
			}

			if ($s !== $orig && !$this->readOnly) {
				file_put_contents($file, $s);
			}
		}

		echo str_pad('', 40), "\x0DDone.\n";
		return $success;
	}


	public function fix($message, $line = NULL)
	{
		$this->write($this->readOnly ? 'FOUND' : 'FIX', $message, $line, 'aqua');
		$this->error = $this->readOnly;
	}


	public function warning($message, $line = NULL)
	{
		$this->write('WARNING', $message, $line, 'yellow');
	}


	public function error($message, $line = NULL)
	{
		$this->write('ERROR', $message, $line, 'red');
		$this->error = TRUE;
	}


	private function write($type, $message, $line, $color)
	{
		$base = basename($this->file);
		echo $this->color($color, str_pad("[$type]", 10)),
			$base === $this->file ? '' : $this->color('silver', dirname($this->file) . DIRECTORY_SEPARATOR),
			$this->color('white', $base . ($line ? ':' . $line : '')), '    ',
			$this->color($color, $message), "\n";
	}


	public function color($color = NULL, $s = NULL)
	{
		static $colors = [
			'black' => '0;30', 'gray' => '1;30', 'silver' => '0;37', 'white' => '1;37',
			'navy' => '0;34', 'blue' => '1;34', 'green' => '0;32', 'lime' => '1;32',
			'teal' => '0;36', 'aqua' => '1;36', 'maroon' => '0;31', 'red' => '1;31',
			'purple' => '0;35', 'fuchsia' => '1;35', 'olive' => '0;33', 'yellow' => '1;33',
			NULL => '0',
		];
		if ($this->useColors) {
			$c = explode('/', $color);
			$s = "\033[" . ($c[0] ? $colors[$c[0]] : '')
				. (empty($c[1]) ? '' : ';4' . substr($colors[$c[1]], -1))
				. 'm' . $s . ($s === NULL ? '' : "\033[0m");
		}
		return $s;
	}


	public function is($extensions)
	{
		return in_array(pathinfo($this->file, PATHINFO_EXTENSION), explode(',', $extensions));
	}

}



$checker = new CodeChecker;
foreach ($options['--ignore'] as $ignore) {
	$checker->ignore[] = $ignore;
}
$checker->readOnly = !isset($options['--fix']);

// control characters checker
$checker->tasks[] = function ($s) {
	if (!Strings::match($s, '#^[^\x00-\x08\x0B\x0C\x0E-\x1F]*+$#')) {
		$this->error('Contains control characters');
	}
};

// BOM remover
$checker->tasks[] = function ($s) {
	if (substr($s, 0, 3) === "\xEF\xBB\xBF") {
		$this->fix('contains BOM');
		return substr($s, 3);
	}
};

// UTF-8 checker
$checker->tasks[] = function ($s) {
	if (!Strings::checkEncoding($s)) {
		$this->error('Is not valid UTF-8 file');
	}
};

// invalid phpDoc checker
$checker->tasks[] = function ($s) {
	if ($this->is('php,phpt')) {
		foreach (token_get_all($s) as $token) {
			if ($token[0] === T_COMMENT && Strings::match($token[1], '#/\*\s.*@[a-z]#isA')) {
				$this->warning('Missing /** in phpDoc comment', $token[2]);
			}
		}
	}
};

// short PHP 5.4 arrays
if (isset($options['--short-arrays'])) {
	$checker->tasks[] = function ($s) {
		if ($this->is('php,phpt')) {
			$out = '';
			$brackets = [];
			$tokens = token_get_all($s);

			for ($i = 0; $i < count($tokens); $i++) {
				$token = $tokens[$i];
				if ($token === '(') {
					$brackets[] = FALSE;

				} elseif ($token === ')') {
					$token = array_pop($brackets) ? ']' : ')';

				} elseif (is_array($token) && $token[0] === T_ARRAY) {
					$a = $i + 1;
					if (isset($tokens[$a]) && $tokens[$a][0] === T_WHITESPACE) {
						$a++;
					}
					if (isset($tokens[$a]) && $tokens[$a] === '(') {
						$this->fix('uses old array() syntax', $token[2]);
						$i = $a;
						$brackets[] = TRUE;
						$token = '[';
					}
				}
				$out .= is_array($token) ? $token[1] : $token;
			}
			return $out;
		}
	};
}

// PHP 7.0 strict types declaration
if (isset($options['--strict-types'])) {
	$checker->tasks[] = function ($s) {
		if ($this->is('php,phpt')) {
			$declarations = '';
			$tokens = token_get_all($s);
			for ($i = 0; $i < count($tokens); $i++) {
				if ($tokens[$i][0] === T_DECLARE) {
					while (isset($tokens[++$i]) && $tokens[$i] !== ';') {
						$declarations .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
					}
				} elseif (!in_array($tokens[$i][0], [T_OPEN_TAG, T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], TRUE)) {
					break;
				}
			}
			if (!preg_match('#\bstrict_types\s*=\s*1\b#', $declarations)) {
				$this->error('Missing declare(strict_types=1)');
			}
		}
	};
}

// invalid doublequoted string checker
$checker->tasks[] = function ($s) {
	if ($this->is('php,phpt')) {
		$prev = NULL;
		foreach (token_get_all($s) as $token) {
			if (($token[0] === T_ENCAPSED_AND_WHITESPACE && ($prev[0] !== T_START_HEREDOC || !strpos($prev[1], "'")))
				|| ($token[0] === T_CONSTANT_ENCAPSED_STRING && $token[1][0] === '"')
			) {
				$m = Strings::match($token[1], '#^([^\\\\]|\\\\[\\\\nrtvefxu0-7\W])*+#'); // more strict: '#^([^\\\\]|\\\\[\\\\nrtvefu$"x0-7])*+#'
				if ($token[1] !== $m[0]) {
					$this->warning('Invalid escape sequence ' . substr($token[1], strlen($m[0]), 2) . ' in double quoted string', $token[2]);
				}
			}
			$prev = $token;
		}
	}
};

// newline characters normalizer for the current OS
if (isset($options['--eol'])) {
	$checker->tasks[] = function ($s) {
		$new = str_replace("\n", PHP_EOL, str_replace(["\r\n", "\r"], "\n", $s));
		if (!$this->is('sh') && $new !== $s) {
			$this->fix('contains non-system line-endings');
			return $new;
		}
	};
}

// trailing ? > remover
$checker->tasks[] = function ($s) {
	if ($this->is('php,phpt')) {
		$tmp = rtrim($s);
		if (substr($tmp, -2) === '?>') {
			$this->fix('contains closing PHP tag ?>');
			return substr($tmp, 0, -2);
		}
	}
};

// lint Latte templates
$checker->tasks[] = function ($s) {
	if ($this->is('latte')) {
		try {
			$latte = new Latte\Engine;
			$latte->setLoader(new Latte\Loaders\StringLoader);
			$latte->compile($s);
		} catch (Latte\CompileException $e) {
			if (!preg_match('#Unknown (macro|attribute)#A', $e->getMessage())) {
				$this->error($e->getMessage(), $e->sourceLine);
			}
		}
	}
};

// lint Neon
$checker->tasks[] = function ($s) {
	if ($this->is('neon')) {
		try {
			Nette\Neon\Neon::decode($s);
		} catch (Nette\Neon\Exception $e) {
			$this->error($e->getMessage());
		}
	}
};

// white-space remover
$checker->tasks[] = function ($s) {
	$new = Strings::replace($s, '#[\t ]+(\r?\n)#', '$1'); // right trim
	$eol = preg_match('#\r?\n#', $new, $m) ? $m[0] : PHP_EOL;
	$new = rtrim($new); // trailing trim
	if ($new !== '') {
		$new .= $eol;
	}
	if ($new !== $s) {
		$bytes = strlen($s) - strlen($new);
		$this->fix("$bytes bytes of whitespaces");
		return $new;
	}
};

// indentation and tabs checker
$checker->tasks[] = function ($s) {
	if ($this->is('php,phpt,css,less,js,json,neon')) {
		$orig = $s;
		if ($this->is('php,phpt')) { // remove spaces from strings
			$res = '';
			foreach (token_get_all($s) as $token) {
				if (is_array($token) && in_array($token[0], [T_ENCAPSED_AND_WHITESPACE, T_CONSTANT_ENCAPSED_STRING])) {
					$token[1] = preg_replace('#\s#', '.', $token[1]);
				}
				$res .= is_array($token) ? $token[1] : $token;
			}
			$s = $res;
		}
		if (preg_match('#^\t*+\ (?!\*)#m', $s, $m, PREG_OFFSET_CAPTURE)) {
			$line = $m[0][1] ? substr_count($orig, "\n", 0, $m[0][1]) + 1 : 1;
			$this->error('Mixed tabs and spaces indentation', $line);
		}
		if (preg_match('#(?<=[\S ])(?<!^//)\t#m', $s, $m, PREG_OFFSET_CAPTURE)) {
			$line = substr_count($orig, "\n", 0, $m[0][1]) + 1;
			$this->error('Found unexpected tabulator', $line);
		}
	} elseif ($this->is('yml') && ($pos = strpos($s, "\t")) !== FALSE) {
		$line = substr_count($s, "\n", 0, $pos) + 1;
		$this->error('Found unexpected tabulator', $line);
	}
};

$ok = $checker->run($options['-d']);

exit($ok ? 0 : 1);
