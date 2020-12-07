<?php

/*
 * @Author       : IFantace
 * @Date         : 2020-11-30 17:46:45
 * @LastEditors  : IFantace
 * @LastEditTime : 2020-12-07 17:21:59
 * @Description  : 針對單一資料的操作
 */

namespace Ifantace\LaravelCommon\Objects;

use Ifantace\LaravelCommon\Traits\CommonTraits;
use Illuminate\Support\Facades\Auth;

abstract class CommonObject
{
    use CommonTraits;

    /**
     * 用來操作的repository
     *
     * @var \Ifantace\LaravelCommon\Repositories
     */
    protected $repository;

    /**
     * 當前使用者
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    protected $operator;

    /**
     * 所有欄位
     *
     * @var array
     */
    protected $all_column;

    /**
     * 新增可填入的欄位
     *
     * @var array
     */
    protected $fillable_column;

    /**
     * 可被更新的欄位
     *
     * @var array
     */
    protected $updatable_column;

    /**
     * 可人為設定的欄位
     *
     * @var array
     */
    protected $settable_column;

    /**
     * 初始化，如果有primary key，則搜尋並設定data
     *
     * @param string $primary_key
     */
    public function __construct($primary_key = null)
    {
        $this->initColumn();
        $this->setCreator();
        if ($primary_key !== null) {
            // 載入資料
            $this->setDataByPrimary($primary_key);
        } else {
            // 初始化系統資料
            $this->initSystemData();
        }
    }

    /**
     * 設定操作者
     *
     * @return void
     */
    protected function setCreator()
    {
        $this->operator = Auth::user();
    }

    /**
     * 透過primary key取得並設定此物件
     *
     * @param string $primary_key 主鍵
     * @return bool
     */
    public function setDataByPrimary($primary_key)
    {
        $data = $this->findDataByPrimary($primary_key);
        if ($data !== null) {
            $this->setData($data, $this->all_column);
            return true;
        }
        return false;
    }

    /**
     * 設定此物件資料，可透過columns過濾不必要的資料
     *
     * @param array|object $data 來源資料
     * @param array $columns
     * @return static
     */
    public function setData($data, $columns = null)
    {
        $this_column = ($columns === null ? $this->settable_column : $columns);
        foreach ($this_column as $each_column) {
            if (is_array($data)) {
                if (array_key_exists($each_column, $data)) {
                    $this->$each_column = $data[$each_column];
                }
            } else {
                $this->$each_column = $data->$each_column;
            }
        }
        return $this;
    }

    /**
     * 透過指定的欄位取得資料array
     *
     * @param array $columns
     * @return array
     */
    public function filterByColumn($columns)
    {
        $return_array = [];
        foreach ($columns as $each_column) {
            $return_array[$each_column] = isset($this->$each_column) ? $this->$each_column : null;
        }
        return $return_array;
    }

    /**
     * 透過可全部的欄位過濾資料
     *
     * @return array
     */
    public function filterColumnByAllColumn()
    {
        return $this->filterByColumn($this->all_column);
    }

    /**
     * 透過可新增的欄位過濾資料
     *
     * @return array
     */
    public function filterColumnByFillableColumn()
    {
        return $this->filterByColumn($this->fillable_column);
    }

    /**
     * 透過可設定的欄位過濾資料
     *
     * @return array
     */
    public function filterColumnBySettableColumn()
    {
        return $this->filterByColumn($this->settable_column);
    }

    /**
     * 透過設定的可更新的欄位過濾資料
     *
     * @return array
     */
    public function filterColumnByUpdatableColumn()
    {
        return $this->filterByColumn($this->updatable_column);
    }

    /**
     * 取得指定的資料欄位array
     *
     * @param string $column_name
     * @return array
     */
    public function getColumn($column_name = "all_column")
    {
        return $this->$column_name;
    }

    /**
     * 確認是否重複
     *
     * @return bool
     *
     * @author IFantace <aa431125@gmail.com>
     */
    public function isDuplicate()
    {
        return $this->findDuplicate() !== null;
    }

    /**
     * 初始化Object欄位all_column, fillable_column, updatable_column, settable_column
     *
     * @return bool
     */
    abstract public function initColumn();

    /**
     * 初始化由系統指定的欄位
     *
     * @return bool
     */
    abstract public function initSystemData();

    /**
     * 建立資料
     *
     * @return bool
     */
    abstract public function create();

    /**
     * 更新資料
     *
     * @return bool
     */
    abstract public function update();

    /**
     * 刪除資料
     *
     * @return bool
     */
    abstract public function delete();

    /**
     * 搜尋重複unique欄位的資料
     *
     * @return bool
     */
    abstract public function findDuplicate();

    /**
     * 搜尋指定的Primary的資料
     *
     * @param mixed $primary_key
     * @return \Illuminate\Database\Eloquent\Model｜null
     */
    abstract public function findDataByPrimary($primary_key);
}
