<?php

/**
 *
 *    This function library contains custom functions related to
 *    dealing with files and the file system including files themselves,
 *    directories, and file uploads.
 *
 * @todo
 *        NEEDED FUNCTIONALITY
 *
 *        IMPROVEMENTS TO EXISTING FUNCTIONALITY:
 *        - readfile - Outputs a file
 *        - realpath - Returns canonicalized absolute pathname
 *        - rename - Renames a file or directory
 *        - rmdir - Removes directory
 *        - stat - Gives information about a file
 *        - file - Reads entire file into an array
 *        - fstat - Gets information about a file using an open file pointer
 *        - fwrite - Binary-safe file write
 *        - fread - Binary-safe file read
 *        - is_readable - Tells whether the filename is readable
 *        - is_writable - Tells whether the filename is writable
 *        - mkdir - Makes directory
 *        - is_dir - Tells whether the filename is a directory
 *                    needs clearstatcache() after execution
 *        - is_executable - Tells whether the filename is executable
 *                    needs clearstatcache() after execution
 *        - is_file � Tells whether the filename is a regular file
 *                    needs clearstatcache() after execution
 *
 *        NEW FUNCTIONALITY
 *        - Handle file uploads
 *        - Read contents of a directory into an array
 *        - Images: Thumbnail Creation
 *        - Bulk thumbnail creation
 *        - Extract Zip/GZip
 *        - Auto Image Gallery
 *        - Watermark image with GD
 *        - Clean up file names of bad characters
 *        - Parse CSV/ Tab-Separated/ Pipe Separated Files
 */
class Files
{

    /**
     *
     */
    public function __construct()
    {
        // Valid modes for fopen(), used to verify that the mode(s) chosen throughout these functions will work for the file
        $this->valid_fopen_modes = array("r", "r+", "w", "w+", "a", "a+", "x", "x+");
    }

    /////////////////////////////////////////////////////////////////////////////////////////
    //	The first section of this file contains improvements upon existing PHP functionality
    //	such as adding debugging, or adding a layer of safety to the existing functionality
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * changes file (or directory) permissions
     *
     * @param    string $file   the full path of the file or directory we are working with
     * @param    int    $perms  the new permissions. MUST BE OCTAL!!!
     * @param    bool   $strict whether to perform an explicit check for the file's existence first.
     *
     * @return    bool    true, on success
     */
    function change_perms($file, $perms, $strict = true)
    {

        // first, ensure that $perms is an octal
        if (filter_var($perms, FILTER_VALIDATE_INT, array("flags" => FILTER_FLAG_ALLOW_OCTAL)) === false) {
            return false;
            // $perms was not an octal
        } else {
            if (($strict == true) && (!file_exists($file))) {
                return false;
                // file doesn't exist
            } else {
                chmod($file, $perms);

                return true;
                // all's well that ends well
            }
        }
    }

    /**
     * copies file from one location to another
     *
     * @param    string $infile               the full path & name of the file being copied
     * @param    string $outfile              the full path & name of the new destination
     * @param bool      $delete_infile
     * @param    bool   $allow_overwrite      whether an existing file of the
     *                                        same name as $outfile should be overwritten
     *
     * @internal param $copyfile
     * @return    bool    true on success, false otherwise
     */
    function copyfile($infile, $outfile, $delete_infile = false, $allow_overwrite = false)
    {
        if (($allow_overwrite == false) && (file_exists($outfile))) {
            return false;
            // the destination file already exists and we don't want to overwrite it
        }
        if (!file_exists($infile)) {
            return false;
            // original file doesn't exist
        }

        copy($infile, $outfile);

        // if we want to delete the original, go right ahead
        if ($delete_infile == true) {
            unlink($infile);
        }

        return true;
    }

    /**
     * provides improved functionality to PHP's native file_get_contents function
     *
     * @param    string $file   the full path of the file we are working with
     * @param    string $method do we return an array, or a string? Options are, "array" or "string"
     * @param    bool   $strict whether to perform an explicit check for the file's existence first.
     *
     * @return    string or array, depending upon the $method param
     * the $output variable is populated with the file's contents
     */
    function filegetcontents($file, $method = "string", $strict = true)
    {
        if (($strict == true) && (!file_exists($file))) {
            return false;
        } else {
            if ($method == "array") {
                $output = file($file);
            } else {
                $output = file_get_contents(urlencode($file));
            }

            return $output;
        }
    }

