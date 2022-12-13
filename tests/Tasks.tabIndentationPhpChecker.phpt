<?php

declare(strict_types=1);

use Nette\CodeChecker\Result;
use Nette\CodeChecker\Tasks;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	Tasks::tabIndentationPhpChecker("a
a
\tb
\t\tc
", $result);
	Assert::same([], $result->getMessages());
});


test(function () {
	$result = new Result;
	Tasks::tabIndentationPhpChecker("<?php echo \"a\tb\" ?>", $result);
	Assert::same([], $result->getMessages());
});


test(function () {
	$result = new Result;
	Tasks::tabIndentationPhpChecker("<?php echo 'a\tb' ?>", $result);
	Assert::same([], $result->getMessages());
});


test(function () {
	$result = new Result;
	Tasks::tabIndentationPhpChecker("<?php echo '
a
b
' ?>
a
 \tb
", $result);
	Assert::same([[Result::Error, 'Used space to indent instead of tab', 6]], $result->getMessages());
});


test(function () {
	$result = new Result;
	Tasks::tabIndentationPhpChecker("<?php echo <<<'XX'\n\n\tXX;\n?>", $result);
	Assert::same([], $result->getMessages());
});
