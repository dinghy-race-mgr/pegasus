<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 21/08/2020
 * Time: 18:24
 */

function report_page($params=array())
{
    $html = <<<EOT
    <!DOCTYPE html><html lang="en">
    <head>
        <title>{title}</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="racemanager">
        <meta name="author" content="mark elkington">

        <meta http-equiv="cache-control" content="max-age=0" />
        <meta http-equiv="cache-control" content="no-cache" />
        <meta http-equiv="expires" content="0" />
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <meta http-equiv="pragma" content="no-cache" />
        
        <link rel="icon" href="../common/images/logos/favicon.png">

        <!-- Custom styles for this template -->
        {style}

    <head>
    <body>
        {body}
    </body>
    </html>
EOT;

    return $html;
}

function table_style1($params=array())
{
    $html = <<<EOT
    <style>
      body      {font-family: Kalinga,sans-serif; font-size: 0.8em;}
        h1      {font-family: Kalinga,arial,helvetica,sans-serif; font-size: 250%; letter-spacing: -1px; color: rgb(44, 76, 124);}
        h2      {font-family: Kalinga,arial,helvetica,sans-serif; font-size: 200%; color: rgb(44, 76, 124);}
        h3      {font-family: Kalinga,arial,helvetica,sans-serif; font-size: 150%; color: rgb(194, 0, 0);}
        p       {font-weight: normal; color: rgb(0, 0, 0); line-height: 1.2em; padding-bottom: 0.2em;}
        
        td      {display: table-cell; vertical-align: inherit; padding: 5px}
        th      {padding: 5px} 
        a:link  {color: rgb(44, 76, 124); text-decoration: none;}
               
        .container { display: flex; }       
        .column { flex: 1; }
        
        .title  {font-family: Kalinga,arial,helvetica,sans-serif; font-weight: bold; font-size: 250%; letter-spacing: -1px; color: rgb(44, 76, 124);}
        .title2 {font-family: Kalinga,arial,helvetica,sans-serif; font-weight: bold; font-size: 200%; color: rgb(44, 76, 124); padding-top: 10px}
        
        .divider   {border-bottom: 1px solid rgb(204, 204, 204); margin-top: 5px; margin-bottom: 5px;}        
        .clearfix   {display: block;}

        .table      {display: table; width: 100%;}
        .table-row  {display: table-row;}
        .table-cell {display: table-cell;}
        .truncate   {white-space: nowrap; overflow: hidden; text-overflow: ellipsis;}
        
        .note    {font-weight: normal; color: rgb(0, 0, 0); line-height: 1.2em; margin: 0px; }
        
        .text-center {text-align: center;}
        .text-right {text-align: right; padding-right: 15px; }
        .text-left {text-align: left; padding-left: 15px; }
        .text-grey   {color: rgb(119, 119, 119);}
        .text-alert  {color: rgb(200, 76, 44)}
        
        .pull-right  {text-align: right;}
        .pull-left   {text-align: left;}
        .pull-center {text-align: center;}
        
        .noshade   {background: none repeat scroll 0% 0% rgb(255, 255, 255);}
        .lightshade{background: none repeat scroll 0% 0% rgb(238, 238, 238);}
        .darkshade {background: none repeat scroll 0% 0% rgb(153, 153, 153);}
        
        @media all {
           .page-break	{ display: none; }
        }
        
        @media print {
           .noprint { display:none }
           .page-break	{ display: block; page-break-before: always; }
        }

    </style>
EOT;

    return $html;
}

