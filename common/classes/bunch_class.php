<?php

/*
 *
 * Class to handle storing boats in a bunch-list - for quick access on timer page
 */

/*
require_once ("./template_class.php");

$bunch = array();

session_start();
$bunch_o = new BUNCH($bunch);

$bunch_o->is_empty() ? $state = "empty" : $state = "not empty";
echo "bunch is $state<br>";
// dummy data
$node1 = array("entryid"=> 123456, "lastlap" => false, "label" => "Hornet 1234", "link"  => "http:www.bbc.co.uk");
$node2 = array("entryid"=> 123465, "lastlap" => false, "label" => "HurricaneSX 123", "link"  => "http:www.bbc.co.uk");
$node3 = array("entryid"=> 123458, "lastlap" => true, "label" => "Laser 123078", "link"  => "http:www.bbc.co.uk");

$bunch = $bunch_o->add_node($node1);
$bunch = $bunch_o->add_node($node2);
$bunch = $bunch_o->add_node($node3);
$bunch_o->list_bunch();

$bunch_o->is_empty() ? $state = "empty" : $state = "not empty";
echo "bunch is $state<br>";

echo "sift top node down<br>";
// sift node1 below node2
$bunch = $bunch_o->siftdown_node(0);
$bunch_o->list_bunch();

echo "sift bottom node up<br>";
// sift node 3 above node2
$bunch = $bunch_o->siftup_node(2);
$bunch_o->list_bunch();

$htm = $bunch_o->render();

$tmpl_o = new TEMPLATE(array("../templates/general_tm.php", "../../rm_racebox/templates/layouts_tm.php"));
echo $tmpl_o->get_template("basic_page", array("theme"=>"flatly_", "loc"=> "../..", "navbar"=>"", "footer"=>"", "body"=> $htm));
*/


class BUNCH
{
    //private $db;

    //Method: construct class object
    public function __construct($eventid, $link, $bunch = array())
    {
        $this->eventid = $eventid;
        $this->link = $link;
        $this->bunch = $bunch;
    }

    public function get_bunch()
    {
        return $this->bunch;
    }

    private function reindex_nodes()
    {
        ksort($this->bunch);
        $i=0;
        $new = array();
        foreach ($this->bunch as $k=>$v)
        {
            $new[$i] = $v;
            $i++;
        }
        return $new;
    }

    public function add_node($node = array())
    {
        // check not a duplicate
        $add = true;
        foreach ($this->bunch as $current_node)
        {
            if (strtolower($node['label']) == strtolower($current_node['label']) )
            {
                $add = false;
                break;
            }
        }

        if ($add)
        {
            $this->bunch[] = $node;
            $this->bunch = $this->reindex_nodes();
        }

        return $add;
    }

    public function del_node($nodeid)
    {
        unset($this->bunch[$nodeid]);
        $this->bunch = $this->reindex_nodes();

        return $this->bunch;
    }

    public function siftup_node($nodeid)
    {
        $min_key = 0;
        if (($nodeid - 1) >= $min_key)                               // check not first node
        {
            $tmp = $this->bunch[$nodeid];
            $this->bunch[$nodeid] = $this->bunch[$nodeid - 1];
            $this->bunch[$nodeid - 1] = $tmp;

            $this->bunch = $this->reindex_nodes();
        }
        return $this->bunch;
    }

    public function siftdown_node($nodeid)
    {
        $max_key = $this->array_key_max_value($this->bunch);
        if (($nodeid + 1) <= $max_key)                               // check not last node
        {
            $tmp = $this->bunch[$nodeid];
            $this->bunch[$nodeid] = $this->bunch[$nodeid + 1];
            $this->bunch[$nodeid + 1] = $tmp;

            $this->bunch = $this->reindex_nodes();
        }

        return $this->bunch;
    }

    public function search_nodes($entryid)
    {
        foreach($this->bunch as $key => $node)
        {
            if ( $node['entryid'] === $entryid )
            {
                return $key;
            }
        }
        return false;
    }

    public function is_empty()
    {
        count($this->bunch) <= 0 ? $is_empty = true : $is_empty = false;
        return $is_empty;
    }

    public function render()
    {

        $htm = "";
        foreach($this->bunch as $i=>$node)
        {
            $node['lastlap'] ? $bcolor = "warning" : $bcolor = "info";

            $htm.= <<<EOT
            <div class="row" style="margin-left: 10px; margin-bottom: 10px">
                <div class="col-md-9" style="padding: 0px 0px 0px 0px;">
                    <a type="button" href="{$node['link']}" class="btn btn-block btn-$bcolor btn-md" style="color:black; font-weight: bold">{$node['label']}</a>
                </div>
                <div class="col-md-1" style="padding: 0px 0px 0px 0px;">
                    <a type="button" href="{$this->link}?pagestate=bunch&eventid={$this->eventid}&node=$i&action=up" class="btn btn-link btn-md"  title="up" style="padding: 0px 0px 0px 10px;">
                        <span class="glyphicon glyphicon-chevron-up"  aria-hidden="false"></span>
                    </a>
                    <a type="button" href="{$this->link}?pagestate=bunch&eventid={$this->eventid}&node=$i&action=down" class="btn btn-link btn-md"  title="down" style="padding: 0px 0px 0px 10px;"">
                        <span class="glyphicon glyphicon-chevron-down" aria-hidden="true"></span>
                    </a>
                </div>
                <div class="col-md-2" style="padding: 0px 0px 0px 0px;">
                    <a type="button" href="{$this->link}?pagestate=bunch&eventid={$this->eventid}&node=$i&action=delnode" class="btn btn-link btn-md" style="padding: 10px 0px 0px 10px; color: darkred" title="remove">
                        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                    </a>
                </div>                    
            </div>
EOT;

        }

        return $htm;
    }

    public function list_bunch()
    {
        echo "<pre>".print_r($this->bunch,true)."</pre>";
    }

    private function array_key_max_value($array)
    {
        $result = 0;
        foreach ($array as $key => $value) {
            if ($key > $result) {
                $result = $key;
            }
        }

        return $result;
    }
}

