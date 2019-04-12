<?php

/**
 * db_connect()
 * 
 * @param mixed $server
 * @return
 */
function db_connect($server)
{
   $conn = mysqli_connect($_SESSION[$server]['DBserver'], $_SESSION[$server]['DBuser'], $_SESSION[$server]['DBpwd']) or
            die("An internal error has occurred.<br /><span style=\"color:darkred\"> database connection failed - $server</span><br /> Error: (" . mysqli_connect_errno() . ") <i>" . mysqli_connect_error() ."</i>");
   mysqli_select_db($conn, $_SESSION[$server]['DBase']);    

   return $conn;
}

/**
 * db_query()
 * 
 * @param mixed $conn
 * @param mixed $sql
 * @return
 */
function db_query($conn, $sql)
{
    $result = mysqli_query($conn, $sql) or die("An internal error has occurred.<br />Query: <span style=\"color:darkred\"> $sql</span><br /> Error: (" . mysqli_errno($conn) . ") <i>" . mysqli_error($conn) ."</i>");

    return $result;
}

/**
 * db_fetchrow()
 * 
 * @param mixed $result
 * @return
 */
function db_fetchrow($result)
{
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

    return $row;
}

/**
 * db_numrows()
 * 
 * @param mixed $result
 * @return
 */
function db_numrows($result)
{
    $numrows = mysqli_num_rows($result);

    return $numrows;
}

/**
 * db_lastinsert()
 * 
 * @param mixed $conn
 * @return
 */
function db_lastinsert($conn)
{
    $id = mysqli_insert_id($conn);

    return $id;
}

/**
 * db_affectedrows()
 * 
 * @param mixed $conn
 * @return
 */
function db_affectedrows($conn)
{
    $numrows = mysqli_affected_rows($conn);

    return $numrows;
}

/**
 * db_close()
 * 
 * @param mixed $conn
 * @return
 */
function db_close($conn)
{
    mysqli_close($conn);
}

?>