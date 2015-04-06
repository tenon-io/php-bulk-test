<?php

/**
 *
 * Class for handling images using GD, primarily via file upload
 *
 * SOME USAGE EXAMPLES FOR THE uploadFile METHOD, WHICH IS THE PRIMARY REASON THIS CLASS EXISTS
 *
 * // Do Not retain original, create a safe "full" image, a thumbnail, and a smaller thumbnail
 * $file_info = Images::uploadFile('item_pic', $_SERVER['DOCUMENT_ROOT'] . '/images/bodyparts/',
 *              FALSE, $config['max_bp_img'], $config['max_bp_img_lgthumb'], $config['max_bp_img_smthumb']);
 *
 * // Retain original, create a safe "full" image, a thumbnail, and a smaller thumbnail
 * $file_info = Images::uploadFile('item_pic', $_SERVER['DOCUMENT_ROOT'] . '/images/bodyparts/',
 *              TRUE, $config['max_bp_img'], $config['max_bp_img_lgthumb'], $config['max_bp_img_smthumb']);
 *
 * // retain the original, no safe "full" image, no thumbnails
 * $file_info = Images::uploadFile('item_pic', $_SERVER['DOCUMENT_ROOT'] . '/images/bodyparts/',
 *                TRUE, NULL, NULL, NULL);
 *
 * // do not retain the original, create only the safe "full" image
 * $file_info = Images::uploadFile('item_pic', $_SERVER['DOCUMENT_ROOT'] . '/images/bodyparts/',
 *                FALSE, $config['max_bp_img'], NULL, NULL);
 *
 * // retain the original, no safe "full" image, create a thumbnail and a smaller thumbnail
 * $file_info = Images::uploadFile('item_pic', $_SERVER['DOCUMENT_ROOT'] . '/images/bodyparts/',
 *               TRUE, NULL, $config['max_bp_img_lgthumb'], $config['max_bp_img_smthumb']);
 *
 *
 * @todo   figure out how to resize animated gifs, keeping the animation
 * @author Karl Groves <karl@karlgroves.com>
 */
class Images
{

    /**
     *
     * @return array
     */
    public static function getValMsgArray()
    {
        return array(1 => _('The uploaded file exceeds the upload_max_filesize directive in php.ini'),
                     2 => _('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'),
                     3 => _('The uploaded file was only partially uploaded'),
                     4 => _('No file was uploaded'),
                     6 => _('Missing a temporary folder'),
                     7 => _('Failed to write file to disk.'),
                     8 => _('A PHP extension stopped the file upload.'));
    }

    /**
     *
     * @param   string $src_image
     * @param   string $dest_image
     * @param   int    $thumb_size
     * @param   int    $jpg_quality
     *
     * @return  bool
     */
    public static function square_crop($src_image, $dest_image, $thumb_size = 50, $jpg_quality = 90)
    {

        // Get dimensions of existing image
        $image = getimagesize($src_image);

        // Check for valid dimensions
        if ($image[0] <= 0 || $image[1] <= 0) {
            //echo "ERROR";
            return false;
        }

        // Determine format from MIME-Type
        $image['format'] = strtolower(preg_replace('/^.*?\//', '', $image['mime']));

        // Import image
        switch ($image['format']) {
            case 'jpg' :
            case 'jpeg' :
                $image_data = imagecreatefromjpeg($src_image);
                break;
            case 'png' :
                $image_data = imagecreatefrompng($src_image);
                break;
            case 'gif' :
                $image_data = imagecreatefromgif($src_image);
                break;
            default :
                // Unsupported format
                return false;
                break;
        }
        // end switch
        // Verify import
        if ($image_data == false) {
            echo "ERROR";

            return false;
        }

        // Calculate measurements
        if ($image[0] > $image[1]) {
            // For landscape images
            $x_offset = ($image[0] - $image[1]) / 2;
            $y_offset = 0;
            $square_size = $image[0] - ($x_offset * 2);
        } else {
            // For portrait and square images
            $x_offset = 0;
            $y_offset = ($image[1] - $image[0]) / 2;
            $square_size = $image[1] - ($y_offset * 2);
        }

        // Resize and crop
        $canvas = imagecreatetruecolor($thumb_size, $thumb_size);
        if (imagecopyresampled($canvas, $image_data, 0, 0, $x_offset, $y_offset, $thumb_size, $thumb_size, $square_size, $square_size)) {

            // Create thumbnail
            switch (strtolower(preg_replace('/^.*\./', '', $dest_image))) {
                case 'jpg' :
                case 'jpeg' :
                    return imagejpeg($canvas, $dest_image, $jpg_quality);
                    break;
                case 'png' :
                    return imagepng($canvas, $dest_image);
                    break;
                case 'gif' :
                    return imagegif($canvas, $dest_image);
                    break;
                default :
                    // Unsupported format
                    return false;
                    break;
            }
        } else {
            return false;
        }
    }

