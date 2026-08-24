<?php

namespace Modules\Common\Recommendations;

use Illuminate\Contracts\Foundation\Application;
use Modules\Common\Recommendations\DTO\RecommendationDTO;

class RecommendationEngineService
{
    public function __construct(
        private StatsCollectorService $collector,
        private Application $app,
    ) {}

    /**
     * @return RecommendationDTO[]
     */
    public function generate(int $mosqueId, ?string $category = null): array
    {
        $stats = $this->collector->collect($mosqueId);

        $recommendations = [];

        foreach (config('recommendations.rules', []) as $ruleClass) {
            if (! is_string($ruleClass) || ! class_exists($ruleClass)) {
                continue;
            }

            /** @var RecommendationRule $rule */
            $rule = $this->app->make($ruleClass);

            $dto = $rule->evaluate($stats);

            if ($dto instanceof RecommendationDTO) {
                $recommendations[] = $dto;
            }
        }

        if ($category !== null) {
            $recommendations = array_values(array_filter(
                $recommendations,
                fn(RecommendationDTO $dto) => $dto->category === $category
            ));
        }

        return $recommendations;
    }
}