    /**
     * provides improved functionality to PHP's native file_put_contents function
     *
     * @param    string $file   the full path of the file we are working with
     * @param   string  $data
     * @param    bool   $strict whether to perform an explicit check for the file's existence first.
     *
     * @return    mixed    false on failure
     */
    function fileputcontents($file, $data, $strict = true)
    {
        if (($strict == true) && (!file_exists($file))) {
            return false;
        } else {
            return file_put_contents($file, $data);
        }
    }

    /**
     * returns the last time file was accessed
     *
     * @param    string $file   the full path of the file we are working with
     * @param    string $format the format for the return value
     *                          must be a valid format for date()
     * @param    bool   $strict whether to perform an explicit check for the file's existence first.
     *
     * @return    mixed, false on failure,  else string with the last accessed time, formatted as desired
     */
    function file_atime($file, $format = "F d Y H:i:s", $strict = true)
    {
        if (($strict == true) && (!file_exists($file))) {
            return false;
            // file doesn't exist
        } else {
            $output = date("$format", fileatime($file));
            clearstatcache();
        }

        return $output;
    }

    /**
     * returns the last time file was modified
     *
     * @param    string $file   the full path of the file we are working with
     * @param string    $format
     * @param    bool   $strict whether to perform an explicit check for the file's existence first.
     *
     * @return    string    the last modified time, formatted as desired
     */
    function file_mtime($file, $format = "F d Y H:i:s", $strict = true)
    {
        if (($strict == true) && (!file_exists($file))) {
            return false;
            // file doesn't exist
        } else {
            $output = date($format, fileatime($file));
            clearstatcache();

            return $output;
        }
    }

    /**
     * returns the octal value for permissins on the specified file
     *
     * @param    string $file   the full path of the file or directory we are working with
     * @param    bool   $strict whether to perform an explicit check for the file's existence first.
     *
     * @return    string    the permissions are returned
     */
    function check_perms($file, $strict = true)
    {
        if (($strict == true) && (!file_exists($file))) {
            return false;
            // file doesn't exist
        } else {
            $output = substr(sprintf('%o', fileperms($file)), -4);
            clearstatcache();

            return $output;
        }
    }

    /**
     * gets the size, in bytes, of a file
     *
     * @param    string $file   the full path of the file or directory we are working with
     * @param    bool   $strict whether to perform an explicit check for the file's existence first.
     *
     * @return    string    the size of the file is returned, in bytes
     */
    function get_filesize($file, $strict = true)
    {
        if (($strict == true) && (!file_exists($file))) {
            return false;
            // file doesn't exist
        } else {
            $output = filesize($file);
            clearstatcache();

            return $output;
        }
    }

    /**
     * @param    string $file         the full path of the file or directory we are working with
     * @param    string $mode         the mode to use when locking
     *                                To acquire a shared lock (reader), set operation to LOCK_SH
     *                                To acquire an exclusive lock (writer), set operation to LOCK_EX
     *                                To release a lock (shared or exclusive), set operation to LOCK_UN
     *                                If you don't want flock() to block while locking, add LOCK_NB
     * @param    bool   $strict       whether to perform an explicit check for the file's existence first.
     *
     * @internal param $lock_file
     * @desc     locks a file
     * @return bool
     */
    function lock_file($file, $mode = "LOCK_EX", $strict = true)
    {

        if (($strict == true) && (!file_exists($file))) {
            return false;
            // file doesn't exist
        } else {
            flock($file, $mode);

            return true;
        }
    }

    /**
     * @param    string $file   the full path of the file or directory we are working with
     * @param           $mode
     * @param    bool   $strict whether to perform an explicit check for the file's existence first.
     *
     * @return bool
     */
    function file_open($file, $mode, $strict = true)
    {
        if (($strict == true) && (!file_exists($file))) {
            return false;
            // file doesn't exist
        } else {
            fopen($file, $mode);

            return true;
        }
    }

