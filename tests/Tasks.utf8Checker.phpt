<?php declare(strict_types=1);

use Nette\CodeChecker\{Result, Tasks};
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	Tasks::utf8Checker("\xc5\xbelu\xc5\xa5ou\xc4\x8dk\xc3\xbd", $result); // UTF-8   žluťoučký
	Assert::same([], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::utf8Checker("\xFF", $result);
	Assert::same([[Result::Error, 'Is not valid UTF-8 file', 1]], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::utf8Checker("a\n'\xED\xA0\x80'", $result); // surrogate
	Assert::same([[Result::Error, 'Is not valid UTF-8 file', 2]], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::utf8Checker("a\n\xC0\x80", $result); // overlong
	Assert::same([[Result::Error, 'Is not valid UTF-8 file', 2]], $result->getMessages());
});
