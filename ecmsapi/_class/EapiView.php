<?php 
class EapiView
{
    protected $api      = null;
    protected $error    = null;
    protected $assign   = [
        'pagetitle' => '',
        'pagekey' => '',
        'pagedes' => ''
    ];
    protected $conf     = [];

    public function __construct($conf = [] , $api)
    {
        $this->api = $api;
        $this->conf = $conf;
    }

    public function assign($name = '' , $value = null)
    {
        if(is_array($name)){
            $this->assign = array_merge($this->assign , $name);
        }else if(is_string($name)){
            $name = trim($name);
            if($name !== ''){
                $this->assign[$name] = $value;
            }
        }
        return $this;
    }

    // 获取模板内容
    protected function text($tempid = 0)
    {
        $v = $this->api->load('db')->one('[!db.pre!]enewsclasstemp' , '*' , 'tempid='.$tempid);
        return false !== $v ? $v['temptext'] : '';
    }

    // 替换公共变量
    protected function replaceVars($text)
    {
        global $public_r;
        $text = str_replace('[!--news.url--]', $public_r['newsurl'], $text);
        $text = str_replace('[!--pagetitle--]' , $this->assign['pagetitle'] , $text);
        $text = str_replace('[!--pagekey--]' , $this->assign['pagekey'] , $text);
        $text = str_replace('[!--pagedes--]' , $this->assign['pagedes'] , $text);
        $text = stripSlashes($text);
        return $text;
    }


    public function view($tempid = 0 , $cachetime = 0, $assign = []){
        $_view = [
            'id'        => $tempid,
            'file'      => ECMS_PATH . 'e/data/tmp/dt_tempclasstemp'.$tempid.'.php',
            'cachetime' =>  (int)$cachetime
        ];
        $this->assign($assign);
        unset($tempid);
        unset($cachetime);
        unset($assign);
        extract($this->assign);
        $api = $this->api; // 将api释放到模板
        // 缓存文件
        if(file_exists($_view['file'])){
            if($cachetime === 0 || $_view['cachetime'] <= filemtime($_view['file'])){
                ob_start();
                include($_view['file']);
                $string = ob_get_contents();
                ob_end_clean();
                $string = RepExeCode($string); //解析代码
                $string = $this->replaceVars($string);
                return $string;
            }
        }
        $text = $this->text($_view['id']);

        $text=stripSlashes($text);
        $text=ReplaceTempvar($text);//替换全局模板变量
        //替换标签
        $text=DoRepEcmsLoopBq($text);
        $text=RepBq($text);
        //写文件
        WriteFiletext($_view['file'],AddCheckViewTempCode().$text);
        unset($text);
        //读取文件内容
        ob_start();
        include($_view['file']);
        $string = ob_get_contents();
        ob_end_clean();
        $string = RepExeCode($string);//解析代码
        $string = $this->replaceVars($string);
        return $string;
    }
}