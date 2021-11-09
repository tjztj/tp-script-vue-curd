<?php

namespace tpScriptVueCurd\option\generate_table\traits;

use tpScriptVueCurd\option\generate_table\GenerateColumnOption;

trait ColumnSetTypes
{


    /**
     * 设置字段类型为 int ,长度如果传入0，代表不改变长度，如果传入数字，代表改变字段长度为传入值
     * @param int $length
     * @param int|null $default
     * @return $this
     */
    public function setTypeInt(int $length = 11, ?int $default = 0): self
    {
        $this->type = 'int';
        if ($length) {
            $this->length = $length;
        }
        if (!is_null($default)) {
            $this->default = $default;
        }
        $this->setChart(false);
        return $this;
    }

    /**
     * 设置字段类型为 bigint ,长度如果传入0，代表不改变长度，如果传入数字，代表改变字段长度为传入值
     * @param int $length
     * @param int|null $default
     * @return $this
     */
    public function setTypeBigInt(int $length = 20, ?int $default = 0): self
    {
        $this->type = 'bigint';
        if ($length) {
            $this->length = $length;
        }
        if (!is_null($default)) {
            $this->default = $default;
        }
        $this->setChart(false);
        return $this;
    }

    /**
     * 设置字段类型为 varchar ,长度如果传入0，代表不改变长度，如果传入数字，代表改变字段长度为传入值
     * @param int $length
     * @param string|null $default
     * @return $this
     */
    public function setTypeVarchar(int $length = 255, ?string $default = ''): self
    {
        $this->type = 'varchar';
        if ($length) {
            $this->length = $length;
        }
        if (!is_null($default)) {
            $this->default = $default;
        }
        $this->setChart(true);
        return $this;
    }

    /**
     * 设置字段类型为 decimal ,长度如果传入0，代表不改变长度，如果传入数字，代表改变字段长度为传入值
     * @param int $length
     * @param int $precision
     * @param int|null $default
     * @return $this
     */
    public function setTypeDecimal(int $length = 11,int $precision=2, ?int $default = 0): self
    {
        $this->type = 'decimal';
        if ($length) {
            $this->length = $length;
            $this->precision=$precision;
        }
        if (!is_null($default)) {
            $this->default = $default;
        }
        $this->setChart(false);
        return $this;
    }


    /**
     * 设置字段类型为 float ,长度如果传入0，代表不改变长度，如果传入数字，代表改变字段长度为传入值
     * @param int $length
     * @param int $precision
     * @param int|null $default
     * @return $this
     */
    public function setTypeFloat(int $length = 11,int $precision=2, ?int $default = 0): self
    {
        $this->type = 'float';
        if ($length) {
            $this->length = $length;
            $this->precision=$precision;
        }
        if (!is_null($default)) {
            $this->default = $default;
        }
        $this->setChart(false);
        return $this;
    }


    /**
     * 设置字段类型为 text
     * @return $this
     */
    public function setTypeText(): self
    {
        $this->type = 'text';
        $this->length = null;
        $this->setChart(true);
        return $this;
    }

    /**
     * 设置字段类型为 longtext
     * @return $this
     */
    public function setTypeLongText(): self
    {
        $this->type = 'longtext';
        $this->length = null;
        $this->setChart(true);
        return $this;
    }

    /**
     * 设置字段类型为 json
     * @return $this
     */
    public function setTypeJson(): self
    {
        $this->type = 'json';
        $this->length = null;
        $this->setChart(false);
        return $this;
    }
}
