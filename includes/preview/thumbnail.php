<?php

define("thumbnail_MAX_WIDTH", 320);
define("thumbnail_MAX_HEIGHT", 240);

function thumbnail_create_view($fileID) {
    if(!extension_loaded('gd')) {
        return false;
    }
    $maxWidth = thumbnail_MAX_WIDTH;
    $maxHeight = thumbnail_MAX_HEIGHT;
    $sourceFile = fs_get_data_uri($fileID, false);
    $destinationFile = fs_get_view_uri($fileID, false);

    list($srcWidth, $srcHeight, $srcType) = getImageSize($sourceFile);

    if($srcWidth <= $maxWidth && $srcHeight <= $maxHeight) {
        return copy($sourceFile, $destinationFile);
    } elseif($srcWidth > $srcHeight) {
        $thumbWidth = $maxWidth;
        $thumbHeight = intval($srcHeight*$thumbWidth/$srcWidth);
    } else {
        $thumbHeight = $maxHeight;
        $thumbWidth = intval($thumbHeight*$srcWidth/$srcHeight);
    }

    $memoryUsage = $thumbWidth*$thumbHeight*4+
                    $srcWidth*$srcHeight*4+
                    memory_get_usage();

    if($memoryUsage > string_filesize_to_bytes(ini_get('memory_limit'))) {
        return false;
    }

    $Thumbnail = imageCreateTrueColor($thumbWidth, $thumbHeight);

    switch($srcType) {
        case IMAGETYPE_GIF:

            $srcImage = ImageCreateFromGIF($sourceFile);

            $colorTransparent = imageColorTransparent($srcImage);
            imagePaletteCopy($srcImage, $Thumbnail);
            imageFill($Thumbnail, 0, 0, $colorTransparent);
            imageColorTransparent($Thumbnail, $colorTransparent);

            imageCopyResampled($Thumbnail, $srcImage, 0, 0, 0, 0, 
                        $thumbWidth, $thumbHeight, $srcWidth, $srcHeight);
            imageDestroy($srcImage);
            return imageGIF($Thumbnail, $destinationFile);

        case IMAGETYPE_JPEG:

            $srcImage = ImageCreateFromJPEG($sourceFile);
            imageCopyResampled($Thumbnail, $srcImage, 0, 0, 0, 0, 
                        $thumbWidth, $thumbHeight, $srcWidth, $srcHeight);
            imageDestroy($srcImage);
            return imageJPEG($Thumbnail, $destinationFile, 90);

        case IMAGETYPE_PNG:

            $srcImage = ImageCreateFromPNG($sourceFile);
            imageAlphaBlending($Thumbnail, false);
            imageSaveAlpha($Thumbnail, true);
            imageCopyResampled($Thumbnail, $srcImage, 0, 0, 0, 0, 
                        $thumbWidth, $thumbHeight, $srcWidth, $srcHeight);
            imageDestroy($srcImage);
            return imagePNG($Thumbnail, $destinationFile);

        default:

            return false;
    }
}

function thumbnail_get_tags($fileID) {
    $tagsArray = array();
    $sourceFile = fs_get_data_uri($fileID, false);

    if(extension_loaded('gd')) {
        list($srcWidth, $srcHeight, $srcType) = getImageSize($sourceFile);
        $tagsArray['Width'] = $srcWidth;
        $tagsArray['Height'] = $srcHeight;
    }
    if(
        extension_loaded('exif') &&
        ($exifData = @exif_read_data($sourceFile, 'ANY_TAG', true)) !== false)
    {
        $tagsArray += isset($exifData['IFD0']) ? $exifData['IFD0'] : array();
        $tagsArray += isset($exifData['EXIF']) ? $exifData['EXIF'] : array();
    }
    return $tagsArray;
}

function thumbnail_print_view_html($fileID) {

}

function thumbnail_get_view_uris($fileID) {

}

?>
