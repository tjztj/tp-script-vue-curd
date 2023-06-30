<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;


/**
 * 文件
 * Class FilesField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class FilesField extends ModelField
{

    protected string $url='';//默认值是 tpScriptVueCurdUploadDefaultUrl
    protected bool $canExcelImport=false;//不能使用excel导入数据
    protected bool $canExport=false;//不能导出此字段数据
    protected string $accept='';//上传文件类型
    protected bool $multiple=true;//是否可多选
    protected bool $canDown=true;//上传成功后与详情页面是否可下载

    protected bool $checkFilesIsLocal=true;//验证上传的文件是否为本地文件

    /**
     * @var callable
     */
    protected $fileFieldShowUrlDo=null;

    /**
     * @var callable $fileInfoOn
     */
    private $fileInfoOn;


    //有时间再完善
    protected array $acceptTexts=[
        'application/vnd.ms-excel'=>'Excel',//.xls
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'Excel',//.xls
        '.xls'=>'Excel',
        '.xlsx'=>'Excel',
        'application/pdf'=>'pdf',
        '.pdf'=>'pdf',
        'application/vnd.ms-powerpoint'=>'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation'=>'ppt',
        '.ppt'=>'ppt',
        '.pptx'=>'ppt',
        'application/msword'=>'Word',//.xls
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'Word',//.xls
        '.doc'=>'Word',
        '.docx'=>'Word',
        'image/png'=>'png图片',
        '.png'=>'png图片',
        'image/jpeg'=>'jpg/jpeg图片',
        '.jpg'=>'jpg图片',
        '.jpeg'=>'jpeg图片',
        'image/*'=>'图片',
        'video/*'=>'视频',
    ];


    private static array $doShowFields=[];


    /**上传地址
     * @param string|null $url
     * @return $this|string
     */
    public function url(string $url = null)
    {
        if(is_null($url)){
            return $this->url?:tpScriptVueCurdUploadDefaultUrl();
        }
        $this->url=$url;
        $this->fieldPushAttrByWhere('url',$url);
        return $this;
    }
    /**
     * 是否可多选
     * @param bool|null $multiple
     * @return $this|bool
     */
    public function multiple(bool $multiple=null){
        return $this->doAttr('multiple',$multiple);
    }

    /**
     * 文件上传类型
     * @param string|null $accept
     * @return $this|string
     */
    public function accept(string $accept=null){
        if(is_null($accept)){
            return $this->accept;
        }
        $this->accept=str_replace(' ','',$accept);
        $this->fieldPushAttrByWhere('accept',$this->accept);
        return $this;
    }

    public function acceptTexts(array $acceptTexts=null){
        return $this->doAttr('acceptTexts',$acceptTexts);
    }

    /**
     * 上传成功后与详情页面是否可下载
     * @param bool|null $canDown
     * @return FilesField
     */
    public function canDown(bool $canDown=null){
        return $this->doAttr('canDown',$canDown);
    }


    /**
     * 设置保存的值
     * @param array $data 数据值集合
     * @return $this
     * @throws \think\Exception
     */
    protected function setSaveVal(array $data,BaseModel $old): self
    {
        if(isset($data[$this->name()])){
            $this->save=$data[$this->name()];
            if($this->save){
                $arr=explode('|',$this->save);
                foreach ($arr as $k=>$v){
                    if($this->checkFilesIsLocal()&&!self::checkFilesLocal($v)){
                        throw new \think\Exception((count($arr)>1?('第'.($k+1).'个'):'').'文件路径非法');
                    }
                }
                if($this->accept){
                    $accepts=explode(',',$this->accept);
                    foreach ($arr as $k=>$v){
                        $have=false;
                        foreach ($accepts as $val){
                            if(self::checkUrlIsMimeOrExt($v,$val)){
                                $have=true;
                                break;
                            }
                        }
                        if(!$have){
                            throw new \think\Exception((count($arr)>1?('第'.($k+1).'个'):'').'文件不符合要求');
                        }
                    }
                }
            }

        }
        $this->defaultCheckRequired($this->save,'请上传附件');
        return $this;
    }
    /**
     * 文件显示前，对地址处理
     * @param callable $fileFieldShowUrlDo
     * @return $this
     */
    public function setFileFieldShowUrlDo(callable $fileFieldShowUrlDo):self{
        $this->fileFieldShowUrlDo=$fileFieldShowUrlDo;
        return $this;
    }

    /**
     * 显示时对数据处理
     * @param array $dataBaseData
     */
    public function doShowData(array &$dataBaseData): void
    {
        parent::doShowData($dataBaseData);
        if(isset($dataBaseData[$this->name()])){
            $dataBaseData[$this->name()]=fileFieldShowUrlDo(trim($dataBaseData[$this->name()]),$this);

            $fileFieldShowUrlDo=$this->fileFieldShowUrlDo;
            if($fileFieldShowUrlDo!==null){
                $dataBaseData[$this->name()]=$fileFieldShowUrlDo($dataBaseData[$this->name()],$dataBaseData);
            }

            $dataBaseData[$this->name().'Arr']=$dataBaseData[$this->name()]?explode('|',$dataBaseData[$this->name()]):[];
            $dataBaseData[$this->name().'InfoArr']=[];
            self::$doShowFields[$this->guid()]=[
                'field'=>$this,
                'urls'=>$dataBaseData[$this->name().'Arr'],
                'infos'=>[]
            ];
            foreach ($dataBaseData[$this->name().'Arr'] as $v){
                isset(self::$doShowFields[$this->guid()]['infos'][$v])||self::$doShowFields[$this->guid()]['infos'][$v]=[];
                $dataBaseData[$this->name().'InfoArr'][$v]=&self::$doShowFields[$this->guid()]['infos'][$v];
            }


        }
    }


    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl):void{
        $excelFieldTpl->explain="此列不可导入数据";
    }


    /**
     * 可截断显示，如果有的话
     * @param $fileInfos
     */
    public function onFileInfo(&$fileInfos):void{
        if(!isset($this->fileInfoOn)||is_null($this->fileInfoOn)){
            return;
        }
        $func=$this->fileInfoOn;
        $func($fileInfos,$this);
    }


    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,'/tpscriptvuecurd/field/files/index.js'),
            new Show($type,'/tpscriptvuecurd/field/files/show.js'),
            new Edit($type,'/tpscriptvuecurd/field/files/edit.js')
        );
    }

    public static function setShowFileInfos():void{
        $urls=[];
        foreach (self::$doShowFields as $guid=>$obj){
            array_push($urls,...$obj['urls']);
        }
        if(empty($urls)){
            return;
        }

        $infos=[];
        foreach (tpScriptVueCurdGetFileInfosByUrls($urls) as $v){
            $infos[$v['url']]=$v;
        }


        foreach (self::$doShowFields as $guid=>$obj){
            $list=[];
            foreach ($obj['urls'] as $v){
                $list[$v]=$infos[$v]??['id'=>$v,'url'=>$v,'original_name'=>''];
            }
            $obj['field']->onFileInfo($list);
            foreach ($obj['infos'] as $url=>$info){
                self::$doShowFields[$guid]['infos'][$url]=$list[$url];
            }
        }

    }



    public static function checkUrlIsMimeOrExt(string $url,string $mimeOrExt):bool{
        $mimeOrExt=strtolower($mimeOrExt);
        $ext=strtolower( pathinfo($url, PATHINFO_EXTENSION));

        if(strpos($mimeOrExt,'.')!==false){
            return $ext===strtolower(pathinfo($mimeOrExt, PATHINFO_EXTENSION));
        }

        $mime=self::getExtMime($ext);

        if($mimeOrExt===$mime){
            return true;
        }

        if($mime===strtolower(pathinfo($mimeOrExt, PATHINFO_EXTENSION))){
            return true;
        }

        if(strpos($mimeOrExt,'*')!==false){
            $str='/^'.str_replace(['/','-','*'],['\\/','-','.+'],$mimeOrExt).'$/';
            if(preg_match ($str,$mime)){
                return true;
            }
        }

        return false;
    }

    public static function getMimeAboutExt(string $mime):array{
        $data=self::extAboutMime();

        static $arr=null;
        if(is_null($arr)){
            $arr=[];
            foreach ($data as $k=>$v){
                if(is_array($v)){
                    foreach ($v as $val){
                        isset($arr[$val])||$arr[$val]=[];
                        $arr[$val][]=$k;
                    }
                }else{
                    isset($arr[$v])||$arr[$v]=[];
                    $arr[$v][]=$k;
                }
            }
        }
        $mime=strtolower($mime);
        if(stripos($mime,'*')===false){
            return $arr[$mime]??[];
        }

        $return=[];
        foreach ($arr as $k=>$v){
            if(stripos($mime,'*')!==false){
                array_push($return,...$v);
            }
        }
        return $return;
    }


    public static function getExtMime(string $extOrurl):?string{
        $ext=stripos($extOrurl,'.')===false?$extOrurl:pathinfo($extOrurl, PATHINFO_EXTENSION);
        $ext=strtolower($ext);
        $data=self::extAboutMime();
        if(!isset($data[$ext])){
            return null;
        }
        return is_array($data[$ext])?current($data[$ext]):$data[$ext];
    }


    public static function extAboutMime(){
        return array(
            'hqx'	=>	array('application/mac-binhex40', 'application/mac-binhex', 'application/x-binhex40', 'application/x-mac-binhex40'),
            'cpt'	=>	'application/mac-compactpro',
            'csv'	=>	array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain'),
            'bin'	=>	array('application/macbinary', 'application/mac-binary', 'application/octet-stream', 'application/x-binary', 'application/x-macbinary'),
            'dms'	=>	'application/octet-stream',
            'lha'	=>	'application/octet-stream',
            'lzh'	=>	'application/octet-stream',
            'exe'	=>	array('application/octet-stream', 'application/x-msdownload'),
            'class'	=>	'application/octet-stream',
            'psd'	=>	array('application/x-photoshop', 'image/vnd.adobe.photoshop'),
            'so'	=>	'application/octet-stream',
            'sea'	=>	'application/octet-stream',
            'dll'	=>	'application/octet-stream',
            'oda'	=>	'application/oda',
            'pdf'	=>	array('application/pdf', 'application/force-download', 'application/x-download', 'binary/octet-stream'),
            'ai'	=>	array('application/pdf', 'application/postscript'),
            'eps'	=>	'application/postscript',
            'ps'	=>	'application/postscript',
            'smi'	=>	'application/smil',
            'smil'	=>	'application/smil',
            'mif'	=>	'application/vnd.mif',
            'xls'	=>	array('application/vnd.ms-excel', 'application/msexcel', 'application/x-msexcel', 'application/x-ms-excel', 'application/x-excel', 'application/x-dos_ms_excel', 'application/xls', 'application/x-xls', 'application/excel', 'application/download', 'application/vnd.ms-office', 'application/msword'),
            'ppt'	=>	array('application/powerpoint', 'application/vnd.ms-powerpoint', 'application/vnd.ms-office', 'application/msword'),
            'pptx'	=> 	array('application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/x-zip', 'application/zip'),
            'wbxml'	=>	'application/wbxml',
            'wmlc'	=>	'application/wmlc',
            'dcr'	=>	'application/x-director',
            'dir'	=>	'application/x-director',
            'dxr'	=>	'application/x-director',
            'dvi'	=>	'application/x-dvi',
            'gtar'	=>	'application/x-gtar',
            'gz'	=>	'application/x-gzip',
            'gzip'  =>	'application/x-gzip',
            'php'	=>	array('application/x-httpd-php', 'application/php', 'application/x-php', 'text/php', 'text/x-php', 'application/x-httpd-php-source'),
            'php4'	=>	'application/x-httpd-php',
            'php3'	=>	'application/x-httpd-php',
            'phtml'	=>	'application/x-httpd-php',
            'phps'	=>	'application/x-httpd-php-source',
            'js'	=>	array('application/x-javascript', 'text/plain'),
            'swf'	=>	'application/x-shockwave-flash',
            'sit'	=>	'application/x-stuffit',
            'tar'	=>	'application/x-tar',
            'tgz'	=>	array('application/x-tar', 'application/x-gzip-compressed'),
            'z'	=>	'application/x-compress',
            'xhtml'	=>	'application/xhtml+xml',
            'xht'	=>	'application/xhtml+xml',
            'zip'	=>	array('application/x-zip', 'application/zip', 'application/x-zip-compressed', 'application/s-compressed', 'multipart/x-zip'),
            'rar'	=>	array('application/x-rar', 'application/rar', 'application/x-rar-compressed'),
            'mid'	=>	'audio/midi',
            'midi'	=>	'audio/midi',
            'mpga'	=>	'audio/mpeg',
            'mp2'	=>	'audio/mpeg',
            'mp3'	=>	array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
            'aif'	=>	array('audio/x-aiff', 'audio/aiff'),
            'aiff'	=>	array('audio/x-aiff', 'audio/aiff'),
            'aifc'	=>	'audio/x-aiff',
            'ram'	=>	'audio/x-pn-realaudio',
            'rm'	=>	'audio/x-pn-realaudio',
            'rpm'	=>	'audio/x-pn-realaudio-plugin',
            'ra'	=>	'audio/x-realaudio',
            'rv'	=>	'video/vnd.rn-realvideo',
            'wav'	=>	array('audio/x-wav', 'audio/wave', 'audio/wav'),
            'bmp'	=>	array('image/bmp', 'image/x-bmp', 'image/x-bitmap', 'image/x-xbitmap', 'image/x-win-bitmap', 'image/x-windows-bmp', 'image/ms-bmp', 'image/x-ms-bmp', 'application/bmp', 'application/x-bmp', 'application/x-win-bitmap'),
            'gif'	=>	'image/gif',
            'jpeg'	=>	array('image/jpeg', 'image/pjpeg'),
            'jpg'	=>	array('image/jpeg', 'image/pjpeg'),
            'jpe'	=>	array('image/jpeg', 'image/pjpeg'),
            'jp2'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'j2k'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'jpf'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'jpg2'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'jpx'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'jpm'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'mj2'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'mjp2'	=>	array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
            'png'	=>	array('image/png',  'image/x-png'),
            'tiff'	=>	'image/tiff',
            'tif'	=>	'image/tiff',
            'css'	=>	array('text/css', 'text/plain'),
            'html'	=>	array('text/html', 'text/plain'),
            'htm'	=>	array('text/html', 'text/plain'),
            'shtml'	=>	array('text/html', 'text/plain'),
            'txt'	=>	'text/plain',
            'text'	=>	'text/plain',
            'log'	=>	array('text/plain', 'text/x-log'),
            'rtx'	=>	'text/richtext',
            'rtf'	=>	'text/rtf',
            'xml'	=>	array('application/xml', 'text/xml', 'text/plain'),
            'xsl'	=>	array('application/xml', 'text/xsl', 'text/xml'),
            'mpeg'	=>	'video/mpeg',
            'mpg'	=>	'video/mpeg',
            'mpe'	=>	'video/mpeg',
            'qt'	=>	'video/quicktime',
            'mov'	=>	'video/quicktime',
            'avi'	=>	array('video/x-msvideo', 'video/msvideo', 'video/avi', 'application/x-troff-msvideo'),
            'movie'	=>	'video/x-sgi-movie',
            'doc'	=>	array('application/msword', 'application/vnd.ms-office'),
            'docx'	=>	array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/msword', 'application/x-zip'),
            'dot'	=>	array('application/msword', 'application/vnd.ms-office'),
            'dotx'	=>	array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/msword'),
            'xlsx'	=>	array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/vnd.ms-excel', 'application/msword', 'application/x-zip'),
            'word'	=>	array('application/msword', 'application/octet-stream'),
            'xl'	=>	'application/excel',
            'eml'	=>	'message/rfc822',
            'json'  =>	array('application/json', 'text/json'),
            'pem'   =>	array('application/x-x509-user-cert', 'application/x-pem-file', 'application/octet-stream'),
            'p10'   =>	array('application/x-pkcs10', 'application/pkcs10'),
            'p12'   =>	'application/x-pkcs12',
            'p7a'   =>	'application/x-pkcs7-signature',
            'p7c'   =>	array('application/pkcs7-mime', 'application/x-pkcs7-mime'),
            'p7m'   =>	array('application/pkcs7-mime', 'application/x-pkcs7-mime'),
            'p7r'   =>	'application/x-pkcs7-certreqresp',
            'p7s'   =>	'application/pkcs7-signature',
            'crt'   =>	array('application/x-x509-ca-cert', 'application/x-x509-user-cert', 'application/pkix-cert'),
            'crl'   =>	array('application/pkix-crl', 'application/pkcs-crl'),
            'der'   =>	'application/x-x509-ca-cert',
            'kdb'   =>	'application/octet-stream',
            'pgp'   =>	'application/pgp',
            'gpg'   =>	'application/gpg-keys',
            'sst'   =>	'application/octet-stream',
            'csr'   =>	'application/octet-stream',
            'rsa'   =>	'application/x-pkcs7',
            'cer'   =>	array('application/pkix-cert', 'application/x-x509-ca-cert'),
            '3g2'   =>	'video/3gpp2',
            '3gp'   =>	array('video/3gp', 'video/3gpp'),
            'mp4'   =>	'video/mp4',
            'm4a'   =>	'audio/x-m4a',
            'f4v'   =>	array('video/mp4', 'video/x-f4v'),
            'flv'	=>	'video/x-flv',
            'webm'	=>	'video/webm',
            'aac'   =>	array('audio/x-aac', 'audio/aac'),
            'm4u'   =>	'application/vnd.mpegurl',
            'm3u'   =>	'text/plain',
            'xspf'  =>	'application/xspf+xml',
            'vlc'   =>	'application/videolan',
            'wmv'   =>	array('video/x-ms-wmv', 'video/x-ms-asf'),
            'au'    =>	'audio/x-au',
            'ac3'   =>	'audio/ac3',
            'flac'  =>	'audio/x-flac',
            'ogg'   =>	array('audio/ogg', 'video/ogg', 'application/ogg'),
            'kmz'	=>	array('application/vnd.google-earth.kmz', 'application/zip', 'application/x-zip'),
            'kml'	=>	array('application/vnd.google-earth.kml+xml', 'application/xml', 'text/xml'),
            'ics'	=>	'text/calendar',
            'ical'	=>	'text/calendar',
            'zsh'	=>	'text/x-scriptzsh',
            '7z'	=>	array('application/x-7z-compressed', 'application/x-compressed', 'application/x-zip-compressed', 'application/zip', 'multipart/x-zip'),
            '7zip'	=>	array('application/x-7z-compressed', 'application/x-compressed', 'application/x-zip-compressed', 'application/zip', 'multipart/x-zip'),
            'cdr'	=>	array('application/cdr', 'application/coreldraw', 'application/x-cdr', 'application/x-coreldraw', 'image/cdr', 'image/x-cdr', 'zz-application/zz-winassoc-cdr'),
            'wma'	=>	array('audio/x-ms-wma', 'video/x-ms-asf'),
            'jar'	=>	array('application/java-archive', 'application/x-java-application', 'application/x-jar', 'application/x-compressed'),
            'svg'	=>	array('image/svg+xml', 'application/xml', 'text/xml'),
            'vcf'	=>	'text/x-vcard',
            'srt'	=>	array('text/srt', 'text/plain'),
            'vtt'	=>	array('text/vtt', 'text/plain'),
            'ico'	=>	array('image/x-icon', 'image/x-ico', 'image/vnd.microsoft.icon'),
            'odc'	=>	'application/vnd.oasis.opendocument.chart',
            'otc'	=>	'application/vnd.oasis.opendocument.chart-template',
            'odf'	=>	'application/vnd.oasis.opendocument.formula',
            'otf'	=>	'application/vnd.oasis.opendocument.formula-template',
            'odg'	=>	'application/vnd.oasis.opendocument.graphics',
            'otg'	=>	'application/vnd.oasis.opendocument.graphics-template',
            'odi'	=>	'application/vnd.oasis.opendocument.image',
            'oti'	=>	'application/vnd.oasis.opendocument.image-template',
            'odp'	=>	'application/vnd.oasis.opendocument.presentation',
            'otp'	=>	'application/vnd.oasis.opendocument.presentation-template',
            'ods'	=>	'application/vnd.oasis.opendocument.spreadsheet',
            'ots'	=>	'application/vnd.oasis.opendocument.spreadsheet-template',
            'odt'	=>	'application/vnd.oasis.opendocument.text',
            'odm'	=>	'application/vnd.oasis.opendocument.text-master',
            'ott'	=>	'application/vnd.oasis.opendocument.text-template',
            'oth'	=>	'application/vnd.oasis.opendocument.text-web'
        );
    }

    /**
     * 数据库字段生成配置
     * @param  GenerateColumnOption $option
     * @return void
     */
    public function getGenerateColumnConfig(GenerateColumnOption $option):void{
        $option->setTypeText();
    }


    /**
     * 验证是否为本地文件，不验证文件是否存在
     * @param $files
     * @param string $separator
     * @return bool
     */
    public static function checkFilesLocal($files,string $separator='|'):bool{
        if(!is_array($files)){
            $files=explode($separator,$files);
        }
        $files=array_filter($files);
        if(empty($files)){
            return true;
        }

        foreach ($files as $v){
            $v=str_replace('\\','',$v);
            if(mb_strpos($v,'..')!==false){
                return false;
            }
            if(mb_strpos($v, '/') !== 0&&mb_strpos($v, request()->domain()) !== 0){
                return false;
            }
        }
        return true;
    }


    /**
     * 是否要验证上传的文件为本地文件
     * @param bool|null $checkFilesIsLocal
     * @return $this|bool
     */
    public function checkFilesIsLocal(bool $checkFilesIsLocal=null){
        if(is_null($checkFilesIsLocal)){
            return $this->checkFilesIsLocal;
        }
        $this->checkFilesIsLocal=$checkFilesIsLocal;
        return $this;
    }
}