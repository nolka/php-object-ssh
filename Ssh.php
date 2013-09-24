<?php

class Ssh
{

    private $conn = null;
    private $err = null;
    private $out = null;
    private $in = null;
    private $shell = null;
    private $host;
    private $port;
    private $eventHandlers = array();
    private $lastCmd = null;
    private $shellCommandPrompt = null;

    const EVENT_ON_COMMAND_COMPLETE = 0;
    const EVENT_ON_SHELL_COMPLETE = 1;

    public function __construct()
    {
        if (!function_exists('ssh2_connect'))
        {
            throw new Exception("ssh2 module not installed!");
        }
    }

    public function __call($name, $arguments)
    {
        if (!in_array($name, get_class_methods($this)))
        {
            return $this->exec($name . " " . implode(" ", $arguments));
        }
    }

    public function connect($host, $port = 22)
    {
        $this->host = $host;
        $this->port = $port;

        $this->conn = @ssh2_connect($host, $port);
        if (!$this->conn)
        {
            throw new Exception("Failed to connect to host {$host}:{$port}");
        }
    }

    public function disconnect()
    {
        $this->conn = null;
        unset($this->conn);
    }

    public function auth($login, $passwd)
    {
        if (!ssh2_auth_password($this->conn, $login, $passwd))
        {
            throw new Exception("Authentication with login and password failed!");
        }
    }

    public function authFile($login, $pubkey, $privkey = null, $pass = null)
    {
        if (!ssh2_auth_pubkey_file($this->conn, $login, $pubkey, $privkey, $pass))
        {
            throw new Exception("Authorization with public key failed");
        }
    }

    public function getExitCode()
    {
        return intval($this->exec('echo $?'));
    }

    public function exec($cmd)
    {
        // removing blank lines or comments
        $cmd = preg_replace("/(^[\r\n\#]*|[\r\n]+)[\s\t]*[\r\n]+/", "", $cmd);
        $this->lastCmd = $cmd;
        if ($this->shell !== null)
        {
            return $this->shell($cmd);
        }
        else
        {
            $stream = ssh2_exec($this->conn, $cmd);

            $this->error = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
            $errorMessage = $this->readStream($this->error, true);
            if (strlen($errorMessage) != 0)
            {
                throw new Exception($errorMessage);
            }

            return $this->readStream($stream);
        }
    }

    public function execBatch($code, $delimiter = "\r\n")
    {
        $commands = explode("\r\n", $code);
        foreach ($commands as $cmd)
        {
            $result = $this->exec($cmd);
        }
        return $result;
    }

    public function shell($cmd)
    {
        $this->lastCmd = $cmd;
        if ($this->shell == null)
        {
            $this->shell = ssh2_shell($this->conn);
            //sleep(1);
            $welcome = explode("\n", $this->readShellOutput());
            $this->shellCommandPrompt = array_pop($welcome);
        }

        fwrite($this->shell, $cmd . PHP_EOL);

        return $this->readShellOutput();
    }

    public function endShell()
    {
        fclose($this->shell);
        $this->shell = null;
    }

    protected function readStream($stream, $isStdErr = false)
    {
        stream_set_blocking($stream, true);
        $response = stream_get_contents($stream);
        stream_set_blocking($stream, false);
        if (!$isStdErr)
            $this->raiseEvent(self::EVENT_ON_COMMAND_COMPLETE, array($this->lastCmd, $response));
        return $response;
    }

    protected function readShellOutput($raiseEvent = true)
    {
        $response = null;
        sleep(1);
        if ($this->shellCommandPrompt == null)
        {
            $response = stream_get_contents($this->shell);
        }
        else
        {
            $response = array();
            $promptLen = strlen($this->shellCommandPrompt);
            while ($line = fgets($this->shell))
            {
                if (substr($line, 0, $promptLen) == $this->shellCommandPrompt)
                {
                    
                } // do nothing
                else if (substr($line, 0, 2) == "> ")
                {
                    
                } // do nothing
                else
                {
                    $response[] = $line;
                }
            }
            $response = implode("", $response);
        }
        if ($raiseEvent)
            $this->raiseEvent(self::EVENT_ON_SHELL_COMPLETE, array($this->lastCmd, $response));
        return preg_replace('/^.+\n/', '', $response);
    }

    public function raiseEvent($event, $args)
    {
        if (empty($this->eventHandlers))
            return;
        foreach ($this->eventHandlers[$event] as $handler)
        {
            call_user_func_array($handler, $args);
        }
    }

    public function attachEventHandler($event, $handler)
    {
        $this->eventHandlers[$event][] = $handler;
    }

}