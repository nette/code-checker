<?php

declare(strict_types=1);

use Nette\CodeChecker\Result;
use Nette\CodeChecker\Tasks;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	Tasks::phpSyntaxChecker('', $result);
	Assert::same([], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::phpSyntaxChecker('<?php echo 1;', $result);
	Assert::same([], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::phpSyntaxChecker('<?php if', $result);
	Assert::count(1, $result->getMessages());
	Assert::same(Result::ERROR, $result->getMessages()[0][0]);
	Assert::contains('syntax error, unexpected end of file', $result->getMessages()[0][1]);
});
