<?php

function fm_addcompetitor($params = array())
{
    global $boat_o;

    $lbl_width = "col-xs-3";
    $fld_width = "col-xs-7";

    $class_list = u_selectlist($boat_o->boat_getclasslist());  // list of classes

// form  - instructions + fields
    $html = <<<EOT
        <div class="alert well well-sm" role="alert">
            <p class="text-info lead">This form will add a new boat to raceManager so you can enter them into this race &hellip;</p>
            <p class="text-danger">WARNING - This form does NOT enter the boat in the race - use the <b>Enter Boats</b> option after registering it here</p>
        </div>

        <!-- field #1 - class -->
        <div class="form-group">
            <label class="$lbl_width control-label">boat class</label>
            <div class="$fld_width selectfieldgroup">
                <select class="form-control" name="classid" required data-fv-notempty-message="choose the class of boat">
                     $class_list
                </select>
            </div>
        </div>

        <!-- field #2 - sail number -->
        <div class="form-group">
            <label class="$lbl_width control-label">sail number</label>
            <div class="$fld_width inputfieldgroup">
                <input type="text" class="form-control" id="sailnum" name="sailnum" value=""
                    placeholder="sail number"
                    required data-fv-notempty-message="this information is required"
                />
            </div>
        </div>

        <!-- field #3 - helm name -->
        <div class="form-group">
            <label class="$lbl_width control-label">helm</label>
            <div class="$fld_width inputfieldgroup">
                <input type="text" class="form-control" style="text-transform: capitalize;" id="helm" name="helm" value=""
                    placeholder="helm's name"
                    required data-fv-notempty-message="this information is required"
                />
            </div>
        </div>

        <!-- field #4 - crew name -->
        <div class="form-group">
            <label class="$lbl_width control-label">crew</label>
            <div class="$fld_width inputfieldgroup">
                <input type="text" class="form-control" style="text-transform: capitalize;" id="crew" name="crew" value=""
                    placeholder="crew's name - if applicable"
                />
            </div>
        </div>

        <!-- field #5 - club -->
        <div class="form-group">
            <label class="$lbl_width control-label">club</label>
            <div class="$fld_width inputfieldgroup">
                <input type="text" class="form-control" style="text-transform: capitalize;" id="club" name="club" value="{$_SESSION['clubname']}"
                    required data-fv-notempty-message="this information is required"
                />
            </div>
        </div>
EOT;
    return $html;
}

