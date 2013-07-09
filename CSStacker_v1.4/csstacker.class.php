<?php

namespace u4u;

/**
 * CSStacker - A stacker for CSS files
 *
 * @author Camilo Sperberg - http://unreal4u.com/
 * @version 1.4
 */
class csstacker {
    /**
     * Holds all the files to add to the stack
     * @var array
     */
    private $files = array();

    /**
     * Holds the total number of CSS files to process
     * @var int
     */
    private $qCSS = 0;

    /**
     * The filename of the CSS file
     * @var string
     */
    private $filename = '';

    /**
     * The last modification date of the CSS cache file
     * @var string
     */
    private $lastmod = '';

    /**
     * Whether to include CSS reset or not. Defaults to FALSE
     * @var boolean
     */
    public $resetCSS = false;

    /**
     * Holds all errors or notices of this object
     * @var array
     */
    public $CSSErrors = array();

    public function __construct() {
        // Not implemented yet
    }

    /**
     * Adds CSS files to the stack
     *
     * @param mixed $file The location of the file to add to the stack
     * @return boolean Returns always true
     */
    public function add($file) {
        if (is_array($file)) {
            foreach ($file as $a) {
                if ($this->verify_original_css($a)) {
                    $this->qCSS++;
                }
            }
        } else if ($this->verify_original_css($file)) {
            $this->qCSS++;
        }

        return true;
    }

