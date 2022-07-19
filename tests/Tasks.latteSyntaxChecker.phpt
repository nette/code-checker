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
	Assert::same([[Result::WARNING, 'Unexpected tag {hello} (on line 1 at column 1)', 1]], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::latteSyntaxChecker('{hello', $result);
	Assert::same([[Result::ERROR, 'Unterminated Latte tag (on line 1 at column 2)', 1]], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::latteSyntaxChecker('{var $x = +}', $result); // invalid PHP code
	Assert::count(1, $result->getMessages());
	Assert::same(Result::ERROR, $result->getMessages()[0][0]);
	Assert::contains('Unexpected end (on line 1 at column 12)', $result->getMessages()[0][1]);
});
