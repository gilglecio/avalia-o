#!/bin/bash

PARAMNS=$1
PORT=3000

./vendor/bin/behat --out="public/behat.html" -f html $PARAMNS --stop-on-failure --lang="pt"

xdg-open http://localhost:$PORT/behat.html