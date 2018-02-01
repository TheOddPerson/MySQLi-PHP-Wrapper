# MySQLi-PHP-Wrapper
A PHP wrapper for MySQLi to be able to perform most MySQL queries in a single line of PHP code<br>

This wrapper is preconfigured to use Smarty variables <br>
more information on Smarty here: https://www.smarty.net/<br>

Permission control has been implemented for Insert / Update / Delete Commands<br>
function HasPermission ($PermissionName) as boolean should be somewhere in your code <br>
where $PermissionName = CanAdd | CanAlter | CanDelete<br>
This behaviour can be skipped by uncommenting the included HasPermission function<br>
<br>
Smarty must be initialized before including ConnectMySQL.php<br>
Otherwise update the following variables<br>
MySQLAddress = MySQL Server Hostname or IP<br>
MySQLUser = MySQL Username<br>
MySQLPass = MySQL Password<br>
MySQLDB = MySQL Database Name<br>
<br>

### All user input should be sanatised via MySQLi_Sanitize($string)

## Reference and Syntax

### MySQLi_Select ($table, $columns, $orderby = '', $direction = 'ASC', $skip = 0, $limit = 'a')    
  Select all rows in a table<br>
  $table = table name as string <br>
  $columns = column names to return as array of strings<br>
  $orderby = column name as string<br>
  $directoin = 'ASC' or 'DESC'<br>
  $skip = MySQL skip as integer<br>
  $limit = MySQL limit as integer (default value of 'a' omits the option)<br>
<br>
### MySQLi_SelectWhere ($table, $columns, $where, $orderby = '0', $direction = 'ASC', $skip = 0, $limit = 'a') 
  Select rows matching a WHERE statement<br>
  See MySQLi_Select <br>
  $where = MySQL WHERE statement as string ie: "ID = 4" or "Name = 'Nick'"  !!Escape user input<br>
<br>
### MySQLi_Search  ($table, $columns, $columnstocompare, $string, $searchtype = 'NATURAL LANGUAGE MODE', $skip = 0, $limit = 'a', $othercolumnstosearch = array()) 
  Perform a search within a table<br>
  See MySQLi_Select<br>
  $columnstocompare = array of column names as string = columns to include in search<br>
  $string = search term as string<br>
  $search type = MySQL search type<br>
  <br>
### MySQLi_Insert ($table, $columns, $values)
  Insert a single row into a table<br>
  $table = table name as string<br>
  $columns = column names as array of string<br>
  $values = values as array of string<br>
<br>
### MySQLi_Update ($table, $columns, $values, $ID, $primaryKey = 'ID')
  Update a single row<br>
  $table = table name as string<br>
  $columns = column names as array of string<br>
  $values = values as array of string  <br>
  $ID = primary key value of row to update as string<br>
  $primaryKey = column name of primary key as string<br>

### MySQLi_Delete ($table, $where, $Name)
  Delete a single row<br>
  $table = table name as string<br>
  $where = MySQL WHERE statement !!Escape user input<br>

### MySQLi_Compare ($table1, $columns, $table2, $compare1, $compare2, $where, $orderby = 'ID', $direction = 'ASC', $skip = 0, $limit = 'a') {
  MySQL Select $columns from $table which are not duplicated in $table2<br>
  $columns1 = columns to compare in $table1 to $table2's $columns2<br>
  $columns2 = columns to compare in $table2 to $table1's $columns1<br>
