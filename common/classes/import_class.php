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
        $this->field_map = $field_map;        // mapping between database and csv field names
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
        //echo print_r($files,true);

        // check it is a csv file
        if (strpos($files['importfile']['type'], "/csv") === false)
            {   //echo "fails type test<br>";
                $this->file_inf['filetype'] = "This file is not the correct type - it must be a CSV file";}

        // check file is readable
        if ($this->open_import($files['importfile']['tmp_name']))
        {
            ini_set('auto_detect_line_endings', true);

            // check I can interpret the first line
            $this->header = fgetcsv($this->handle, 0);
            //echo print_r($this->field_map, true);

            foreach ($this->header as $value)
            {
                $value = preg_replace('/[^\w-]/', '', $value);
                //echo "value: $value<br>";
                if (!in_array($value, $this->field_map))
                    { $this->file_inf['filehdr'].= $value." is invalid field<br> "; }
            }
        }
        else
        {
            $this->file_inf['fileopen'] = "not able to read supplied import file";
        }

        if (!empty($this->file_inf)) { return false; }
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
            if ($row = array_combine($this->header, $row))
            {
                $row_transform = array();
                foreach ($row as $key=>$value)
                {
                    $key = preg_replace('/[^\w-]/', '', $key);
                    $dkey = array_search($key, $this->field_map );
                    $row_transform[$dkey] = $value;
                }
                $this->imp_data[] = $row_transform;
            }
        }
        $this->close_import();
        $this->num_imports = $i;

        return true;
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


    public function get_import_val()
    {
        $no_updates = false;
        $no_inserts = false;
        $updates = rtrim($this->import_inf['update'], ", ");
        if (empty($updates))
        {
            $updates = "<i>- none -</i>";
            $no_updates = true;
        }

        $inserts = rtrim($this->import_inf['insert'],", ");
        if (empty($inserts))
        {
            $inserts = "<i>- none -</i>";
            $no_inserts = true;
        }

        $bufr = "";
        if (!empty($this->import_fail_line))
        {
            $bufr.= <<<EOT
            <p class="text-danger"><b>Import failed on line {$this->import_fail_line}</b></p>
EOT;
        }
        if ($no_updates AND $no_inserts)
        {
            $bufr.= <<<EOT
        <div style="padding-left:30px;">
            <p><b>No changes made</b></p>
        </div>
EOT;
        }
        else
        {
            $bufr.= <<<EOT
        <div style="padding-left:30px;">
            <p><b>Inserts:</b></p><p style="padding-left:60px; padding-right:60px">$inserts</p>
            <p><b>Updates:</b></p> <p style="padding-left:60px; padding-right:60px">$updates</p>
        </div>
EOT;
        }


        return $bufr;
    }


    public function import_data ($table, $truncate)
    {

        // if truncate - empty table
        if ($truncate)
        {
            $empty = $this->db->db_truncate(array($table));
            if (!$empty)
            {
                $this->import_fail_line = -100;
                echo "fail line: ".$this->import_fail_line."<br>";
                return false;
            }
        }

        $i = 1;
        foreach ($this->imp_data as $key=>$row)
        {
            $i++;
            $row['updby'] = "import";

            // check if update processing required
            $exists   = $this->imp_ref[$i]['exists'];
            $item     = $this->imp_ref[$i]['ref'];
            $record_id = $this->imp_ref[$i]['id'];

            if (!$exists)       // record does not exist - so insert
            {
                $insert_rs = $this->db->db_insert( $table, $row );
                if (!$insert_rs)
                {
                    $this->import_fail_line = $i;
                    //echo "insert - fail<br>";
                    return false;
                }
                else
                {
                    $this->import_inf['insert'].= "$item, ";
                    //echo "insert - success<br>";
                }
            }
            else                      // already exists so update
            {
                $update_rs = $this->db->db_update( $table, $row, array ('id'=>$record_id) );

                if ($update_rs == -1)
                {
                    $this->import_fail_line = $i;
                    //echo "update fail line: ".$this->import_fail_line."<br>";
                    return false;
                }
                elseif ( $update_rs > 0 )
                {
                    $this->import_inf['update'].= "$item , ";
                    //echo "update - success<br>";
                }
                //echo "update - no change<br>";
            }
        }
        return true;
    }

}
?>