#!/usr/bin/env sh

set -e

vendor/bin/php-cs-fixer fix --config=.php_cs.dist --using-cache=no --diff --verbose --allow-risky=yes
vendor/bin/phpcbf -p
