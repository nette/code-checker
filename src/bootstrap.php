<?php

/**
 * Source Codes Checker.
 *
 * This file is part of the Nette Framework (https://nette.org)
 */

namespace Nette\CodeChecker;

use Nette\CommandLine\Parser;

$autoload = is_file(__DIR__ . '/../vendor/autoload.php')
	? __DIR__ . '/../vendor/autoload.php'
	: __DIR__ . '/../../../autoload.php';
if (@!include $autoload) {
	echo 'Install packages using `composer update`';
	exit(1);
}

set_exception_handler(function ($e) {
	echo "Error: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}\n";
	die(2);
});

set_error_handler(function ($severity, $message, $file, $line) {
	if (($severity & error_reporting()) === $severity) {
		throw new \ErrorException($message, 0, $severity, $file, $line);
	}
	return false;
});

set_time_limit(0);


echo '
CodeChecker version 3.0
-----------------------
';

$cmd = new Parser(<<<'XX'
Usage:
    php code-checker [options]

Options:
    -d <path>             Folder or file to scan (default: current directory)
    -i | --ignore <mask>  Files to ignore
    -f | --fix            Fixes files
    -l | --eol            Convert newline characters
    --no-progress         Do not show progress dots
    --short-arrays        Enforces PHP 5.4 short array syntax
    --strict-types        Checks whether PHP 7.0 directive strict_types is enabled


XX
, [
	'-d' => [Parser::REALPATH => true, Parser::VALUE => getcwd()],
	'--ignore' => [Parser::REPEATABLE => true],
]);

$options = $cmd->parse();
if ($cmd->isEmpty()) {
	$cmd->help();
}

$checker = new Checker;
$tasks = 'Nette\CodeChecker\Tasks';

foreach ($options['--ignore'] as $ignore) {
	$checker->ignore[] = $ignore;
}
$checker->readOnly = !isset($options['--fix']);
$checker->showProgress = !isset($options['--no-progress']);

$checker->addTask([$tasks, 'controlCharactersChecker']);
$checker->addTask([$tasks, 'bomFixer']);
$checker->addTask([$tasks, 'utf8Checker']);
$checker->addTask([$tasks, 'phpSyntaxChecker'], '*.php,*.phpt');
$checker->addTask([$tasks, 'invalidPhpDocChecker'], '*.php,*.phpt');

if (isset($options['--short-arrays'])) {
	$checker->addTask([$tasks, 'shortArraySyntaxFixer'], '*.php,*.phpt');
}
if (isset($options['--strict-types'])) {
	$checker->addTask([$tasks, 'strictTypesDeclarationChecker'], '*.php,*.phpt');
}
if (isset($options['--eol'])) {
	$checker->addTask([$tasks, 'newlineNormalizer'], '!*.sh');
}

$checker->addTask([$tasks, 'invalidDoubleQuotedStringChecker'], '*.php,*.phpt');
$checker->addTask([$tasks, 'trailingPhpTagRemover'], '*.php,*.phpt');
$checker->addTask([$tasks, 'latteSyntaxChecker'], '*.latte');
$checker->addTask([$tasks, 'neonSyntaxChecker'], '*.neon');
$checker->addTask([$tasks, 'jsonSyntaxChecker'], '*.json');
$checker->addTask([$tasks, 'yamlIndentationChecker'], '*.yml');
$checker->addTask([$tasks, 'trailingWhiteSpaceFixer']);
$checker->addTask([$tasks, 'tabIndentationChecker'], '*.css,*.less,*.js,*.json,*.neon');
$checker->addTask([$tasks, 'tabIndentationPhpChecker'], '*.php,*.phpt');
$checker->addTask([$tasks, 'unexpectedTabsChecker'], '*.yml');

$ok = $checker->run($options['-d']);

exit($ok ? 0 : 1);
