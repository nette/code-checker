Code Checker
============

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/code-checker.svg)](https://packagist.org/packages/nette/code-checker)
[![Build Status](https://travis-ci.org/nette/code-checker.svg?branch=master)](https://travis-ci.org/nette/code-checker)
[![Latest Stable Version](https://poser.pugx.org/nette/code-checker/v/stable)](https://github.com/nette/code-checker/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/code-checker/blob/master/license.md)

A simple tool to check source code against a set of Nette coding standards.

Code Checker requires PHP 5.4 or newer. The best way how to install is to use a [Composer](https://doc.nette.org/composer):

```
composer create-project nette/code-checker
```
Note that this is a tool and not a library, so it cannot be installed using the command `composer require`.

Usage:

```
php code-checker.php [options]

Options:
	-d <path>             folder to scan (default: current directory)
	-i | --ignore <mask>  files or directories to ignore (can be used multiple times)
	-f | --fix            fixes files
	-l | --eol            convert newline characters
	--short-arrays        enforces PHP 5.4 short array syntax
```