    /**
     * @param    string $file   the full path of the file or directory we are working with
     * @param    bool   $strict whether to perform an explicit check for the file's existence first.
     *
     * @desc      get an improved pathinfo array with the following keys
     *            dirname => /var/www/html
     *            basename => example.html
     *            extension => html
     *            basenameWE => example (as in, basename without extension)
     * @return bool|mixed
     */
    function get_pathinfo($file, $strict = true)
    {

        if (($strict == true) && (!file_exists($file))) {
            return false;
            // file doesn't exist
        } else {
            //get a basic pathinfo
            $output = pathinfo($file);

            $output["basenameWE"] = substr($output["basename"], 0, strlen($output["basename"]) - (strlen($output["extension"]) + 1));

            return $output;
        }
    }

    /**
     * "touches" a file, updating its access time and (optionally) its last modified time
     *
     * @param    string $file       the full path of the file or directory we are working with
     * @param    bool   $keep_mtime whether to maintain the lastmodified time of the file
     * @param    bool   $strict     whether to perform an explicit check for the file's existence first.
     *
     * @return bool
     */
    function touchfile($file, $keep_mtime = false, $strict = true)
    {

        if ($keep_mtime == true) {
            $mtime = date("U", filemtime($file));
        } else {
            $mtime = date("U");
        }
        if (($strict == true) && (!file_exists($file))) {
            return false;
            // file doesn't exist
        } else {
            touch($file, $mtime);

            return true;
        }
    }

    /**
     * if a file exists, delete it
     *
     * @param    string $file   the full path of the file or directory we are working with
     * @param    bool   $strict whether to perform an explicit check for the file's existence first.
     *
     * @return    bool
     */
    function file_delete($file, $strict = true)
    {

        if (($strict == true) && (!file_exists($file))) {
            return false;
            // file doesn't exist
        } else {
            unlink($file);

            return true;
        }
    }

    /**
     * @param    string $path         the full path of the directory we are working with
     * @param    string $mode         the mode to create the directory in
     * @param    bool   $recursive    whether to make this creation recursive
     *                                (meaning whether to create the directory and all other
     *                                directories needed in order to make this one)
     * @param    string $context      any special "context" wrapper
     *
     * @return bool
     */
    function makedirectory($path, $mode = "0777", $recursive = false, $context = null)
    {

        $path = trim($path);

        // Sanity check to make sure the directory doesn't already exist
        if (is_dir($path)) {
            return false;
            // directory could not be created because it exists already
        }

        if (is_null($context)) {
            if (!mkdir($path, $mode, $recursive)) {
                return false;
                // directory could not be created
            } else {
                return true;
            }
        } // $context parameter should be passed to mkdir
        else {
            if (!mkdir($path, $mode, $recursive, $context)) {
                return false;
                // directory could not be created
            } else {
                return true;
            }
        }
    }

    /**
     * deletes all contents of a directory
     *
     * @param    string $directory the full path of the directory we are working with
     * @param    bool   $strict    whether to perform an explicit check for the directory's existence first.
     *
     * @return bool
     */
    function delete_dircontents($directory, $strict = true)
    {
        $dir_files_array = Files::get_dir_files_array($directory);

        if (!is_array($dir_files_array)) {
            return false;
        }

        foreach ($dir_files_array AS $file) {
            if (is_file($directory . "/" . $file)) {
                Files::file_delete($directory . "/" . $file, $strict);
            }
        }

        return true;
    }

    ////////////////////////////////////////////////////////////////////////////////////////
    //	The second section of this file contains completely new, specialized functionality
    //	such as methods for writing, reading, and manipulating files and directories in more
    //	specialized ways than that which exists in current functionality
    /////////////////////////////////////////////////////////////////////////////////////////

    /**
     * creates a gzipped file
     *
     * @param    string $infile
     * @param    string $outfile
     * @param   bool    $binary
     * @param    bool   $strict
     *
     * @return    bool    TRUE on success
     */
    function create_gzip($infile, $outfile, $binary = false, $strict = true)
    {

        if (($strict == true) && (!file_exists($infile))) {
            return false;
            // file doesn't exist
        } else {
            // append binary flag to mode, if desired
            if ($binary == true) {
                $mode = "wb";
            } else {
                $mode = "w";
            }

            $data = implode("", file($infile));
            $gzdata = gzencode($data, 9);
            $fp = fopen($outfile, $mode);
            fwrite($fp, $gzdata);
            fclose($fp);

            return true;
        }
    }

