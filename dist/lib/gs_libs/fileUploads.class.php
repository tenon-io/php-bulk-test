<?php

/**
 * Class fileUploads
 */
class fileUploads
{

    /**
     * @param null $allowedMimeTypes
     * @param null $bannedMimeTypes
     */
    public function __construct($allowedMimeTypes = null, $bannedMimeTypes = null)
    {
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->bannedMimeTypes = $bannedMimeTypes;
    }

    /**
     * Destructor. Unsets all object variables
     */
    public function __destruct()
    {
        $vars = get_object_vars($this);
        if (is_array($vars)) {
            foreach ($vars as $key => $val) {
                $this->$key = null;
            }
        }
    }

    /**
     *
     * @return array
     */
    public static function getValMsgArray()
    {
        return array(1 => _("The uploaded file exceeds the upload_max_filesize directive in php.ini"),
                     2 => _("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"),
                     3 => _("The uploaded file was only partially uploaded"),
                     4 => _("No file was uploaded"),
                     6 => _("Missing a temporary folder"),
                     7 => _("Failed to write file to disk."),
                     8 => _("A PHP extension stopped the file upload."));
    }

    /**
     *
     * removes anything non alphanumeric from the file name
     *
     * @param   string $file_name
     *
     * @return  string
     */
    public function cleanFileName($file_name)
    {
        $pattern = "[^a-zA-Z0-9\.]";
        $file_name = ereg_replace($pattern, "", $file_name);

        $remove_these = array(' ', '`', '"', '\'', '\\', '/', '~', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '+', '=', '{', '}', '[', ']', '|', ':', ';', '<', ',', '>', '?');
        $file_name = str_replace($remove_these, '', $file_name);

        return $file_name;
    }

    /**
     *
     * @param   string $varName the name of the file
     * @param   string $dest    the path of the file
     *
     * @return string
     */
    public function uploadFile($varName, $dest)
    {

        try {
            // if the file size isn't greater than zero, return false
            if (!$_FILES[$varName]['size'] > 0) {
                return false;
            }

            foreach ($_FILES[$varName] AS $key => $val) {
                $output[$key] = $val;
            }

            $file_name = $this->cleanFileName($_FILES[$varName]['name']);

            $output['destination'] = $dest . $file_name;

            // if the file already exists in the site, rename it
            while (file_exists($output['destination'])) {
                // if previous file name existed then try another number+_+filename
                $file_name = date('YmdHis') . '_' . $file_name;
                $output['destination'] = $dest . $file_name;
            }

            $output['name'] = $file_name;

            // copy the file to the destination
            if (is_uploaded_file($_FILES[$varName]['tmp_name'])) {
                if (false == move_uploaded_file($_FILES[$varName]['tmp_name'], $output['destination'])) {
                    throw new gException(_('Could not upload file'));
                } else {
                    return $output;
                }
            } else {
                throw new gException(_('No file uloaded!'));

            }
        } catch (gException $e) {
            echo $e->getMessage();

            return false;
        }
    }

}
