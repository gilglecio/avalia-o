#!/bin/bash

PORT=3000

xdg-open http://localhost:$PORT/
php -S localhost:$PORT -t public .htrouter.php