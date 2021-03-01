<?php

declare(strict_types=1);

use Nette\CodeChecker\Result;
use Nette\CodeChecker\Tasks;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


foreach (['ok1.php', 'ok2.php', 'ok3.php'] as $file) {
	$contents = file_get_contents(__DIR__ . '/fixtures/strict-types/' . $file);
	$result = new Result;
	Tasks::strictTypesDeclarationChecker($contents, $result);
	Assert::same([], $result->getMessages());
}

foreach (['ko1.php', 'ko2.php', 'ko3.php'] as $file) {
	$contents = file_get_contents(__DIR__ . '/fixtures/strict-types/' . $file);
	$result = new Result;
	Tasks::strictTypesDeclarationChecker($contents, $result);
	Assert::same([[Result::ERROR, 'Missing declare(strict_types=1)', null]], $result->getMessages());
}
