<?php

namespace tpScriptVueCurd\option\generate_table\traits;

trait ColumnGetterSetter
{
    /**
     * 获取字段名
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 获取字段类型
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * 获取字段长度
     * @return int|null
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    /**
     * 设置字段长度
     * @param int|null $length
     */
    public function setLength(?int $length): void
    {
        $this->length = $length;
    }

    /**
     * 获取默认值
     * @return string|null
     */
    public function getDefault(): ?string
    {
        return $this->default??null;
    }

    /**
     * 设置默认值
     * @param string|null $default
     */
    public function setDefault(?string $default): void
    {
        $this->default = $default;
    }

    /**
     * 获取备注说明
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * 设置备注说明
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }


    /**
     * 是否要设置编码
     * @param bool $chart
     */
    public function setChart(bool $chart):void{
        $this->chart=$chart?'CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci':'';
    }

    public function getChart():string{
        return $this->chart;
    }
}