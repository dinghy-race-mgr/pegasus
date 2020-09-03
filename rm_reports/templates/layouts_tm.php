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
      body    {font-family: Kalinga,sans-serif; font-size: 0.8em;}
        h1      {font-family: Kalinga,arial,helvetica,sans-serif; font-size: 250%; letter-spacing: -1px; color: rgb(44, 76, 124);}
        h2      {font-family: Kalinga,arial,helvetica,sans-serif; font-size: 200%; color: rgb(44, 76, 124);}
        h3      {font-family: Kalinga,arial,helvetica,sans-serif; font-size: 150%; color: rgb(194, 0, 0);}
        p       {font-weight: normal; color: rgb(0, 0, 0); line-height: 1.2em; padding-bottom: 0.2em;}
        
        td      {display: table-cell; vertical-align: inherit; padding: 5px}
        th      {padding: 5px} 
        a:link  {color: rgb(44, 76, 124); text-decoration: none;}
        
        .title {font-family: Kalinga,arial,helvetica,sans-serif; font-weight: bold; font-size: 250%; letter-spacing: -1px; color: rgb(44, 76, 124);}
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