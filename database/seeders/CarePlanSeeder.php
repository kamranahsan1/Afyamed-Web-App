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

        CarePlan::query()->updateOrCreate(
            ['slug' => 'personalized-everyday-care'],
            [
                'title' => 'Personalized Everyday Care',
                'category' => 'membership',
                'tagline' => 'Your everyday health companion. Designed to help you stay healthy—not just treat illness.',
                'summary' => 'Membership care plan with complimentary onboarding, monthly prescription/wellness delivery, family tools, and discounted telehealth.',
                'body' => <<<'HTML'
<p><strong>Personalized Everyday Care</strong> is your everyday health companion—designed to help you stay healthy, not just treat illness.</p>
<p>Members get a personalized plan, family dashboard, smart reminders, priority telehealth, and monthly care benefits including discounted consultations.</p>
HTML,
                'benefits' => [
                    [
                        'title' => 'Complimentary Initial Health Consultation',
                        'description' => 'Start with a licensed healthcare provider who will help create your personalized care plan.',
                    ],
                    [
                        'title' => 'Insurance Support',
                        'description' => 'Store your insurance details and get help navigating your healthcare benefits.',
                    ],
                    [
                        'title' => 'Automatic Monthly Prescription & Wellness Bundle',
                        'description' => 'Your eligible prescription refills and selected wellness essentials are automatically prepared each month and delivered together in one complimentary monthly order.',
                    ],
                    [
                        'title' => 'Personalized Care Plan',
                        'description' => 'Receive a plan tailored to your health goals, lifestyle, and medical needs.',
                    ],
                    [
                        'title' => 'Family Health Dashboard',
                        'description' => 'Manage medications, appointments, and health information for yourself and your loved ones in one place.',
                    ],
                    [
                        'title' => 'Smart Health Reminders',
                        'description' => 'Never miss a medication, refill, doctor appointment, lab test, or follow-up again.',
                    ],
                    [
                        'title' => 'Priority Access to Telehealth',
                        'description' => 'Book appointments faster and receive exclusive member pricing on eligible consultations.',
                    ],
                    [
                        'title' => 'Discounted Monthly Consultations',
                        'description' => 'Members receive one telehealth consultation each month at a discounted price, making ongoing care more convenient and affordable.',
                    ],
                ],
                'member_events' => [
                    [
                        'code' => 'complimentary_initial_consult',
                        'title' => 'Complimentary initial health consultation',
                        'description' => 'One-time free consultation with a licensed provider to build the personalized care plan.',
                        'type' => 'complimentary',
                        'frequency' => 'once',
                        'quantity' => 1,
                        'discount_percent' => null,
                        'active' => true,
                    ],
                    [
                        'code' => 'monthly_complimentary_consult',
                        'title' => '1 complimentary consultation per month',
                        'description' => 'Members get one complimentary / deeply discounted telehealth consultation each calendar month.',
                        'type' => 'complimentary',
                        'frequency' => 'monthly',
                        'quantity' => 1,
                        'discount_percent' => 100,
                        'active' => true,
                    ],
                    [
                        'code' => 'doctor_consultation_discount',
                        'title' => 'Discount on doctor consultation',
                        'description' => 'Exclusive member pricing on eligible doctor consultations beyond the monthly complimentary consult.',
                        'type' => 'discount',
                        'frequency' => 'ongoing',
                        'quantity' => null,
                        'discount_percent' => 20,
                        'active' => true,
                    ],
                    [
                        'code' => 'monthly_rx_wellness_bundle',
                        'title' => 'Monthly prescription & wellness bundle',
                        'description' => 'Eligible refills and wellness essentials prepared and delivered together once per month at no delivery charge.',
                        'type' => 'bundle',
                        'frequency' => 'monthly',
                        'quantity' => 1,
                        'discount_percent' => null,
                        'active' => true,
                    ],
                    [
                        'code' => 'priority_telehealth',
                        'title' => 'Priority telehealth booking',
                        'description' => 'Faster appointment booking and priority access to eligible telehealth slots.',
                        'type' => 'priority',
                        'frequency' => 'ongoing',
                        'quantity' => null,
                        'discount_percent' => null,
                        'active' => true,
                    ],
                ],
                'status' => 'published',
                'sort_order' => 0,
                'created_by' => $adminId,
            ],
        );

        $clinical = [
            [
                'title' => 'Diabetes Care Plan',
                'slug' => 'diabetes-care-plan',
                'summary' => 'Day-to-day guidance for managing type 2 diabetes with diet, activity, and medication reminders.',
                'body' => '<p>Keep blood sugar steady with monitoring, balanced meals, daily movement, and on-time medicines.</p>',
                'sort_order' => 10,
            ],
            [
                'title' => 'Hypertension Management',
                'slug' => 'hypertension-management',
                'summary' => 'Practical plan for controlling high blood pressure at home and knowing when to seek care.',
                'body' => '<p>Focus on salt reduction, regular BP checks, and consistent medication use.</p>',
                'sort_order' => 11,
            ],
            [
                'title' => 'Post-Consultation Recovery',
                'slug' => 'post-consultation-recovery',
                'summary' => 'After-visit steps so patients know what to do after a telehealth or clinic consultation.',
                'body' => '<p>Follow advice, track symptoms for 7 days, and book follow-up if needed.</p>',
                'sort_order' => 12,
            ],
        ];

        foreach ($clinical as $plan) {
            CarePlan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                [
                    ...$plan,
                    'category' => 'clinical',
                    'tagline' => null,
                    'benefits' => [],
                    'member_events' => [],
                    'status' => 'published',
                    'created_by' => $adminId,
                ],
            );
        }
    }
}
