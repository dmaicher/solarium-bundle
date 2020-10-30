test:
	vendor/bin/phpunit

phpstan:
	vendor/bin/phpstan analyse -c phpstan.neon -a vendor/autoload.php -l 5 src tests

build: test phpstan

php_cs_fixer_fix:
	vendor/bin/php-cs-fixer fix --config .php_cs src tests

php_cs_fixer_check:
	vendor/bin/php-cs-fixer fix --config .php_cs src tests --dry-run --diff --diff-format=udiff
