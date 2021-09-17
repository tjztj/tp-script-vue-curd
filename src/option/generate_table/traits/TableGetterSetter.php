<?php

namespace tpScriptVueCurd\option\generate_table\traits;

trait TableGetterSetter
{

    /**
     * @return string
     */
    public function getEngine(): string
    {
        return $this->engine;
    }

    /**
     * @param string $engine
     */
    public function setEngine(string $engine): void
    {
        $this->engine = $engine;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }
    /**
     * @return bool
     */
    public function isModifyColumn(): bool
    {
        return $this->modifyColumn;
    }

    /**
     * @param bool $modifyColumn
     */
    public function setModifyColumn(bool $modifyColumn): void
    {
        $this->modifyColumn = $modifyColumn;
    }
}