<?php
/**
 * layouts_tm.php
 *
 * @abstract Page layout templates for the racebox application
 *
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 *
 * %%copyright%%
 * %%license%%
 *
 * templates:
 *     two_col_page
 *     basic_page
 *     support_team
 */

function two_col_page($params = array())
{
    $refresh = "";
    if (isset($params['refresh']) and $params['refresh'] > 0) {
        $refresh = "<meta http-equiv=\"refresh\" content=\"{$params['refresh']}\">";
    }

    isset($params['body_attr']) ? $body_attr = $params['body_attr'] : $body_attr = "";

    isset($params["l_width"]) ? $l_width = $params["l_width"] : $l_width = 10;         // set column widths
    $r_width = 12 - $l_width;

    $form_links = "";
    if ($params['forms']) {
        $form_links = <<<EOT
        <link rel="stylesheet" href="{loc}/common/oss/bs-validator/dist/css/formValidation.min.css">
        <script type="text/javascript" src="{loc}/common/oss/bs-validator/dist/js/formValidation.min.js"></script>
        <script type="text/javascript" src="{loc}/common/oss/bs-validator/dist/js/framework/bootstrap.min.js"></script>
        <script type="text/javascript" src="{loc}/common/oss/bs-validator/dist/js/addons/mandatoryIcon.js"></script>
EOT;
    }
    $table_links = "";
    if ($params['tables']) {
        $table_links = <<<EOT
        <link rel="stylesheet" href="{loc}/common/oss/bs-jasny/css/jasny-bootstrap.min.css">
        <script type="text/javascript" src="{loc}/common/oss/bs-jasny/js/jasny-bootstrap.min.js"></script>
EOT;
    }

    $tab_context = "";
    if ($params['page'] == "entries" OR $params['page'] == "timer" OR $params['page'] == "results") {
        $tab_context = <<<EOT
        <!-- keeps panel (fleet) context on relevant pages -->
        <script type="text/javascript">
            $(function() {
              $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
                localStorage.setItem('lastTab', $(this).attr('href'));
              });

              var lastTab = localStorage.getItem('lastTab');

              if (lastTab) {
                  $('[href="'+lastTab+'"]').tab('show');
              }
              else {
                  $('[href="#fleet1"]').tab('show');
              }
            });
        </script>
EOT;
    }

    if ($params['page']=="start")
    {
        $countdown = <<<EOT
        <script type="text/javascript" src="{loc}/common/scripts/jquery.countdown.js"></script>
EOT;
    }
    else
    {
        $countdown = "";
    }

    $html = <<<EOT
    <!DOCTYPE html><html lang="en">
    <head>
            <title>{title}</title>
            $refresh
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="description" content="">
            <meta name="author" content="">

            <link   rel="shortcut icon"    href="{loc}/common/images/favicon.ico">
            <link   rel="stylesheet"       href="{loc}/common/oss/bootstrap/css/bootstrap.min.css" >
            <link   rel="stylesheet"       href="{loc}/common/oss/bootstrap/css/bootstrap-theme.min.css">
            <script type="text/javascript" src="{loc}/common/oss/jquery/jquery.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bootstrap/js/bootstrap.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bs-growl/jquery.bootstrap-growl.min.js"></script>
            <script type="text/javascript" src="{loc}/common/scripts/clock.js"></script>

            $form_links
            $table_links

            <!-- Custom styles for this template -->
            <link href="{stylesheet}" rel="stylesheet">

    </head>
    <body $body_attr>
        $countdown
        {navbar}
        <div class="container-fluid" role="main">
            <div class="row">
                <div id="lhcol" class="col-md-$l_width col-sm-$l_width col-xs-$l_width" style="padding-right: 5%">
                    {l_top}
                    {l_mid}
                    {l_bot}
                </div>
                <div id="rhcol" class="col-md-$r_width col-sm-$r_width col-xs-$r_width">
                    <div class="margin-top-20">

                        {r_top}
                        {r_mid}
                        {r_bot}

                    </div>
                </div>
            </div>
        </div>
        {footer}

        <!-- popover activation for all popovers -->
        <script type="text/javascript">
            $(document).ready(function() {
            $("[data-toggle=popover]").popover({trigger: 'hover',html: 'true'});
            });
        </script>

        <!-- tooltip activation for all tooltips -->
        <script type="text/javascript">
            $(document).ready(function() {
            $("[data-toggle=tooltip]").tooltip({trigger: 'hover',html: 'true'});
            });
        </script>
        $tab_context
    </body>
    </html>
EOT;
    return $html;
}


function basic_page($params = array())
{
    if (isset($params['form_validation'])) {
        $formval_hdr = <<<EOT
        <link rel="stylesheet" href="{loc}/common/oss/bs-validator/dist/css/formValidation.min.css">
        <script type="text/javascript" src="{loc}/common/oss/bs-validator/dist/js/formValidation.min.js"></script>
        <script type="text/javascript" src="{loc}/common/oss/bs-validator/dist/js/framework/bootstrap.min.js"></script>
        <script type="text/javascript" src="{loc}/common/oss/bs-validator/dist/js/addons/mandatoryIcon.js"></script>
EOT;

        $formval_ftr = <<<EOT
        <script>
            $(document).ready(function() {
                $('#{id}Form').formValidation({
                    excluded: [':disabled'],
                })
                $('#resetBtn').click(function() {
                 $('#{id}Form').data('bootstrapValidator').resetForm(true);
                });
            });
        </script>
EOT;
    }
    else
    {
        $formval_hdr = "";
        $formval_ftr = "";
    }
    $html = <<<EOT
    <!DOCTYPE html><html lang="en">
    <head>
            <title>{title}</title>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="description" content="">
            <meta name="author" content="">

            <link   rel="shortcut icon"    href="{loc}/common/images/favicon.ico">
            <link   rel="stylesheet"       href="{loc}/common/oss/bootstrap/css/bootstrap.min.css" >
            <link   rel="stylesheet"       href="{loc}/common/oss/bootstrap/css/bootstrap-theme.min.css">
            <script type="text/javascript" src="{loc}/common/oss/jquery/jquery.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bootstrap/js/bootstrap.min.js"></script>
            <script type="text/javascript" src="{loc}/common/oss/bs-growl/jquery.bootstrap-growl.min.js"></script>

            $formval_hdr

            <!-- Custom styles for this template -->
            <link href="{stylesheet}" rel="stylesheet">

    </head>
    <body>
        <script type="text/javascript" src="../common/oss/countuptimer/dist/jquery.countdown.js"></script>
        {navbar}
        <div class="container-fluid" role="main">
            {body}
            $formval_ftr
        </div>
        {footer}



        <!-- popover activation for all popovers -->
        <script type="text/javascript">
            $(document).ready(function() {
            $("[data-toggle=popover]").popover({trigger: 'hover',html: 'true'});
            });
        </script>

        <!-- tooltip activation for all tooltips -->
        <script type="text/javascript">
            $(document).ready(function() {
            $("[data-toggle=tooltip]").tooltip({trigger: 'hover',html: 'true'});
            });
        </script>
    </body>
    </html>
EOT;
    return $html;

}





function support_team($params = array())
{
    $html = <<<EOT
    <a class="btn btn-default" role="button" data-content="{info}" data-toggle="popover"
        data-placement="bottom" href="#" data-original-title="<b>{label}</b>">
        <h4>
            <span class="glyphicon glyphicon-user text-primary" style="font-size: 2em;"></span>
            <span class="glyphicon glyphicon-user text-danger"  style="font-size: 2em;"></span>
        </h4>
        {label}
    </a>
EOT;
    return $html;
}

?>