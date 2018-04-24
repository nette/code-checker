<?php

declare(strict_types=1);

use Nette\CodeChecker\Result;
use Nette\CodeChecker\Tasks;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	Tasks::invalidPhpDocChecker('<?php ?>', $result);
	Assert::same([], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::invalidPhpDocChecker('<?php /** @var */ ?>', $result);
	Assert::same([], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::invalidPhpDocChecker('<?php /* comment */ ?>', $result);
	Assert::same([], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::invalidPhpDocChecker('/* @not php */', $result);
	Assert::same([], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::invalidPhpDocChecker('<?php /* @var */ ?>', $result);
	Assert::same([[Result::WARNING, 'Missing /** in phpDoc comment', 1]], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::invalidPhpDocChecker('<?php /* email@gmail.com */ ?>', $result);
	Assert::same([], $result->getMessages());
});
