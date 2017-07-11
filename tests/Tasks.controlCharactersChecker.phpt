<?php

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
	Assert::same([[Result::ERROR, 'Contains control characters', null]], $result->getMessages());
});
