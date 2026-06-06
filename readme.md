Code Checker
============

[![Downloads this Month](https://img.shields.io/packagist/dm/nette/code-checker.svg)](https://packagist.org/packages/nette/code-checker)
[![Tests](https://github.com/nette/code-checker/workflows/Tests/badge.svg?branch=master)](https://github.com/nette/code-checker/actions)
[![Latest Stable Version](https://poser.pugx.org/nette/code-checker/v/stable)](https://github.com/nette/code-checker/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/nette/code-checker/blob/master/license.md)


Introduction
------------

Code Checker is a command-line tool that scans your source code for common formal errors – and can fix many of them automatically. It works with PHP, Latte, NEON, JSON and YAML files, checking encoding, line endings, indentation, trailing whitespace and more.

It runs as a standalone application, not as a project dependency.

Documentation can be found on the [website](https://doc.nette.org/tools/code-checker). If you like it, **[please make a donation now](https://github.com/sponsors/dg)**. Thank you!


Installation
------------

Install it globally via Composer:

```shell
composer global require nette/code-checker
```

Make sure your global Composer `bin` directory is in your [`PATH`](https://getcomposer.org/doc/03-cli.md#global).
The `code-checker` command is then available from anywhere, on any operating system.

Alternatively, install it as a standalone project:

```shell
composer create-project nette/code-checker
```

It requires PHP 8.0 or higher.


Usage
-----

By default, Code Checker runs in **read-only mode** and only reports the problems it finds:

```shell
code-checker
```

To actually repair the files, add `--fix`. **Back up your files first**, or run it on a clean working tree so you can review the changes afterwards with `git diff`:

```shell
code-checker --fix
```

You can limit the scan to a specific path, skip files, or run faster syntax-only checks:

```shell
code-checker -d src --ignore "temp/*"
code-checker --only-syntax
```

In read-only mode the tool exits with code `0` when everything is fine and `1` when any problem is found, so it fits nicely into CI pipelines.

Full list of options:

```
Usage: code-checker [options]

Options:
    -d <path>             Folder or file to scan (default: current directory)
    -i | --ignore <mask>  Files to ignore
    -f | --fix            Fix the files
    -l | --eol            Normalize line endings to the system default
    --only-syntax         Check syntax only (faster)
    --no-progress         Do not show progress dots
    --version             Show version
```


What Code Checker Does
----------------------

- checks the syntax of **PHP**, **Latte**, **NEON** and **JSON** files
- removes the [BOM](https://doc.nette.org/glossary#toc-bom)
- verifies that files are valid **UTF-8**
- checks for [control characters](https://doc.nette.org/glossary#toc-control-characters)
- detects malformed phpDoc comments around annotations (e.g. `/* @var` instead of `/** @var`)
- checks that PHP files declare `strict_types=1`
- enforces tabs for indentation in PHP, CSS, JS and TS files, and spaces in YAML
- removes trailing whitespace and blank lines at the end of files
- normalizes line endings to the system default (with the `-l` parameter)
