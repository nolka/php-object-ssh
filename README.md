php-object-ssh
==============

call commands over ssh like object methods

usage example
=============

$ssh = new $Ssh();
$ssh->connet('localhost'); // by default ssh client try to connect on port 22. If Non standard port is used, it can be passed as second argument
$ssh->auth('login', 'password');
var_dump($ssh->ls('-la'));
var_dump($ssh->whoami());

this wrapper supports interactive shell
==============
// ... connection made...

var_dump( $ssh->shell('sudo -i')); // trying to get root access
var_dump($ssh->shell('password')); // input our password here
var_dump($ssh->shell('reboot')); // call reboot command :D
