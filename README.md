# Video_Converter
Place this app in **nextcloud/apps/**

## Features

* Video Conversion
* Override or not
* More incoming...

## Output supported

* MP4
* AVI
* More incoming...

## Requirements

* FFmpeg
* FFprobe

## Usage

If you want to use this app on external local storage add the mount points on your config.php file like this (no / at the end)
```php
'external' => 
            array (
                0 => '/home/localfoler',
                1 => '/share/anotherfolder',
            ),
```
