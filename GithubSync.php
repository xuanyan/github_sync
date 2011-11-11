<?php

/*
 * This file is part of the Geek-Zoo Projects.
 *
 * @copyright (c) 2011 Geek-Zoo Projects More info http://www.geek-zoo.com
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License
 * @author xuanyan <xuanyan@geek-zoo.com>
 *
 */

class GitHubSync
{
    private $config = array(
        'branch' => null,
        'syncfolder' => null,
        'raw_url' => 'https://raw.github.com/:owner_name/:name/:branch/%s',
        'log_file' => null
    );

    function __construct($config = array())
    {
        set_time_limit(0);

        $this->config = array_merge($this->config, $config);
        if ($this->config['syncfolder'] === null) {
            $this->config['syncfolder'] = dirname(__FILE__);
        }
    }

    public function start()
    {
        $this->log(date('Y-m-d H:i:s:'));
        if (!$payload = $this->getPayload()) {
            return false;
        }

        if (!isset($payload['ref']) || !isset($payload['repository']['url'])) {
            $this->log('cant get payload[ref] or payload[repository][url]');
            return false;
        }

        $this->log(var_export($payload, true));
        
        $payload['ref'] = explode('/', $payload['ref']);
        $branch = end($payload['ref']);
        
        if ($this->config['branch'] !== null) {
            // if is set the sync branch then check the current branch if need sync
            if ($branch != $this->config['branch']) {
                $this->log("current branch : $branch   sync branch: {$this->config['branch']}");
                return false;
            }
        } else {
            // if is not set the sync branch then add the branch follder
            $this->config['syncfolder'] .= "/$branch";
            $this->log("syncfolder : {$this->config['syncfolder']}");
        }

        $payload['repository']['url'] = substr(strrchr($payload['repository']['url'], '.com/'), 5);
        list($owner_name, $name) = explode('/', $payload['repository']['url']);

        $this->config['raw_url'] = strtr($this->config['raw_url'], array(
            ':owner_name' => $owner_name,
            ':name' => $name,
            ':branch' => $branch
        ));

        $uplists = array();
        foreach ($payload['commits'] as $key => $val) {
            $this->log("id: {$val['id']}");
            
            $uplists += ($val['removed']+$val['modified']+$val['added']);
        }
        array_map(array($this, 'updateFile'), $uplists);

        return count($uplists);
    }

    private function log($msg)
    {
        if ($this->config['log_file'] === null) {
            return false;
        }

        return @file_put_contents($this->config['log_file'], $msg."\n", FILE_APPEND);
    }

    private function UpdateFile($file)
    {
        $data = @file_get_contents(sprintf($this->config['raw_url'], urlencode($file)));
        $filename = $this->config['syncfolder'] . '/' . $file;

        // if find : update it else: delete it!
        if (stripos($http_response_header[0], 'HTTP/1.1 200') !== false) {
            $this->log("update file: $filename");
            
            @mkdir(dirname($filename), 0777, true);
            @file_put_contents($filename, $data);
        } else {
            $this->log("delete file: $filename");
            @unlink($filename);
        }
    }

    private function getPayload()
    {
        if (!isset($_POST['payload'])) {
            $this->log('$_POST[payload] is not set');
            return false;
        }
        // did not do the stripslashes thing
        //$_POST['payload'] = stripslashes($_POST['payload']);

        if (!$array = @json_decode($_POST['payload'], true)) {
            $this->log('cant decode $_POST[payload] json, check the magic GPC');
            
            return false;
        }

        if (empty($array['commits'])) {
            $this->log('$_POST[payload] did not have the coomits array');
            return false;
        }

        return $array;
    }
}

?>