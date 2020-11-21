# image-changer
Resize images and add  a white background

### Description
Folder with images should have only images.
It doesn't work without `PHP 7.4`, `composer`, `ext-img` and `ext-fileinfo`.

You can run it:
`php index.php [folder_with_images] [width] [height] [type:"resize"|"crop"]`

##### Example: `php index.php "images" 762 1100 resize`