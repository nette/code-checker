<?php

declare(strict_types=1);

use Nette\CodeChecker\Result;
use Nette\CodeChecker\Tasks;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	Tasks::unexpectedTabsChecker("a\nb", $result);
	Assert::same([], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::unexpectedTabsChecker("\t", $result);
	Assert::same([[Result::ERROR, 'Found unexpected tabulator', 1]], $result->getMessages());
});
