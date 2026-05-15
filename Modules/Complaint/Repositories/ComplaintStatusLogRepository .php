<?php

namespace Modules\Complaint\Repositories;

use Modules\Complaint\Models\Complaint_status_log;

class ComplaintStatusLogRepository implements ComplaintStatusLogRepositoryInterface
{
public function all()
{
return Complaint_status_log::with(['complaint', 'user'])->get();
}

public function find($id)
{
return Complaint_status_log::with(['complaint', 'user'])->findOrFail($id);
}

public function create(array $data)
{
return Complaint_status_log::create($data);
}

public function delete($id)
{
$statusLog = $this->find($id);
$statusLog->delete();
return $statusLog;
}
}
