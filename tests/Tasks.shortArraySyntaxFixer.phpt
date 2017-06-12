<?php

use Nette\CodeChecker\Tasks;
use Nette\CodeChecker\Result;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	$content = '<?php $a = array(array(1 + (1))) ?>';
	Tasks::shortArraySyntaxFixer($content, $result);
	Assert::count(2, $result->getMessages());
	Assert::same('<?php $a = [[1 + (1)]] ?>', $content);
});

test(function () {
	$result = new Result;
	$content = '$a = array(array(1 + (1)))';
	Tasks::shortArraySyntaxFixer($content, $result);
	Assert::same([], $result->getMessages());
	Assert::same('$a = array(array(1 + (1)))', $content);
});
