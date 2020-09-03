<?php

function fm_addcompetitor($params = array())
{
    global $boat_o;

    $lbl_width = "col-xs-3";
    $fld_width = "col-xs-7";

    $class_list = u_selectlist($boat_o->boat_getclasslist());  // list of classes

// form instructions
    $html = <<<EOT
        <p>This form will add a new competitor to the raceManager database so you can then enter them into your race&hellip;</p>
        <p class="text-danger">WARNING - This form does NOT enter the new competitor in the race</p>

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
    $category_list  = u_selectcodelist($codes['category'], "default");
    $crew_list      = u_selectcodelist($codes['crew'], "default");
    $rig_list       = u_selectcodelist($codes['rig'], "default");
    $spinnaker_list = u_selectcodelist($codes['spinnaker'], "default");
    $engine_list    = u_selectcodelist($codes['engine'], "default");
    $keel_list      = u_selectcodelist($codes['keel'], "default");

// form instructions
    $html = <<<EOT
    <div class="alert alert-danger alert-dismissable" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <b>Care</b> - please provide accurate information so that the class will be allocated to the correct fleet.
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
    $html = <<<EOT
    <div style="margin-top: -40px">
        <form class="form-horizontal" id="sailnumform" action="entries_add_sc.php?eventid={eventid}&pagestate=search"
              method="post" role="search" autocomplete="off">
            <div style="padding-left:80px; padding-right:80px;">
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
           </div>
        </form>
    </div>
EOT;
    return $html;
}

function addentry_search_result($params)
{
    $html = "";
    $num_results = count($params);
    if ($num_results <= 0) // nothing found
    {
        $html.= <<<EOT
        <div class="alert alert-info" role="alert">no boats found - try again</div>
        <!--p class="text-danger bg-danger text-center" style="margin: 20px 20% 0px 20%; padding: 10px 0px 10px 0px ">
            &nbsp;no boats found - try again&nbsp;
        </p -->
EOT;
    }
    else                      // results found
    {
        $rows = "";
        foreach ($params as $result)
        {
            $team = u_truncatestring(rtrim($result['helm'] . "/" . $result['crew'], "/"), 32);
            $button = <<<EOT
            <span >
                <a id="enterone" href="entries_add_sc.php?eventid={eventid}&pagestate=enterone&competitorid={$result['id']}"
                   role="button" class="btn btn-link" style="padding:0px" target="_self">
                    <span class="label label-default" style="font-size: 100%">
                        enter&nbsp;<span class="glyphicon glyphicon-triangle-right">
                     </span>&nbsp;</span>
                </a>
            </span>
EOT;
            $rows.=<<<EOT
            <tr>
               <td>{$result['classname']}</td>
               <td>{$result['sailnum']}</td>
               <td>$team</td>
               <td>$button</td>
            </tr>
EOT;
        }

        $html.= <<<EOT
        <p style="padding-top: 10px;">$num_results found:</p>
        <table class="table table-hover" >
            <tbody>
                $rows
            </tbody>
        </table>
EOT;
    }
    return $html;
}

