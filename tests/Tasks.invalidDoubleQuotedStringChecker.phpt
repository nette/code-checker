<?php

use Nette\CodeChecker\Tasks;
use Nette\CodeChecker\Result;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	Tasks::invalidDoubleQuotedStringChecker('<?php $a = "\x10"', $result);
	Assert::same([], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::invalidDoubleQuotedStringChecker('<?php $a = "\X10"', $result);
	Assert::same([[Result::WARNING, 'Invalid escape sequence \\X in double quoted string', 1]], $result->getMessages());
});
