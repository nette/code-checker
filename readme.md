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

If you like Nette, **[please make a donation now](https://nette.org/donate)**. Thank you!


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
	--strict-types        checks whether PHP 7.0 directive strict_types is enabled
```


Installation
------------

It requires PHP version 7.1 and supports PHP up to 7.3. (Version 2.x works with PHP 5.6.)

Install it via Composer. This project is not meant to be run as a dependency, so install it as standalone:

```
composer create-project nette/code-checker
```

Or install it globally via:

```
composer global require nette/code-checker
```

and make sure your global vendor binaries directory is in [your `$PATH` environment variable](https://getcomposer.org/doc/03-cli.md#global).
