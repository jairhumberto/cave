@echo off
if "%OS%"=="Windows_NT" @setlocal

:init
if "%PHP_COMMAND%" == "" goto set_php_command

REM %~dp0 is the pathname of the current script under NT
if "%CAVE_HOME%" == "" set CAVE_HOME=%~dp0..

"%PHP_COMMAND%" -d html_errors=off -qC "%CAVE_HOME%\bin\cave" %*
goto cleanup

:set_php_command
REM PHP_COMMAND environment variable not found, assuming php.exe is on path.
set PHP_COMMAND=php.exe
goto init

:err_home
echo ERROR: Environment var CAVE_HOME not set. Please point this
echo variable to your local cave installation!

:cleanup
if "%OS%"=="Windows_NT" @endlocal
