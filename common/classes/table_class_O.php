<?php
/*------------------------------------------------------------------------------
** File:		table_class.php
** Class:       xxxxx
** Description:	xxxxxxxx 
** Version:		1.0
** Updated:     19-May-2014
** Author:		Mark Elkington
** HomePage:    www.pegasus.co.uk 
**------------------------------------------------------------------------------
** COPYRIGHT (c) %!date!% MARK ELKINGTON
**
** The source code included in this package is free software; you can
** redistribute it and/or modify it under the terms of the GNU General Public
** License as published by the Free Software Foundation. This license can be
** read at:
**
** http://www.opensource.org/licenses/gpl-license.php
**
** This program is distributed in the hope that it will be useful, but WITHOUT 
** ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
** FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. 
**------------------------------------------------------------------------------ */


class TABLE
{    
    public function __construct($cols, $table_attr, $header, $click_able, $style="")
    /*
    creates table object with an optional header
    */
	{
        $bufr = "";
        $this->tablebufr = "<table class=\"table $table_attr\" style=\"$style\">";
        if ($header)
        {
            $bufr = "<thead >";		
    		foreach($header as $col)
    		{
                $bufr.= "<th style=\"{$col['attr']}\" width=\"{$col['width']}\">{$col['label']}</th>";			
    		}
            $bufr.= "</thead>";
        }

        $click_able ? $bufr.= "<tbody data-link=\"row\" class=\"rowlink\">" : $bufr.= "<tbody>";
        $bufr.= "<!-- data -->";
         
        $this->tablebufr.= $bufr;        
        $this->cols = $cols;   
        
        return $this;     
	}
    
     
    public function table_addrow($row, $rowattr, $cellattr)
    /*
    adds a single row
    */
    {    
		// create html for row
		$bufr = "<tr class=\"$rowattr\">";		
		foreach($row as $key=>$cell)
		{
            $bufr.= "<td class=\"{$cellattr["$key"]}\">{$cell['value']}</td>";			
		}
        $bufr.= "</tr>";
        
        $position = strpos ( $this->tablebufr , "<!-- data -->" );
		$this->tablebufr = substr_replace ( $this->tablebufr , $bufr , $position , 0 );
    }

    
    public function table_addrows($rows, $rowattr, $cellattr, $ignorecols=array())
    /*
    adds a number of rows - only use if table does not need different custom cell or row styling
    */
    {    
		$bufr = "";
        // create html for row				
		foreach($rows as $row)
		{
            $bufr.= "<tr class=\"$rowattr\">";
            foreach ($row as $key=>$cell)
            {
                if (in_array($key, $ignorecols))
                {
                    $bufr.= "";
                }
                else
                {
                    in_array($key, $cellattr) ? $attr = $cellattr["$key"] : $attr = "" ;
                    $bufr.= "<td style=\"$attr\" >$cell</td>";
                }
            }
            $bufr.= "</tr>";
		}
        $position = strpos ( $this->tablebufr , "<!-- data -->" );
		$this->tablebufr = substr_replace ( $this->tablebufr , $bufr , $position , 0 );
    }


    public function table_addfooter($left, $right, $attr)
    /*
    adds a footer at the bottom of the table with left and right text    
    */
    {    
        $left_cols  = round($this->cols/2);
        $right_cols = $this->cols - $left_cols;
        $bufr = "";
        $bufr.= "<tr class=\"$attr\" ><th colspan=\"$left_cols\"  style=\"text-align: left\">$left</th><th colspan=\"$right_cols\" $attr style=\"text-align: right\">$right</th></tr>";
        $this->tablebufr.= $bufr;        
    }


    public function table_display()
    /*
    returns full table html markup
    */
    {    
		$this->tablebufr.= "</tbody></table>";
        return $this->tablebufr;
    }
}	


?>