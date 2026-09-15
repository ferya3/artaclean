<?php

declare(strict_types=1);

return [
    'title' => 'Find the machine you need',
    'subtitle' => 'Four short questions, and we name the machine for the job.',
    'step' => 'Step :number',
    'skip' => 'Skip',
    'find' => 'Find my machine',
    'start_over' => 'Start over',

    'q1' => 'What are you cleaning?',
    'q2' => 'What is on it?',
    'q3' => 'How large is the area?',
    'q4' => 'How often?',

    'area' => [
        'under_500' => 'Under 500 m²',
        '500_2000' => '500 – 2,000 m²',
        '2000_10000' => '2,000 – 10,000 m²',
        'over_10000' => 'Over 10,000 m²',
    ],

    'frequency' => [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'occasional' => 'Occasionally',
    ],

    'result_title' => 'Our recommendation',
    'brief' => ':surface, :soil, :area.',
    'required' => 'Required productivity: :value m²/h.',
    'best_match' => 'Best match',
    'alternatives' => 'Alternatives',
    'relaxed_note' => 'Nothing stocked matched both conditions, so this is ranked on surface alone. Talk to a specialist about this kind of soil.',
    'no_match' => 'No stocked machine matches that combination. A specialist can propose a custom configuration.',

    'unsure_title' => 'Not sure?',
    'unsure_body' => 'Leave your number and a specialist will call with these answers in hand.',
    'request_call' => 'Request a call',

    'exact_title' => 'Know your metreage?',
    'exact_body' => 'The calculator works out the required productivity and the cleaning time.',
    'by_site_title' => 'Or start from your site',

    'why_title' => 'Why this machine',
    'why_surface' => 'Rated for the surface',
    'why_soil' => 'Rated for the soil',
    'why_productivity' => 'Productivity',
    'why_productivity_value' => ':machine m²/h against the :required m²/h you need',
    'why_frequency' => 'Duty and power source',
];