    /**
     * Create a thumbnail image from $inputFileName no taller or wider than
     * $maxSize. Returns the new image resource or false on error.
     * Author: mthorn.net
     *
     * @param   string $inputFileName
     * @param   int    $maxSize
     *
     * @return  string
     */
    public static function thumbnail($inputFileName, $maxSize = 100)
    {
        $info = getimagesize($inputFileName);

        $type = isset($info['type']) ? $info['type'] : $info[2];

        // Check support of file type
        if (!(imagetypes() & $type)) {
            // Server does not support file type
            return false;
        }

        $width = isset($info['width']) ? $info['width'] : $info[0];
        $height = isset($info['height']) ? $info['height'] : $info[1];
        // Calculate aspect ratio
        $wRatio = $maxSize / $width;
        $hRatio = $maxSize / $height;

        // Using imagecreatefromstring will automatically detect the file type
        $sourceImage = imagecreatefromstring(file_get_contents($inputFileName));

        // Calculate a proportional width and height no larger than the max size.
        if (($width <= $maxSize) && ($height <= $maxSize)) {
            // Input is smaller than thumbnail, do nothing
            return $sourceImage;
        } elseif (($wRatio * $height) < $maxSize) {
            // Image is horizontal
            $tHeight = ceil($wRatio * $height);
            $tWidth = $maxSize;
        } else {
            // Image is vertical
            $tWidth = ceil($hRatio * $width);
            $tHeight = $maxSize;
        }

        $thumb = imagecreatetruecolor($tWidth, $tHeight);

        if ($sourceImage === false) {
            // Could not load image
            return false;
        }

        // Copy resampled makes a smooth thumbnail
        imagecopyresampled($thumb, $sourceImage, 0, 0, 0, 0, $tWidth, $tHeight, $width, $height);
        imagedestroy($sourceImage);

        return $thumb;
    }

    /**
     * Save the image to a file. Type is determined from the extension.
     * $quality is only used for jpegs.
     * Author: mthorn.net
     *
     * @param   resource $im       resource handler for the new image
     * @param   string   $fileName name for the new image
     * @param   int      $quality  quality of the image, only relevant if jpg/ jpeg
     *
     * @return  bool
     */
    public static function imageToFile($im, $fileName, $quality = 90)
    {
        if (!$im || file_exists($fileName)) {
            return false;
        }

        $ext = strtolower(substr($fileName, strrpos($fileName, '.')));
        switch ($ext) {
            case '.gif' :
                imagegif($im, $fileName);
                break;
            case '.jpg' :
            case '.jpeg' :
                imagejpeg($im, $fileName, $quality);
                break;
            case '.png' :
                imagepng($im, $fileName);
                break;
            case '.bmp' :
                imagewbmp($im, $fileName);
                break;
            default :
                return false;
        }

        return true;
    }

    /**
     *
     * Thanks to ZeBadger for original example, and Davide Gualano for pointing me to it
     * Original at http://it.php.net/manual/en/function.imagecreatefromgif.php#59787
     *
     * @param   string $filename
     *
     * @return  bool
     */
    public static function is_animated_gif($filename)
    {
        $raw = file_get_contents($filename);

        $offset = 0;
        $frames = 0;
        while ($frames < 2) {
            $where1 = strpos($raw, "\x00\x21\xF9\x04", $offset);
            if ($where1 === false) {
                break;
            } else {
                $offset = $where1 + 1;
                $where2 = strpos($raw, "\x00\x2C", $offset);
                if ($where2 === false) {
                    break;
                } else {
                    if ($where1 + 8 == $where2) {
                        $frames++;
                    }
                    $offset = $where2 + 1;
                }
            }
        }

        return $frames > 1;
    }