function report_style($params=array())
{
    $html = <<<EOT
        <style>
        :root {
           --blue: steelblue;
           --darkblue: rgb(44, 76, 124);
           --red: rgb(194, 0, 0);
           --darkred: darkred;
           --white: white;
           --black: black;
        }
        
        
        *            {box-sizing: border-box;}
        /*body         {font-family: verdana,sans-serif; font-size: 1.0em;}*/
        /*h1           {font-family: Kalinga,arial,helvetica,sans-serif; font-size: 200%; letter-spacing: -1px; color: var(--darkblue);}*/
        /*h2           {font-family: Kalinga,arial,helvetica,sans-serif; font-size: 150%; color: var(--darkblue);}*/
        /*h3           {font-family: Kalinga,arial,helvetica,sans-serif; font-size: 125%; color: var(--red);}*/
        /*p            {font-weight: normal; color: rgb(0, 0, 0); font-size: 1.0em; line-height: 1.2em; padding-bottom: 0.2em;}*/
        
        /*td           {display: table-cell; vertical-align: inherit;}*/
        /*th           {font-size: 0.8em !important; font-weight: normal !important;}*/
        /*a:link       {color: var(--darkblue); text-decoration: none;}*/
        
        /*.title       {font-family: Kalinga,arial,helvetica,sans-serif; font-weight: bold; font-size: 200%; letter-spacing: -1px; color: var(--darkblue);}*/
        /*.title2      {font-family: Kalinga,arial,helvetica,sans-serif; font-weight: bold; font-size: 150%; color: var(--darkblue); padding-top: 40px}*/
        
        /*.divider     {border-top: 1px solid slategrey; margin-top: 15px; margin-bottom: 5px;}*/
        /*.clearfix    {display: block;}*/
        
        /*.table       {display: table; width: 100%; min-width: 70%}*/
        /*.table-col   {font-size: 0.8em !important; font-weight: normal !important; background: none repeat scroll 0% 0% rgb(153, 153, 153);}*/
        /*.table-row   {display: table-row;}*/
        /*.table-cell  {display: table-cell; vertical-align: inherit;}*/
        /*.truncate    {white-space: nowrap; overflow: hidden; text-overflow: ellipsis;}*/
                
        .text-center {text-align: center;}
        .text-right  {text-align: right; padding-right: 15px; }
        .text-left   {text-align: left; padding-left: 15px; }
        .text-grey   {color: slategrey;}
        .text-alert  {color: var(--darkred);}
        .text-info   {color: var(--blue);}
        .small-note  {color: var(--blue); font-weight: normal; line-height: 1.0em; font-size: 0.8em;}
        .note        {font-weight: normal; color: rgb(0, 0, 0); line-height: 1.2em; margin: 0px; }
        
        .pull-right  {text-align: right;}
        .pull-left   {text-align: left;}
        .pull-center {text-align: center;}
        .pull-top    {vertical-align: top;}
        
        .noshade     {background: none repeat scroll 0% 0% rgb(255, 255, 255);}
        .lightshade  {background: none repeat scroll 0% 0% rgb(238, 238, 238);}
        .darkshade   {background: none repeat scroll 0% 0% rgb(153, 153, 153);}
        
        /* --------------------------------------------------------------------------------------------------------------------*/
        
        .report-hdr  {font-family: Kalinga,Verdana,sans-serif; font-weight: bold; font-size: 150%; color: var(--darkblue); padding-top: 40px; text-align: right;}
        .club-logo   {border: 1px solid #ddd; border-radius: 4px; padding: 5px; width: 330px;}
        .event-hdr-left  {font-family: Kalinga,Verdana,sans-serif; font-weight: bold; font-size: 200%; letter-spacing: -1px; color: var(--darkblue); text-align: left; width: 50%; display: inline-block;}
        .event-hdr-right {width: 45%; display: inline-block; text-align: right; color: var(--darkred);}
        .series-notes {text-align: left; color: var(--darkred);}
        .fleet-hdr   {font-family: Kalinga,arial,helvetica,sans-serif; font-weight: bold; font-size: 150%; color: var(--darkblue); padding-top: 40px}
        .fleet-info  {font-family: Verdana,sans-serif; font-size: 1.0em; text-align: left;}
        .spacer      {border-top: 1px solid slategrey; margin-top: 15px; margin-bottom: 5px; display: block;}
        .codes-info  {color: var(--blue); font-weight: normal; line-height: 1.0em; font-size: 0.8em; margin-top: 25px;}
        .footer      {color: var(--blue); font-weight: normal; line-height: 1.0em; font-size: 0.8em; margin-top: 25px;}
        

        
        
        
        @media all   {.page-break	{ display: none; }  }
        
        /* print styles */
        @media print {
           @page         { size: A4 portrait; }
           @page :left   { margin-left: 2cm; }
           @page :right  { margin-left: 2cm; }
        
           table, figure { page-break-inside: avoid; }
           body          { font-size: 12pt; }
        
           .noprint      { display: none; }
           .page-break	 { display: block; page-break-before: always; }
        }
        </style>         

EOT;

}