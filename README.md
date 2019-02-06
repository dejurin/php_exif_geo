php_exif_geo
============

PHP Exif (geo with make and model)
Definition pictures geo-coordinates, as well as obtain information about the phone and the time the picture.

```include "Exifdata.php";

$object = New Exifdata;

var_dump($object->exifdata->get_exif('file.jpg'));

============
Определение геокоординат фотографии, а также получение информации о телефоне и времени снимка.