    /**
     *
     * removes anything non alphanumeric from the file name
     *
     * @param   string $image_name
     *
     * @return  string
     */
    public static function cleanFileName($image_name)
    {
        $image_name = preg_replace("/[^a-zA-Z0-9]/", "", $image_name);

        $remove_these = array(' ', '`', '"', '\'', '\\', '/', '~', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '+', '=', '{', '}', '[', ']', '|', ':', ';', '<', ',', '>', '?');
        $image_name = str_replace($remove_these, '', $image_name);

        return $image_name;
    }

    /**
     *
     * @param   string $varName
     * @param   string $tmpPath
     * @param   bool   $retainOrig
     * @param   int    $maxSize
     * @param   int    $lgThumbSize
     * @param   int    $sqThumbSize
     *
     * @return string
     */
    public static function uploadFile($varName, $tmpPath, $retainOrig = true, $maxSize = null, $lgThumbSize = null, $sqThumbSize = null)
    {

        // if the file size isn't greater than zero, return false
        if (!$_FILES[$varName]['size'] > 0) {
            return false;
        }

        foreach ($_FILES[$varName] AS $key => $val) {
            $output[$key] = $val;
        }

        //echo "<hr>FROM IMAGES.CLASS.PHP";
        //echo  "image_name: " . $_FILES[$varName]['name'] . "<br>\n";
        //echo  "file_type: " . $_FILES[$varName]['type'] . "<br>\n";
        //echo  "file_size: " . $_FILES[$varName]['size'] . "<br>\n";
        //echo  "temp_name: " . $_FILES[$varName]['tmp_name'] . "<br>\n";
        //echo  "error_code: " . $_FILES[$varName]['error'] . "<br>\n";

        $image_name = Images::cleanFileName($_FILES[$varName]['name']);

        $output['destination'] = $tmpPath . $image_name;
        //echo  "Destination: " . $output['destination'] . " <br>\n";
        // if the file already exists in the site, rename it
        $num = 1;
        while (file_exists($output['destination'])) {
            $num++;
            // if previous file name existed then try another number+_+filename
            $image_name = $num . "" . $image_name;
            $output['destination'] = $tmpPath . $image_name;
        }
        //echo  "Final Destination: " . $output['destination'] . " <br>\n";

        $output['name'] = $image_name;

        // copy the image to the destination
        move_uploaded_file($_FILES['item_pic']['tmp_name'], $output['destination']) or die("Picture upload failed");

        // this creates the thumbnail
        $infile = $tmpPath . $image_name;
        //echo  "Infile $infile <br>\n";
        // get the DATA size of the file
        $imgsize = filesize($infile);
        //echo  "Image Data Size: $imgsize <br>\n";
        // resize it to the maximum allowed size
        if (!is_null($maxSize)) {
            $full = Images::thumbnail($infile, $maxSize);
            $output['final_img'] = $maxSize . "_" . $image_name;
            Images::imageToFile($full, $tmpPath . $output['final_img']);
        }

        // if we're creating a large thumbnail, let's do it
        if (!is_null($lgThumbSize)) {
            $im = Images::thumbnail($infile, $lgThumbSize);
            $output['final_lg_thumb'] = $lgThumbSize . "_" . $image_name;
            Images::imageToFile($im, $tmpPath . $output['final_lg_thumb']);
        }

        // if we're creating a small thumbnail, let's do it
        if (!is_null($sqThumbSize)) {
            // Make the Square Thumbnail
            $output['final_sq_thumb'] = $sqThumbSize . "_" . $image_name;
            Images::square_crop($infile, $tmpPath . $output['final_sq_thumb'], $sqThumbSize);
        }

        // if we're not retaining the original, then dump it
        if (false === $retainOrig) {
            unlink($output['destination']);
            $output['destination'] = '';
        }

        return $output;
    }

}