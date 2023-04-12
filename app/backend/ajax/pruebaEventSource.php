<?php
header('Content-Type: text/event-stream');

header('Cache-Control: no-cache');

echo 'data: El número es:'. rand(5, 15). "\n\n";

flush();