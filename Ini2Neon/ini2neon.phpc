<?php

/**
 * INI to NEON converter.
 *
 * This file is part of the Nette Framework (http://nette.org)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

require __DIR__ . '/../../Nette-minified/nette.min.php';


echo '
INI2NEON
--------
';

if (empty($_SERVER['argv'][1])) {
	die('Usage: php ini2neon.php [file]');
}

$srcFile = $_SERVER['argv'][1];
if (!is_file($srcFile)) {
	die("Missing file $srcFile");
}


$ini = parse_ini_file($srcFile, TRUE);
$data = array();
foreach ($ini as $secName => $secData) {
	if (is_array($secData)) {
		if (substr($secName, -1) === '!') {
			$secName = substr($secName, 0, -1);

		} else {
			$tmp = array();
			foreach ($secData as $key => $val) {
				$cursor = & $tmp;
				$parts = explode('.', $key, substr($key, 0, 4) === 'php.' ? 2 : 1000); // section php.
				foreach ($parts as $part) {
					if (preg_match('#^(\w+-)+\w+$#', $part)) { // class name?
						$part = strtr($part, '-', '\\');
					}
					if (!isset($cursor[$part]) || is_array($cursor[$part])) {
						$cursor = & $cursor[$part];
					} else {
						throw new Nette\InvalidStateException("Invalid key '$key' in section [$secName] in '$file'.");
					}
				}
				if ($val === '1' || $val === '') { // boolean?
					$val = (bool) $val;
				}
				$cursor = $val;
			}
			$secData = $tmp;
		}
	}
	$data[$secName] = $secData === array() ? NULL : $secData;
}


$destFile = preg_replace('#\.ini$#i', '', $srcFile) . '.neon';
file_put_contents($destFile, Nette\Utils\Neon::encode($data, Nette\Utils\Neon::BLOCK));
echo "Saved to $destFile\n";
