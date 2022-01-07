<?php
/**
 * Created by YNOTE_HELPER.
 * User: ogenes
 * Date: 2022/1/6
 */

$path = "/Users/ogenes/Library/Containers/com.tencent.QQMusicMac/Data/Library/Application Support/QQMusicMac/iQmc/";

$output = '/Users/ogenes/Music/';
$ret = $files = scandir($path);

$specialSymbol = [
    " " => "\ ",
    "(" => "\(",
    ")" => "\)",
    "'" => "\'",
    "|" => "\|"
];
foreach ($ret as $item) {
    $file = $path.$item;
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    if ($ext === 'qmc0') {
        $filename = pathinfo($file, PATHINFO_FILENAME);
    
        $mp3 = $output . $filename . ".mp3";
        if (file_exists($mp3)) {
            continue;
        }
        $file = str_replace(array_keys($specialSymbol), array_values($specialSymbol), $file);
        $mp3 = str_replace(array_keys($specialSymbol), array_values($specialSymbol), $mp3);
        $cmd = "/usr/local/bin/qmcdump {$file} {$mp3}";
        echo $cmd;
        exec($cmd);
        echo PHP_EOL;
    }
}

