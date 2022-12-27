# csv table
This code access a `./data.csv` file with at least the following columns (as the first row)

```csv
"id","stamp"
```

* `add.php` will append the posted row onto the end of `./data.csv`
* `edit.php?id=00` will replace the row matching ID with the posted row

