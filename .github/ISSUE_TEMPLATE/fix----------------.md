---
name: Fix-我解决了一个问题，想帮助其它人
about: 人人为我，我为人人
title: ''
labels: helps
assignees: meolu

---

**问题**：错误信息
样例：执行flask db upgrade 后提示Can`t connet to local MYSQL server through socket "/var/lib/mysql/mysql.sock",

**解决**：
可将mysql.sock做一个软连接，比如mysql.sock文件在/usr/local/mysql/mysqld.sock，则执行ln -s /usr/local/mysqld.sock /var/lib/mysql/mysql.sock 即可
