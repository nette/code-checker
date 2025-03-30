<?php

declare(strict_types=1);

use Nette\CodeChecker\Result;
use Nette\CodeChecker\Tasks;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	Tasks::neonSyntaxChecker('a: b', $result);
	Assert::same([], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::neonSyntaxChecker('a: b: c', $result);
	Assert::same([[Result::Error, 'Unexpected \':\' on line 1 at column 5', 1]], $result->getMessages());
});
