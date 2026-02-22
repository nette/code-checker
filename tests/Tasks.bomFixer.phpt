<?php declare(strict_types=1);

use Nette\CodeChecker\Result;
use Nette\CodeChecker\Tasks;
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
	Assert::same([[Result::Fix, 'contains BOM', 1]], $result->getMessages());
	Assert::same('hello', $content);
});
