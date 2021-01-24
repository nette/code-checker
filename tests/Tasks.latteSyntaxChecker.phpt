<?php

declare(strict_types=1);

use Nette\CodeChecker\Result;
use Nette\CodeChecker\Tasks;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	Tasks::latteSyntaxChecker('', $result); // no error
	Assert::same([], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::latteSyntaxChecker('{hello}', $result); // ignores unknown macros
	Assert::same([[Result::WARNING, 'Unknown tag {hello}', 1]], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::latteSyntaxChecker('{hello', $result);
	Assert::same([[Result::ERROR, 'Malformed tag contents.', 1]], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::latteSyntaxChecker('{var $x = +}', $result); // invalid PHP code
	Assert::count(1, $result->getMessages());
	Assert::same(Result::ERROR, $result->getMessages()[0][0]);
	Assert::contains('syntax error, unexpected', $result->getMessages()[0][1]);
});
