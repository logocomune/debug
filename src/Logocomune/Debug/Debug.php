<?php
namespace Logocomune;

/**
 *
 * Class Debug. Useful for printing debug information
 *
 *
 *
 * @package Logocomune\Debug
 */
class Debug
{

    static private $conf = array(
        'render_method' => 'print_r',//'var_dump','print_r','var_export  [display variable]
        'float_decimal' => 3, //decimal precision
        'html' => null, //Custom Html template
        'text' => null, //Custom text
        'trace' => false, //Enable/Disable trace
        'debug' => true, //Enable/Disable debug
        'render_local' => true,
    );

    private $debugTracePosition = 1;

    private $scriptPathInfo;
    private $debugTrace;
    private $var = null;


    public static function renderAs($type)
    {
        switch ($type) {
            case'var_dump':
                static::$conf['render_method'] = 'var_dump';
                break;

            case'var_export':
                static::$conf['render_method'] = 'var_export';
                break;
            default:

            case'print_r':
                static::$conf['render_method'] = 'print_r';
                break;


        }
    }

    /**
     * Enable backtrace
     */
    public static function backtrace()
    {
        static::$conf['trace'] = true;
    }

    /**
     * Disable backtrace
     */
    public static function backtraceOff()
    {
        static::$conf['trace'] = false;
    }

    /**
     * Enable debug
     */
    public static function enable()
    {
        static::$conf['debug'] = true;
    }

    /**
     * Disable debug
     */
    public static function disable()
    {
        static::$conf['debug'] = false;
    }

    function __construct()
    {

        $this->debugTrace = debug_backtrace();
        $this->scriptPathInfo = pathinfo(realpath($_SERVER['SCRIPT_FILENAME']));
    }

    /**
     * @param $debugTracePosition
     * @return $this
     */
    public function setDebugTracePosition($debugTracePosition)
    {
        $this->debugTracePosition = $debugTracePosition;
        return $this;
    }

    /**
     * @param $var
     * @return $this
     */
    public function setVar($var)
    {
        $this->var = $var;
        return $this;
    }

    /**
     * Test if script is called into a shell
     * @return bool
     */
    private function isCli()
    {
        return php_sapi_name() == 'cli';
    }

    protected function getFileName()
    {
        $filePath = $this->scriptPathInfo['dirname'];
        return "/" . substr(str_replace($filePath, '', $this->debugTrace[$this->debugTracePosition]['file']), 1);
    }

    protected function getFileLineNumber()
    {
        return $this->debugTrace[$this->debugTracePosition]['line'];
    }

    /**
     * Return current memory in MB
     * @return string
     */
    protected function getMemoryUsage()
    {
        $mem = memory_get_usage() / (1024 * 1024); //MB
        return number_format($mem, static::$conf['float_decimal']);

    }

    /**
     * Return current peak memory in MB
     *
     * @return string
     */
    protected function getPeakMemoryUsage()
    {
        $mem = memory_get_peak_usage() / (1024 * 1024); //MB
        return number_format($mem, static::$conf['float_decimal']);
    }

    /**
     * Return trace list for (html and text)
     *
     * @return string
     */
    protected function getTrace()
    {
        $trace = $this->debugTrace;
        $str = '';

        if (static::$conf['trace']) {
            if ($this->isCli()) {
                $str .= "Trace:\n";
            } else {
                $str .= "<h5>Trace</h5>";
            }
            for ($i = count($trace) - 1; $i >= $this->debugTracePosition; $i--) {
                $class = '';
                if (isset($trace[$i]['class'])) {
                    $class .= $trace[$i]['class'] . '::';
                }
                if (isset($trace[$i]['function'])) {
                    $class .= $trace[$i]['function'];
                }
                if ($this->isCli()) {
                    $str .= $trace[$i]['file'];
                    if ($class !== '') {
                        $str .= " [" . $class . "] ";
                    }
                    $str .= "(line: " . $trace[$i]['line'] . ")\n";
                } else {


                    $str .= "<p>" . (isset($trace[$i]['file']) ? $trace[$i]['file'] : '');
                    if ($class !== '') {
                        $str .= " [" . $class . "] ";
                    }
                    $str .= isset($trace[$i]['line']) ? "(line: " . $trace[$i]['line'] . ')' : '';
                    $str .= '</p>';
                }
            }
        }
        return $str;
    }

