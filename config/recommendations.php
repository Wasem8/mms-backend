<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Recommendation Rules
    |--------------------------------------------------------------------------
    |
    | Ordered list of rule classes evaluated by the RecommendationEngineService.
    | Add a new rule here (and implement RecommendationRule) — no need to touch
    | the engine or any existing rule.
    |
    */

    'rules' => [
        \Modules\Common\Recommendations\Rules\ComplaintResolutionRule::class,
        \Modules\Common\Recommendations\Rules\MaintenanceBacklogRule::class,
        \Modules\Common\Recommendations\Rules\VolunteerLowCompletionRule::class,
        \Modules\Common\Recommendations\Rules\CampaignNearGoalRule::class,
    ],

];
