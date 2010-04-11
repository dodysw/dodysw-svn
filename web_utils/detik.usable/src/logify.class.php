<?

// start libdetikusable
class Logify {
    var $log = array();
    function add ($line_number,$string) {
        $this->log[] = array($line_number,$string);
    }
    function write_error ($title,$description='<p>This is an expected error. Check your configuration and internet connection</p>') {
        global $error; $error = TRUE;
        echo '<div style="border:thin solid #ffaaaa;background-color:#ffcccc;margin:10;text-align:center;"><h3>'.$title.'</h3>'.$description.'</div>';
        echo '<h4>Log history</h4>';
        echo $this->dump();
        echo '<h4>Debug traceback</h4>';
        $this->print_debug_backtrace();
    }
    function dump () {
        echo '<pre>';
        foreach ($this->log as $temp_arr) echo '#'.$temp_arr[0].': '.htmlspecialchars($temp_arr[1])."\r\n";
        echo '</pre>';
    }
    function print_debug_backtrace () {
		if (PHP_VERSION >= 4.3) {
			$MAXSTRLEN = 128;
			echo '<pre>';
			$traceArr = debug_backtrace();
			array_shift($traceArr);
			$tabs = sizeof($traceArr)-1;
			foreach ($traceArr as $arr) {
				$args = array();
				for ($i=0; $i < $tabs; $i++) $s .= ' &nbsp; ';
				$tabs -= 1;
				if (isset($arr['class'])) $s .= $arr['class'].'.';
				if (isset($arr['args']))
				 foreach($arr['args'] as $v) {
					if (is_null($v)) $args[] = 'null';
					else if (is_array($v)) $args[] = 'Array['.sizeof($v).']';
					else if (is_object($v)) $args[] = 'Object:'.get_class($v);
					else if (is_bool($v)) $args[] = $v ? 'true' : 'false';
					else {
						$v = (string) @$v;
						$str = htmlspecialchars(substr($v,0,$MAXSTRLEN));
						if (strlen($v) > $MAXSTRLEN) $str .= '...';
						$args[] = $str;
					}
				}
				echo '<b>'.$arr['function']."</b>\t".'('.implode(', ',$args).')';
				echo @sprintf('<font color="#808080" size="-1"> # line %4d, file: <a href="file:/%s">%s</a></font>', $arr['line'],$arr['file'],$arr['file']);
				echo  "\r\n";
			}
			echo  '</pre>';
		}
    }
}

$log = new Logify();

function assert_callback( $script, $line, $message ) {
    global $author_email,$log;
    echo $log->write_error('Assertion error at line# '.$line, '<p>Send this whole page to <a href="mailto:'.$author_email.'">author</a> to help improve the next version.</p>');
    exit;
}
assert_options(ASSERT_CALLBACK,assert_callback);


?>