    /**
     * creates a new file
     *
     * @param    string $file   the full path of the file or directory we are working with
     * @param    string $data   the content of the new file
     * @param    bool   $binary
     * @param    bool   $strict whether to perform an explicit check for the file's existence first.
     *
     * @return   bool    TRUE on success
     */
    function write_new_file($file, $data, $binary = false, $strict = true)
    {

        if (($strict == true) && (file_exists($file))) {
            return false;
            // file already exists
        } else {
            // append binary flag to mode, if desired
            if ($binary == true) {
                $mode = "wb";
            } else {
                $mode = "w";
            }
            $handle = fopen($file, $mode);

            if (!$handle) {
                return false;
                // could not open the file
            } else {
                if (flock($handle, LOCK_EX)) {
                    fwrite($handle, $data);
                    flock($handle, LOCK_UN);
                    // release the lock
                    fclose($handle);

                    return true;
                } else {
                    return false;
                    // could not lock the file for writing
                }
            }
        }
    }

    /**
     * appends data to a file that already exists
     *
     * @param    string $file the full path of the file or directory we are working with
     * @param    string $data the content of the new file
     * @param    string $mode how the file will be entered.
     *                        $mode reference:
     *                        "r" -  read only
     *                        "r+" - read and write if the file exists already
     *                        "w" -  write only. creates file of doesn't exist - OVERWRITES EXISTING FILE CONTENTS
     *                        "w+" - read and write. creates file if it doesn't exist- OVERWRITES EXISTING FILE CONTENTS
     *                        "a" -  write only to the end of the file. creates file if it doesn't exist
     *                        "a+" - read and write. creates file if it doesn't exist
     *                        "x" -  writing only. creates file if it doesn't exist. RETURNS FALSE if it does exist already
     *                        "x+" - read and write. creates file if it doesn't exist. RETURNS FALSE if it does exist already
     * @param bool      $binary
     *
     * @return    bool    TRUE on success
     */
    function append_file($file, $data, $mode = "a", $binary = false)
    {

        // if the $mode chosen is not a valid mode for fopen(), something is wrong
        if (!in_array($mode, $this->valid_fopen_modes)) {
            return false;
            // file open mode is invalid
        }

        // if the file doesn't exist, and mode requires it, return an error
        if ((!file_exists($file)) && (($mode !== "a") && ($mode !== "a+") && ($mode !== "r") && ($mode !== "r+"))) {
            return false;
            // file doesn't exist
        } // if the file exists, and the mode will cause fopen to choke, return an error
        elseif ((file_exists($file)) && (($mode !== "w") || ($mode !== "w+") || ($mode !== "x") || ($mode !== "x+"))) {
            return false;
            // file exists but invalid open mode defined
        } // otherwise, everything is cool and we're ready to rock.
        else {
            // append binary flag to mode, if desired
            if ($binary == true) {
                $mode .= "b";
            }

            $handle = fopen($file, $mode);
            // one last check
            if (!$handle) {
                return false;
                // file opening failure
            } else {
                if (flock($handle, LOCK_EX)) {
                    fwrite($handle, $data);
                    flock($handle, LOCK_UN);
                    // release the lock
                    fclose($handle);

                    return true;
                } else {
                    return false;
                    // cannot lock the file
                }
            }
        }
    }

    /**
     * returns a string of the file contents
     *            there is an important difference between this function and other file
     *            reading functions in this library: this one is intended to output
     *            text files (HTML, CSS, etc) with special characters converted, ready for
     *            output to the screen, kind of like a quick & dirty source viewer
     *
     * @param    string $file   the full path of the file we are working with
     * @param    bool   $strict whether to perform an explicit check for the file's existence first.
     *
     * @return    string    the $output variable is populated with the file's data
     */
    function output_file_contents($file, $strict = true)
    {
        if (($strict == true) && (!file_exists($file))) {
            return false;
            // file does not exist
        } else {
            $fp = fopen($file, "r");
            //open in read-only mode

            if (!$fp) {
                return false;
                // file could not be opened
            } else {
                while (!feof($fp)) {
                    $data = fgets($fp, 900);
                    $data = htmlspecialchars($data);
                }
                $output .= $data;

                return $output;
            }
        }
    }

    /**
     * @param      $file
     * @param bool $strict
     *
     * @return bool|string
     */
    function find_extension($file, $strict = true)
    {

        if (($strict == true) && (!file_exists($file))) {
            return false;
            // file doesn't exist
        } else {
            $revfile = strrev($file);
            $type = strtolower(strrev(substr($revfile, 0, strpos($revfile, "."))));

            return $type;
        }
    }

