<?php

/* www.kukufun.com github_sync by xuanyan <xunayan1983@gmail.com> */


error_reporting(E_ALL);
set_time_limit(0);
require_once './GithubSync.php';
GithubSync::Start('git://github.com/xuanyan/github_sync.git', dirname(__FILE__));

exit;


?>