<?php


foreach (glob(__DIR__ . '/mms/*.php') as $routeFile) {
    require $routeFile;
}
