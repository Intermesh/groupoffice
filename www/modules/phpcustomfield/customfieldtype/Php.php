<?php

namespace GO\Phpcustomfield\Customfieldtype;


class Php extends \GO\Customfields\Customfieldtype\AbstractCustomfieldtype{
	
	public function name(){
		return 'Php';
	}
	
	public function formatDisplay($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
		
		if (empty($this->field->extra_options))
			return '';
			
		$f = $this->field->extra_options;
		$old = ini_set("display_errors", "on");
		$method = function ($cf, $model) use($f) {
			
			try{
				$ret = eval($f);
			} catch(\Throwable $e){
				\GO::debug($this->field->id);
				\GO::debug($e->getMessage());
				return "Error: ".$this->field->id.", ".$e->getMessage();
			}
						
			return (string) $ret;
		};
		if($old!==false)
			ini_set("display_errors", $old);
		
		return (string)$method($model, $model->getModel()); //cast to string because displaypanel checks for field.length
	}
	
	public function formatFormOutput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {		
		return $this->formatDisplay($key, $attributes, $model);
	}
    
    public function formatRawOutput($key, &$attributes, \GO\Customfields\Model\AbstractCustomFieldsRecord $model) {
        return $this->formatDisplay($key, $attributes, $model);
    }

    public function fieldSql() {
		return "TINYINT(1) NOT NULL DEFAULT 1";
	}
	
	private function php_syntax_error($code){
    $braces=0;
    $inString=0;
    foreach (token_get_all('<?php ' . $code) as $token) {
        if (is_array($token)) {
            switch ($token[0]) {
                case T_CURLY_OPEN:
                case T_DOLLAR_OPEN_CURLY_BRACES:
                case T_START_HEREDOC: ++$inString; break;
                case T_END_HEREDOC:   --$inString; break;
            }
        } else if ($inString & 1) {
            switch ($token) {
                case '`': case '\'':
                case '"': --$inString; break;
            }
        } else {
            switch ($token) {
                case '`': case '\'':
                case '"': ++$inString; break;
                case '{': ++$braces; break;
                case '}':
                    if ($inString) {
                        --$inString;
                    } else {
                        --$braces;
                        if ($braces < 0) break 2;
                    }
                    break;
            }
        }
    }
    $inString = @ini_set('log_errors', false);
    $token = @ini_set('display_errors', true);
    ob_start();
    $braces || $code = "if(0){{$code}\n}";
    if (eval($code) === false) {
        if ($braces) {
            $braces = PHP_INT_MAX;
        } else {
            false !== strpos($code,"\r") && $code = strtr(str_replace("\r\n","\n",$code),"\r","\n");
            $braces = substr_count($code,"\n");
        }
        $code = ob_get_clean();
        $code = strip_tags($code);
        if (preg_match("'syntax error, (.+) in .+ on line (\d+)$'s", $code, $code)) {
            $code[2] = (int) $code[2];
            $code = $code[2] <= $braces
                ? array($code[1], $code[2])
                : array('unexpected $end' . substr($code[1], 14), $braces);
        } else $code = array('syntax error', 0);
    } else {
        ob_end_clean();
        $code = false;
    }
    @ini_set('display_errors', $token);
    @ini_set('log_errors', $inString);
    return $code;
}
}
