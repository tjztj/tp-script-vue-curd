<?php

namespace tpScriptVueCurd\option\generate_table\traits;

trait TableGetterSetter
{

    /**
     * 获取表引擎
     * @return string
     */
    public function getEngine(): string
    {
        return $this->engine;
    }

    /**
     * 设置表引擎 InnoDB|MyISAM
     * @param string $engine
     */
    public function setEngine(string $engine): void
    {
        $this->engine = $engine;
    }

    /**
     * 获取表的备注与说明
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * 设置表的备注与说明
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }
    /**
     * 获取表时候可以修改列（如果为false，表示只会新增列）
     * @return bool
     */
    public function isModifyColumn(): bool
    {
        return $this->modifyColumn;
    }

    /**
     * 设置表时候可以修改列（如果为false，表示只会新增列；如果为true，当模型配置的字段有所更改时，会更改表中字段相关信息）
     * @param bool $modifyColumn
     */
    public function setModifyColumn(bool $modifyColumn): void
    {
        $this->modifyColumn = $modifyColumn;
    }
}