function fm_addclass($params = array())
{
    global $boat_o;

    $lbl_width = "col-xs-3";
    $fld_width = "col-xs-7";

// get codes required for select fields
    $codes = $boat_o->boat_getclasscodes();
    $category_list  = u_selectcodelist($codes['category'], "default", false);
    $crew_list      = u_selectcodelist($codes['crew'], "default", false);
    $rig_list       = u_selectcodelist($codes['rig'], "default", false);
    $spinnaker_list = u_selectcodelist($codes['spinnaker'], "default", false);
    $engine_list    = u_selectcodelist($codes['engine'], "default", false);
    $keel_list      = u_selectcodelist($codes['keel'], "default", false);

// form instructions
    $html = <<<EOT
    <div class="alert well well-sm" role="alert">
        <p class="text-info lead"><b>Care</b> - please provide accurate information so that the class will be allocated to the correct fleet.</p>
    </div>

    <!-- field #1 - class name -->
    <div class="form-group">
        <label class="$lbl_width control-label">class name</label>
        <div class="$fld_width inputfieldgroup">
            <input type="text" class="form-control" id="classname" name="classname" value=""
                placeholder="class name e.g whizbang 200"
                required data-fv-notempty-message="this information is required"
            />
        </div>
    </div>

    <!-- field #2 - PN number -->
    <div class="form-group" >
        <label data-placement="right" data-toggle="popover" data-trigger="focus" title="Setting PY" data-content="If you cannot find a PY for this class use an estimate based on similar classes - it can be adjusted after the results are published by the results team" class="$lbl_width control-label">PY number&nbsp;&nbsp;<span class="text-primary glyphicon glyphicon-info-sign"></span> </label>
        <div class="$fld_width inputfieldgroup">
            <input type="text" class="form-control" id="py" name="py" value=""
                required
                placeholder="if unsure - use number from similar class"
                min="{$_SESSION['min_py']}" max="{$_SESSION['max_py']}"
               data-fv-between-message="The PY must be between {$_SESSION['min_py']} and {$_SESSION['max_py']}"
            />
        </div>
    </div>

    <!-- field #3 - boat category -->
    <div class="form-group">
        <label class="$lbl_width control-label">boat type</label>
        <div class="$fld_width selectfieldgroup">
            <select class="form-control" name="category" required data-fv-notempty-message="choose one of these options">
                 $category_list
            </select>
        </div>
    </div>

    <!-- field #4 - crew limits -->
    <div class="form-group">
        <label class="$lbl_width control-label">no. of crew</label>
        <div class="$fld_width selectfieldgroup">
            <select class="form-control" name="crew" required data-fv-notempty-message="choose one of these options">
                 $crew_list
            </select>
        </div>
    </div>

    <!-- field #5 - rig type -->
    <div class="form-group">
        <label class="$lbl_width control-label">rig type</label>
        <div class="$fld_width selectfieldgroup">
            <select class="form-control" name="rig" required data-fv-notempty-message="choose one of these options">
                 $rig_list
            </select>
        </div>
    </div>

    <!-- field #5 - spinnaker type -->
    <div class="form-group">
        <label class="$lbl_width control-label">spinnaker type</label>
        <div class="$fld_width selectfieldgroup">
            <select class="form-control" name="spinnaker" required data-fv-notempty-message="choose one of these options">
                 $spinnaker_list
            </select>
        </div>
    </div>

    <!-- field #6 - engine type -->
    <div class="form-group">
        <label class="$lbl_width control-label">engine</label>
        <div class="$fld_width selectfieldgroup">
            <select class="form-control" name="engine" required data-fv-notempty-message="choose one of these options">
                 $engine_list
            </select>
        </div>
    </div>

    <!-- field #7 - keel type -->
    <div class="form-group">
        <label class="$lbl_width control-label">keel</label>
        <div class="$fld_width selectfieldgroup">
            <select class="form-control" name="keel" required data-fv-notempty-message="choose one of these options">
                 $keel_list
            </select>
        </div>
    </div>
EOT;
    return $html;
}

function fm_editentry($params = array())
{
    $lbl_width = "col-xs-3";
    $fld_width = "col-xs-7";
    $fld_narrow = "col-xs-3";

    $helm = "";
    if ($_SESSION['points_allocation'] == "boat") {
        $helm = <<<EOT
            <div class="form-group form-condensed">
                <label for="helm" class="control-label $lbl_width">Helm</label>
                <div class="change2 $fld_width"><input name="helm" type="text" class="form-control" id="idhelm"></div>
            </div>
EOT;
    }

    $html = <<<EOT
        <h4><span class="dynmsg"></span></h4>
        <p class="text-info lead">Only enter information you want to change </p>
        <div class="change"><input name="entryid" type="hidden" id="identryid"></div>

        $helm

        <div class="form-group form-condensed">
            <label for="crew" class="control-label $lbl_width">Crew</label>
            <div class="change3 $fld_width"><input name="crew" type="text" class="form-control" id="idcrew" placeholder="only use for double hander"></div>
        </div>

        <div class="form-group form-condensed">
            <label for="sailnum" class="control-label $lbl_width">Sail No.</label>
            <div class="change4 $fld_narrow"><input name="sailnum" type="text" class="form-control" id="idsailnum"></div>
        </div>

        <div class="form-group form-condensed">
            <label for="pn" class="control-label $lbl_width">PY</label>
            <div class="change5 $fld_narrow"><input name="pn" type="text" class="form-control" id="idpn"></div>
        </div>


EOT;
    return $html;
}

