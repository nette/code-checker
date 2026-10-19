<?php declare(strict_types=1);

use Nette\CodeChecker\Result;
use Nette\CodeChecker\Tasks;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	Tasks::phpSyntaxChecker('', $result);
	Assert::same([], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::phpSyntaxChecker('<?php echo 1;', $result);
	Assert::same([], $result->getMessages());
});

test(function () {
	$result = new Result;
	Tasks::phpSyntaxChecker('<?php if', $result);
	Assert::count(1, $result->getMessages());
	Assert::same(Result::Error, $result->getMessages()[0][0]);
	Assert::contains('syntax error, unexpected end of file', $result->getMessages()[0][1]);
});

test(function () {
	foreach (['@phpVersion 99', '@phpVersion >= 99', '@phpVersion < 5', '@phpVersion != ' . PHP_VERSION] as $annotation) {
		$result = new Result;
		Tasks::phpSyntaxChecker("<?php /** $annotation */ if", $result);
		Assert::same([], $result->getMessages(), $annotation);
	}

	$result = new Result;
	Tasks::phpSyntaxChecker('<?php /** @phpVersion >= 5 */ if', $result);
	Assert::count(1, $result->getMessages());
});
