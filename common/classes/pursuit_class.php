<?php
class PURSUIT
{
    public function __construct($eventid)
    {
        $this->eventid = $eventid;
    }

    public function render_form($entry, $last)
    {
        $scoring_arr = array("NCS"=>"wrong course", "OCS"=>"false start", "DNF"=>"did not finish", "DNS"=>"did not start", "DNC"=>"did not launch");

        $lblw = "8";
        $fldw = "4";

        if (empty($entry['f_line']) or empty($entry['f_pos']) )    // no finish yet for this boat
        {
            if (empty($last))                                      // first boat finished
            {
                $val['f_line'] = "";
                $val['f_pos'] = "";
                empty($entry['lap']) ? $val['lap'] = "" : $val['lap'] = $entry['lap'];
                empty($entry['code']) ? $val['code'] = "" : $val['code'] = $entry['lap'];
                $entered = false;
            }
            else                                                   // use last boat finished as guess
            {
                $val['f_line'] = $last['f_line'];
                $val['f_pos'] = $last['f_pos'] + 1;
                empty($entry['lap']) ? $val['lap'] = $last['lap'] : $val['lap'] = $entry['lap'];
                empty($entry['code']) ? $val['code'] = "" : $val['code'] = $entry['code'];
                $entered = false;
            }
        }
        else                                                       // already have finish use db values
        {
            $val = array("f_line" => $entry['f_line'], "f_pos" => $entry['f_pos'], "lap" => $entry['lap'], "code" => $entry['code']);
            $entered = true;
        }

        $code_options_htm = "<option value='' > - none - </option>";
        foreach ($scoring_arr as $code=>$text)
        {
            $code == $entry['code'] ? $selected = "selected" : $selected = "";
            $code_options_htm.= "<option value='$code' $selected>$code - $text&nbsp;</option>";
        }

        $htm = "";

        $entered ? $status_txt = "<p class='text-success'>Submitted values</p>" : $status_txt = "<p class='text-warning'>Predicted Values</p>";

        $htm.=<<<EOT
        <div class="panel panel-success margin-top-40">
            <div class="panel-heading"><h4 class="panel-title">Record Boat Finish ...</h4></div>
            <div class="panel-body">
                <div style="min-height:350px">
                <p style="font-size: 1.3em">{$entry['class']} - {$entry['sailnum']}</p>
                $status_txt
                <p></p>
                <form id="resulteditForm" class="form-horizontal" action="timer_sc.php?eventid={$this->eventid}&pagestate=processfinishpursuit" method="post"
                    data-fv-framework="bootstrap"
                    data-fv-icon-valid="glyphicon glyphicon-ok"
                    data-fv-icon-invalid="glyphicon glyphicon-remove"
                    data-fv-icon-validating="glyphicon glyphicon-refresh">
                          
                    <!-- hidden fields -->
                    <input name="entryid" type="hidden" value="{$entry['id']}">   
                    <input name="prev_fl" type="hidden" value="{$entry['f_line']}">  
                    <input name="prev_status" type="hidden" value="{$entry['status']}"> 
                    <input name="boat" type="hidden" value="{$entry['class']} - {$entry['sailnum']}"> 
                                     
                    <!-- finish line -->
                    <div class="form-group">
                        <label class="col-xs-$lblw control-label text-success">finish line</label>
                        <div class="col-xs-$fldw inputfieldgroup">
                            <input type="text" class="form-control" id="f_line" name="f_line" value="{$val['f_line']}" autofocus
                        />
                        </div>
                    </div>
                    
                    <!-- finish line position -->
                    <div class="form-group">
                        <label class="col-xs-$lblw control-label text-success">finish position</label>
                        <div class="col-xs-$fldw inputfieldgroup">
                            <input type="text" class="form-control" id="f_pos" name="f_pos" value="{$val['f_pos']}"
                        />
                        </div>
                    </div>
                    
                    <!-- laps -->
                    <div class="form-group">
                        <label class="col-xs-$lblw control-label text-success">laps</label>
                        <div class="col-xs-$fldw inputfieldgroup">
                            <input type="text" class="form-control" id="lap" name="lap" value="{$val['lap']}"
                        />
                        </div>
                    </div>
                    
                    <!-- code -->
                    <div class="form-group">
                        <label class="col-xs-2 control-label text-success">code</label>
                        <div class = "col-xs-10">
                            <select class="form-control" name="code" id="idcode">
                                $code_options_htm
                            </select >
                        </div>
                    </div>
                    
                    <!-- cancel/submit buttons -->
                    <div>
                        <table style="width:90%"><tr>
                        <td class="align-top pull-left">
                            <!--button onclick='window.top.location.href = "timer_pg.php?eventid={$this->eventid}";' type="button" class="btn btn-xs btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove"></span>&nbsp;cancel</button -->
                        </td>
                        <td class="align-top pull-right">
                            <button type="submit" class="btn btn-sm btn-success"><span class="glyphicon glyphicon-ok"></span>&nbsp;UPDATE</button>
                        </td>                   
                        </tr></table>
                    </div>
                    
                    <!-- script>
                        $(document).ready(function() {
                            $('#resulteditForm').formValidation({
                                excluded: [':disabled'],
                            })
                            $('#resetBtn').click(function() {
                             $('#{id}Form').data('bootstrapValidator').resetForm(true);
                            });
                        });
                    </script -->
                               
                </form> 
                </div>          
            </div>
        </div>
EOT;
        return $htm;
    }

    public function process_form()
    {

    }

    public function render_empty_form($boat, $notes, $style = "normal")
    {
        if (empty($boat))
        {
            $report = "";
        }
        else
        {
            if ($style == "warning")
            {
                $report = <<<EOT
                <div class="alert alert-danger">
                  <strong>$notes</strong> .
                </div>
EOT;
            }
            else
            {
                $report = <<<EOT
                <div class="alert alert-success">              
                  <strong>$notes</strong>
                </div>
EOT;
            }


        }



        $htm = <<<EOT
        <div class="panel panel-success margin-top-40">
            <div class="panel-heading"><h4 class="panel-title">Record Boat Finish ...</h4></div>
            <div class="panel-body">
                <div style="min-height:350px">
                <p class='text-success'>last boat processed:</p> 
                <p style="font-size: 1.3em">$boat</p>
                $report
                </div>
            </div>
        </div>
EOT;

        return $htm;
    }
}
