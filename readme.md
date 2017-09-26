Code Checker
============

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/code-checker.svg)](https://packagist.org/packages/nette/code-checker)
[![Build Status](https://travis-ci.org/nette/code-checker.svg?branch=master)](https://travis-ci.org/nette/code-checker)
[![Latest Stable Version](https://poser.pugx.org/nette/code-checker/v/stable)](https://github.com/nette/code-checker/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/code-checker/blob/master/license.md)


Introduction
------------

A simple tool to check source code against a set of Nette coding standards.

Documentation can be found on the [website](https://doc.nette.org/code-checker).


Installation
------------

The recommended way to install is via Composer:

```
composer create-project nette/code-checker
```

Note that this is a tool and not a library, so it cannot be installed using the command `composer require`.

It requires PHP version 5.6 and supports PHP up to 7.2.


Usage
-----

```
code-checker [options]

Options:
	-d <path>             folder to scan (default: current directory)
	-i | --ignore <mask>  files or directories to ignore (can be used multiple times)
	-f | --fix            fixes files
	-l | --eol            convert newline characters
	--no-progress         do not show progress dots
	--short-arrays        enforces PHP 5.4 short array syntax
	--strict-types        checks whether PHP 7.0 directive strict_types is enabled
```
