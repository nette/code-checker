<?php

declare(strict_types=1);

use Nette\CodeChecker\Result;
use Nette\CodeChecker\Tasks;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	$content = 'hello
world
';
	Tasks::trailingWhiteSpaceFixer($content, $result);
	Assert::same([], $result->getMessages());
});


test(function () { // trailing spaces
	$result = new Result;
	$content = 'hello
world

';
	Tasks::trailingWhiteSpaceFixer($content, $result);
	Assert::count(1, $result->getMessages());
	Assert::same('hello
world
', $content);
});


test(function () { // missing spaces
	$result = new Result;
	$content = 'hello';
	Tasks::trailingWhiteSpaceFixer($content, $result);
	Assert::count(1, $result->getMessages());
	Assert::same('hello' . PHP_EOL, $content);
});


test(function () { // empty
	$result = new Result;
	$content = '';
	Tasks::trailingWhiteSpaceFixer($content, $result);
	Assert::same([], $result->getMessages());
});
