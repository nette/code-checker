Code Checker
============

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/code-checker.svg)](https://packagist.org/packages/nette/code-checker)

A simple tool to check source code against a set of Nette coding standards.

Code Checker requires PHP 5.3.1 or newer. The best way how to install is to use a [Composer](http://doc.nette.org/composer):

```
composer create-project nette/code-checker
```
Note that this is a tool and not a library, so it cannot be installed using the command `composer require`.

Usage:

```
php code-checker.php [options]

Options:
	-d <path>  folder to scan (optional)
	-f         fixes files
	-l         convert newline characters
```
