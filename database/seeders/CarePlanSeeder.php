<?php

namespace Database\Seeders;

use App\Models\CarePlan;
use App\Models\WebAdmin;
use Illuminate\Database\Seeder;

class CarePlanSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = WebAdmin::query()->value('id');

        $plans = [
            [
                'title' => 'Diabetes Care Plan',
                'slug' => 'diabetes-care-plan',
                'summary' => 'Day-to-day guidance for managing type 2 diabetes with diet, activity, and medication reminders.',
                'body' => <<<'HTML'
<p>This care plan helps patients keep blood sugar steady and reduce long-term complications.</p>
<ul>
<li>Check fasting glucose as advised by your doctor</li>
<li>Prefer whole grains, vegetables, lean protein, and controlled portions</li>
<li>Walk at least 30 minutes most days of the week</li>
<li>Take medicines on schedule and never skip doses without medical advice</li>
<li>Book a follow-up if readings stay high for 3 consecutive days</li>
</ul>
HTML,
                'status' => 'published',
                'sort_order' => 1,
            ],
            [
                'title' => 'Hypertension Management',
                'slug' => 'hypertension-management',
                'summary' => 'A practical plan for controlling high blood pressure at home and knowing when to seek care.',
                'body' => <<<'HTML'
<p>Focus on salt reduction, regular monitoring, and consistent medication use.</p>
<ul>
<li>Measure blood pressure at the same time each day</li>
<li>Limit salt, processed foods, and sugary drinks</li>
<li>Maintain a healthy weight and reduce stress where possible</li>
<li>Continue prescribed anti-hypertensives unless your doctor changes them</li>
<li>Seek urgent care for chest pain, severe headache, or sudden vision changes</li>
</ul>
HTML,
                'status' => 'published',
                'sort_order' => 2,
            ],
            [
                'title' => 'Maternal Wellness Plan',
                'slug' => 'maternal-wellness-plan',
                'summary' => 'Support for pregnancy checkups, nutrition, and warning signs that need medical attention.',
                'body' => <<<'HTML'
<p>Designed for expectant mothers following AfyaMed clinician guidance.</p>
<ul>
<li>Attend all antenatal appointments on schedule</li>
<li>Take prenatal vitamins as prescribed</li>
<li>Stay hydrated and eat iron-rich, balanced meals</li>
<li>Rest when needed and avoid heavy lifting</li>
<li>Contact your doctor for bleeding, severe swelling, reduced baby movement, or fever</li>
</ul>
HTML,
                'status' => 'published',
                'sort_order' => 3,
            ],
            [
                'title' => 'Post-Consultation Recovery',
                'slug' => 'post-consultation-recovery',
                'summary' => 'Simple after-visit steps so patients know what to do after a telehealth or clinic consultation.',
                'body' => <<<'HTML'
<p>Use this plan for the first 7 days after a consultation.</p>
<ol>
<li>Follow the doctor’s advice and complete any prescribed medicines</li>
<li>Upload reports or prescriptions in the AfyaMed app if requested</li>
<li>Track symptoms daily and note improvements or new concerns</li>
<li>Book pharmacy delivery or lab tests from the app when ordered</li>
<li>Request a follow-up if symptoms worsen or do not improve in the agreed timeframe</li>
</ol>
HTML,
                'status' => 'published',
                'sort_order' => 4,
            ],
            [
                'title' => 'Child Fever Home Care',
                'slug' => 'child-fever-home-care',
                'summary' => 'Home-care checklist for mild childhood fever, with clear red-flag symptoms for parents.',
                'body' => <<<'HTML'
<p>For mild fever in children when a clinician has already advised home care.</p>
<ul>
<li>Offer fluids frequently and light meals</li>
<li>Dress the child in light clothing and keep the room comfortable</li>
<li>Use fever medicine only as prescribed for age and weight</li>
<li>Monitor temperature every 4–6 hours</li>
<li>Seek urgent care for difficulty breathing, rash, unusual sleepiness, or fever lasting more than 3 days</li>
</ul>
HTML,
                'status' => 'published',
                'sort_order' => 5,
            ],
            [
                'title' => 'General Wellness Basics',
                'slug' => 'general-wellness-basics',
                'summary' => 'Starter lifestyle plan for sleep, hydration, activity, and preventive checkups.',
                'body' => <<<'HTML'
<p>A light wellness baseline for patients who want healthier daily habits.</p>
<ul>
<li>Sleep 7–8 hours most nights</li>
<li>Drink water regularly through the day</li>
<li>Move your body for at least 150 minutes each week</li>
<li>Schedule annual health checks and vaccinations as recommended</li>
<li>Use AfyaMed to book doctors, labs, and pharmacy support when needed</li>
</ul>
HTML,
                'status' => 'draft',
                'sort_order' => 6,
            ],
        ];

        foreach ($plans as $plan) {
            CarePlan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                [
                    ...$plan,
                    'created_by' => $adminId,
                ],
            );
        }
    }
}
