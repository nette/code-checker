<?php declare(strict_types=1);

use Nette\CodeChecker\Result;
use Nette\CodeChecker\Tasks;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	Tasks::controlCharactersChecker(" \t \n \r", $result);
	Assert::same([], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::controlCharactersChecker("\x00", $result);
	Assert::same([[Result::Error, 'Contains control characters', 1]], $result->getMessages());
});
