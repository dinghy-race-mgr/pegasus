<?php

function pursuit_start_form($params = array())
{

    $classes = $params['classes'];   // already sorted from slow o fast
//    u_array_sort_by_column($classes, "nat_py", SORT_DESC);
    $classlist_slow = array();
    foreach ($classes as $row)
    {
        $classlist_slow[$row['id']] = $row['classname'];
    }

    $class_select_slow = u_selectlist($classlist_slow);  // select list for classes - slow to fast

    $classes = $params['classes'];
    u_array_sort_by_column($classes, "nat_py", SORT_ASC);
    $classlist_fast = array();
    foreach ($classes as $row)
    {
        $classlist_fast[$row['id']] = $row['classname'];
    }
    $class_select_fast = u_selectlist($classlist_fast);  // select list for classes - fast to slow


    $bufr = <<<EOT
    <div class="container" style="margin-top: 40px;">
        <div class="jumbotron">
            <p class="text-primary">{instructions}</p>
        </div>
        <form enctype="multipart/form-data" id="pursuitstartForm" action="{script}" method="post"
            data-fv-addons="mandatoryIcon"
            data-fv-addons-mandatoryicon-icon="glyphicon glyphicon-asterisk"
            data-fv-framework="bootstrap"
            data-fv-icon-valid="glyphicon glyphicon-ok"
            data-fv-icon-invalid="glyphicon glyphicon-remove"
            data-fv-icon-validating="glyphicon glyphicon-refresh"
            >
            
        <!-- slowest/fastest class -->
        <div class="row form-inline">
            <label class="col-sm-3 control-label text-right" style="margin-top: 10px !important;">Classes (Slowest/Fastest)</label>
            <div class="form-group">                   
                <div class="selectfieldgroup">
                    <select class="form-control" name="maxclassid" required data-fv-notempty-message="choose the slowest class to include">
                         $class_select_slow
                    </select>
                </div>

            </div>
            <div class="form-group" style="margin-left: 30px !important">                   
                <div class="selectfieldgroup">
                    <select class="form-control" name="minclassid" required data-fv-notempty-message="choose the fastest class to include">
                         $class_select_fast
                    </select>
                </div>
            </div>
        </div>
        
        <!-- race length -->
        <div class="row margin-top-20">
            <label class="col-sm-3 control-label text-right" style="margin-top: 10px !important;">Race Length</label>
            <div class="form-group">  
            <div class="col-sm-3" style="padding-left:0px !important;">                 
                 <input type="number" class="form-control" id="length"  name="length" value="" placeholder="length of race in minutes" 
                    required 
                    data-fv-notempty-message="please ad the race length in minutes (e.g. 90)"
                    data-fv-integer="true"
                    data-fv-integer-message="must be number of minutes"
                 />
            </div>
            </div>
        </div>
        
        <!-- pn value -->
        <div class="row margin-top-20">
            <label class="col-sm-3 control-label text-right">Choose Handicap Source</label>                                 
            <div class="col-sm-8">
              <label class="radio-inline"><input type="radio" name="pntype" value="national" checked>&nbsp;RYA PY&nbsp;&nbsp;&nbsp;</label>
              <label class="radio-inline"><input type="radio" name="pntype" value="local" >&nbsp;Local PY&nbsp;&nbsp;&nbsp;</label>                 
            </div>               
        </div>
        
        <!-- start interval -->
        <div class="row margin-top-20">
            <label class="col-sm-3 control-label text-right">Choose Start Interval</label>                                 
            <div class="col-sm-8">
              <label class="radio-inline"><input type="radio" name="startint" value="60" checked>&nbsp;1 minute&nbsp;&nbsp;&nbsp;</label>
              <label class="radio-inline"><input type="radio" name="startint" value="30" >&nbsp;30 seconds&nbsp;&nbsp;&nbsp;</label>                 
            </div>               
        </div>
        
        <!-- boat types --> 
        <div class="row margin-top-20">
            <label class="col-sm-3 control-label text-right">Include Boat Types</label>                         
            <div class="col-sm-5">
              <label class="checkbox-inline"><input type="checkbox" name="typeD" value="1" checked>&nbsp;Dinghy&nbsp;&nbsp;&nbsp;</label>
              <label class="checkbox-inline"><input type="checkbox" name="typeK" value="0" >&nbsp;Keelboat&nbsp;&nbsp;&nbsp;</label>               
              <label class="checkbox-inline"><input type="checkbox" name="typeF" value="0" >&nbsp;Foiler&nbsp;&nbsp;&nbsp;</label>
              <label class="checkbox-inline"><input type="checkbox" name="typeM" value="0" >&nbsp;Multihull&nbsp;&nbsp;&nbsp;</label>                           
            </div>  
            <div class="col-sm-4 help-block">select the boat types you want to include - must be at least one type</div>            
        </div>                  
            
        <div class="row margin-top-20">
            <div class="col-sm-8 col-sm-offset-1">
                <div class="pull-left">
                    <a class="btn btn-md btn-warning" style="min-width: 200px;" type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
                    <span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;<b>Cancel</b></a>
                </div>
                <div class="pull-right">
                    <button type="submit" class="btn btn-md btn-primary"  style="min-width: 200px;" >
                    <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;&nbsp;<b>Get Start Times</b></button>
                </div>
            </div>
        </div>
        </form>
    </div>
    <script language="javascript">
        $(document).ready(function() {
            $('#pursuitstartForm').formValidation({
                excluded: [':disabled'],
            })
            $('#resetBtn').click(function() {
             $('#pursuitstartForm').data('bootstrapValidator').resetForm(true);
            });
        });
    </script>
    <script language="javascript">
    function quitBox(cmd)
    {   
        if (cmd=='quit')
        {
            open(location, '_self').close();
        }   
        return false;   
    }
    </script>
    

<script>
EOT;
    return $bufr;
}