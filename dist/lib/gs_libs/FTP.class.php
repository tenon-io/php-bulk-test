<?php

/**
 *
 * Presents a large number of functions for doing things with FTP
 *
 */
class FTP
{

    public $server, $user_name, $user_pass, $dst_dir, $port, $timeout, $conn_id, $login_result;

    /**
     *
     * class constructor. Connects to the FTP server
     *
     * @param   string $server    The server to connect to
     * @param   string $user_name The username at the server
     * @param   string $user_pass The password at the server
     * @param   string $dst_dir   The directory to connect to
     * @param   int    $port      The port to connect to
     * @param   int    $timeout   The number of seconds to wait
     * @param   bool   $debug
     *
     * @return \FTP
     */
    public function __construct($server, $user_name, $user_pass, $dst_dir, $port = 21, $timeout = 90, $debug = false)
    {

        $this->debug = $debug;

        if (false != $this->debug) {
            echo '<pre>$server <br>';
            var_dump($server);
            echo '</pre><br>';

            echo '<pre>$user_name <br>';
            var_dump($user_name);
            echo '</pre><br>';

            echo '<pre>$user_pass <br>';
            var_dump($user_pass);
            echo '</pre><br>';

            echo '<pre>$dst_dir <br>';
            var_dump($dst_dir);
            echo '</pre><br>';

            echo '<pre>$port <br>';
            var_dump($port);
            echo '</pre><br>';

            echo '<pre>$timeout <br>';
            var_dump($timeout);
            echo '</pre><br>';
        }

        if (($server != "") && ($user_name != "") && ($user_pass != "") && ($dst_dir != "")) {
            $this->server = $server;
            $this->user_name = $user_name;
            $this->user_pass = $user_pass;
            $this->dst_dir = $dst_dir;
            $this->port = $port;
            $this->timeout = $timeout;
        } else {
            if (false != $this->debug) {
                echo _('BAD PARAMETERS');
            }

            return false;
            // bad parameters
        }
        if (!$this->connect() || !$this->setdir()) {
            if (false != $this->debug) {
                echo _('CANNOT CONNECT TO FTP');
            }

            return false;
            // bad connection or the destination directory doesn't exist
        } else {
            if (false != $this->debug) {
                echo _('A-OK');
            }

            return true;
            // ALL OK
        }
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
     * Opens an FTP connection
     *
     * @return  bool
     */
    public function connect()
    {
        if (false != $this->debug) {
            echo '<pre>ftp_server<br>';
            var_dump($this->server);
            echo '</pre>';
        }

        $this->conn_id = ftp_connect($this->server, $this->port, $this->timeout);

        if (false != $this->debug) {
            echo '<pre>';
            var_dump($this->conn_id);
            echo '</pre>';
        }

        $this->login_result = ftp_login($this->conn_id, $this->user_name, $this->user_pass);

        if (false != $this->debug) {
            echo '<pre>';
            var_dump($this->login_result);
            echo '</pre>';
        }

        if ((!$this->conn_id) || (!$this->login_result)) {
            if (false != $this->debug) {
                echo 'CANNOT CONECT TO FTP<br>';

                return false;
            }
        } else {
            return true;
        }
    }

    /**
     *
     * Changes the current directory on a FTP server
     *
     * @return  bool
     */
    public function setdir()
    {
        if (!@ftp_chdir($this->conn_id, $this->dst_dir)) {
            if (false != $this->debug) {
                echo 'CANNOT SETDIR';
            }

            return false;
        } else {
            return true;
        }
    }

    /**
     *
     * alias for ftp_cdup — Changes to the parent directory
     *
     * @return  bool
     */
    public function cdup()
    {
        if (ftp_cdup($this->conn_id)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * alias for ftp_chdir - Changes the current directory on a FTP server
     *
     * @param   string $dir name of the directory to change to
     *
     * @return string
     */
    public function chDir($dir)
    {
        if (ftp_chdir($this->conn_id, $dir)) {
            return ftp_pwd($this->conn_id);
        } else {
            return false;
        }
    }

    /**
     *
     * alias of ftp_chmod — Set permissions on a file via FTP
     *
     * @param   string $mode
     * @param   string $fileName
     *
     * @return  bool
     */
    public function chmod($mode, $fileName)
    {
        if (ftp_chmod($this->conn_id, $mode, $fileName) !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * alias of ftp_close — Closes an FTP connection
     *
     * @return bool
     */
    public function close()
    {
        if (false == ftp_close($this->conn_id)) {
            return false;
        }

        return true;
    }

    /**
     *
     * alias for ftp_delete — Deletes a file on the FTP server
     *
     * @param   string $file
     *
     * @return  bool
     */
    public function delete($file)
    {
        if (ftp_delete($this->conn_id, $file)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * alias for ftp_exec — Requests execution of a raw command on the FTP server
     *
     * @param   string $command
     *
     * @return  bool
     */
    public function exec($command)
    {
        if (ftp_exec($this->conn_id, $command)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * alias for ftp_fget — Downloads a file from the FTP server and saves to an open file
     * alias for ftp_nb_fget — Retrieves a file from the FTP server and writes it to an open file (non-blocking)
     *
     * @param   string $file_to_write   full path to the local file to which we're writing
     * @param   string $remote_file     FTP path to the remote file we're copying from
     * @param   string $file_mode       mode to use on the local file. use http://www.php.net/manual/en/function.fopen.php for reference
     * @param   int    $ftp_mode        The transfer mode. Must be either FTP_ASCII or FTP_BINARY.
     * @param   int    $resume_pos      The position in the remote file to start downloading from.
     * @param   bool   $blocking        Whether to perform blocking. If FALSE, uploads the file asynchronously,
     *                                  so your program can perform other operations while the file is being uploaded.
     *
     * @return bool|int
     */
    public function fget($file_to_write, $remote_file, $file_mode = 'W', $ftp_mode = FTP_ASCII, $resume_pos = 0, $blocking = true)
    {
        if (!file_exists($file_to_write)) {
            return false;
        }
        $handle = fopen($file_to_write, $file_mode);
        if (!is_resource($handle)) {
            return false;
        }

        if ($blocking == true) {
            if (ftp_fget($this->conn_id, $handle, $remote_file, $ftp_mode, $resume_pos)) {
                return true;
            } else {
                return false;
            }
        } else {
            return ftp_nb_fget($this->conn_id, $handle, $remote_file, $ftp_mode, $resume_pos);
        }
    }

    /**
     * alias for ftp_fput — Uploads from an open file to the FTP server
     * alias for ftp_nb_fput — Stores a file from an open file to the FTP server (non-blocking)
     *
     * @param   string $file        full_path to the file to put
     * @param   string $file_mode   mode to use on the local file. use http://www.php.net/manual/en/function.fopen.php for reference
     * @param   int    $ftp_mode    The transfer mode. Must be either FTP_ASCII or FTP_BINARY.
     * @param   bool   $blocking    Whether to perform blocking. If FALSE, uploads the file asynchronously,
     *                              so your program can perform other operations while the file is being uploaded.
     *
     * @return bool|int
     */
    public function fput($file, $file_mode = 'r', $ftp_mode = FTP_ASCII, $blocking = true)
    {
        if (!file_exists($file)) {
            return false;
        }
        $handle = fopen($file, $file_mode);
        if (!is_resource($handle)) {
            return false;
        }
        if ($blocking == true) {
            if (ftp_fput($this->conn_id, $file, $handle, FTP_ASCII)) {
                return true;
            } else {
                return false;
            }
        } else {
            return ftp_nb_fput($this->conn_id, $file, $handle, $ftp_mode);
        }
    }

    /**
     *
     * alias for ftp_get_option — Retrieves various runtime behaviours of the current FTP stream
     *
     * @param   int $option
     *
     * @return  mixed
     */
    public function getOption($option)
    {
        return ftp_get_option($this->conn_id, $option);
    }

    /**
     *
     * alias for ftp_get — Downloads a file from the FTP server
     * alias for ftp_nb_get — Retrieves a file from the FTP server and writes it to a local file (non-blocking)
     *
     * @param   string $local_file      The local file path (will be overwritten if the file already exists).
     * @param   string $server_file     The remote file path (IOW, the file to get)
     * @param   int    $mode            The transfer mode. Must be either FTP_ASCII or FTP_BINARY.
     * @param   int    $resumepos       The position in the remote file to start downloading from.
     * @param   bool   $blocking        Whether to perform blocking. If FALSE, uploads the file asynchronously,
     *                                  so your program can perform other operations while the file is being uploaded.
     *
     * @return  bool
     */
    public function get($local_file, $server_file, $mode = FTP_ASCII, $resumepos = 0, $blocking = true)
    {
        if ($blocking == true) {
            if (ftp_get($local_file, $server_file, $mode, $resumepos)) {
                return true;
            } else {
                return false;
            }
        } else {
            return ftp_nb_get($local_file, $server_file, $mode, $resumepos);
        }
    }

    /**
     *
     * alias for ftp_mkdir — Creates a directory
     *
     * @param   string $dir
     *
     * @return  bool
     */
    public function mkdir($dir)
    {
        if (ftp_mkdir($this->conn_id, $dir)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * alias for ftp_nb_continue — Continues retrieving/sending a file (non-blocking)
     *
     * @return  int
     */
    public function nbContinue()
    {
        return ftp_nb_continue($this->conn_id);
    }

    /**
     *
     * alias for ftp_nlist — Returns a list of files in the given directory
     *
     * @param   string $dir Which directory to list files from. Defaults to current dir
     *
     * @return  array
     */
    public function dirList($dir = ".")
    {
        return ftp_nlist($this->conn_id, $dir);
    }

    /**
     *
     * creates a multidimensional array of files in a directory including contents of subdirectories
     *
     * @param   string $path Path to the directory to list
     *
     * @return  array
     */
    public function recursiveDirList($path)
    {
        static $allFiles = array();
        $contents = ftp_nlist($this->conn_id, $path);

        foreach ($contents as $currentFile) {
            // assuming its a folder if there's no dot in the name
            if (strpos($currentFile, '.') === false) {
                //@TODO where is this declared?
                ftpRecursiveFileListing($this->conn_id, $currentFile);
            }
            $allFiles[$path][] = substr($currentFile, strlen($path) + 1);
        }

        return $allFiles;
    }

    /**
     *
     * alias for ftp_pasv — Turns passive mode on or off
     *
     * @param   bool $on whether or not to turn passive mode on
     *
     * @return  bool
     */
    public function pasv($on = true)
    {
        return ftp_pasv($this->conn_id, $on);
    }

    /**
     *
     * alias for ftp_put — Uploads a file to the FTP server
     * also alias for ftp_nb_put — Stores a file on the FTP server (non-blocking)
     *
     * @param   string $remoteFile  The remote file path. (the destination location)
     * @param   string $localFile   The local file path (the file to put)
     * @param   int    $mode        The transfer mode. Must be either FTP_ASCII or FTP_BINARY.
     * @param   int    $startPos    The position in the remote file to start uploading to.
     * @param   bool   $blocking    Whether to perform blocking. Setting to FALSE uploads the file asynchronously,
     *                              so your program can perform other operations while the file is being uploaded.
     *
     * @return  bool
     */
    public function put($remoteFile, $localFile, $mode = FTP_ASCII, $startPos = 0, $blocking = true)
    {
        if ($blocking == true) {
            if (ftp_put($this->conn_id, $filePath, $file, $mode, $startPos)) {
                return true;
            } else {
                return false;
            }
        } else {
            if (ftp_nb_put($this->conn_id, $filePath, $file, $mode, $startPos)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     *
     * alias of ftp_pwd — Returns the current directory name
     *
     * @return  string
     */
    public function currentDir()
    {
        return ftp_pwd($this->conn_id);
    }

    /**
     *
     * alias for ftp_raw — Sends an arbitrary command to an FTP server
     *
     * @param   string $command
     *
     * @return  bool
     */
    public function raw($command)
    {
        return ftp_raw($this->conn_id, trim($command));
    }

    /**
     *
     * alias for ftp_rawlist — Returns a detailed list of files in the given directory
     *
     * @param   string $dir
     *
     * @return  array
     */
    public function rawlist($dir)
    {
        return ftp_rawlist($this->conn_id, $dir);
    }

    /**
     *
     * alias for ftp_rename — Renames a file or a directory on the FTP server
     *
     * @param   string $old
     * @param   string $new
     *
     * @return  bool
     */
    public function rename($old, $new)
    {
        if (ftp_rename($this->conn_id, $old, $new)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * alias for ftp_rmdir — Removes a directory
     *
     * @param   string $dir
     *
     * @return  bool
     */
    public function rmdir($dir)
    {
        if (ftp_rmdir($this->conn_id, $dir)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * alias for ftp_set_option — Set miscellaneous runtime FTP options
     *
     * @param   string $option
     * @param   string $value
     *
     * @return  bool
     */
    public function setOption($option, $value)
    {
        return ftp_set_option($this->conn_id, $option, $value);
    }

    /**
     *
     * alias for ftp_size — Returns the size of the given file
     *
     * @param   string $file ;
     *
     * @return  int
     */
    public function getSize($file)
    {
        $res = ftp_size($this->conn_id, $file);
        if ($res != -1) {
            return $res;
        } else {
            return false;
        }
    }

    /**
     *
     * determines, based on the file extension, what the FTP transfer mode should be
     *
     * @param   string $file path to the file
     *
     * @return  int
     */
    function getFTPMode($file)
    {
        $path_parts = pathinfo($file);

        if (!isset($path_parts['extension'])) {
            return FTP_BINARY;
        }

        switch (strtolower($path_parts['extension'])) {
            case 'am' :
            case 'asp' :
            case 'bat' :
            case 'c' :
            case 'cfm' :
            case 'cgi' :
            case 'conf' :
            case 'cpp' :
            case 'css' :
            case 'dhtml' :
            case 'diz' :
            case 'h' :
            case 'hpp' :
            case 'htm' :
            case 'html' :
            case 'in' :
            case 'inc' :
            case 'js' :
            case 'm4' :
            case 'mak' :
            case 'nfs' :
            case 'nsi' :
            case 'pas' :
            case 'patch' :
            case 'php' :
            case 'php3' :
            case 'php4' :
            case 'php5' :
            case 'phtml' :
            case 'pl' :
            case 'po' :
            case 'py' :
            case 'qmail' :
            case 'sh' :
            case 'shtml' :
            case 'sql' :
            case 'tcl' :
            case 'tpl' :
            case 'txt' :
            case 'vbs' :
            case 'xml' :
            case 'xrc' :
                return FTP_ASCII;
        }

        return FTP_BINARY;
    }

    /**
     *
     * emulates PHP's native file_get_contents. This uses FTP to get the content of a remote file into a string
     *
     * @param   string $remote_file The file to retrieve
     * @param   int    $mode
     * @param   int    $resume_pos  The position in the remote file to start downloading from.
     *
     * @return  string
     */
    function FTPGetContents($remote_file, $mode = FTP_ASCII, $resume_pos = null)
    {
        $pipes = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if ($pipes === false) {
            return false;
        }
        if (!stream_set_blocking($pipes[1], 0)) {
            fclose($pipes[0]);
            fclose($pipes[1]);

            return false;
        }
        $fail = false;
        $data = '';
        if (is_null($resume_pos)) {
            $ret = ftp_nb_fget($this->conn_id, $pipes[0], $remote_file, $mode);
        } else {
            $ret = ftp_nb_fget($this->conn_id, $pipes[0], $remote_file, $mode, $resume_pos);
        }
        while ($ret == FTP_MOREDATA) {
            while (!$fail && !feof($pipes[1])) {
                $r = fread($pipes[1], 8192);
                if ($r === '') {
                    break;
                }
                if ($r === false) {
                    $fail = true;
                    break;
                }
                $data .= $r;
            }
            $ret = ftp_nb_continue($this->conn_id);
        }
        while (!$fail && !feof($pipes[1])) {
            $r = fread($pipes[1], 8192);
            if ($r === '') {
                break;
            }
            if ($r === false) {
                $fail = true;
                break;
            }
            $data .= $r;
        }
        fclose($pipes[0]);
        fclose($pipes[1]);
        if ($fail || $ret != FTP_FINISHED) {
            return false;
        }

        return $data;
    }

}