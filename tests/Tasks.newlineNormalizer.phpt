<?php

use Nette\CodeChecker\Tasks;
use Nette\CodeChecker\Result;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	$content = "a\r\nb\nc";
	Tasks::newlineNormalizer($content, $result);
	Assert::same([[Result::FIX, 'contains non-system line-endings', NULL]], $result->getMessages());
	Assert::same('a' . PHP_EOL . 'b' . PHP_EOL . 'c', $content);
});

test(function () {
	$result = new Result;
	$content = 'a' . PHP_EOL . 'b';
	Tasks::newlineNormalizer($content, $result);
	Assert::same([], $result->getMessages());
});
