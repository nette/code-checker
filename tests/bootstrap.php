<?php declare(strict_types=1);

if (@!include __DIR__ . '/../vendor/autoload.php') { // @ dependencies may not be installed
	echo 'Install Nette Tester using `composer install`';
	exit(1);
}


Tester\Environment::setup();


function test(Closure $function): void
{
	$function();
}
