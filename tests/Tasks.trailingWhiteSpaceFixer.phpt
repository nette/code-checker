<?php declare(strict_types=1);

use Nette\CodeChecker\{Result, Tasks};
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
	Assert::same([[Result::Fix, 'missing newline at end of file', 1]], $result->getMessages());
	Assert::same('hello' . PHP_EOL, $content);
});


test(function () { // trailing spaces without newline
	$result = new Result;
	$content = "hello\nworld  ";
	Tasks::trailingWhiteSpaceFixer($content, $result);
	Assert::same([[Result::Fix, '2 bytes of whitespaces', 2]], $result->getMessages());
	Assert::same("hello\nworld\n", $content);
});


test(function () { // empty
	$result = new Result;
	$content = '';
	Tasks::trailingWhiteSpaceFixer($content, $result);
	Assert::same([], $result->getMessages());
});
