<?php declare(strict_types=1);

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
	Assert::same([[Result::Warning, 'Unexpected tag {hello} (on line 1 at column 1)', 1]], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::latteSyntaxChecker('{hello', $result);
	Assert::same([[Result::Error, 'Unexpected end, expecting end of Latte tag started on line 1 at column 1 (on line 1 at column 7)', 1]], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::latteSyntaxChecker('{var $x = +}', $result); // invalid PHP code
	Assert::count(1, $result->getMessages());
	Assert::same(Result::Error, $result->getMessages()[0][0]);
	Assert::same('Unexpected end (on line 1 at column 12)', $result->getMessages()[0][1]);
});
