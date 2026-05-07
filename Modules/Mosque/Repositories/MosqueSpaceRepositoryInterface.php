<?php

namespace Modules\Mosque\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Mosque\Models\MosqueSpace;

interface MosqueSpaceRepositoryInterface
{
    /**
     * إنشاء مساحة جديدة
     */
    public function create(array $data): MosqueSpace;

    /**
     * تحديث بيانات مساحة
     */
    public function update(MosqueSpace $space, array $data): MosqueSpace;

    /**
     * حذف مساحة
     */
    public function delete(MosqueSpace $space): void;

    /**
     * جلب جميع المساحات التابعة لمسجد معين
     */
    public function getByMosque(int $mosqueId): Collection;
}
