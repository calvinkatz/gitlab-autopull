<?php
$json = json_decode(file_get_contents('php://input'));
$event = $json->{'object_kind'};
$project = $json->{'project'}->{'name'};
if(strcmp($event, 'push') == 0) {
  shell_exec("cd /opt/projects/$project && git reset --hard HEAD && git pull");
}
?>
