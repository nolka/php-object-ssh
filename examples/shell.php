<?

header('Content-type: text/plain');

include "../Ssh.php";

$ssh = new Ssh();
$ssh->connect('192.168.1.116');
$ssh->auth('tech', 'tech');

$code = array(
'
clear;
echo "hi";
clear;
echo "jopa"
for i  in {0..9}; do
echo $i;
done;
',
'
cat /var/log/syslog &>/dev/null;
if [ $? == 0 ]; then 
  echo "zaebato";
else 
  echo "ne zaebato"; 
fi;
'
);

foreach($code as $c)
echo $ssh->shell($c);

