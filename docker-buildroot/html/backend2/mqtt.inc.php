<?php 
function send_mqtt_msg($topic) {
  if (!class_exists('Mosquitto\Client')) return;

  $client = new Mosquitto\Client;
  $client->onConnect( function() use ($client,$topic) {
   $client->publish($topic,'V',1);
  });
  $client->onPublish( function() use ($client) {
   $client->disconnect();
  });
  $client->connect('mosquitto',1883);
  $client->loopForever();
 }
?>