function fm_addentry($params = array())
{
    $search_bufr = fm_addentry_search($params['pagestate'], $params['search']);
    $entry_bufr = fm_addentry_entries($params['pagestate'], $params['entries'], $params['error']);

    $instructions = "";
    if ($params['pagestate'] == "init")
    {
        $instructions = <<<EOT
        <div class="row">
            <div class="col-lg-12 col-lg-offset-1">
                <div class="alert well well-sm" role="alert">
                    <!-- button type="button" class="close" style="right: 1px !important" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button -->
                    <p class="text-info">Search for boats by class, sail number, or helm name - then click enter button.</p>
                </div>
                <hr>
            </div>
        </div>
EOT;
    }


    $html = <<<EOT
    $instructions
    
    <div class="row">
        <div class="col-lg-8 col-md-8">
            <form class="form-horizontal" id="sailnumform" action="entries_add_sc.php?eventid={eventid}&pagestate=search"
                  method="post" role="search" autocomplete="off">
                    <div class="input-group">
                        <input id="searchstr" autocomplete="off" class="form-control input-md" type="text"
                               placeholder="sailnumber, class, or helm's name" name="searchstr" />
                        <span class="input-group-btn">
                               <button class="btn btn-warning btn-md" type="submit">
                               &nbsp;&nbsp;
                               <span class="glyphicon glyphicon-search" aria-hidden="true" style="vertical-align: middle"></span>
                               &nbsp;&nbsp;
                               </button>
                        </span>
                    </div>
            </form>
            <div class="well margin-top-10">$search_bufr</div>
        </div>
        
        <div class="col-lg-4 col-md-4">
            <div class="well">$entry_bufr</div>
        </div>
    </div>

EOT;
    return $html;
}

function fm_addentry_search($pagestate, $results)
{
    $bufr = "";

    $num_results = count($results);
    if ($num_results <= 0 and $pagestate != "init")                                // nothing found
    {
        $bufr.= <<<EOT
        <div class="text-danger" role="alert">no boats found - try again &hellip;</div>
EOT;
    }
    else                                                 // results found
    {
        $rows = "";
        foreach ($results as $result)
        {
            $team = u_truncatestring(rtrim($result['helm'] . "/" . $result['crew'], "/"), 32);
            $button = <<<EOT
            <span >
                <a id="enterone" href="entries_add_sc.php?eventid={eventid}&pagestate=enterone&competitorid={$result['id']}"
                   role="button" class="btn btn-link" style="padding: 0px 0px 0px 0px !important; font-weight: 100;" target="_self">
                    <span class="label label-success">enter&nbsp;<span class="glyphicon glyphicon-triangle-right"></span>&nbsp;
                </a>
            </span>
EOT;
            $rows.=<<<EOT
            <tr style="vertical-align:top">
               <td>{$result['classname']}</td>
               <td>{$result['sailnum']}</td>
               <td>$team</td>
               <td>$button</td>
            </tr>
EOT;
        }

        $bufr.= <<<EOT
        <p style="padding-top: 10px;">$num_results found:</p>
        <table class="table table-hover table-condensed" >
            <tbody>
                $rows
            </tbody>
        </table>
EOT;
    }

    return $bufr;
}

function fm_addentry_entries($pagestate, $entries, $error)
{
    $bufr = "";

    if ($pagestate == "init") {
        $bufr.= <<<EOT
            <p class="text-primary">entered boats listed here &hellip;</p>
EOT;
    }
    else
    {
        if (count($entries) > 0) {
            $num_entries = count($entries);
            $bufr.= <<<EOT
            <p class="text-primary">$num_entries entered in this session &hellip;</p>
EOT;
            foreach ($entries as $entry) {
                if (substr_count($entry, "fail") > 0 or substr_count($entry, "error") > 0)     // error or not found
                {
                    $bufr.= <<<EOT
                    <p class="text-danger">&nbsp;$entry <span class="glyphicon glyphicon-remove"></span></p>
EOT;
                } else                                                                        // entry found
                {
                    $bufr.= <<<EOT
                    <p class="">&nbsp;$entry <span class="glyphicon glyphicon-ok"></span></p>
EOT;
                }
            }
        } else {
            $bufr.= <<<EOT
                <p class="text-primary ">none entered so far &hellip;</p>
EOT;
        }

        if (isset($error))  // display error
        {
            $bufr.= <<<EOT
                    <p class="text-danger">&nbsp;$error</p>
EOT;
        }
    }

    return $bufr;
}


