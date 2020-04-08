<?php

namespace App\Repositories;

use App\Group;
use Illuminate\Support\Facades\Log;

class GroupRepository
{
    private $model;

    public function __construct(Group $model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model->all();
    }

    public function insert($id)
    {
        $group = $this->model
        ->where("group_id", $id)
        ->first();

        if ($group != null)
            return $group;

        return $this->model->create([
            "group_id" => $id
        ]);
    }

    public function delete($id)
    {
        $this->model
        ->where("group_id", $id)
        ->delete();
    }
}
