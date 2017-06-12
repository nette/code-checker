<?php

use Nette\CodeChecker\Tasks;
use Nette\CodeChecker\Result;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	$content = 'hello';
	Tasks::bomFixer($content, $result);
	Assert::same([], $result->getMessages());
	Assert::same('hello', $content);
});

test(function () {
	$result = new Result;
	$content = "\xEF\xBB\xBFhello";
	Tasks::bomFixer($content, $result);
	Assert::same([[Result::FIX, 'contains BOM', NULL]], $result->getMessages());
	Assert::same('hello', $content);
});
