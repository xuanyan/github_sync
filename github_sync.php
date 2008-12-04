<?php

/* www.kukufun.com github_sync by xuanyan <xunayan1983@gmail.com> */

error_reporting(E_ALL);
set_time_limit(0);
define('ROOT_PATH', dirname(__FILE__));

if (!isset($_POST['payload']))
{
    exit;
}

if (get_magic_quotes_gpc())
{
    $_POST['payload'] = stripslashes($_POST['payload']);
}

if (!$array = @json_decode($_POST['payload'], true))
{
    exit;
}

if (empty($array['commits']))
{
    exit;
}

foreach ($array['commits'] as $key => $val)
{
    $update_lists = array();
    if (!empty($val['modified']))
    {
        $update_lists = array_merge($update_lists, $val['modified']);
    }

    if (!empty($val['added']))
    {
        $update_lists = array_merge($update_lists, $val['added']);
    }

    if (!empty($val['removed']))
    {
        foreach ($val['removed'] as $v)
        {
            $filename = ROOT_PATH . '/' . $v;
            if (file_exists($filename))
            {
                @unlink($filename);
            }
        }
    }

    $url = dirname(dirname($val['url'])) . "/raw/$val[id]/%s";

    foreach ($update_lists as $v)
    {
        $data = @file_get_contents(sprintf($url, urlencode($v)));
        $filename = ROOT_PATH . '/' . $v;
        $dir = dirname($filename);
        if (!file_exists($dir))
        {
            @mkdir($dir, 0777, true);
        }
        @file_put_contents($filename, $data);
    }
}

?>