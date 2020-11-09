test:
	vendor/bin/phpunit

phpstan:
	vendor/bin/phpstan analyse -c phpstan.neon -a vendor/autoload.php -l 5 src tests

build: test phpstan

php_cs_fixer_fix: php-cs-fixer.phar
	./php-cs-fixer.phar fix --config .php_cs src tests

php_cs_fixer_check: php-cs-fixer.phar
	./php-cs-fixer.phar fix --config .php_cs src tests --dry-run --diff --diff-format=udiff

php-cs-fixer.phar:
	wget https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/v2.16.6/php-cs-fixer.phar && chmod 777 php-cs-fixer.phar
