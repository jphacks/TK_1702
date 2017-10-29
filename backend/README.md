# backend

run

```sh
$ tools/prepare.sh
```

```sql
CREATE USER 'slashapp'@'localhost' IDENTIFIED BY 'abcd1234';
CREATE SCHEMA slashapp;
GRANT ON slashapp.* TO 'slashapp'@'localhost';
```

```sh
$ tools/reset_db.sh
```
