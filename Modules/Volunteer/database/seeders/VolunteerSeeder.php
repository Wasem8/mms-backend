<?php

namespace Modules\Volunteer\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Mosque\Models\Mosque;
use Modules\User\Models\User;
use Modules\Volunteer\Enums\ApplicationStatus;
use Modules\Volunteer\Enums\OpportunityStatus;
use Modules\Volunteer\Enums\TaskStatus;
use Modules\Volunteer\Models\VolunteerApplication;
use Modules\Volunteer\Models\VolunteerCertificate;
use Modules\Volunteer\Models\VolunteerOpportunity;
use Modules\Volunteer\Models\VolunteerTask;

class VolunteerSeeder extends Seeder
{
    private const OPPORTUNITIES_PER_MOSQUE = 3;

    /**
     * Number of dedicated volunteer users to create.
     */
    private const VOLUNTEER_USERS_COUNT = 15;

    private const TITLES = [
        'تنظيف وترتيب المسجد',
        'استقبال المصلين في صلاة التراويح',
        'حملة توزيع إفطار صائم',
        'تنظيم الحفل السنوي للمسجد',
        'صيانة وتجهيز قاعة الدروس',
        'الإشراف على حلقات تحفيظ القرآن',
    ];

    public function run(): void
    {
        $mosques = Mosque::all();

        if ($mosques->isEmpty()) {
            $this->command?->warn('No mosques found — skipping VolunteerDatabaseSeeder. Seed the Mosque module first.');

            return;
        }

        $volunteers = $this->seedVolunteerUsers();

        foreach ($mosques as $mosque) {
            for ($i = 0; $i < self::OPPORTUNITIES_PER_MOSQUE; $i++) {
                $opportunity = $this->createOpportunity($mosque);
                $this->seedApplicationsForOpportunity($opportunity, $volunteers);
            }
        }
    }

    /**
     * Create dedicated volunteer users and assign them the `volunteer` role.
     *
     * @return Collection<int, User>
     */
    private function seedVolunteerUsers(): Collection
    {
        $volunteers = collect();

        for ($i = 0; $i < self::VOLUNTEER_USERS_COUNT; $i++) {
            $user = User::factory()->create([
                'name' => fake()->name(),
                'phone' => fake()->unique()->numerify('09########'),
            ]);

            if (method_exists($user, 'assignRole') && ! $user->hasRole('volunteer')) {
                $user->assignRole('volunteer');
            }

            $volunteers->push($user);
        }

        return $volunteers;
    }

    private function createOpportunity(Mosque $mosque): VolunteerOpportunity
    {
        $status = fake()->randomElement(OpportunityStatus::cases());

        // Closed opportunities sit fully in the past for consistent seed data;
        // open ones can start recently or up to a month out.
        $startDate = $status === OpportunityStatus::Closed
            ? fake()->dateTimeBetween('-3 months', '-1 month')
            : fake()->dateTimeBetween('-2 weeks', '+1 month');

        $endDate = (clone $startDate)->modify('+' . rand(3, 21) . ' days');

        return VolunteerOpportunity::create([
            'mosque_id' => $mosque->id,
            'title' => fake()->randomElement(self::TITLES),
            'description' => fake()->realText(180),
            'required_volunteers' => rand(2, 8),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
        ]);
    }

    /**
     * Create applications (and their downstream tasks/certificates) for a single opportunity.
     *
     * @param  Collection<int, User>  $volunteers
     */
    private function seedApplicationsForOpportunity(VolunteerOpportunity $opportunity, Collection $volunteers): void
    {
        $applicantCount = min($opportunity->required_volunteers + rand(1, 2), $volunteers->count());
        $applicants = $volunteers->random($applicantCount);
        $applicants = $applicants instanceof Collection ? $applicants : collect([$applicants]);

        $approvedSoFar = 0;

        foreach ($applicants as $volunteer) {
            $status = $approvedSoFar < $opportunity->required_volunteers
                ? ApplicationStatus::Approved
                : fake()->randomElement([ApplicationStatus::Pending, ApplicationStatus::Rejected]);

            if ($status === ApplicationStatus::Approved) {
                $approvedSoFar++;
            }

            $application = VolunteerApplication::create([
                'opportunity_id' => $opportunity->id,
                'volunteer_id' => $volunteer->id,
                'status' => $status,
            ]);

            if ($status === ApplicationStatus::Approved) {
                $this->seedTasksForApplication($application, $opportunity);
            }
        }
    }

    private function seedTasksForApplication(VolunteerApplication $application, VolunteerOpportunity $opportunity): void
    {
        $opportunityClosed = $opportunity->status === OpportunityStatus::Closed;
        $taskCount = rand(1, 3);

        for ($i = 0; $i < $taskCount; $i++) {
            VolunteerTask::create([
                'application_id' => $application->id,
                'task_description' => fake()->sentence(6),
                'status' => $opportunityClosed
                    ? TaskStatus::Completed
                    : fake()->randomElement([TaskStatus::Assigned, TaskStatus::Completed]),
            ]);
        }

        // Issue a certificate once the opportunity is closed (tasks are done).
        if ($opportunityClosed) {
            VolunteerCertificate::create([
                'volunteer_id' => $application->volunteer_id,
                'opportunity_id' => $opportunity->id,
                'certificate_url' => 'https://placeholder.wasl-mms.test/certificates/' . fake()->uuid() . '.pdf',
                'issued_at' => Carbon::parse($opportunity->end_date)->addDay(),
            ]);
        }
    }
}
