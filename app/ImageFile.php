<?php
/* ImageFile class
 * Represents an image to be included in a diagram (e.g. photo)
 */
namespace vendor\WebtreesModules\gvexport;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Site;

class ImageFile
{
    public int $dpi;
    private object $mediaFile;
    private object $tree;
    private int $type;

    function __construct($media_file, $tree, $dpi) {
        $this->mediaFile = $media_file;
        $this->tree = $tree;
        $this->dpi = $dpi;
    }


    /**
     *  Return the image path for including in diagram
     *
     * @return string
     */
    public function getImageLocation(): string
    {
        $filename = $this->mediaFile->filename();
        $full_media_path = Site::getPreference('INDEX_DIRECTORY') . $this->tree->getPreference('MEDIA_DIRECTORY') . $filename;
        // If SVG then scale image and provide location of temp file
        if ($_REQUEST["vars"]["otype"] == "svg" || $_REQUEST["vars"]["otype"] == "pdf") {
            $temp_dir = sys_get_temp_dir_my() . "/" . md5(Auth::id());
            $temp_image_file = $temp_dir . "/" . $filename;

            $image = $this->loadImage($full_media_path);
            if ($image) {
                $img_resized = imagescale($image, $this->dpi, -1);
                if ($this->saveImage($img_resized, $temp_image_file)) {
                    return $temp_dir . "/" . $filename;
                } else {
                    // Resize failed for some reason, despite being supported format
                    // (e.g. invalid file). Return path to original file instead, as most
                    // of the time the file will work even if PHP couldn't load it.
                    return $full_media_path;
                }
            } else {
                // Don't scale as not one of our recognised formats, just return original image path
                return $full_media_path;
            }
        } else {
                return $full_media_path;
        }
    }

    /** Load the image into PHP
     *
     * @param $filepath
     * @return false|\GdImage|resource
     */
    private function loadImage($filepath)
    {
        $this->type = exif_imagetype($filepath);
        switch ($this->type) {
            case IMAGETYPE_GIF:
                $image = @imageCreateFromGif($filepath);
                break;
            case IMAGETYPE_JPEG:
                $image = @imageCreateFromJpeg($filepath);
                break;
            case IMAGETYPE_PNG:
                $image = @imageCreateFromPng($filepath);
                break;
            case IMAGETYPE_BMP:
                $image = @imageCreateFromBmp($filepath);
                break;
            default:
                return false;
        }
        if (!$image) {
            return false;
        }
        return $image;
    }

    /** Take PHP image (GdImage) and save it as a
     *  file on the hard drive in the provided place
     *
     * @param $image
     * @param $temp_image_file_path
     * @return bool
     */
    private function saveImage($image, $temp_image_file_path): bool
    {
        $dir = dirname($temp_image_file_path);
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        switch ($this->type) {
            case IMAGETYPE_GIF:
                imagegif($image, $temp_image_file_path);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($image, $temp_image_file_path);
                break;
            case IMAGETYPE_PNG:
                imagepng($image, $temp_image_file_path);
                break;
            case IMAGETYPE_BMP:
                imagewbmp($image, $temp_image_file_path);
                break;
            default:
                return false;
        }
        imagedestroy($image);
        return true;
    }

}