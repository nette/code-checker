<?php declare(strict_types=1);

use Nette\CodeChecker\Result;
use Nette\CodeChecker\Tasks;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	$content = "a\r\nb\nc";
	Tasks::newlineNormalizer($content, $result);
	Assert::same([[Result::Fix, 'contains non-system line-endings', PHP_EOL === "\n" ? 1 : 2]], $result->getMessages());
	Assert::same('a' . PHP_EOL . 'b' . PHP_EOL . 'c', $content);
});

test(function () {
	$result = new Result;
	$content = 'a' . PHP_EOL . 'b';
	Tasks::newlineNormalizer($content, $result);
	Assert::same([], $result->getMessages());
});
