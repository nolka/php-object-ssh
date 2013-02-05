<?

header('Content-type: text/plain');

include "../Ssh.php";

$ssh = new Ssh();
$ssh->connect('192.168.1.116');
$ssh->auth('tech', 'tech');
$ssh->attachEventHandler(Ssh::EVENT_ON_COMMAND_COMPLETE, function($cmd, $resp)
{
  print_r(array(
    'command',
    'command' => $cmd,
    'response' => $resp
    ));
});
$ssh->attachEventHandler(Ssh::EVENT_ON_SHELL_COMPLETE, function($cmd, $resp)
{
  print_r(array(
    'shell',
    'command' => $cmd,
    'response' => $resp
    ));
});

$ssh->shell('sudo -i');
$ssh->shell('tech');
$ssh->whoami();
$ssh->exit();
$ssh->whoami();
$ssh->uptime();