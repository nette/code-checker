Code Checker
============

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/code-checker.svg)](https://packagist.org/packages/nette/code-checker)
[![Tests](https://github.com/nette/code-checker/workflows/Tests/badge.svg?branch=master)](https://github.com/nette/code-checker/actions)
[![Latest Stable Version](https://poser.pugx.org/nette/code-checker/v/stable)](https://github.com/nette/code-checker/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/code-checker/blob/master/license.md)


Introduction
------------

The tool called that checks and possibly repairs some of the formal errors in your source code.

Documentation can be found on the [website](https://doc.nette.org/code-checker). If you like it, **[please make a donation now](https://github.com/sponsors/dg)**. Thank you!


Usage
-----

```
Usage: php code-checker [options]

Options:
    -d <path>             Folder or file to scan (default: current directory)
    -i | --ignore <mask>  Files to ignore
    -f | --fix            Fixes files
    -l | --eol            Convert newline characters
    --no-progress         Do not show progress dots
    --strict-types        Checks whether PHP 7.0 directive strict_types is enabled
```

Without parameters, it checks the current working directory in a read-only mode, with `-f` parameter it fixes files.

Before you get to know the tool, be sure to backup your files first.

You can create a batch file, e.g. `code.bat`, for easier execution of Code-Checker under Windows:

```shell
php path_to\Nette_tools\Code-Checker\code-checker %*
```


What Code-Checker Does?
-----------------------

- removes [BOM](https://doc.nette.org/glossary#toc-bom)
- checks validity of [Latte](https://latte.nette.org) templates
- checks validity of  `.neon`, `.php` and `.json` files
- checks for [control characters](https://doc.nette.org/glossary#toc-control-characters)
- checks whether the file is encoded in UTF-8
- controls misspelled `/* @annotations */` (second asterisk missing)
- removes PHP ending tags `?>` in PHP files
- removes trailing whitespace and unnecessary blank lines from the end of a file
- normalizes line endings to system-default (with the `-l` parameter)


Installation
------------

Install it via Composer. This project is not meant to be run as a dependency, so install it as standalone:

```
composer create-project nette/code-checker
```

Or install it globally via:

```
composer global require nette/code-checker
```

and make sure your global vendor binaries directory is in [your `$PATH` environment variable](https://getcomposer.org/doc/03-cli.md#global).

It requires PHP version 8.0.
