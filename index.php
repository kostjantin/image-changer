<?php

use Gumlet\ImageResize;

require_once "vendor/autoload.php";

define('MAIN_FOLDER_PATH', $arv[1] ?? 'images');
define('IMAGE_WIDTH', $argv[2] ?? 1);
define('IMAGE_HEIGHT', $argv[3] ?? 1);
define('TYPE_PROCESSING', $argv[4] ?? 'resize');

define('NEW_DIR', createDir(MAIN_FOLDER_PATH . '-' . date('Ymd.His') . DIRECTORY_SEPARATOR));
define('NEW_TMP_DIR', createDir(NEW_DIR . '_tmp' . DIRECTORY_SEPARATOR));

function recursiveReadDir(string $dir): void
{
    createDir(NEW_DIR . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR);
    createDir(NEW_TMP_DIR . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR);

    foreach (new DirectoryIterator($dir) as $fileInfo) {
        if ($fileInfo->isDot()) {
            continue;
        }

        if ($fileInfo->isDir()) {
            var_dump($fileInfo->getPath() . DIRECTORY_SEPARATOR . $fileInfo->getFilename());
            recursiveReadDir($fileInfo->getPath() . DIRECTORY_SEPARATOR . $fileInfo->getFilename());
            continue;
        }

        if (TYPE_PROCESSING === 'resize') {
            resizeImage($fileInfo);
        } elseif (TYPE_PROCESSING === 'crop') {
            cropAndResizeImage($fileInfo);
        }
    }
}

function resizeImage(DirectoryIterator $imageFile): void
{
    $img = imagecreatetruecolor(IMAGE_WIDTH, IMAGE_HEIGHT);
    $bg = imagecolorallocate($img, 255, 255, 255);
    imagefilledrectangle($img, 0, 0, IMAGE_WIDTH, IMAGE_HEIGHT, $bg);


    $image = new ImageResize($imageFile->getPathname());
    $image->resizeToBestFit(IMAGE_WIDTH, IMAGE_HEIGHT, true);

    $image->addFilter(function ($imageDesc) use (&$img, $imageFile) {

        $imageWidth = imagesx($imageDesc);
        $imageHeight = imagesy($imageDesc);

        $imageX = IMAGE_WIDTH - $imageWidth;
        if ($imageX > 0) {
            $imageX /= 2;
        }

        $imageY = IMAGE_HEIGHT - $imageHeight;
        if ($imageY > 0) {
            $imageY /= 2;
        }

        imagecopy($img, $imageDesc, $imageX, $imageY, 0, 0, $imageWidth, $imageHeight);
        imagejpeg($img, NEW_DIR . $imageFile->getPathname());
        imagedestroy($img);
    });
    $image->save(NEW_TMP_DIR . $imageFile->getPathname());
}

function cropAndResizeImage(DirectoryIterator $imageFile): void
{
    $img = imagecreatetruecolor(IMAGE_WIDTH, IMAGE_HEIGHT);
    $bg = imagecolorallocate($img, 255, 255, 255);
    imagefilledrectangle($img, 0, 0, IMAGE_WIDTH, IMAGE_HEIGHT, $bg);


    // Crop image
    $image = new ImageResize($imageFile->getPathname());
    $image->addFilter(function ($imageDesc) use ($imageFile) {
        $cropped = imagecropauto($imageDesc, IMG_CROP_SIDES);
        if ($cropped === false) {
            var_dump('Bug');
            return;
        }

        imagejpeg($cropped, NEW_TMP_DIR . $imageFile->getPathname());
        imagedestroy($cropped);
    });

    $image->save(NEW_DIR . $imageFile->getPathname());


    $image = new ImageResize(NEW_TMP_DIR . $imageFile->getPathname());
    $image->resizeToBestFit(IMAGE_WIDTH, IMAGE_HEIGHT, true);
    $image->addFilter(function ($imageDesc) use (&$img, $imageFile) {
        $imageWidth = imagesx($imageDesc);
        $imageHeight = imagesy($imageDesc);

        $imageX = IMAGE_WIDTH - $imageWidth;
        if ($imageX > 0) {
            $imageX /= 2;
        }

        $imageY = IMAGE_HEIGHT - $imageHeight;
        if ($imageY > 0) {
            $imageY /= 2;
        }

        imagecopy($img, $imageDesc, $imageX, $imageY, 0, 0, $imageWidth, $imageHeight);
        imagejpeg($img, NEW_DIR . $imageFile->getPathname());
        imagedestroy($img);
    });
    $image->save(NEW_TMP_DIR . $imageFile->getPathname());
}

function createDir(string $path): string
{
    if (!mkdir($concurrentDirectory = $path) && !is_dir($concurrentDirectory)) {
        throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
    }

    return $concurrentDirectory;
}

//function resizeToBestFit()
//{
//    if ($this->getSourceWidth() <= $max_width && $this->getSourceHeight() <= $max_height && $allow_enlarge === false) {
//        return $this;
//    }
//
//    $ratio  = $this->getSourceHeight() / $this->getSourceWidth();
//    $width = $max_width;
//    $height = $width * $ratio;
//
//    if ($height > $max_height) {
//        $height = $max_height;
//        $width = (int) round($height / $ratio);
//    }
//    if (!$allow_enlarge) {
//        // if the user hasn't explicitly allowed enlarging,
//        // but either of the dimensions are larger then the original,
//        // then just use original dimensions - this logic may need rethinking
//
//        if ($width > $this->getSourceWidth() || $height > $this->getSourceHeight()) {
//            $width  = $this->getSourceWidth();
//            $height = $this->getSourceHeight();
//        }
//    }
//
//    $this->source_x = 0;
//    $this->source_y = 0;
//
//    $this->dest_w = $width;
//    $this->dest_h = $height;
//
//    $this->source_w = $this->getSourceWidth();
//    $this->source_h = $this->getSourceHeight();
//}

recursiveReadDir(MAIN_FOLDER_PATH);