<?php

namespace Modules\Community\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Community\Models\Sermon;
use Modules\Community\Models\SermonSelection;
use Modules\User\Models\User;

class SermonSelectedForFriday
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Sermon $sermon,               // original sermon (has mosqueManager relation)
        public SermonSelection $selection,
        public User $deliveringMosqueManager,
    ) {}
}
