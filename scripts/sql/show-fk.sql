SELECT
	k.table_schema as 'schema',
	k.table_name as 'table' ,
	k.column_name as 'column',
	k.constraint_name as 'constraint_name'

FROM 
	`information_schema`.`KEY_COLUMN_USAGE` k
WHERE 
	k.referenced_column_name is not null
