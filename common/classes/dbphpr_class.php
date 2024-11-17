<?php
/**
 * THIS IS A CLONE OF THE DB_CLASS WHICH CAN BE USED IN phprunner APPLICATIONS WITHOUT CLASHING WITH INTERNAL
 * DB CLASS.
 *
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
class DBPHPR
{
    private $link;

    // FIXME
    // provide a method to get the last query used - write query to the object with an private put_last_query
    // then a simple get_last_query which can be called from app to help with debugging
    // could include an object to print it
    // query would in array with fields of sql, type, rows affected, status

    public function __construct()
	{
	    // echo "<pre>".print_r($_SESSION,true)."</pre>";

	    global $connection;
		mb_internal_encoding( 'UTF-8' );
		mb_regex_encoding( 'UTF-8' );
		$this->link = new mysqli( $_SESSION['db_host'], $_SESSION['db_user'], $_SESSION['db_pass'], $_SESSION['db_name']);
		$this->link->set_charset( "utf8" );
		
        if( $this->link->connect_errno )
        {
            $this->db_log_errors( "Connect failed", $this->link->connect_error );
            u_exitnicely("dbphpr_class.php",0,"database connection error - host: {$_SESSION['db_host']}, user: {$_SESSION['db_user']}, dbase: {$_SESSION['db_name']}",
                "check configuration setup - either fix configuration or set database user to match configuration",
                array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__,
                "calledby" => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function'], "args" => func_get_args()));
        }
	}

    public function db_disconnect()
    {
		$this->link->close();
	}

    public function db_query($query)
    {
        if ($_SESSION['sql_debug']) { u_writedbg("QUERY: $query",__FILE__,__FUNCTION__,__LINE__); }
        $results = $this->link->query( $query );
        return $results;
    }

    public function db_get_row( $query )
    {
        if ($_SESSION['sql_debug']) { u_writedbg("QUERY: $query",__FILE__,__FUNCTION__,__LINE__); }
        $row = $this->link->query( $query );
        if( $this->link->error )
        {
            $this->db_log_errors( $this->link->error, $query );
            return false;
        }
        else
        {
            $r = $row->fetch_assoc();
            return $r;   
        }
    }
    

    public function db_get_rows( $query )
    {
        //Overwrite the $row var to null
        if ($_SESSION['sql_debug']) { u_writedbg("QUERY: $query",__FILE__,__FUNCTION__,__LINE__); }
        $results = $this->link->query( $query );

        if( $this->link->error )
        {
            $this->db_log_errors( $this->link->error, $query );
            return false;
        }
        else
        {
            $rows = array();
            while( $r = $results->fetch_assoc() )
            {
                $rows[] = $r;
            }
            return $rows;   
        }
    }
    

    public function db_num_rows( $query )
    {
        if ($_SESSION['sql_debug']) { u_writedbg("QUERY: $query",__FILE__,__FUNCTION__,__LINE__); }
        $num_rows = $this->link->query( $query );
        if( $this->link->error )
        {
            $this->db_log_errors( $this->link->error, $query );
            return $this->link->error;
        }
        else
        {
            return $num_rows->num_rows;
        }
    }


    public function db_exists( $table = '', $check_val = '', $where = array() )
    {
        if( empty($table) OR empty($check_val) OR empty($params) )
        {
            return false;
        }


        if( empty( $where ) )
        {
            $where_clause = "";
        }
        else
        {
            $clause = array();
            foreach( $where as $field => $value )
            {
                $clause[] = "`$field` = '$value'";
            }
            $where_clause = ' WHERE 1=1 AND '. implode(' AND ', $clause);
        }

        $rs_check = "SELECT $check_val FROM ".$table." $where_clause";
    	$number = $this->db_num_rows( $rs_check );
        if( $number === 0 )
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    

    public function db_insert( $table, $variables = array() )
    {
        //Make sure the array isn't empty
        if( empty( $variables ) )
        {
            return false;
        }
        
        $query = "INSERT INTO ". $table;
        $fields = array();
        $values = array();
        foreach( $variables as $field => $value )
        {
            $fields[] = "`".$field."`";
            $values[] = "'".addslashes($value)."'";
        }
        $fields = ' (' . implode(', ', $fields) . ')';
        $values = '('. implode(', ', $values) .')';
        
        $query .= $fields .' VALUES '. $values;

        if ($_SESSION['sql_debug']) { u_writedbg("QUERY: $query",__FILE__,__FUNCTION__,__LINE__); }
        $insert  = $this->link->query( $query );
        $numrows = $this->link->affected_rows;
        //echo "<pre>INSERT QUERY: ".$query."<br>".$numrows."</pre>";
        
        if( $this->link->error )
        {
            //return false; 
            $this->db_log_errors( $this->link->error, $query );
            return false;
        }
        else
        {
            return true;
        }
    }
       

    public function db_lastid()
    {
        return $this->link->insert_id;
    }
    

    public function db_delete( $table, $where = array(), $limit = '' )
    {
        //Delete clauses require a where param, otherwise use "truncate"
        if( empty( $where ) )
        {
            return false;
        }
        
        $query = "DELETE FROM ". $table;
        $clause = array();
        foreach( $where as $field => $value )
        {
            $clause[] = "`$field` = '$value'";
        }
        $query .= " WHERE ". implode(' AND ', $clause);
        
        if( !empty( $limit ) )
        {
            $query .= " LIMIT ". $limit;
        }

        if ($_SESSION['sql_debug']) { u_writedbg("QUERY: $query",__FILE__,__FUNCTION__,__LINE__); }
        $delete = $this->link->query( $query );
        $numrows = $this->link->affected_rows;
        //echo "<pre>DELETE QUERY: ".$query."<br>".$numrows."</pre>";

        if( $this->link->error )
        {
            $this->db_log_errors( $this->link->error, $query );
            return false;
        }
        else
        {
            return $numrows;
        }
    }


    public function db_update( $table, $variables = array(), $where = array(), $limit = '' )
    {
        // returns -1 if update failed, 0 if successful but no rows changed, >1 no. rows changed
        if( empty( $variables ) )
        {
            return -1;
        }
        $query = "UPDATE ". $table ." SET ";
        $updates = array();
        foreach( $variables as $field => $value )
        {            
            $updates[] = "`$field` = '".addslashes($value)."'";
        }
        $query .= implode(', ', $updates);
        
        //Add the $where clauses as needed
        if( !empty( $where ) )
        {
            $clause = array();
            foreach( $where as $field => $value )
            {
                $clause[] = "`$field` = '$value'";
                //echo "<pre>field = $field</pre>";
            }
            $query .= ' WHERE '. implode(' AND ', $clause);   
        }
        
        if( !empty( $limit ) )
        {
            $query .= ' LIMIT '. $limit;
        }

        if ($_SESSION['sql_debug']) { u_writedbg("QUERY: $query",__FILE__,__FUNCTION__,__LINE__); }
        //echo "<pre>$query</pre>";
        //error_log("UPD SQL: $query \n",3, $_SESSION['dbg_file']);
        $update = $this->link->query( $query );
        $numrows = $this->link->affected_rows;         // might be zero if no records changed
        //echo "<pre> UPDATE QUERY: ".$query."<br>".$numrows."</pre>";


        if( $this->link->error )
        {
            $this->db_log_errors( $this->link->error, $query );
            $numrows = -1;
        }
        //u_writedbg("$query|$numrows", __FILE__, __FUNCTION__, __LINE__);
        return $numrows;
    }
    

    public function db_truncate( $tables = array() )
    {
        $truncated = 0;
        if( !empty( $tables ) )
        {
            foreach( $tables as $table )
            {
                $query = "TRUNCATE TABLE `".trim($table)."`";
                if ($_SESSION['sql_debug']) { u_writedbg("QUERY: $query",__FILE__,__FUNCTION__,__LINE__); }
                $this->link->query( $query );
                if( !$this->link->error )
                {
                    $truncated++;
                }
            }
        }
        return $truncated;
    }


    public function db_getsystemlabel($codetype, $code)
    {
        $query = "SELECT label FROM t_code_system WHERE groupname = '$codetype' and code = '$code'";
        $code = $this->db_get_row($query);     
        return $code["label"];
    }


    public function db_checksystemcode($codetype, $code)
    {
        $query = "SELECT id FROM t_code_system WHERE groupname = '$codetype' and code = '$code'";
        $code = $this->db_get_row($query);
        if ($code) {
            return true;
        } else {
            return false;
        }
    }

    
    public function db_getsystemcodes($codetype)
    {
        $query = "SELECT code, label, defaultval FROM t_code_system WHERE groupname = '$codetype' ORDER BY rank";
        $codes = $this->db_get_rows( $query );
        return $codes;
    }


    public function db_getresultcodes($mode, $active="1")
    {
        $where = "active = $active ";
        if ($mode == "start")
            { $where.= " AND startcode = '1' "; }
        elseif ($mode == "timer")
            { $where.= " AND timercode = '1' "; }
        elseif ($mode == "timing" )
            { $where.= " AND timing = '1' "; }
        elseif ($mode =="enter" )
            { $where.= " AND code = 'DUT' "; }
        
        $query = "SELECT code, short, info, scoringtype, scoring, timing FROM t_code_result WHERE $where ORDER BY `rank` ASC, code";
        $codes = $this->db_get_rows( $query );
        
        $codelist = array();
        foreach($codes as $key=>$code)
        {
            $codelist["{$code['code']}"] = $code;
        }
        return $codelist;
    }
    
    
    public function db_getresultcode($code)
    {
        $query = "SELECT * FROM t_code_result WHERE code = '$code'";
        $code = $this->db_get_row( $query );
        if (empty($code)) { return false; }
        return $code;
    }
    
    public function db_getinivalues($process_bool, $category="")
    {
        $code = array();
        $query = "SELECT `parameter`, `value` FROM t_ini ";
        if (!empty($category))
        {
            $query.= " WHERE category = '$category'";
        }
        $rs = $this->db_get_rows( $query );

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

    
    public function db_getlinks($category)
    {
        $query = "SELECT label, url, tip, category, `rank` FROM t_link";
        if (!empty($category))
        {
            $query.= " WHERE category = '$category'";
        }
        $links = $this->db_get_rows( $query );
        return $links;
    }

    public function db_getmessages($eventid, $search, $order="")
    {
        $query = "SELECT * FROM t_message WHERE 1=1 ";
        foreach ($search as $k => $v)
        {
            $query.= "AND $k LIKE '%$v% ";
        }
        empty($order) ? $query.= "ORDER BY eventid, upddate" : $query.= "ORDER BY $order";

        $rs = $this->db_get_rows($query);
        return $rs;
    }

    public function db_createmessage($eventid, $message, $application="")
    {
        $status = false;
        if (!empty($eventid) and !empty($message))
        {
            $message['eventid'] = $eventid;
            if (!array_key_exists("status", $message))
            {
                $message['status'] = "received";
            }
            if (!array_key_exists("updby", $message))
            {
                $message['updby'] = $application;
            }
            $insert = $this->db_insert( "t_message", $message);
            if ($insert)
            {
                $status = true;
            }
        }
        return $status;
    }


    public function db_table_to_file($loc, $table)
    {
        $backup_file = "$loc/{$table}-".date("Y-m-d-H-i-s").'.sql';

        $bufr = "\n/*---------------------------------------------------------------".
            "\n  TABLE: `{$table}`".
            "\n  ---------------------------------------------------------------*/\n";
        $bufr.= "TRUNCATE TABLE `$table`;\n";

        $result = $this->db_get_rows("SELECT * FROM `$table`");
        foreach ($result as $key=>$row)
        {
            $bufr.= "INSERT INTO ". $table;
            $fields = array();
            $values = array();
            foreach( $row as $field => $value )
            {
                $fields[] = "`".$field."`";
                if (is_null($value) OR is_numeric($value))
                    { $values[] = $value; }
                else
                    { $values[] = "'".addslashes($value)."'"; }
            }
            $bufr.= ' (' . implode(', ', $fields) . ')  VALUES  ('. implode(', ', $values) .");\n";
        }

        // write to file
        $handle = fopen($backup_file,'w+');
        if ($handle)
        {
            fwrite($handle,$bufr);
            fclose($handle);
            return $backup_file;  // return name of file
        }
    return false;
    }


    public function db_table_to_temptable($table)
    {
        $result = false;

        // get name of temp table
        if (($pos = strpos($table, "_")) !== FALSE)
        {
            $temptable = "z_" . substr($table, $pos + 1);

            // drop temp table
            $result = $this->db_query("DROP TABLE IF EXISTS $temptable");

            // create temptable with structure from table
            $result = $this->db_query("CREATE TABLE $temptable LIKE $table");

            // copy data from table to temptable
            $result = $this->db_query("INSERT INTO $temptable SELECT * FROM $table");
        }
        return $result;
    }

   // FIXME - next two methods should be private
   
    // Method: send error messages to error log
    public function db_log_errors( $error, $query )
    {    
        $message = "DATABASE ERROR [".date('Y-m-d H:i:s')."]".PHP_EOL;
        $message.= "Query: ". htmlentities( $query ).PHP_EOL;
        $message.= "Error: $error<br />".PHP_EOL;
        error_log($message, 3, $_SESSION['syslog']);
    }
    
    // Method: send debug messages to debug log
    public function db_log_debug( $method, $query )
    {    
        $message = "DEBUG [".date('Y-m-d H:i:s')."]".PHP_EOL;
        $message.= "Method: $method".PHP_EOL;
        $message.= "Query: ". htmlentities( $query ).PHP_EOL;
        error_log($message, 3, $_SESSION['debuglog']);
    }
    
}


