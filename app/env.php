<?php

$nome_computador = exec('hostname');
$nome_usuario = exec('whoami');

$filename = __DIR__."/../{$nome_usuario}@{$nome_computador}.local.json";

if (!file_exists($filename)) {
    $dist = __DIR__.'/../env.json';

    $open = fopen($filename, 'w+');

    fwrite($open, file_get_contents($dist));
    fclose($open);
}

return json_decode(file_get_contents($filename), true);
