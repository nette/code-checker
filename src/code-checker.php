<?php

/**
 * Source Codes Checker.
 *
 * Copyright (c) 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 */

require __DIR__ . '/loader.php';

use Nette\String;


echo '
CodeChecker version 0.9
-----------------------
';

$options = getopt('d:fl');

if (!$options) { ?>
Usage: php code-checker.php [options]

Options:
	-d <path>  folder to scan (optional)
	-f         fixes files
	-l         convert newline characters

<?php
}

// configuration
$accept = array(
	'*.php', '*.phpc', '*.phpt', '*.inc',
	'*.txt', '*.texy',
	'*.css', '*.js', '*.htm', '*.html', '*.phtml', '*.xml',
	'*.ini', '*.config',
	'*.sh',	'*.bat',
	'.htaccess', '.gitignore',
);

$ignore = array(
	'.*', '*.tmp', 'tmp', 'temp', 'log',
);

$folder = isset($options['d']) ? $options['d'] : getcwd();



// execution
set_time_limit(0);

if (!isset($options['f'])) {
	echo "Running in read-only mode\n";
}

echo "Scanning folder $folder...\n";

$counter = 0;
foreach (Nette\Finder::findFiles($accept)->from($folder)->exclude($ignore) as $file)
{
	echo str_pad(str_repeat('.', $counter++ % 40), 40), "\x0D";

    $orig = $s = file_get_contents($file);
    $shortName = ltrim(str_replace($folder, '', $file), '/\\') . '  ';
    $ext = pathinfo($file, PATHINFO_EXTENSION);

	// search for control characters
	if (String::match($s, '#[\x00-\x08\x0B\x0C\x0E-\x1F]#')) {
		echo "[ERROR] $shortName contains control characters\n";
		continue;
	}

    // remove BOM
    if (substr($s, 0, 3) === "\xEF\xBB\xBF") {
    	$s = substr($s, 3);
		echo "[FIX] $shortName contains BOM\n";
    }

	// search for UTF-8 characters
	if (!String::checkEncoding($s)) {
		echo "[ERROR] $shortName in not valid UTF-8 file\n";
		continue;
	}

	// convert newline characters for the current OS
	if (isset($options['l'])) {
		$old = $s;
		$s = str_replace("\n", PHP_EOL, str_replace("\r\n", "\n", $s));
		if ($old !== $s) {
			echo "[FIX] $shortName contains non-system line-endings\n";
		}
	}

    if ($ext === 'php') {
		// remove trailing ? >
		$tmp = rtrim($s);
		if (substr($tmp, -2) === '?>') {
			$s = substr($tmp, 0, -2);
			echo "[FIX] $shortName contains closing PHP tag ?>\n";
		}
    }

	// trim white-space
	$old = $s;
    $s = String::replace($s, "#[\t ]+(\r?\n)#", '$1'); // right trim
    $s = String::replace($s, "#(\r?\n)+$#", '$1'); // trailing trim
    if ($old !== $s) {
    	$bytes = strlen($old) - strlen($s);
		echo "[FIX] $shortName $bytes bytes of whitespaces\n";
   	}

    // save if option -f is present
    if ($s !== $orig && isset($options['f'])) {
		file_put_contents($file, $s);
   	}
}

echo "\nDone. ";
