#!/usr/bin/env

from="/Users/wushuiyong/workspace/git/walle-web";
to="/Users/wushuiyong/workspace/git/walle-web-git/";

cd $from
cp -rf README.md assets components composer.json console controllers mail messages migrations models views web widgets yii $to

gst
