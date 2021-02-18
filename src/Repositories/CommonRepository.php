<?php

/*
 * @Author       : IFantace
 * @Date         : 2020-11-30 17:46:45
 * @LastEditors  : IFantace
 * @LastEditTime : 2021-02-18 20:01:43
 * @Description  : 資料庫邏輯部分
 */

namespace Ifantace\LaravelCommon\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use PDO;

abstract class CommonRepository
{
    /**
     * this repository model
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * model connection
     *
     * @var string
     */
    protected $connection_name;

    /**
     * model table
     *
     * @var string
     */
    protected $table_name;

    /**
     * table columns
     *
     * @var array
     */
    protected $columns;

    public function __construct(Model $model)
    {
        $this->setModel($model);
    }

    public function __call($fun, $args)
    {
        return call_user_func_array(array($this->model, $fun), $args);
    }

    public function __set($key, $value)
    {
        $this->model->$key = $value;
    }

    /**
     * init repository
     *
     * @return void
     */
    protected function init()
    {
        $this->connection_name = $this->model->getConnectionName();
        $this->table_name = $this->model->getTable();
        $this->columns = Schema::connection($this->connection_name)->getColumnListing($this->table_name);
    }

    /**
     * set model
     *
     * @param Model $model model
     *
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
        $this->init();
        return $this;
    }

    /**
     * Return whereRaw content, which equal whereIn.
     *
     * @param string $column_name Column name.
     * @param array $data_array Search array.
     *
     * @return string String of whereIn in whereRaw
     *
     * @author IFantace <aa431125@gmail.com>
     */
    public function createWhereInRaw($column_name, array $data_array)
    {
        if (count($data_array) == 0) {
            return '1 = 0';
        }
        return $column_name . ' In (\'' . join('\',\'', $data_array) . '\')';
    }

    /**
     * search table
     *
     * @param string $query_string
     * @param array $columns_not_search just array eq.['A','B','C']
     * @param array $columns_change_search two-dimensional array
     * eq. ['A'=> ['1' => '正常','0' => '關閉中','-1' => '申請中','-2' => '拒絕申請',]]
     * @param array $special_column two-dimensional array
     * eq. ['A'=> ['1', '0', '-1', '-2']]
     *
     * @return $this
     */
    public function searchAllColumn(
        $query_string,
        array $columns_not_search = array(),
        array $columns_change_search = array(),
        array $columns_whereIn = array()
    ) {
        // 添加轉換搜尋的key到不要搜尋的column
        $columns_not_search = array_merge($columns_not_search, array_keys($columns_change_search));
        // 去除不要搜尋的欄位
        $columns = array_values(array_diff($this->columns, $columns_not_search));
        // 去除系統時間
        $columns = array_values(array_diff($columns, ['created_at', 'updated_at', 'deleted_at']));
        $this->model = $this->model->where(
            function ($query_all_column) use ($columns, $query_string, $columns_change_search, $columns_whereIn) {
                // 搜尋要搜尋的欄位
                foreach ($columns as $each_column) {
                    $query_all_column->orWhere($each_column, 'like', '%' . $query_string . '%');
                }
                // 搜尋轉換的欄位，例如'A'=> ['1' => '正常','0' => '關閉中','-1' => '申請中','-2' => '拒絕申請']，query_string = 正，對到1，用1去搜尋A欄位
                foreach ($columns_change_search as $search_column_name => $change_key_array) {
                    foreach ($change_key_array as $inside_value => $outer_value) {
                        if (strpos($outer_value, $query_string) !== false) {
                            $query_all_column->orWhere($search_column_name, 'like', '%' . $inside_value . '%');
                        }
                    }
                }
                // 用whereIn搜尋指定的欄位
                foreach ($columns_whereIn as $column_name => $value_array) {
                    $query_all_column->orWhereRaw($this->createWhereInRaw($column_name, $value_array));
                }
            }
        );
        return $this;
    }

