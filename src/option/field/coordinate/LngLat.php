<?php
namespace tpScriptVueCurd\option\field\coordinate;
class LngLat
{
    public string $lng='';//经度
    public string $lat='';//纬度

    /**
     * @param string $lng 经度
     * @param string $lat 纬度
     */
    public function __construct(string $lng,string $lat='')
    {
        if($lat===''){
            [$this->lng,$this->lat]=explode(',',$lng);
        }else{
            $this->lng=$lng;
            $this->lat=$lat;
        }
    }

    public function toArray():array{
        return ['lng'=>$this->lng,'lat'=>$this->lat];
    }
}