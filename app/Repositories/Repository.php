<?php

namespace Corp\Repositories;

use Config;

abstract class Repository  {

    protected $model = FALSE;

    public function get($select = '*',$take = FALSE,$pagination = FALSE) {
        $builder = $this->model->select($select);
        if ($take !== FALSE) {
            $builder->take($take);
        }

        if ($pagination) {
            return $this->check($builder->paginate(Config::get('settings.pagination')));
        }

        return $this->check($builder->get());
    }

    protected function check($result) {
        if ($result->isEmpty()) {
            return FALSE;
        }
        $result->transform(function ($item){
            if (is_string($item->img) && is_object(json_decode($item->img)) && json_last_error() == JSON_ERROR_NONE) {
                $item->img = json_decode($item->img);
            }

            return $item;
        });
        return $result;
    }

}