    /**
     * Does all the heavy job
     *
     * @param string $method Choose between "file", "inline", "filename". Defaults to "file"
     * @param string $force Whether to "force" creation or leave default behaviour ("")
     * @return mixed Can return boolean, int or string depending on options
     */
    public function printme($method='file', $force='') {
        if ($this->qCSS > 0 and ($method == 'file' or $method == 'inline' or $method == 'filename')) {
            if ($force == 'force') {
                $force = true;
                $this->error('NOTICE', 'Cache creation is being forced!');
            } else {
                $force = false;
            }

            $this->filename = $this->get_cache_filename();
            $this->lastmod = time();
            $status = $this->status($method, $force);
            switch ($status['act']) {
                case 1:
                    header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified', true, 304);
                    $this->output_headers();
                break;
                case 2:
                    ob_start();
                    header('Content-Type: text/css; charset=' . CHARSET);
                    $this->output_headers();
                    if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) and GZIP_CONTENTS) {
                        $enc = explode(',', str_replace(' ', '', $_SERVER['HTTP_ACCEPT_ENCODING']));
                        if (in_array('gzip', $enc)) {
                            header('Content-Encoding: gzip');
                            echo gzencode($status['css'], GZIP_LEVEL);
                            return true;
                        }
                    }
                    echo $status['css'];
                    header('Content-Length: ' . ob_get_length());
                    ob_end_flush();
                break;
                case 3:
                    echo $status['css'];
                break;
                case 4:
                    return str_replace(CACHE_LOCATION, EXTERNAL_ROUTE, $status['css']);
                break;
            }
            return 1;
        }
        return 0;
    }

    /**
	 * Verifies if original CSS's can be readable or not
	 *
	 * @param string The file to verify
	 */
    private function verify_original_css($file) {
        $is_valid = TRUE;
        if (is_readable($file) and !in_array($file, $this->files)) {
            $this->files[] = $file;
        } else {
            $is_valid = FALSE;
            if (!file_exists($file)) {
                $this->error('WARNING', 'File "' . $file . '" doesn\'t exist');
            } else {
                $this->error('FATAL', 'File "' . $file . '" exists but I cannot read it. Permission problems?');
            }
        }
        return $is_valid;
    }

    /**
	 * Does several checks, and based on that returns a status code and the corresponding CSS
	 *
	 * @param string $method
	 * @param boolean $force When forcing creation, true. Otherwise, false
	 * @return array
	 */
    private function status($method, $force) {
        $out = array(
            'act' => '1',
            'css' => false
        );

        $cache_created = false;
        $is_cache_valid = false;
        if (USE_CSS_CACHE and $force == false) {
            $is_cache_valid = $this->valid_cache();
        } else {
            $force = true;
        }

        if ($is_cache_valid and $force == false) {
            if ($method == 'file' and USE_CSS_CACHE and USE_BROWSER_CACHE and isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $this->lastmod) {
                return $out;
            } elseif ($method == 'filename') {
                return array(
                    'act' => '4',
                    'css' => $this->filename,
                );
            } elseif ($method == 'inline') {
                return array(
                    'act' => '3',
                    'css' => $this->get_cache()
                );
            } else {
                $out['act'] = 2;
            }
        } elseif ($force == true or !$is_cache_valid) {
            $out['css'] = $this->compress();
            if ($method == 'file') {
                $out['act'] = 2;
            } elseif ($method == 'inline') {
                $out['act'] = 3;
            }
        }
        if ($out['css'] !== false) {
            $cache_created = $this->create_cache($out['css']);
        } else {
            $out['css'] = $this->get_cache();
        }
        if ($method == 'filename') {
            $out['act'] = 4;
            $out['css'] = $cache_created;
        }
        return $out;
    }

    /**
     * Outputs the necesary browser cache headers
	 */
    private function output_headers() {
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $this->lastmod) . ' GMT');
        header('Cache-Control: public, must-revalidate, max-age=' . TIME_BROWSER_CACHE);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + TIME_BROWSER_CACHE) . ' GMT');
        return true;
    }

    /**
	 * Checks if cache is still valid
	 */
    private function valid_cache($check=NULL) {
        if (!USE_CSS_CACHE) {
            return false;
        }
        $is_valid = true;
        if (!is_readable($this->filename)) {
            $is_valid = false;
            if (!file_exists($this->filename)) {
                $this->error('NOTICE', 'Cache file doesn\'t exist.');
            } else {
                $this->error('FATAL', 'Couldn\'t read the cache file. Please check permissions!');
            }
        }

        if ($is_valid) {
            foreach ($this->files as $a) {
                if (filemtime($a) > filemtime($this->filename) and $is_valid) {
                    $is_valid = false;
                }
            }
        }

        if (!empty($check) or !$is_valid) {
            return $is_valid;
        } else {
            $this->lastmod = filemtime($this->filename);
            return $is_valid;
        }
    }

    /**
	 * Creates or get an unique name for the cache based on original CSS filenames
	 */
    private function get_cache_filename() {
        $filename = '';
        foreach ($this->files as $a) {
            $filename .= $a;
        }
        return CACHE_LOCATION . md5($filename) . '.css';
    }

    /**
	 * Creates the cache file
	 */
    private function create_cache($contents) {
        $created = false;
        if (is_writable(CACHE_LOCATION)) {
            if (USE_CSS_CACHE) {
                file_put_contents($this->filename, $contents);
                $created = $this->filename;
            }
        } else {
            $this->error('WARNING', 'Cache location isn\'t writable');
        }
        return $created;
    }

    /**
	 * Rescues the cache content
	 */
    private function get_cache() {
        $cache = false;
        $recreate = false;
        if (is_readable($this->filename)) {
            if (!$this->valid_cache(true)) {
                $recreate = true;
            }

            if ($recreate === false) {
                $cache = file_get_contents($this->filename);
            } else {
                unlink($this->filename);
                $this->error('NOTICE', 'Cache file out-dated. Deleted so it can be recreated');
            }
        } else {
            if (!file_exists($this->filename)) {
                $this->error('NOTICE', 'Cache file doesn\'t exist');
            } else {
                $this->error('WARNING', 'Cache file exists, but I could\'t read it. Permission or disk problems?');
            }
        }
        return $cache;
    }

    /**
     * Does several replaces in order to strip comments and remove some common unnecesary spaces
	 */
    private function compress() {
        if ($this->resetCSS) {
            $out = 'html{color:#000;background:#FFF}body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,textarea,p,blockquote,th,td{margin:0;padding:0}table{border-collapse:collapse;border-spacing:0}fieldset,img{border:0}address,caption,cite,code,dfn,em,strong,th,var{font-style:normal;font-weight:normal}li{list-style:none}caption,th{text-align:left}h1,h2,h3,h4,h5,h6{font-size:100%;font-weight:normal}q:before,q:after{content:\'\'}abbr,acronym{border:0;font-variant:normal}sup{vertical-align:text-top}sub{vertical-align:text-bottom}input,textarea,select{font-family:inherit;font-size:inherit;font-weight:inherit}input,textarea,select{*font-size:100%}legend{color:#000}';
            // CSS reset is taken from Yahoo developer and optimized/compressed:
            // http://developer.yahoo.com/yui/3/cssreset/
            // Original file is the Global one, its size is 501 bytes, compressed to 343 bytes.
        } else {
            $out = '';
        }

        foreach ($this->files as $a) {
            $css_content = file_get_contents($a);
            if (strlen($css_content) > 94325) {
                $tmp_buffer = str_split($css_content, 94300);
            } else {
                $tmp_buffer[] = $css_content;
                // Why this? See: http://www.php.net/manual/en/function.preg-replace.php#93840
                // Note that around that area it is impossible to do a good optimization!
                // If you happen to have such big CSS files, it would be better to separate it into smaller ones
            }

            unset($css_content);
            $tmp_buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $tmp_buffer); // Strip out all comments
            $tmp_buffer = str_replace(array(
                "\r\n",
                "\r",
                "\n",
                "\t",
                '  ',
                '    '
            ), '', $tmp_buffer); // All enter types + additional white spaces
            if (OPTIMIZE_CSS === TRUE) {
                $out .= $this->optimize($tmp_buffer);
            } else {
                if (is_array($tmp_buffer)) {
                    foreach ($tmp_buffer as $b) {
                        $out .= $b;
                    }
                    unset($b);
                } else {
                    $out .= $tmp_buffer;
                }
            }
            unset($tmp_buffer);
        }
        return $out;
    }

    /**
	 * Goes a little further replacing color codes and some common colors with their color codes
	 */
    private function optimize($the_css) {
        $original = array(
            ', ',
            ' , ',
            ';}',
            '; }',
            ' ; }',
            ' :',
            ': ',
            ' {',
            '; ',  // Some common typos and unnecesary characters
            ':black',
            ':dark grey',
            ':fuchsia',
            ':light grey',
            ':orange',
            ':white',
            ':yellow'
        ); // Come common colors which are shorter in hex
        $replace = array(
            ',',
            ',',
            '}',
            '}',
            '}',
            ':',
            ':',
            '{',
            ';',  // their replacements
            ':#000',
            ':#666',
            ':#F0F',
            ':#CCC',
            ':#F60',
            ':#FFF',
            ':#FF0'
        );
        $the_css = str_replace(array(
            ' 0px',
            ' 0em',
            ' 0%',
            ' 0ex',
            ' 0cm',
            ' 0mm',
            ' 0in',
            ' 0pt',
            ' 0pc'
        ), ' 0', $the_css);
        $the_css = str_replace(array(
            ':0px',
            ':0em',
            ':0%',
            ':0ex',
            ':0cm',
            ':0mm',
            ':0in',
            ':0pt',
            ':0pc'
        ), ':0', $the_css); // 0 needs no unit
        foreach ($the_css as $a) { // Color code optimization. Ex: #334455 -> #345
            $how_many = substr_count($a, '#');
            $offset = 0;
            for ($i = 0; $i < $how_many; $i++) {
                $from = strpos($a, '#', $offset) + 1;
                $tmp = substr($a, $from, 6);
                $k = strlen($tmp);
                for ($j = 0; $j < $k + 1; $j++) {
                    if ($j % 2 != 0) {
                        if ($pre[$j - 1] == $tmp{$j}) {
                            $pre[] = TRUE;
                        } else {
                            $pre[] = FALSE;
                        }
                    } elseif ($j != $k) {
                        $pre[] = $tmp{$j};
                    }
                }
                if ($pre[1] and $pre[3] and $pre[5] and !in_array('#' . $tmp, $original)) {
                    $original[] = '#' . $tmp;
                    $replace[] = '#' . $pre[0] . $pre[2] . $pre[4];
                }
                unset($pre, $tmp, $j, $k);
                $offset = $from;
            }
        }
        $the_css = str_ireplace($original, $replace, $the_css);
        $out = '';
        foreach ($the_css as $a) {
            $out .= $a;
        }

        unset($original, $replace, $how_many, $offset, $from, $i, $a, $the_css);
        $out = implode('}', array_reverse(array_unique(array_reverse(explode('}', $out)))));
        // NOTE: This is beta! If you happen to have problems with the CSS, comment the above line
        //       What it does is stripping out repeated declarations, which means that:
        //   body{color:#000}p{background:#EEE}body{color:#000}
        //       will result in:
        //   p{background:#EEE}body{color:#000}
        return $out;
    }

    /**
	 * Keeps a log on all errors or notices
	 */
    private function error($errtype, $errmsg) {
        $this->CSSErrors[] = array(
            'type' => $errtype,
            'errm' => $errmsg
        );
    }
}
