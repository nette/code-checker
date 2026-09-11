<?php declare(strict_types=1);

use Nette\CodeChecker\Result;
use Nette\CodeChecker\Tasks;
use Tester\Assert;

require __DIR__ . '/bootstrap.php';


test(function () {
	$result = new Result;
	$s = "a\na\n\tb\n\t\tc\n";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([], $result->getMessages());
	Assert::same("a\na\n\tb\n\t\tc\n", $s);
});


test(function () {
	$result = new Result;
	$s = "a\na\n \tb\n\t\tc\n";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Fix, 'used spaces to indent instead of tabs', 3]], $result->getMessages());
	Assert::same("a\na\n\tb\n\t\tc\n", $s);
});


test(function () {
	$result = new Result;
	$s = "a\tb";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Fix, 'unexpected tabulator', 1]], $result->getMessages());
	Assert::same('a b', $s);
});


test(function () {
	$result = new Result;
	$s = "\ta\t\tb";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Fix, 'unexpected tabulator', 1]], $result->getMessages());
	Assert::same("\ta  b", $s);
});


test(function () { // tabulators of a commented-out line belong to its indentation
	$result = new Result;
	$s = "//\t\ta\tb";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Fix, 'unexpected tabulator', 1]], $result->getMessages());
	Assert::same("//\t\ta b", $s);
});


test(function () { // indent width detected as 4
	$result = new Result;
	$s = "if (x) {\n    foo();\n    if (y) {\n        bar();\n    }\n}\n";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Fix, 'used spaces to indent instead of tabs', 2]], $result->getMessages());
	Assert::same("if (x) {\n\tfoo();\n\tif (y) {\n\t\tbar();\n\t}\n}\n", $s);
});


test(function () { // indent width detected as 2
	$result = new Result;
	$s = "a\n  b\n    c\n";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Fix, 'used spaces to indent instead of tabs', 2]], $result->getMessages());
	Assert::same("a\n\tb\n\t\tc\n", $s);
});


test(function () { // indent width detected as 8
	$result = new Result;
	$s = "a\n        b\n                c\n";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Fix, 'used spaces to indent instead of tabs', 2]], $result->getMessages());
	Assert::same("a\n\tb\n\t\tc\n", $s);
});


test(function () { // depths of 12 mean a width of 4, not 3
	$result = new Result;
	$s = "a\n            b\n";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Fix, 'used spaces to indent instead of tabs', 2]], $result->getMessages());
	Assert::same("a\n\t\t\tb\n", $s);
});


test(function () { // indent width detected as 1
	$result = new Result;
	$s = "a\n b\n  c\n";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Fix, 'used spaces to indent instead of tabs', 2]], $result->getMessages());
	Assert::same("a\n\tb\n\t\tc\n", $s);
});


test(function () { // a line pasted into a file indented with tabs
	$result = new Result;
	$s = "a\n\tb\n\t\tc\n        d\n\t\te\n";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Fix, 'used spaces to indent instead of tabs', 4]], $result->getMessages());
	Assert::same("a\n\tb\n\t\tc\n\t\td\n\t\te\n", $s);
});


test(function () { // a block pasted into a file indented with tabs
	$result = new Result;
	$s = "a\n\tb\n        c\n            d\n        e\n";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Fix, 'used spaces to indent instead of tabs', 3]], $result->getMessages());
	Assert::same("a\n\tb\n\t\tc\n\t\t\td\n\t\te\n", $s);
});


test(function () { // mixed tabs and spaces
	$result = new Result;
	$s = "\t  a\n  b\n";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Fix, 'mixed tabs and spaces to indent', 1]], $result->getMessages());
	Assert::same("\t\ta\n\tb\n", $s);
});


test(function () { // indentation that is not a multiple of the detected width, nothing is converted
	$result = new Result;
	$s = "a\n    b\n    c\n    d\n     e\n";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Error, 'Indentation is not a multiple of 4 spaces', 5]], $result->getMessages());
	Assert::same("a\n    b\n    c\n    d\n     e\n", $s);
});


test(function () { // a rare depth does not affect the detected width, it is reported instead
	$result = new Result;
	$s = "function foo() {\n    a();\n  b();\n    c();\n}\n";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Error, 'Indentation is not a multiple of 4 spaces', 3]], $result->getMessages());
	Assert::same("function foo() {\n    a();\n  b();\n    c();\n}\n", $s);
});


test(function () { // stray one-space lines do not turn the width into 1
	$result = new Result;
	$s = "a\n    b\n    c\n    d\n    e\n f\n g\n";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Error, 'Indentation is not a multiple of 4 spaces', 6]], $result->getMessages());
	Assert::same("a\n    b\n    c\n    d\n    e\n f\n g\n", $s);
});


test(function () { // a line holding only whitespace is not reported
	$result = new Result;
	$s = "a\n    b\n   \n    c\n";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Fix, 'used spaces to indent instead of tabs', 2]], $result->getMessages());
	Assert::same("a\n\tb\n   \n\tc\n", $s);
});


test(function () { // phpDoc continuation is left intact
	$result = new Result;
	$s = "\t/**\n\t * @param\n\t */\n";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([], $result->getMessages());
	Assert::same("\t/**\n\t * @param\n\t */\n", $s);
});


test(function () { // phpDoc continuation keeps its asterisk aligned
	$result = new Result;
	$s = "    /**\n     * @param\n     */\n";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Fix, 'used spaces to indent instead of tabs', 1]], $result->getMessages());
	Assert::same("\t/**\n\t * @param\n\t */\n", $s);
});


test(function () { // an error prevents any conversion, including the tabulators
	$result = new Result;
	$s = "a\tb\n     c\n";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Error, 'Indentation is not a multiple of 4 spaces', 2]], $result->getMessages());
	Assert::same("a\tb\n     c\n", $s);
});


test(function () { // CRLF line endings
	$result = new Result;
	$s = "a\r\n    b\r\n   \r\n    c\r\n";
	Tasks::tabIndentationFixer($s, $result);
	Assert::same([[Result::Fix, 'used spaces to indent instead of tabs', 2]], $result->getMessages());
	Assert::same("a\r\n\tb\r\n   \r\n\tc\r\n", $s);
});
