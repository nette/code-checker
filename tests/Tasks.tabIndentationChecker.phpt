<?php

use Nette\CodeChecker\Tasks;
use Nette\CodeChecker\Result;
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
	Assert::same([[Result::ERROR, 'Used space to indent instead of tab', 3]], $result->getMessages());
});


test(function () {
	$result = new Result;
	Tasks::tabIndentationChecker("a\tb", $result);
	Assert::same([[Result::ERROR, 'Found unexpected tabulator', 1]], $result->getMessages());
});
