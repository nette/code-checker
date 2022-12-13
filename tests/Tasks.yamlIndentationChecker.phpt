<?php

declare(strict_types=1);

use Nette\CodeChecker\Result;
use Nette\CodeChecker\Tasks;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	Tasks::yamlIndentationChecker('hello', $result);
	Assert::same([], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::yamlIndentationChecker("\thello", $result);
	Assert::same([[Result::Error, 'Used tabs to indent instead of spaces', 1]], $result->getMessages());
});
