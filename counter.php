<?php
// Datei, in der der Zählerstand gespeichert wird
$file = __DIR__ . '/counter.txt';

// Wenn Datei nicht existiert, mit 0 starten
if (!file_exists($file)) {
    file_put_contents($file, '0');
}

// Aktuellen Wert lesen
$count = (int) file_get_contents($file);

// Zähler erhöhen
$count++;

// Neuen Wert speichern
file_put_contents($file, (string)$count);

// Ausgabe (als Text, damit JS sie einfach anzeigen kann)
header('Content-Type: text/plain');
echo $count;
?>