function addentry_boats_entered($params)
{
    $html = "";

    if ($params['pagestate'] == "init") {
        $html .= <<<EOT
        <div class="well">
            <p class="text-info lead">entered boats listed here &hellip;</p>
        </div>
EOT;
    } else {
        if (isset($params['entries'])) {
            $num_entries = count($params['entries']);
            $html .= <<<EOT
            <div class="well">
            <p class="text-info lead"><i>$num_entries entered in this session &hellip;</i></p>
EOT;
            foreach ($params['entries'] as $entry) {
                if (substr_count($entry, "fail") > 0 or substr_count($entry, "error") > 0)     // error or not found
                {
                    $html .= <<<EOT
                    <p class="text-danger lead">&nbsp;$entry <span class="glyphicon glyphicon-remove"></span></p>
EOT;
                } else                                                                        // entry found
                {
                    $html .= <<<EOT
                    <p class="lead">&nbsp;$entry <span class="glyphicon glyphicon-ok"></span></p>
EOT;
                }
            }
            $html .= "</div>";
        } else {
            $html .= <<<EOT
            <div class="well">
                <p class="text-info lead">none entered so far &hellip;</p>
            </div>
EOT;
        }

        if (isset($params['error']))  // display error
        {
            $html .= <<<EOT
            <div class="well">
                <p class="text-danger">&nbsp;{$params['error']}</p>
            </div>
EOT;
        }
    }
    return $html;
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

        $tabs.= <<<EOT
        <li role="presentation" class="lead">
              &nbsp;
              <a href="#fleet$i" aria-controls="{$fleet['name']}" role="tab" data-toggle="pill">
              {$fleet['name']} [{$fleet_count}]
              </a>
              &nbsp;
        </li>
EOT;
        if ($fleet_count <= 0)
        {
            $panels .= <<<EOT
            <div role="tabpanel" class="tab-pane" id="fleet$i">
                <div class="well well-sm text-warning lead"><span>no entries in the {$fleet['name']} fleet yet</span></div>
            </div>
EOT;
        }
        else
        {
            // organise data
            $rows = "";
            foreach($entries[$i] as $entry)
            {
                $entryname = "{$entry['class']}  -  {$entry['sailnum']}";
                $rows.= <<<EOT
                <tr class="lead">
                    <td class="truncate" style="" >{$entry['class']}</td>
                    <td class="truncate" style="" >{$entry['sailnum']}</td>                   
                    <td class="truncate" style="" >{$entry['helm']}</td>
                    <td class="truncate" style="" >{$entry['crew']}</td>
                    <td class="truncate" style="" >{$entry['club']}</td>
                    <td style="" >{$entry['pn']}</td>
                    <td style="" >
                       <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true"
                             data-title="edit boat details" data-placement="top">
                       <button type="button" class="btn btn-link inline-button" data-toggle="modal"
                               rel="tooltip" data-original-title="edit boat details" data-placement="bottom" data-target="#changeModal"
                               data-entryid="{$entry['id']}"
                               data-entryname="$entryname"
                               data-helm="{$entry['helm']}"
                               data-crew="{$entry['crew']}"
                               data-sailnum="{$entry['sailnum']}"
                               data-pn="{$entry['pn']}">
                           <span class="label label-default" style="font-size: 100%">
                              &nbsp;&nbsp;<span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;
                           </span>
                       </button>
                       </span>

                       <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="give duty points" data-placement="top">
                       <button type="button" class="btn btn-link inline-button" data-toggle="modal"
                               rel="tooltip" data-original-title="give duty points" data-placement="bottom" data-target="#dutyModal"
                               data-entryid="{$entry['id']}"
                               data-entryname="$entryname" >
                            <span class="label label-default" style="font-size: 100%">
                                &nbsp;&nbsp;<span class="glyphicon glyphicon-flag"></span>&nbsp;&nbsp;
                            </span>
                       </button>
                       </span>

                       <span data-toggle="tooltip" data-delay='{"show":"1000", "hide":"100"}' data-html="true" data-title="remove boat from race" data-placement="top">
                       <button type="button" class="btn btn-link inline-button" data-toggle="modal"
                               rel="tooltip" data-original-title="remove boat from race" data-placement="bottom" data-target="#removeModal"
                               data-entryid="{$entry['id']}"
                               data-entryname="$entryname" >
                            <span class="label label-danger" style="font-size: 100%">
                                &nbsp;&nbsp;<span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;
                            </span>
                       </button>
                       </span>
                    </td>
                </tr>
EOT;
            }


            // create table
            $panels.= <<<EOT
            <div role="tabpanel" class="tab-pane" id="fleet$i" style="overflow-y: auto; height:75vh;">
                <table class="table table-striped table-condensed table-hover" style="1em">
                    <thead class="text-info">
                        <tr>
                            <th style="" width="11%">class</th>
                            <th style="" width="7%">sail no.</th>                            
                            <th style="" width="15%">helm</th>
                            <th style="" width="15%">crew</th>
                            <th style="" width="15%">club</th>
                            <th style="" width="7%">pn</th>
                            <th style="" width="30%"></th>
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
        <ul class="nav nav-pills entry" role="tablist">
           $tabs
        </ul>
        <div class="tab-content">
           $panels
        </div>
    </div>
EOT;
    return $html;
}


