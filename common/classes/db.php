<?php
/**
 * Class DB
 * PHP MySQLi wrapper class to handle generic database queries and operations and
 * some table specific functions for system codes and messages
 *
 * This software is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * @author Mark Elkington <{!email!}>
 * @link {!website!} system description
 * @copyright 2008-2017 Mark Elkington
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *
 * Methods
 *      db_disconnect
 *      db_query
 *      db_get_row
 *      db_get_rows
 *      db_num_rows
 *      db_exists
 *      db_insert
 *      db_lastid
 *      db_delete
 *      db_update
 *      db_truncate
 *
 *      db_getsystemlabel
 *      db_checksystemcode
 *      db_getsystemcodes
 *      db_getresultcodes
 *      db_getresultcode
 *
 *      db_getinivalues
 *
 *      db_getlinks
 *
 *      db_getmessags
 *      db_createmessage
 *
 *      db_table_to_file
 *      db_table_to_temptable
 *
 *      db_log_errors
 *      db_log_debug
 *
 */
class DB
{
    public $pdo;

    public function __construct($dbname, $username = NULL, $password = NULL, $host = 'localhost', $port = 3306, $options = [])
    {
        $default_options = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];
        $options = array_replace($default_options, $options);
        $dsn = "mysql:host=$host;dbname=$dbname;port=$port;charset=utf8mb4";

        try {
            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
    public function run($sql, $args = NULL)
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);
        //echo "<br>";
        //$stmt->debugDumpParams();
        return $stmt;
    }

    public function db_get_rows( $query )
    {
        //Overwrite the $row var to null
        //if ($_SESSION['sql_debug']) { u_writedbg("db_get_rows: $query",__FILE__,__FUNCTION__,__LINE__); }

        //echo "<pre>".print_r($query,true)."</pre>";
        $rows = $this->run($query, array() )->fetchall();
        if ($rows)
        {
            return $rows;
        }
        else
        {
            return false;
        }


//        if( $this->link->error )
//        {
//            $this->db_log_errors( $this->link->error, $query );
//            return false;
//        }
//        else
//        {
//            $rows = array();
//            while( $r = $results->fetch_assoc() )
//            {
//                $rows[] = $r;
//            }
//            return $rows;
//        }
    }

    public function getinivalues($process_bool, $category="")
    {
        $code = array();

        $query = "SELECT `parameter`, `value` FROM t_ini ";
        if (!empty($category)) { $query.= " WHERE `category` = '$category'"; }
        $rs = $this->run("$query", array() )->fetchall();

        if ($process_bool)
        {
            foreach ($rs as $data)
            {
                if ($data['value'] == "on" or $data['value'] == "off")
                {
                    $data['value']=="on" ? $code["{$data['parameter']}"] = true : $code["{$data['parameter']}"] = false;
                }
                else
                {
                    $code["{$data['parameter']}"] = $data['value'];
                }
            }
        }
        else
        {
            $code = $rs;
        }
        return $code;
    }

    public function insert($table, $args)
    {
        $params = array();
        $fields_str = "";
        foreach($args as $k=>$v)
        {
            $fields_str.= "`$k`, ";
            $key = str_replace("-", "_", $k);
            $params[$key] = $v;
        }
        $fields_str = rtrim($fields_str, ", ");

        $keys_str = "";
        foreach ($params as $k=>$v)
        {
            $keys_str.= ":$k, ";
        }
        $keys_str = rtrim($keys_str, ", ");

        $sql = "INSERT INTO $table ($fields_str) VALUES ($keys_str) ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $insertid = $this->pdo->lastInsertId();                  // id for last record inserted
        $count = $stmt->rowCount();                              // number of rows inserted
        //exit("stopping in INSERT (insertid: $insertid, rowcount: $count");

        return $insertid;
    }

    // this is a debug function to allow viewing the actual insert query
    public function insert2( $table, $args )
    {
        //Make sure the array isn't empty
        if( empty( $args ) ) { return false; }

        $query = "INSERT INTO ". $table;
        $fields = array();
        $values = array();
        foreach( $args as $field => $value )
        {
            $fields[] = "`".$field."`";
            $values[] = "'".addslashes($value)."'";
        }
        $fields = ' (' . implode(', ', $fields) . ')';
        $values = '('. implode(', ', $values) .')';

        $query .= $fields .' VALUES '. $values;
        $params = array();

        echo "<pre>INSERT: $query</pre>";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $count = $stmt->rowCount();                              // number of rows inserted
        $insertid = $this->pdo->lastInsertId();                  // id for last record inserted
//        echo "<br>";
//        $stmt->debugDumpParams();
//        exit("stopping in INSERT2 (insertid: $insertid, rowcount: $count");
        return $insertid;
    }


//    public function update( $table, $variables = array(), $where = array(), $limit = '' )
//    {
//        $dbg_on = false;
//
//        // returns -1 if update failed, 0 if successful but no rows changed, >1 no. rows changed
//        if( empty( $variables ) ) { return -1; }
//
//        $sql = "UPDATE ". $table ." SET ";
//        $updates = array();
//        foreach( $variables as $field => $value )
//        {
//            strtolower($value) == "null" ? $updates[] = "`$field` = null" : $updates[] = "`$field` = '".addslashes($value)."'";
//        }
//        $sql .= implode(', ', $updates);
//
//        // Add the $where clauses as needed
//        if( !empty( $where ) )
//        {
//            $clause = array();
//            foreach( $where as $field => $value ) { $clause[] = "`$field` = '$value'"; }
//            $sql .= ' WHERE '. implode(' AND ', $clause);
//        }
//
//        if( !empty( $limit ) ) { $sql .= ' LIMIT '. $limit; }
//
//
//        $stmt = $this->pdo->prepare($sql);
//        $stmt->execute($params);
//        $update = $this->link->query( $query );
//        $numrows = $this->link->affected_rows;         // might be zero if no records changed
//        if ($dbg_on) { u_writedbg("db_update: $query | rows affected: $numrows | result: $update ",__FILE__,__FUNCTION__,__LINE__); }
//
//        if( $this->link->error )
//        {
//            $this->db_log_errors( $this->link->error, $query );
//            $numrows = -1;
//        }
//
//        return $numrows;
//    }

    /*
       Could use try and catch to handle fatal errors
            try {
            $rs = $db->prepare('SELECT * FROM foo');
            $rs->execute();
            $foo = $rs->fetchAll();
            } catch (Exception $e) {
                die("Oh noes! There's an error in the query!");
                // error logging could go here
            }
     */
}




