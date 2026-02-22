<?php declare(strict_types=1);

use Nette\CodeChecker\Result;
use Nette\CodeChecker\Tasks;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	Tasks::tabIndentationChecker("a
a
\tb
\t\tc
", $result);
	Assert::same([], $result->getMessages());
});


test(function () {
	$result = new Result;
	Tasks::tabIndentationChecker("a
a
 \tb
\t\tc
", $result);
	Assert::same([[Result::Error, 'Used space to indent instead of tab', 3]], $result->getMessages());
});


test(function () {
	$result = new Result;
	Tasks::tabIndentationChecker("a\tb", $result);
	Assert::same([[Result::Error, 'Found unexpected tabulator', 1]], $result->getMessages());
});
