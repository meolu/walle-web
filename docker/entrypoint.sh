#!/usr/bin/env bash
set -e

/opt/walle-web/yii walle/setup --interactive=0

exec $@