    /**
     * 轉換傳上來的table參數
     *
     * @param array $table_config
     *
     * @return void
     *
     * @author IFantace <aa431125@gmail.com>
     */
    private function convertTableConfig(array &$table_config)
    {
        if (isset($table_config['sort']) && !isset($table_config['orderBy'])) {
            // sort 轉成 orderBy
            $table_config['orderBy'] = $table_config['sort'];
        }
        if (isset($table_config['perPage']) && !isset($table_config['limit'])) {
            // perPage 轉乘 limit
            $table_config['limit'] = $table_config['perPage'];
        }
        if (isset($table_config['select'])) {
            $table_config['select'] = is_array($table_config['select'])
                ? $table_config['select']
                : [$table_config['select']];
        }
        if (isset($table_config['with'])) {
            $table_config['with'] = is_array($table_config['with'])
                ? $table_config['with']
                : [$table_config['with']];
        }
        if (isset($table_config['withCount'])) {
            $table_config['withCount'] = is_array($table_config['withCount'])
                ? $table_config['withCount']
                : [$table_config['withCount']];
        }
        if (isset($table_config['orderBy'])) {
            if (
                strpos($table_config['orderBy'], '|') !== false
                && !isset($table_config['ascending'])
            ) {
                $tmp_explode = explode('|', $table_config['orderBy']);
                if (count($tmp_explode) > 1) {
                    $table_config['orderBy'] = $tmp_explode[0];
                    $table_config['ascending'] = $tmp_explode[1];
                }
            }
        }
        if (isset($table_config['ascending'])) {
            if (
                strtolower($table_config['ascending'])  === "ascending"
                || strtolower($table_config['ascending']) === "asc"
            ) {
                $table_config['ascending'] = 1;
            } elseif (
                strtolower($table_config['ascending']) === "descending"
                || strtolower($table_config['ascending']) === "desc"
            ) {
                $table_config['ascending'] = 0;
            }
        }
    }

    /**
     * apply table config of sort, select, relation
     *
     * @param array $table_config config of table
     * orderBy: 'column name',
     * ascending: int => 1:ASC, 0:DESC,
     * select: array => column need to select,
     * with: array => search relation,
     * withCount: array => count relation
     *
     * @return void
     *
     * @author IFantace <aa431125@gmail.com>
     */
    private function applyTableConfig(array &$table_config)
    {
        $this->convertTableConfig($table_config);
        // 排序
        if (isset($table_config['orderBy']) && isset($table_config['ascending'])) {
            $this->model = $this->model->orderBy(
                $table_config['orderBy'],
                $table_config['ascending'] == 1
                    ? 'ASC'
                    : 'DESC'
            );
        }
        // 關聯
        if (isset($table_config['with'])) {
            $this->model = $this->model->with($table_config['with']);
            if (isset($table_config['select'])) {
                $table_config['select'] = array_merge($table_config['select'], $table_config['with']);
            }
        }
        // 關聯數量
        if (isset($table_config['withCount'])) {
            $this->model = $this->model->withCount($table_config['withCount']);
            if (isset($table_config['select'])) {
                $table_config['select'] = array_merge($table_config['select'], $table_config['withCount']);
            }
        }
    }

    /**
     * get data with table format
     *
     * @param array $table_config config of table
     * page: int => pagination,
     * limit: int => count of each pagination and this time take,
     *
     * @return array
     */
    public function getTable(array $table_config)
    {
        // 目前總數
        $count = $this->model->count();
        // 套用排序功能
        $this->applyTableConfig($table_config);
        // 搜尋指定的跳頁
        if (isset($table_config['page']) && isset($table_config['limit'])) {
            $this->model = $this->model->skip(($table_config['page'] - 1) * $table_config['limit']);
        }
        // 搜尋指定的數量
        if (isset($table_config['limit'])) {
            $limit = $table_config['limit'];
            $this->model = $this->model->take($limit);
        }
        // 過濾要的欄位
        if (isset($table_config['select'])) {
            $this->model = $this->model->select($table_config['select']);
        }
        // 取得資料
        $data = $this->model->get();
        return ['count' => $count, 'data' => $data];
    }

    /**
     * 用laravel內建的paginate產生table格式查詢
     *
     * @param array $table_config
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     *
     * @author IFantace <aa431125@gmail.com>
     */
    public function getTableByPaginate(array $table_config)
    {
        $this->applyTableConfig($table_config);
        return $this->model->paginate(
            isset($table_config['limit'])
                ? $table_config['limit']
                : null,
            isset($table_config['select']) ? $table_config['select'] : ['*'],
            'page',
            isset($table_config['page'])
                ? $table_config['page']
                : null,
        );
    }
}
