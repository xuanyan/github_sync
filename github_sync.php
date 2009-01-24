<?php

/* www.kukufun.com github_sync by xuanyan <xunayan1983@gmail.com> */


error_reporting(E_ALL);
set_time_limit(0);

require_once './GithubSync.php';

if (get_magic_quotes_gpc())
{
    $_POST['payload'] = stripslashes($_POST['payload']);
}
$array = @json_decode($_POST['payload'], true);

file_put_contents('./github_in.text', var_export($array, true));

GithubSync::Start('git://github.com/xuanyan/github_sync.git', dirname(__FILE__));

exit;


?>