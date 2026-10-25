<?php declare(strict_types=1);

namespace Nette\CodeChecker;

use Latte;
use Nette;
use Nette\Utils\{Strings, Validators};
use function count, defined, is_resource, strlen;


class Tasks
{
	public static function controlCharactersChecker(string $contents, Result $result): void
	{
		if ($m = Strings::match($contents, '#[\x00-\x08\x0B\x0C\x0E-\x1F]#', PREG_OFFSET_CAPTURE)) {
			$result->error('Contains control characters', self::offsetToLine($contents, (int) $m[0][1]));
		}
	}


	public static function bomFixer(string &$contents, Result $result): void
	{
		if (str_starts_with($contents, "\xEF\xBB\xBF")) {
			$result->fix('contains BOM', 1);
			$contents = substr($contents, 3);
		}
	}


	public static function utf8Checker(string $contents, Result $result): void
	{
		if (!Validators::isUnicode($contents)) {
			preg_match(
				'/^(?:[\x00-\x7F]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})*+/',
				$contents,
				$m,
			);
			/** @var array{string} $m */
			$result->error('Is not valid UTF-8 file', self::offsetToLine($contents, strlen($m[0]) + 1));
		}
	}


	public static function newlineNormalizer(string &$contents, Result $result): void
	{
		$new = str_replace("\n", PHP_EOL, str_replace(["\r\n", "\r"], "\n", $contents));
		if ($new !== $contents) {
			$result->fix('contains non-system line-endings', self::offsetToLine($contents, strlen(Strings::findPrefix([$contents, $new]))));
			$contents = $new;
		}
	}


	public static function phpSyntaxChecker(string $contents, Result $result): void
	{
		if (
			preg_match('#@phpVersion\s+(<=|>=|==|!=|<>|<|>|=)?\s*(\d[\w.-]*)#i', $contents, $m)
			&& !version_compare(PHP_VERSION, $m[2], $m[1] ?: '>=')
		) {
			return;
		}
		$php = defined('PHP_BINARY') ? PHP_BINARY : 'php';
		$stdin = tmpfile();
		fwrite($stdin, $contents);
		fseek($stdin, 0);
		$process = proc_open(
			$php . ' -l -d display_errors=1',
			[$stdin, ['pipe', 'w'], ['pipe', 'w']],
			$pipes,
			null,
			null,
			['bypass_shell' => true],
		);
		if (!is_resource($process)) {
			$result->warning('Unable to lint PHP code');
			return;
		}
		$error = stream_get_contents($pipes[1]);
		if (proc_close($process)) {
			$error = strip_tags(explode("\n", $error)[1]);
			$line = preg_match('# on line (\d+)$#', $error, $m) ? (int) $m[1] : null;
			$result->error('Invalid PHP code: ' . $error, $line);
		}
	}


	public static function latteSyntaxChecker(string $contents, Result $result): void
	{
		$latte = new Latte\Engine;
		$latte->setLoader(new Latte\Loaders\StringLoader);
		$latte->enablePhpLinter(PHP_BINARY);
		$latte->addExtension(new Latte\Essential\TranslatorExtension(null));
		$latte->addExtension(new Nette\Bridges\ApplicationLatte\UIExtension(null));
		$latte->addExtension(new Nette\Bridges\CacheLatte\CacheExtension(new Nette\Caching\Storages\DevNullStorage));
		$latte->addExtension(new Nette\Bridges\FormsLatte\FormsExtension);
		$latte->addExtension(new Nette\Bridges\AssetsLatte\LatteExtension(new Nette\Assets\Registry));

		try {
			$code = $latte->compile($contents);
			static::phpSyntaxChecker($code, $result);

		} catch (Latte\CompileException $e) {
			if (!preg_match('#Unexpected (tag|attribute)#A', $e->getMessage())) {
				$result->error($e->getMessage(), $e->position?->line);
			} else {
				$result->warning($e->getMessage(), $e->position?->line);
			}
		}
	}


	public static function neonSyntaxChecker(string $contents, Result $result): void
	{
		try {
			Nette\Neon\Neon::decode($contents);
		} catch (Nette\Neon\Exception $e) {
			$line = preg_match('# on line (\d+)#', $e->getMessage(), $m) ? (int) $m[1] : null;
			$result->error($e->getMessage(), $line);
		}
	}


	public static function jsonSyntaxChecker(string $contents, Result $result): void
	{
		try {
			Nette\Utils\Json::decode($contents);
			if (trim($contents) === '') {
				$result->error('Syntax error');
			}
		} catch (Nette\Utils\JsonException $e) {
			$result->error($e->getMessage());
		}
	}


	public static function yamlIndentationChecker(string $contents, Result $result): void
	{
		if (preg_match('#^\t#m', $contents, $m, PREG_OFFSET_CAPTURE)) {
			$result->error('Used tabs to indent instead of spaces', self::offsetToLine($contents, $m[0][1]));
		}
	}