    /**
     * @param $dir
     *
     * @return array
     */
    function get_dir_files_array($dir)
    {

        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                $files_array[] = $file;
            }
            closedir($handle);
        }

        return $files_array;
    }

    /**
     * @param $size
     *
     * @return string
     */
    function process_file_size($size)
    {

        $i = 0;
        $iec = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
        while (($size / 1024) > 1) {
            $size = $size / 1024;
            $i++;
        }

        return substr($size, 0, strpos($size, '.') + 4) . " $iec[$i]";
    }

    /**
     * @param      $file
     * @param bool $strict
     *
     * @return array|bool
     */
    function get_file_stats($file, $strict = true)
    {

        if (($strict == true) && (!file_exists($file))) {
            return false;
            // file doesn't exist
        } else {
            $output = array();
            $output['fileatime'] = fileatime($file);
            $output['filegroup'] = filegroup($file);
            $output['filemtime'] = filemtime($file);
            $output['fileowner'] = fileowner($file);
            $output['filesize'] = filesize($file);
            $output['is_dir'] = var_export(is_dir($file), true);
            $output['is_executable'] = var_export(is_executable($file), true);
            $output['is_file'] = var_export(is_file($file), true);
            $output['is_link'] = var_export(is_link($file), true);
            $output['is_readable'] = var_export(is_readable($file), true);
            $output['is_uploaded_file'] = var_export(is_uploaded_file($file), true);
            $output['is_writable'] = var_export(is_writable($file), true);

            return $output;
        }
    }

    /**
     * @param $path
     *
     * @return array with the following keys:
     * size - the data size of the directory and its contents
     * count - the total number of files in the directory
     * dircount - the number of sub directories
     */
    function get_directory_size($path)
    {
        $totalsize = 0;
        $totalcount = 0;
        $dircount = 0;
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                $nextpath = $path . '/' . $file;
                if ($file != '.' && $file != '..' && !is_link($nextpath)) {
                    if (is_dir($nextpath)) {
                        $dircount++;
                        $result = Files::get_directory_size($nextpath);
                        $totalsize += $result['size'];
                        $totalcount += $result['count'];
                        $dircount += $result['dircount'];
                    } elseif (is_file($nextpath)) {
                        $totalsize += filesize($nextpath);
                        $totalcount++;
                    }
                }
            }
        }
        closedir($handle);
        $total['size'] = $totalsize;
        // data size of directory
        $total['count'] = $totalcount;
        // total number of files
        $total['dircount'] = $dircount;

        // number of sub directories
        return $total;
    }

    /**
     * takes a file and erases all the data in it
     *
     * @param    string $file   the full path of the file or directory we are working with
     * @param    bool   $strict whether to perform an explicit check for the file's existence first.
     *
     * @return    bool    TRUE on success
     */
    function truncate_file($file, $strict = true)
    {

        if (($strict == true) && (!file_exists($file))) {
            return false;
            // file already exists
        } else {
            $handle = fopen($file, "w");

            if (!$handle) {
                return false;
                // could not open the file
            } else {
                fclose($handle);

                return true;
            }
        }
    }

    /**
     * counts the total number of lines in a file
     *
     * @param    string $filepath the full path to the file
     *
     * @return    int
     */
    function count_lines($filepath)
    {
        // open the file for reading
        $handle = fopen($filepath, "r");

        // set a counter
        $count = 0;

        // loop over the file
        while (fgets($handle)) {
            // increment the counter
            $count++;
        }

        // close the file
        fclose($handle);

        // show the total
        return $count;
    }

    /**
     * copies a remote file to the server
     *            USAGE EXAMPLE:
     *            copyFile("http://test-server.com/file/movie.mpg", "myfolder/");
     *
     * @param    string $url     URL to the file
     * @param    string $dirname the directory to put this into. Must be CHMOD 777
     * @param    null   $return_method
     * @param    bool   $strict  whether to perform an explicit check for the file's existence first.
     *
     * @return    bool
     */
    function copy_remote_file($url, $dirname, $return_method = null, $strict = true)
    {

        // attempt to open the
        @$file = fopen($url, "rb");

        if (($strict == true) && (!$file)) {
            return false;
            // file can't be opened
        } else {
            $filename = basename($url);
            $fc = fopen($dirname . "$filename", "wb");
            while (!feof($file)) {
                $line = fread($file, 1028);
                fwrite($fc, $line);
            }
            fclose($fc);
            if ($return_method == "name") {
                return $filename;
            } elseif ($return_method == "fullpath") {
                return $dirname . $filename;
            } else {
                return true;
            }
        }
    }

    /**
     * lists directory contents arranged by date
     *
     * @param    string $path file path for the directory we're working with
     *
     * @return    array
     */
    function listDirectoryByDate($path)
    {
        $dir = opendir($path);
        $list = array();
        while ($file = readdir($dir)) {
            if ($file != '.' && $file != '..' && !is_dir($file)) {
                $ctime = filectime($path . $file) . rand(1000, 9999);
                $list[$ctime] = $file;
            }
        }
        closedir($dir);
        krsort($list);

        return $list;
    }

    /**
     * @param $path
     *
     * @return bool
     */
    function rrmdir($path)
    {
        return is_file($path) ? @unlink($path) : array_map('rrmdir', glob($path . '/*')) == @rmdir($path);
    }

    /**
     * lists the contents of a zip file
     *
     * @param      $path_to_zip
     * @param      $destination
     * @param bool $fullpath
     * @param bool $dumpzip
     *
     * @return bool
     */
    function get_zip_files_array($path_to_zip, $destination, $fullpath = true, $dumpzip = false)
    {
        $zip = new ZipArchive;
        if ($zip->open($path_to_zip) === true) {
            $zip->extractTo($destination);

            // delete the original zip file
            if ($dumpzip === true) {
                Files::file_delete($path_to_zip);
            }

            $zip->close();
            // return the array
            $dir_files_array = Files::directoryToArray($destination, true);

            return $dir_files_array;
        } else {
            return false;
        }
    }

    /**
     * @param $directory
     * @param $recursive
     *
     * @return array
     */
    function directoryToArray($directory, $recursive = true)
    {
        $array_items = array();
        if ($handle = opendir($directory)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    if (is_dir($directory . "/" . $file)) {
                        if (false != $recursive) {
                            $array_items = array_merge($array_items, Files::directoryToArray($directory . "/" . $file, $recursive));
                        }
                        $file = $directory . "/" . $file;
                        $array_items[] = preg_replace("/\/\//si", "/", $file);
                    } else {
                        $file = $directory . "/" . $file;
                        $array_items[] = preg_replace("/\/\//si", "/", $file);
                    }
                }
            }
            closedir($handle);
        }

        return $array_items;
    }

    /**
     * opens a file, reads it, returns the data
     *
     * @param    string $file the file to be read
     *
     * @return    string
     */
    function getFileData($file)
    {
        $fp = fopen($file, "r");

        $data .= "<pre>";
        while (!feof($fp)) {
            $data = fgets($fp, 900);
            $data .= htmlspecialchars($data);
        }
        $data .= "</pre>";

        return $data;
    }

    /**
     *
     * makes an HTML table out of a CSV file
     *
     * @param string $file
     * @param string $tExtras
     *
     * @return string
     */
    function CSV_to_table($file, $tExtras)
    {
        $output = '';

        $row = 0;
        if (($handle = fopen($file, "r")) !== false) {
            $output .= "<table $tExtras>\n";
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $num = count($data);
                $output .= "<tr>\n";
                $row++;
                for ($c = 0; $c < $num; $c++) {
                    if ($row == 1) {
                        $output .= "<th scope=\"col\">" . $data[$c] . "</th>\n";
                    } else {
                        if ($c == 0) {
                            $output .= "<th scope=\"row\">" . $data[$c] . "</th>\n";
                        } else {
                            $output .= "<td>" . $data[$c] . "</td>\n";
                        }
                    }
                }
                $output .= "</tr>\n";
            }
            $output .= "</table>\n";
            fclose($handle);

            return $output;
        } else {
            return false;
        }
    }

    /**
     *
     * Connects to a remote file and retrieves the MIME type of that file
     *
     * @param   string $url full URL to the remote file
     *
     * @return  string          returns the MIME of the file
     */
    public function getRemoteFileMIMEType($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_exec($ch);

        return curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    }
}
