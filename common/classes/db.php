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
//        $val_str = "";
        foreach ($params as $k=>$v)
        {
            $keys_str.= ":$k, ";
//            $val_str.= "$v, ";
        }
        $keys_str = rtrim($keys_str, ", ");

//        $sql0 = "INSERT INTO $table ($fields_str) VALUES ($val_str) ";
        $sql = "INSERT INTO $table ($fields_str) VALUES ($keys_str) ";
//        echo "<pre>$sql0</pre>";
//        echo "<pre>$sql</pre>";
        //echo "<pre>".print_r($params,true)."</pre>";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $insertid = $this->pdo->lastInsertId();
//        echo "<br>";
//        $stmt->debugDumpParams();
//        exit("stopping in INSERT");
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
        $insertid = $this->pdo->lastInsertId();
        //echo "<br>";
        //$stmt->debugDumpParams();
        //exit("stopping in INSERT2");
        return $insertid;
    }

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




