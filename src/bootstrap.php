<?php declare(strict_types=1);

/**
 * Source Codes Checker.
 *
 * This file is part of the Nette Framework (https://nette.org)
 */

namespace Nette\CodeChecker;

use Nette\CommandLine\{Command, Console, HelpRenderer, Normalizers, Parser, UsageException};
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


$console->writeLine("\nCodeChecker version 3.4.0\n-------------------------");

$command = new Command(usage: 'php code-checker [options]');
$command->addOption(
	'-d',
	'Folder or file to scan',
	valueName: 'path',
	normalizer: Normalizers::realPath(...),
	default: getcwd(),
	defaultDescription: 'current directory',
	repeatable: true,
);
$command->addOption('--ignore', 'Files to ignore', alias: '-i', valueName: 'mask', repeatable: true);
$command->addFlag('--fix', 'Fixes files', alias: '-f');
$command->addFlag('--eol', 'Convert newline characters', alias: '-l');
$command->addFlag('--only-syntax', 'Check only syntax');
$command->addFlag('--no-progress', 'Do not show progress dots');
$command->addFlag('--version', 'Show version');
$command->addFlag('--short-arrays', hidden: true); // ignored
$command->addFlag('--strict-types', hidden: true); // ignored

try {
	$options = (new Parser)->parse($command);
} catch (UsageException $e) {
	(new HelpRenderer)->renderError($e);
	exit(2);
}

if ($options->isEmpty()) {
	(new HelpRenderer($console))->render($command);
} elseif (isset($options['--version'])) {
	exit(0);
}

$checker = new Checker($console);
$tasks = Tasks::class;

foreach ($options['--ignore'] as $ignore) {
	$checker->ignore[] = $ignore;
}

$checker->readOnly = !isset($options['--fix']);
$checker->showProgress = !isset($options['--no-progress']); // off a terminal the status draws nothing anyway

if (isset($options['--only-syntax'])) {
	$checker->addTask([$tasks, 'phpSyntaxChecker'], '*.php,*.phpt');
	$checker->addTask([$tasks, 'latteSyntaxChecker'], '*.latte');
	$checker->addTask([$tasks, 'neonSyntaxChecker'], '*.neon');
	$checker->addTask([$tasks, 'jsonSyntaxChecker'], '*.json');
	$ok = $checker->run($options['-d']);
	exit($ok ? 0 : 1);
}

$checker->addTask([$tasks, 'controlCharactersChecker']);
$checker->addTask([$tasks, 'bomFixer']);
$checker->addTask([$tasks, 'utf8Checker']);
$checker->addTask([$tasks, 'phpSyntaxChecker'], '*.php,*.phpt');
if (isset($options['--eol'])) {
	$checker->addTask([$tasks, 'newlineNormalizer'], '!*.sh');
}

$checker->addTask([$tasks, 'latteSyntaxChecker'], '*.latte');
$checker->addTask([$tasks, 'neonSyntaxChecker'], '*.neon');
$checker->addTask([$tasks, 'jsonSyntaxChecker'], '*.json');
$checker->addTask([$tasks, 'yamlIndentationChecker'], '*.yml');
$checker->addTask([$tasks, 'trailingWhiteSpaceFixer']);
$checker->addTask([$tasks, 'tabIndentationFixer'], '*.css,*.less,*.js,*.mjs,*.cjs,*.ts,*.json');
$checker->addTask([$tasks, 'unexpectedTabsChecker'], '*.yml');

$ok = $checker->run($options['-d']);

exit($ok ? 0 : 1);
