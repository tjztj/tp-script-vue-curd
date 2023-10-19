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
 * 图片
 * Class ImagesField.
 *
 * @author tj 1079798840@qq.com
 */
class ImagesField extends ModelField
{
    protected string $url = ''; // 默认值是 tpScriptVueCurdUploadDefaultUrl
    protected bool $multiple = true; // 是否可多选

    /**
     * @var callable
     */
    protected $imgFieldShowUrlDo;

    protected bool $checkFilesIsLocal = true; // 验证上传的文件是否为本地文件

    protected bool $canExport = false; // 不能导出此字段数据
    protected array $listShowImg = [
        'maxWidth' => '72px', // 最大宽度
        'height' => '64px', // 最大高度、
        'show' => false,
    ]; // 是否在列表中已图片格式显示

    protected string $accept = 'image/*'; // 上传文件类型

    /**最小值
     * @param string|null $url
     * @return $this|string
     */
    public function url(string $url = null)
    {
        if (is_null($url)) {
            return $this->url ?: tpScriptVueCurdUploadDefaultUrl();
        }
        $this->url = $url;
        $this->fieldPushAttrByWhere('url', $this->url);

        return $this;
    }

    /**
     * 文件上传类型.
     *
     * @return $this|string
     */
    public function accept(string $accept = null)
    {
        if (is_null($accept)) {
            return $this->accept;
        }
        $this->accept = str_replace(' ', '', $accept);
        $this->fieldPushAttrByWhere('accept', $this->accept);

        return $this;
    }

    /**
     * 是否可多选.
     *
     * @return $this|bool
     */
    public function multiple(bool $multiple = null)
    {
        return $this->doAttr('multiple', $multiple);
    }

    /**
     * 是否在列表中已图片格式显示.
     *
     * @param bool|array|null $listShowImg
     *
     * @return $this|array
     *
     * @throws \think\Exception
     */
    public function listShowImg($listShowImg = null)
    {
        if (is_null($listShowImg)) {
            return $this->listShowImg;
        }
        if (is_bool($listShowImg)) {
            $this->listShowImg['show'] = $listShowImg;
        } elseif (is_array($listShowImg)) {
            // 性能问题，不用array_merge
            foreach ($listShowImg as $k => $v) {
                isset($this->listShowImg[$k]) && $this->listShowImg[$k] = $v;
            }
        } else {
            throw new \think\Exception($this->name().'字段的listShowImg参数不正确');
        }
        $this->fieldPushAttrByWhere('listShowImg', $this->listShowImg);

        return $this;
    }

    /**
     * 设置保存的值
     *
     * @param array $data 数据值集合
     *
     * @return $this
     */
    public function setSaveVal(array $data, BaseModel $old): self
    {
        if (isset($data[$this->name()])) {
            $this->save = $data[$this->name()];
            if ($this->save) {
                if ($this->checkFilesIsLocal() && !FilesField::checkFilesLocal($this->save)) {
                    throw new \think\Exception('图片路径非法');
                }
                $accepts = explode(',', $this->accept);

                $urls = $this->multiple() ? explode('|', $this->save) : [$this->save];
                foreach ($urls as $k => $v) {
                    $have = false;
                    foreach ($accepts as $val) {
                        if (FilesField::checkUrlIsMimeOrExt($v, $val)) {
                            $have = true;
                            break;
                        }
                    }
                    if (!$have) {
                        throw new \think\Exception((count($urls) > 1 ? ('第'.($k + 1).'个') : '').'文件不符合要求');
                    }
                }
            }
        }
        $this->defaultCheckRequired($this->save, '请上传图片');

        return $this;
    }

    /**
     * 图片显示前，对地址处理.
     *
     * @return $this
     */
    public function setImgFieldShowUrlDo(callable $imgFieldShowUrlDo): self
    {
        $this->imgFieldShowUrlDo = $imgFieldShowUrlDo;

        return $this;
    }

    /**
     * 显示时对数据处理.
     */
    public function doShowData(array &$dataBaseData): void
    {
        parent::doShowData($dataBaseData);
        if (isset($dataBaseData[$this->name()])) {
            $dataBaseData[$this->name()] = imgFieldShowUrlDo(trim($dataBaseData[$this->name()]), $this);

            $imgFieldShowUrlDo = $this->imgFieldShowUrlDo;
            if (null !== $imgFieldShowUrlDo) {
                $dataBaseData[$this->name()] = $imgFieldShowUrlDo($dataBaseData[$this->name()], $dataBaseData);
            }

            $dataBaseData[$this->name().'Arr'] = $dataBaseData[$this->name()] ? explode('|', $dataBaseData[$this->name()]) : [];
        }
    }

    /**
     * 模板导入备注.
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl): void
    {
        $excelFieldTpl->explain = "插入相关图片，可插入多张；\n请将插入的图片缩小到单元格内；\n竖向合并单元格的行将共用图片；\n Excel中请设置不压缩文件中的图像\n或取消保存时自动执行基本压缩；";
        $excelFieldTpl->wrapText = true;
        $excelFieldTpl->width = 42;
    }

    /**
     * EXCEL导入时，对数据的处理.
     */
    public function excelSaveDoData(array &$save): void
    {
        $name = $this->name();
        if (!isset($save[$name])) {
            return;
        }
        if (empty($save[$name])) {
            $save[$name] = [];

            return;
        }
        //        $save[$name]=empty($save[$name])?'':implode('|',array_map(static fn($vo)=>str_replace([public_path(),DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR], DIRECTORY_SEPARATOR, $vo),is_array($save[$name])?$save[$name]:explode('|',$save[$name])));
        $save[$name] = empty($save[$name]) ? '' : implode('|', array_map(static fn ($vo) => str_replace([
            str_replace(DIRECTORY_SEPARATOR, '/', public_path()),
            str_replace('/', DIRECTORY_SEPARATOR, public_path()),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,
        ], '/', $vo), is_array($save[$name]) ? $save[$name] : explode('|', $save[$name])));
    }

    public static function componentUrl(): FieldTpl
    {
        $type = class_basename(static::class);

        return new FieldTpl(
            $type,
            new Index($type, '/tpscriptvuecurd/field/images/index.js'),
            new Show($type, '/tpscriptvuecurd/field/images/show.js'),
            new Edit($type, '/tpscriptvuecurd/field/images/edit.js')
        );
    }

    /**
     * 数据库字段生成配置.
     */
    public function getGenerateColumnConfig(GenerateColumnOption $option): void
    {
        $option->setTypeText();
    }

    /**
     * 是否要验证上传的文件为本地文件.
     *
     * @return $this|bool
     */
    public function checkFilesIsLocal(bool $checkFilesIsLocal = null)
    {
        if (is_null($checkFilesIsLocal)) {
            return $this->checkFilesIsLocal;
        }
        $this->checkFilesIsLocal = $checkFilesIsLocal;

        return $this;
    }
}