	public static function trailingWhiteSpaceFixer(string &$contents, Result $result): void
	{
		$new = Strings::replace($contents, '#[\t ]+(\r?\n)#', '$1'); // right trim
		$eol = preg_match('#\r?\n#', $new, $m) ? $m[0] : PHP_EOL;
		$new = rtrim($new); // trailing trim
		if ($new !== '') {
			$new .= $eol;
		}
		if ($new !== $contents) {
			$bytes = strlen($contents) - strlen($new);
			$len = min(strlen($contents), strlen(Strings::findPrefix([$contents, $new])) + 1);
			$result->fix("$bytes bytes of whitespaces", self::offsetToLine($contents, $len));
			$contents = $new;
		}
	}


	public static function tabIndentationFixer(string &$contents, Result $result): void
	{
		$width = self::detectIndentWidth($contents);
		$lines = explode("\n", $contents);
		$indentFix = $tabFix = null;
		$indentMessage = '';

		foreach ($lines as $i => $line) {
			$length = strspn($line, "\t ");
			$old = substr($line, 0, $length);
			$firstChar = $line[$length] ?? '';

			// tabulators after the indentation, replaced first because it keeps the offsets
			$start = str_starts_with($line, '//') ? 2 + strspn($line, "\t", 2) : $length;
			$rest = substr($line, $start);
			if (str_contains($rest, "\t")) {
				$line = substr($line, 0, $start) . strtr($rest, "\t", ' ');
				$tabFix ??= $i + 1;
			}

			// lines indented with tabs only and lines holding no code need no conversion
			if (str_contains($old, ' ') && $firstChar !== '' && $firstChar !== "\r") {
				$docBlock = $firstChar === '*'; // its asterisk is aligned by one extra space
				$column = self::indentColumn($old, $width) - ($docBlock ? 1 : 0);

				// such a line would end up as tabs followed by spaces, so report it and convert
				// nothing, Checker would throw the fixed contents away anyway
				if ($column % $width) {
					$result->error("Indentation is not a multiple of $width spaces", $i + 1);
					return;
				}

				$indent = str_repeat("\t", intdiv($column, $width)) . ($docBlock ? ' ' : '');
				if ($indent !== $old) {
					$line = $indent . substr($line, $length);
					if ($indentFix === null) {
						$indentFix = $i + 1;
						$indentMessage = $old[0] === "\t" ? 'mixed tabs and spaces to indent' : 'used spaces to indent instead of tabs';
					}
				}
			}

			$lines[$i] = $line;
		}

		if ($indentFix) {
			$result->fix($indentMessage, $indentFix);
		}
		if ($tabFix) {
			$result->fix('unexpected tabulator', $tabFix);
		}
		if ($indentFix || $tabFix) {
			$contents = implode("\n", $lines);
		}
	}


	/**
	 * Guesses how many spaces make one indentation level. Only widths that occur in the wild are
	 * considered and the largest matching one wins, i.e. depths 4, 8, 12 mean a width of 4, not 2.
	 * Rare depths are ignored, so that a mistyped line does not shrink the width for the whole file.
	 */
	private static function detectIndentWidth(string $contents): int
	{
		preg_match_all('#^ ++(?=[^\s*])#m', $contents, $m); // lines indented with spaces only
		$counts = array_count_values(array_map('strlen', $m[0]));
		$limit = max(2, count($m[0]) / 10);
		$frequent = array_filter($counts, fn($count) => $count >= $limit) ?: $counts;

		$gcd = 0;
		foreach (array_keys($frequent) as $depth) {
			$gcd = self::gcd($gcd, $depth);
		}

		// lines pasted into a file indented with tabs, eight spaces there are two levels rather than one
		$candidates = preg_match('#^\t#m', $contents) ? [4, 3, 2] : [8, 4, 3, 2];
		foreach ($candidates as $width) {
			if ($gcd && $gcd % $width === 0) {
				return $width;
			}
		}

		// a genuine one-space file has both levels, unlike a file with a few stray one-space lines
		return isset($frequent[1], $frequent[2]) ? 1 : 4;
	}


	private static function gcd(int $a, int $b): int
	{
		return $b ? self::gcd($b, $a % $b) : $a;
	}


	/** Returns the column where the given indentation ends */
	private static function indentColumn(string $indent, int $width): int
	{
		$column = 0;
		for ($i = 0; $i < strlen($indent); $i++) {
			$column = $indent[$i] === "\t"
				? (intdiv($column, $width) + 1) * $width
				: $column + 1;
		}

		return $column;
	}


	public static function unexpectedTabsChecker(string $contents, Result $result): void
	{
		if (($pos = strpos($contents, "\t")) !== false) {
			$result->error('Found unexpected tabulator', self::offsetToLine($contents, $pos));
		}
	}


	private static function offsetToLine(string $s, int $offset): int
	{
		return $offset ? substr_count($s, "\n", 0, $offset) + 1 : 1;
	}
}
