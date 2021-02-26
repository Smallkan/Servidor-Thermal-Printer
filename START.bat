@echo off

set serverPath=%cd%

setlocal
cd /d %~dp0php

php %serverPath%\server.php