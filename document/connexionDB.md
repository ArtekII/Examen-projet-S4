## Extension PHP et DBDriver correspondant :
- MySQL / MariaDB-> DBriver = MySQLi (extension mysqli)
- PostgreSQL -> DBDriver = Postgre (extension pgsql)
- SQL Server -> DBDriver = SQLSRV (extension sqlsrv)
- Oracle -> DBDriver = OCI8 (extension oci8)
- SQLite -> DBDriver = SQLite3 (extension sqlite3)

## Pour installer extensions :
- sudo apt install php8.x-nom_extension 

## Pour verifier les extensions :
- php -m | grep -i nom_extension

## Commande pour delete dans sqlite3

`DELETE FROM sqlite_sequence WHERE name IN ('clients', 'operateurs', 'type_operations', 'operation_mouvement');`

