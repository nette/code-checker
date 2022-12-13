<?php

declare(strict_types=1);

use Nette\CodeChecker\Result;
use Nette\CodeChecker\Tasks;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	$content = '<?php echo 1 ?>';
	Tasks::trailingPhpTagRemover($content, $result);
	Assert::same([[Result::Fix, 'contains closing PHP tag ?>', 1]], $result->getMessages());
	Assert::same('<?php echo 1 ', $content);
});

test(function () {
	$result = new Result;
	$content = "<?php echo 1 ?>\r\n ";
	Tasks::trailingPhpTagRemover($content, $result);
	Assert::same([[Result::Fix, 'contains closing PHP tag ?>', 1]], $result->getMessages());
	Assert::same('<?php echo 1 ', $content);
});

test(function () {
	$result = new Result;
	$content = '<?php echo 1';
	Tasks::trailingPhpTagRemover($content, $result);
	Assert::same([], $result->getMessages());
});
