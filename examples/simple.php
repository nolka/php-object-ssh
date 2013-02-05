<?

header('Content-type: text/plain');

include "../Ssh.php";

$ssh = new Ssh();
$ssh->connect('localhsot');
$ssh->auth('user', 'password');
echo $ssh->uptime();
