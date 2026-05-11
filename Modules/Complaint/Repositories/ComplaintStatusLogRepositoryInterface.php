<?php

namespace Modules\Complaint\Repositories;


interface ComplaintStatusLogRepositoryInterface
{
    public function all();
    public function find($id);
    public function create(array $data);
    public function delete($id);
}