function entry_tabs($params = array())
{
    $eventid = $params['eventid'];
    $entries = $params['entries'];

    $tabs = "";
    $panels = "";
    for ($i = 1; $i <= $params['num-fleets']; $i++)
    {
        $fleet = $_SESSION["e_$eventid"]["fl_$i"];
        $fleet_count = count($entries[$i]);
        $fleet_name = strtolower($fleet['name']);

        $tabs.= <<<EOT
        <li role="presentation" class="lead text-center">
              <a class="text-primary" href="#fleet$i" aria-controls="$fleet_name" role="tab" data-toggle="pill" style="padding-top: 20px;">
              <b>$fleet_name</b> <span class="badge">$fleet_count</span>             
              </a>
        </li>
EOT;

        if ($fleet_count <= 0)
        {
            $panels .= <<<EOT
            <div role="tabpanel" class="tab-pane" id="fleet$i">
                <div class="well well-sm text-warning lead"><span>no entries in the $fleet_name fleet yet</span></div>
            </div>
EOT;
        }
        else
        {
            // organise data
            $rows = "";
            foreach($entries[$i] as $entry)
            {
                if($entry['code'] == "DUT")
                {
                    $duty_btn_style = "btn-danger";
                    $duty_btn_title = "duty points set - to unset use timer page";
                }
                else
                {
                    $duty_btn_style = "btn-success";
                    $duty_btn_title = "give duty points";
                }

                $entryname = "{$entry['class']}  -  {$entry['sailnum']}";
                $rows.= <<<EOT
                <tr class="table-data">
                    <td class="truncate" style="" >{$entry['class']}</td>
                    <td class="truncate" style="" >{$entry['sailnum']}</td>                   
                    <td class="truncate" style="" >{$entry['helm']}</td>
                    <td class="truncate" style="" >{$entry['crew']}</td>
                    <td class="truncate" style="" >{$entry['club']}</td>
                    <td style="" >{$entry['pn']}</td>
                    <td style="" >
                       <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true"
                             data-title="edit boat details" data-placement="top">
                       <a type="button" class="btn btn-success btn-xs" data-toggle="modal"
                               rel="tooltip" data-original-title="edit boat details" data-placement="bottom" data-target="#changeModal"
                               data-entryid="{$entry['id']}"
                               data-entryname="$entryname"
                               data-helm="{$entry['helm']}"
                               data-crew="{$entry['crew']}"
                               data-sailnum="{$entry['sailnum']}"
                               data-pn="{$entry['pn']}">
                              <span class="glyphicon glyphicon-pencil"></span>
                       </a>
                       </span>
                    </td>
                    <td style="" >
                       <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="$duty_btn_title" data-placement="top">
                       <a type="button" class="btn $duty_btn_style btn-xs" data-toggle="modal"
                               rel="tooltip" data-original-title="give duty points" data-placement="bottom" data-target="#dutyModal"
                               data-entryid="{$entry['id']}"
                               data-entryname="$entryname" >
                               <span class="glyphicon glyphicon-flag"></span>
                       </a>
                       </span>
                    </td>
                    <td style="" >
                       <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="remove boat from race" data-placement="top">
                       <a type="button" class="btn btn-danger btn-xs" data-toggle="modal"
                               rel="tooltip" data-original-title="remove boat from race" data-placement="bottom" data-target="#removeModal"
                               data-entryid="{$entry['id']}"
                               data-entryname="$entryname" >
                               <span class="glyphicon glyphicon-trash"></span>
                       </a>
                       </span>
                    </td>
                </tr>
EOT;
            }


            // create table
            $panels.= <<<EOT
            
            <div role="tabpanel" class="tab-pane" id="fleet$i" style="overflow-y: auto; height:75vh;">
                <h4 class="text-info">$fleet_count entries</h4>
                <table class="table table-striped table-condensed table-hover table-top-padding table-top-border" style="1em">
                    <thead class="text-info" style="border-bottom: 1px solid black;">
                        <tr >
                            <th width="11%">class</th>
                            <th width="7%">sail no.</th>                            
                            <th width="15%">helm</th>
                            <th width="15%">crew</th>
                            <th width="15%">club</th>
                            <th width="7%">pn</th>
                            <th width="5%">edit</th>
                            <th width="5%">duties</th>
                            <th width="5%">delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        $rows
                    </tbody>
                </table>
            </div>
EOT;
        }
    }

    $html = <<<EOT
    <div class="margin-top-10" role="tabpanel">
        <ul class="nav nav-pills pill-fleet" role="tablist"> 
           $tabs
        </ul>
        <div class="tab-content">
           $panels
        </div>
    </div>
EOT;
    return $html;
}


