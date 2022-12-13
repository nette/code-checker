<?php

declare(strict_types=1);

use Nette\CodeChecker\Result;
use Nette\CodeChecker\Tasks;
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
	Assert::same([[Result::Warning, 'Invalid escape sequence \\X in double quoted string', 1]], $result->getMessages());
});
