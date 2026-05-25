<?php

namespace Modules\Complaint\Repositories;


class ComplaintStatusLogRepository implements ComplaintStatusLogRepositoryInterface
{
public function all()
{
return ComplaintStatusLog::with(['complaint', 'user'])->get();
}

public function find($id)
{
return ComplaintStatusLog::with(['complaint', 'user'])->findOrFail($id);
}

public function create(array $data)
{
return ComplaintStatusLog::create($data);
}

public function delete($id)
{
$statusLog = $this->find($id);
$statusLog->delete();
return $statusLog;
}
}