    /**
     * Return html template for rendering
     *
     * @return string
     */
    private function renderHtml()
    {
        if (static::$conf['html'] == null) {
            $html = <<<HTML
<div class="lcf_debug_cnt" style="z-index:9999; background-color:rgba(225,225,225,0.9);position:relative;border-color:red;border-style:dotted;border-width:2px;padding:10px;color:black">
<div class="lcf_text_info"><b>%s</b> (line: <b>%s</b>) [Memory: %s MB (peak: %s MB)]</div>
<div class="lcf_text_var">
<i>%s</i>
<pre style="z-index:1000000; background-color:rgba(124,252,0,0.5);position:relative">%s</pre></div>
<div class="trace">%s</div>
</div>
HTML;
        } else {
            $html = static::$conf['html'];
        }
        return $html;
    }

    /**
     * Return text template for rendering
     *
     * @return string
     */
    private function renderText()
    {
        if (static::$conf['text'] == null) {
            $text = <<<TEXT
-------->
File: %s (line: %s)\n
Memory: %s MB - Memory Peak: %s
###########
%s
-----
%s
###########
%s
TEXT;

        } else {
            $text = static::$conf['text'];
        }
        return $text;
    }


    private function render()
    {
        if ($this->isCli()) {
            $str = $this->renderText();
        } else {
            $str = $this->renderHtml();
        }
        printf($str, $this->getFileName(), $this->getFileLineNumber(), $this->getMemoryUsage(), $this->getPeakMemoryUsage(), $this->varType(), $this->renderVar(), $this->getTrace());
        return $this;
    }

    /**
     * Return render var. According to static::$conf['render_method'] can output a print_r, var_dump or a var_export style.
     *
     * @return string
     */
    protected function renderVar()
    {

        switch (static::$conf['render_method']) {
            case 'var_dump':
                ob_start();
                var_dump($this->var);
                $var = ob_get_clean();
                break;
            case 'print_r':
                ob_start();
                print_r($this->var);
                $var = ob_get_clean();
                break;
            case 'var_export':
                $var = var_export($this->var, true);
                break;

        }
        return $var;
    }

    /**
     * Return variable type for rendering purpose. If variable is an object return also the class name
     *
     * @return string
     */
    protected function varType()
    {
        $s = '';
        $varType = gettype($this->var);
        $s .= ucfirst($varType);
        if ($varType == 'object') {
            $s .= ' instance of ' . get_class($this->var);
        }
        return $s;
    }


    /**
     * Debug main method
     *
     * @param $var var to see
     * @param int $debugTracePosition
     */
    public static function debug($var, $debugTracePosition = 1)
    {
        $deb = null;
        if (static::$conf['debug'] == true) {

            $deb = new static();
            $deb
                ->setVar($var)
                ->setDebugTracePosition($debugTracePosition);
            if (static::$conf['render_local'] == true) {
                $deb->render();
            }

        }
        return $deb;
    }

    /**
     * Override default configurtion
     *
     * @param array $confs
     */
    public static function setConfs($confs = array())
    {
        static::$conf = array_merge(static::$conf, $confs);
    }

    /**
     * Override single value of configuration:
     * es:
     *        LcfDebug::setConf('render_method','var_dump');
     *        LcfDebug::setConf('render_method','var_export');
     * @param $key
     * @param $value
     */
    public static function setConf($key, $value)
    {
        static::$conf = array_merge(static::$conf, array($key => $value));
    }

}
