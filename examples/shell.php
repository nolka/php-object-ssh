<?

header('Content-type: text/plain');

include "../Ssh.php";

$ssh = new Ssh();
$ssh->connect('192.168.1.116');
$ssh->auth('tech', 'tech');
echo $ssh->shell('sudo -i');
echo $ssh->shell('tech');
echo $ssh->whoami();
