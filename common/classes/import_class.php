<?php

class IMPORT_CSV
/*
Class for managing csv imports

*/
{
    private $db;

    //Method: construct class object
    public function __construct(DB $db, $field_map)
    {
        $this->db = $db;
        $this->imp_data = array();           // data to be imported into database - mapped to database fields
        $this->imp_ref = array();            // value to be used to reference line of data
        $this->field_map = $field_map;       // mapping between database and csv field names
        $this->file_inf = array();           // holds error information about file
        $this->data_inf = array();           // holds error information about data rows
        $this->import_inf = array();         // holds error information about import
        $this->handle = NULL;
        $this->header = array();
        $this->num_imports = 0;
        $this->import_fail_line = 0;
    }

    public function check_importfile($files)
    {
        $err = "";

        // check file is readable
        if ($this->open_import($files['importfile']['tmp_name']))
        {
            ini_set('auto_detect_line_endings', true);

            // check I can interpret the first line
            $this->header = fgetcsv($this->handle, 0);

            foreach ($this->header as $value)
            {
                $value = preg_replace('/[^\w-]/', '', trim($value));
                if (!in_array($value, $this->field_map))
                {
                    $err .= $value." is invalid field<br> ";
                }
            }
        }

        if (!empty($err))
        {
            $this->file_inf['filehdr'] = $err;
            return false;
        }
        return true;             
    }


    public function read_importdata()
    {
        ini_set('auto_detect_line_endings', true);

        // read data into array - doing field name swap
        $i=0;
        while (($row = fgetcsv($this->handle, 0)) !== FALSE)
        {
            $i++;
            $err = "";
            if (count($this->header) == count($row))
            {
                $row = array_combine($this->header, $row);
                $row_transform = array();
                foreach ($row as $key=>$value)
                {
                    $key = preg_replace('/[^\w-]/', '', $key);
                    $dkey = array_search($key, $this->field_map );
                    $row_transform[$dkey] = $value;
                }
                $this->imp_data[] = $row_transform;
            }
            else
            {
                $err .= "incorrect number of data values";
            }
            if (!empty($err)) { $this->data_inf[$i] = $err; }
        }
        $this->close_import();
        $this->num_imports = $i;

        if (!empty($this->data_inf))
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public function open_import($filename)
    {
        $this->handle = fopen($filename, "r");
        if (!$this->handle) { return false; }
        return true;
    }

    public function close_import()
    {
        return fclose($this->handle);
    }

    public function get_numimports()
    {
        return $this->num_imports;
    }

    public function get_importdata()
    {
        return $this->imp_data;
    }

    public function put_importdata($data)
    {
        $this->imp_data = $data;
    }

    public function put_importref($data)
    {
        $this->imp_ref = $data;
    }

    public function get_fail_line()
    {
        return $this->import_fail_line;
    }

    public function get_data_info()
    {
        return $this->data_inf;
    }

    public function get_import_info()
    {
        return $this->import_inf;
    }

    public function get_file_val()
    {
        $bufr = "";
        if (!empty($this->file_inf))
        {
            foreach ($this->file_inf as $error)
            {
                $bufr.= "<p>$error</p>";
            }
        }
        return $bufr;
    }

    public function import_data ($table, $truncate, $update)
    {
        // if truncate - empty table
        if ($truncate)
        {
            $empty = $this->db->db_truncate(array($table));
            if (!$empty)
            {
                $this->import_fail_line = -100;
                return false;
            }
        }

        $i = 1;
        $this->import_inf['insert'] = "";
        $this->import_inf['update'] = "";
        foreach ($this->imp_data as $key=>$row)
        {
            $i++;
            // check if update processing required
            $exists   = $this->imp_ref[$i]['exists'];
            $item     = $this->imp_ref[$i]['ref'];
            if (array_key_exists('id', $this->imp_ref[$i]))
            {
                $record_id = $this->imp_ref[$i]['id'];
            }
            else
            {
                $record_id = 0;
            }

            if (!$exists or $truncate)       // record does not exist or we have truncated table - so insert
            {
                $insert_rs = $this->db->db_insert( $table, $row );
                if (!$insert_rs)
                {
                    $this->import_fail_line = $i;
                    return false;
                }
                else
                {
                    $this->import_inf['insert'].= "$item, ";
                }
            }
            else                      // already exists so update if permitted
            {
                if ($update)
                {
                    $update_rs = $this->db->db_update( $table, $row, array ('id'=>$record_id) );
                    if ($update_rs == -1)
                    {
                        $this->import_fail_line = $i;
                        return false;
                    }
                    elseif ( $update_rs > 0 )
                    {
                        $this->import_inf['update'].= "$item , ";
                    }
                }
            }
        }
        return true;
    }

}
