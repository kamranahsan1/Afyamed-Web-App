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

        foreach ($this->plans() as $plan) {
            CarePlan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                [...$plan, 'created_by' => $adminId],
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function plans(): array
    {
        return [
            // ── MEMBERSHIP (paid plans / what members unlock) ──────────────
            [
                'title' => 'Personalized Everyday Care',
                'slug' => 'personalized-everyday-care',
                'category' => 'membership',
                'tagline' => 'Your everyday health companion — stay healthy, not only treat illness.',
                'summary' => 'Main AfyaMed membership. Includes free first consult, monthly medicine/wellness delivery, family tools, reminders, and cheaper doctor calls.',
                'body' => <<<'HTML'
<p><strong>For members:</strong> this is your main AfyaMed subscription plan.</p>
<p>It helps with daily health — medicines, family, reminders, and doctor visits — not only when you are sick.</p>
HTML,
                'benefits' => [
                    [
                        'title' => 'Free first health consultation',
                        'description' => 'Talk once with a licensed doctor/provider who helps build your personal care plan.',
                    ],
                    [
                        'title' => 'Insurance support',
                        'description' => 'Save insurance details in the app and get help understanding your benefits.',
                    ],
                    [
                        'title' => 'Monthly medicine + wellness delivery',
                        'description' => 'Eligible prescription refills and wellness items are prepared every month and delivered together (free monthly order).',
                    ],
                    [
                        'title' => 'Your personal care plan',
                        'description' => 'A plan made for your goals, lifestyle, and medical needs.',
                    ],
                    [
                        'title' => 'Family health dashboard',
                        'description' => 'Manage medicines, appointments, and health info for you and your family in one place.',
                    ],
                    [
                        'title' => 'Smart health reminders',
                        'description' => 'Reminders for medicines, refills, doctor visits, labs, and follow-ups.',
                    ],
                    [
                        'title' => 'Faster telehealth booking',
                        'description' => 'Book video/phone doctor visits faster with member priority.',
                    ],
                    [
                        'title' => 'Cheaper monthly doctor consult',
                        'description' => 'Every month you get 1 telehealth consult at a special member price (or free, based on the event rule).',
                    ],
                ],
                'member_events' => [
                    [
                        'code' => 'complimentary_initial_consult',
                        'title' => 'Free first consultation',
                        'description' => 'One free consult when membership starts.',
                        'type' => 'complimentary',
                        'frequency' => 'once',
                        'quantity' => 1,
                        'discount_percent' => 100,
                        'active' => true,
                    ],
                    [
                        'code' => 'monthly_complimentary_consult',
                        'title' => '1 free/special consult every month',
                        'description' => 'Once per calendar month — member gets 1 complimentary telehealth consult.',
                        'type' => 'complimentary',
                        'frequency' => 'monthly',
                        'quantity' => 1,
                        'discount_percent' => 100,
                        'active' => true,
                    ],
                    [
                        'code' => 'doctor_consultation_discount',
                        'title' => 'Discount on other doctor visits',
                        'description' => 'Extra doctor consultations get member discount (default 20%).',
                        'type' => 'discount',
                        'frequency' => 'ongoing',
                        'quantity' => null,
                        'discount_percent' => 20,
                        'active' => true,
                    ],
                    [
                        'code' => 'monthly_rx_wellness_bundle',
                        'title' => 'Monthly medicine + wellness bundle',
                        'description' => 'One combined delivery per month for eligible items.',
                        'type' => 'bundle',
                        'frequency' => 'monthly',
                        'quantity' => 1,
                        'discount_percent' => null,
                        'active' => true,
                    ],
                    [
                        'code' => 'priority_telehealth',
                        'title' => 'Priority telehealth slots',
                        'description' => 'Members get priority when booking telehealth.',
                        'type' => 'priority',
                        'frequency' => 'ongoing',
                        'quantity' => null,
                        'discount_percent' => null,
                        'active' => true,
                    ],
                ],
                'status' => 'published',
                'sort_order' => 1,
            ],
            [
                'title' => 'Family Care Plus',
                'slug' => 'family-care-plus',
                'category' => 'membership',
                'tagline' => 'Same everyday care, built for the whole family.',
                'summary' => 'Family membership demo — more family members, shared dashboard, and extra monthly consults.',
                'body' => <<<'HTML'
<p><strong>For members:</strong> choose this if you manage health for spouse, children, or parents too.</p>
HTML,
                'benefits' => [
                    [
                        'title' => 'Everything in Everyday Care',
                        'description' => 'All main membership benefits are included.',
                    ],
                    [
                        'title' => 'Up to 5 family profiles',
                        'description' => 'Add family members under one membership.',
                    ],
                    [
                        'title' => '2 monthly member consults',
                        'description' => 'Two telehealth consults per month for the household at member pricing.',
                    ],
                ],
                'member_events' => [
                    [
                        'code' => 'family_monthly_consults',
                        'title' => '2 discounted consults per month',
                        'description' => 'Household can use 2 member-priced telehealth visits each month.',
                        'type' => 'discount',
                        'frequency' => 'monthly',
                        'quantity' => 2,
                        'discount_percent' => 50,
                        'active' => true,
                    ],
                    [
                        'code' => 'family_dashboard_unlock',
                        'title' => 'Family dashboard unlocked',
                        'description' => 'Shared medicines, appointments, and records for linked family members.',
                        'type' => 'feature',
                        'frequency' => 'ongoing',
                        'quantity' => null,
                        'discount_percent' => null,
                        'active' => true,
                    ],
                ],
                'status' => 'published',
                'sort_order' => 2,
            ],

            // ── CLINICAL (doctor-style condition plans) ────────────────────
            [
                'title' => 'Diabetes Care Plan',
                'slug' => 'diabetes-care-plan',
                'category' => 'clinical',
                'tagline' => 'Daily steps to keep blood sugar steady.',
                'summary' => 'Clinical plan for members/patients with diabetes — diet, activity, medicines, and when to call the doctor.',
                'body' => <<<'HTML'
<p><strong>Who it is for:</strong> patients with type 2 diabetes (after a doctor recommends this plan).</p>
<ul>
<li>Check fasting glucose as advised</li>
<li>Prefer vegetables, whole grains, and controlled portions</li>
<li>Walk about 30 minutes most days</li>
<li>Take medicines on time</li>
<li>Book follow-up if readings stay high for 3 days</li>
</ul>
HTML,
                'benefits' => [
                    ['title' => 'Glucose tracking tips', 'description' => 'Simple guidance on when and how to check sugar.'],
                    ['title' => 'Medicine reminders', 'description' => 'Use app reminders so doses are not missed.'],
                    ['title' => 'Doctor follow-up cues', 'description' => 'Clear signs for when to book another consult.'],
                ],
                'member_events' => [],
                'status' => 'published',
                'sort_order' => 10,
            ],
            [
                'title' => 'Hypertension Management',
                'slug' => 'hypertension-management',
                'category' => 'clinical',
                'tagline' => 'Control blood pressure at home with clear rules.',
                'summary' => 'Clinical plan for high blood pressure — daily checks, less salt, medicines, and emergency warning signs.',
                'body' => <<<'HTML'
<p><strong>Who it is for:</strong> patients with high blood pressure.</p>
<ul>
<li>Measure BP at the same time daily</li>
<li>Reduce salt and processed food</li>
<li>Continue prescribed medicines</li>
<li>Urgent care for chest pain, severe headache, or sudden vision change</li>
</ul>
HTML,
                'benefits' => [
                    ['title' => 'Home BP routine', 'description' => 'How often to measure and what to note.'],
                    ['title' => 'Lifestyle checklist', 'description' => 'Salt, weight, stress, and activity basics.'],
                ],
                'member_events' => [],
                'status' => 'published',
                'sort_order' => 11,
            ],
            [
                'title' => 'Maternal Wellness Plan',
                'slug' => 'maternal-wellness-plan',
                'category' => 'clinical',
                'tagline' => 'Support through pregnancy checkups and warning signs.',
                'summary' => 'Clinical plan for expectant mothers — appointments, vitamins, rest, and when to seek urgent care.',
                'body' => <<<'HTML'
<p><strong>Who it is for:</strong> pregnant members following clinician advice.</p>
<ul>
<li>Attend all antenatal visits</li>
<li>Take prenatal vitamins as prescribed</li>
<li>Stay hydrated and rest when needed</li>
<li>Call doctor for bleeding, severe swelling, less baby movement, or fever</li>
</ul>
HTML,
                'benefits' => [
                    ['title' => 'Visit schedule support', 'description' => 'Reminders for antenatal appointments.'],
                    ['title' => 'Red-flag guidance', 'description' => 'Clear list of symptoms that need urgent care.'],
                ],
                'member_events' => [],
                'status' => 'published',
                'sort_order' => 12,
            ],
            [
                'title' => 'Child Fever Home Care',
                'slug' => 'child-fever-home-care',
                'category' => 'clinical',
                'tagline' => 'Safe home care for mild childhood fever.',
                'summary' => 'Clinical plan for parents — fluids, rest, medicine only as prescribed, and red-flag symptoms.',
                'body' => <<<'HTML'
<p><strong>Who it is for:</strong> parents caring for a child with mild fever after clinician advice.</p>
<ul>
<li>Offer fluids often</li>
<li>Light clothing and a comfortable room</li>
<li>Fever medicine only as prescribed for age/weight</li>
<li>Urgent care for breathing trouble, rash, unusual sleepiness, or fever over 3 days</li>
</ul>
HTML,
                'benefits' => [
                    ['title' => 'Parent checklist', 'description' => 'Simple home steps for mild fever.'],
                    ['title' => 'When to go to ER / doctor', 'description' => 'Red-flag list for parents.'],
                ],
                'member_events' => [],
                'status' => 'published',
                'sort_order' => 13,
            ],
            [
                'title' => 'Post-Consultation Recovery',
                'slug' => 'post-consultation-recovery',
                'category' => 'clinical',
                'tagline' => 'What to do in the 7 days after a doctor visit.',
                'summary' => 'Clinical follow-up plan after telehealth or clinic consult — medicines, reports, and when to return.',
                'body' => <<<'HTML'
<p><strong>Who it is for:</strong> any patient after a consultation.</p>
<ol>
<li>Follow the doctor’s advice and finish prescribed medicines</li>
<li>Upload reports in the app if asked</li>
<li>Track symptoms for 7 days</li>
<li>Book pharmacy/lab from the app when ordered</li>
<li>Request follow-up if symptoms worsen</li>
</ol>
HTML,
                'benefits' => [
                    ['title' => '7-day recovery steps', 'description' => 'Clear after-visit checklist.'],
                    ['title' => 'Follow-up reminder', 'description' => 'Prompt to book again if needed.'],
                ],
                'member_events' => [],
                'status' => 'published',
                'sort_order' => 14,
            ],

            // ── WELLNESS (lifestyle / prevention — not disease treatment) ──
            [
                'title' => 'General Wellness Basics',
                'slug' => 'general-wellness-basics',
                'category' => 'wellness',
                'tagline' => 'Simple daily habits for better energy and prevention.',
                'summary' => 'Wellness plan for sleep, water, movement, and yearly checkups — for healthy members who want to stay well.',
                'body' => <<<'HTML'
<p><strong>Who it is for:</strong> anyone who wants basic healthy habits (not a disease treatment plan).</p>
<ul>
<li>Sleep 7–8 hours most nights</li>
<li>Drink water regularly</li>
<li>Move at least 150 minutes per week</li>
<li>Book yearly health checks as recommended</li>
</ul>
HTML,
                'benefits' => [
                    ['title' => 'Habit reminders', 'description' => 'Optional reminders for sleep, water, and activity.'],
                    ['title' => 'Prevention focus', 'description' => 'Encourages checkups before problems grow.'],
                ],
                'member_events' => [],
                'status' => 'published',
                'sort_order' => 20,
            ],
            [
                'title' => 'Stress Reset Plan',
                'slug' => 'stress-reset-plan',
                'category' => 'wellness',
                'tagline' => 'Light daily reset for stress and better focus.',
                'summary' => 'Wellness demo plan — breathing, short walks, screen breaks, and sleep wind-down.',
                'body' => <<<'HTML'
<p><strong>Who it is for:</strong> members feeling stressed or overloaded (not a mental-health emergency plan).</p>
<ul>
<li>5 minutes of calm breathing daily</li>
<li>Short outdoor walk when possible</li>
<li>No screens 30 minutes before bed</li>
<li>If anxiety or low mood is severe, book a doctor consult</li>
</ul>
HTML,
                'benefits' => [
                    ['title' => 'Daily calm routine', 'description' => 'Small steps that fit a busy day.'],
                    ['title' => 'Know when to get help', 'description' => 'Guidance to book clinical care if symptoms are severe.'],
                ],
                'member_events' => [],
                'status' => 'published',
                'sort_order' => 21,
            ],
            [
                'title' => 'Nutrition Starter Plan',
                'slug' => 'nutrition-starter-plan',
                'category' => 'wellness',
                'tagline' => 'Easy food habits without strict diets.',
                'summary' => 'Wellness demo — balanced plates, less sugary drinks, and smarter snacks.',
                'body' => <<<'HTML'
<p><strong>Who it is for:</strong> members who want better eating habits.</p>
<ul>
<li>Fill half the plate with vegetables when possible</li>
<li>Cut sugary drinks most days</li>
<li>Choose water or unsweetened options</li>
<li>Keep snacks simple (fruit, nuts, yogurt)</li>
</ul>
HTML,
                'benefits' => [
                    ['title' => 'Plate guide', 'description' => 'Easy visual rule for meals.'],
                    ['title' => 'Snack swaps', 'description' => 'Healthier everyday snack ideas.'],
                ],
                'member_events' => [],
                'status' => 'draft',
                'sort_order' => 22,
            ],
        ];
    }
}
