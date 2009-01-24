<?php

/* www.kukufun.com GithubSync by xuanyan <xunayan1983@gmail.com> */

class GithubSync
{
    private static $root_path = '';
    private static $url = '';

    public static function Start($public_clone_url, $syncto = '.', $branch = 'master')
    {
        set_time_limit(0);

        if (!$payload = self::GetPayload())
        {
            return false;
        }

        $public_url = str_replace(array('git:', '.git'), array('http:', ''), $public_clone_url);
        self::$url = $public_url . "/raw/$branch/%s";
        self::$root_path = $syncto;

        $uplists = array();
        foreach ($payload['commits'] as $key => $val)
        {
            if (!$update_lists = self::GetUpdateLists($val))
            {
                continue;
            }
            $uplists += $update_lists;
        }

        foreach ($uplists as $v)
        {
            self::UpdateFile($v);
        }

        return count($uplists);
    }

    private static function UpdateFile($file)
    {
        $data = @file_get_contents(sprintf(self::$url, urlencode($file)));
        $filename = self::$root_path . '/' . $file;

        // if find : update it else: delete it!
        if (stripos($http_response_header[0], 'HTTP/1.1 200') !== false)
        {
            @mkdir(dirname($filename), 0777, true);
            @file_put_contents($filename, $data);
        }
        else
        {
            @unlink($filename);
        }
    }

    private static function GetUpdateLists($commits)
    {
        $update_lists = array();
        if (!empty($commits['modified']))
        {
            $update_lists += $commits['modified'];
        }

        if (!empty($commits['added']))
        {
            $update_lists += $commits['added'];
        }

        if (!empty($commits['removed']))
        {
            $update_lists += $commits['removed'];
        }

        return $update_lists;
    }

    private static function GetPayload()
    {
        if (!isset($_POST['payload']))
        {
            return false;
        }

        if (get_magic_quotes_gpc())
        {
            $_POST['payload'] = stripslashes($_POST['payload']);
        }

        if (!$array = @json_decode($_POST['payload'], true))
        {
            return false;
        }

        if (empty($array['commits']))
        {
            return false;
        }

        return $array;
    }
}
?>
