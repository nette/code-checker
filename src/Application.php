<?php declare(strict_types=1);

namespace Nette\CodeChecker;

use Nette\CommandLine\{Attributes as Cli, Command, Console, HelpRenderer, Normalizers, ParseResult};


final readonly class Application
{
	public function __construct(
		private Console $console,
	) {
	}


	/**
	 * @param  list<string>  $paths
	 * @param  list<string>  $ignore
	 */
	#[Cli\Command(usage: 'php code-checker [options]')]
	public function run(
		ParseResult $args,
		Command $command,
		#[Cli\Option(
			'Folder or file to scan',
			valueName: 'path',
			normalizer: [Normalizers::class, 'realPath'],
			defaultDescription: 'current directory',
			repeatable: true,
			name: '-d',
		)]
		array $paths = [],
		#[Cli\Option('Files to ignore', alias: '-i', valueName: 'mask', repeatable: true)]
		array $ignore = [],
		#[Cli\Flag('Fixes files', alias: '-f')]
		?bool $fix = null,
		#[Cli\Flag('Convert newline characters', alias: '-l')]
		?bool $eol = null,
		#[Cli\Flag('Check only syntax')]
		?bool $onlySyntax = null,
		#[Cli\Flag('Do not show progress dots')]
		?bool $noProgress = null,
		#[Cli\Flag('Show version')]
		?bool $version = null,
		#[Cli\Flag(hidden: true)] // ignored
		?bool $shortArrays = null,
		#[Cli\Flag(hidden: true)] // ignored
		?bool $strictTypes = null,
	): int
	{
		if ($args->isEmpty()) {
			(new HelpRenderer($this->console))->render($command);
		} elseif ($version) {
			return 0;
		}

		$checker = new Checker($this->console);
		$tasks = Tasks::class;

		foreach ($ignore as $mask) {
			$checker->ignore[] = $mask;
		}

		$checker->readOnly = !$fix;
		$checker->showProgress = !$noProgress; // off a terminal the status draws nothing anyway
		$paths = $paths ?: [(string) getcwd()];

		if ($onlySyntax) {
			$checker->addTask([$tasks, 'phpSyntaxChecker'], '*.php,*.phpt');
			$checker->addTask([$tasks, 'latteSyntaxChecker'], '*.latte');
			$checker->addTask([$tasks, 'neonSyntaxChecker'], '*.neon');
			$checker->addTask([$tasks, 'jsonSyntaxChecker'], '*.json');
			return $checker->run($paths) ? 0 : 1;
		}

		$checker->addTask([$tasks, 'controlCharactersChecker']);
		$checker->addTask([$tasks, 'bomFixer']);
		$checker->addTask([$tasks, 'utf8Checker']);
		$checker->addTask([$tasks, 'phpSyntaxChecker'], '*.php,*.phpt');
		if ($eol) {
			$checker->addTask([$tasks, 'newlineNormalizer'], '!*.sh');
		}

		$checker->addTask([$tasks, 'latteSyntaxChecker'], '*.latte');
		$checker->addTask([$tasks, 'neonSyntaxChecker'], '*.neon');
		$checker->addTask([$tasks, 'jsonSyntaxChecker'], '*.json');
		$checker->addTask([$tasks, 'yamlIndentationChecker'], '*.yml');
		$checker->addTask([$tasks, 'trailingWhiteSpaceFixer']);
		$checker->addTask([$tasks, 'tabIndentationFixer'], '*.css,*.less,*.js,*.mjs,*.cjs,*.ts,*.json');
		$checker->addTask([$tasks, 'unexpectedTabsChecker'], '*.yml');

		return $checker->run($paths) ? 0 : 1;
	}
}
