#!/usr/bin/env bash
set -e

./yii walle/setup --interactive=0

exec $@
