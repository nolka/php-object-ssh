<?php

class Ssh
{

    private $conn;
    private $err;
    private $out;
    private $in;
    
    private $shell;
    
    private $host;
    private $port;

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
            return $this->exec($name." ".implode(" ", $arguments));
        }
    }

    public function connect($host, $port = 22)
    {
        $this->host = $host;
        $this->port = $port;
        
        $this->conn = ssh2_connect($host, $port);
        if ($this->conn === false)
        {
            throw new Exception("Failed to connect to host {$host}:{$port}");
        }
    }

    public function auth($login, $passwd)
    {
        if (!ssh2_auth_password($this->conn, $login, $passwd))
        {
            throw new Exception("Authentication failed!");
        }
    }
    
    public function exec($cmd)
    {
        $this->out = ssh2_exec($this->conn, $cmd);
            
        $this->error = ssh2_fetch_stream($this->out, SSH2_STREAM_STDERR);
        $errorMessage = $this->readStream($this->error);
        if (strlen($errorMessage) != 0)
        {
            throw new Exception($errorMessage);
        }

        return $this->readStream($this->out);
    }
    
    public function shell($cmd)
    {
        if($this->shell == null)
        {
            $this->shell = ssh2_shell($this->conn);
            sleep(1);
        }
        
        $this->readShellOutput();
        
        fwrite($this->shell, $cmd.PHP_EOL);
        sleep(1);
        
        return $this->readShellOutput();
        
    }
    
    public function endShell()
    {
        fclose($this->shell);
    }
    
    protected function readStream($stream)
    {
        $response = array();
        stream_set_blocking($stream, true);
        while ($response[] = fgets($stream))
        {
            flush();
        }
        stream_set_blocking($stream, false);
        return implode("", $response);
    }

    protected function readShellOutput()
    {
        $out = '';
        while ( $line = fgets ( $this->shell ) ) {
            $out .= $line;
        }
        return $out;
    }
    
    public function disconnect()
    {
        
    }

}