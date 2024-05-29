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
}




