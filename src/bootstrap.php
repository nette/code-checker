<?php declare(strict_types=1);

/**
 * Source Codes Checker.
 *
 * This file is part of the Nette Framework (https://nette.org)
 */

namespace Nette\CodeChecker;

use Nette\CommandLine\{Command, Console, HelpRenderer, Parser, UsageException};
use function function_exists;

$autoload = is_file(__DIR__ . '/../vendor/autoload.php')
	? __DIR__ . '/../vendor/autoload.php'
	: __DIR__ . '/../../../autoload.php';
if (@!include $autoload) { // @ dependencies may not be installed
	echo 'Install packages using `composer update`';
	exit(1);
}

$console = new Console;

set_exception_handler(function (\Throwable $e) use ($console) {
	$console->writeLine("Error: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", 'red');
	die(2);
});

set_error_handler(function (int $severity, string $message, string $file, int $line) {
	if (($severity & error_reporting()) === $severity) {
		throw new \ErrorException($message, 0, $severity, $file, $line);
	}
	return false;
});

if (function_exists('pcntl_signal')) {
	pcntl_signal(SIGINT, function (): void {
		pcntl_signal(SIGINT, SIG_DFL);
		throw new \Exception('Terminated');
	});
} elseif (function_exists('sapi_windows_set_ctrl_handler')) {
	sapi_windows_set_ctrl_handler(function () {
		throw new \Exception('Terminated');
	});
}

set_time_limit(0);


$console->writeLine("\nCodeChecker version 3.5.0\n-------------------------");

$command = Command::from((new Application($console))->run(...));

try {
	$args = (new Parser)->parse($command);
} catch (UsageException $e) {
	(new HelpRenderer)->renderError($e);
	exit(2);
}

exit($args->run());
