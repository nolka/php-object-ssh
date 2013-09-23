<?

header('Content-type: text/plain');

include "../Ssh.php";

$ssh = new Ssh();
$ssh->connect('192.168.1.116');
$ssh->auth('tech', 'tech');
try{
$tpl = "<<EOF
%s
EOF\n";

$text = implode("\n", array('line1', 'line2'));
$text = sprintf($tpl, $text);
echo $text;
echo $ssh->shell('');
echo $ssh->read("-d '' STR ".$text);
echo $ssh->echo('"$STR" > /tmp/ololo');
echo $ssh->endShell();
echo $ssh->whoami();
}
catch(Exception $e)
{
die($e->getMessage());
}