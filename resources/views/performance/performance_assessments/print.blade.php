<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Performance Assessment Form</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 96%;
            margin: 0 auto;
            padding: 20px 0;
        }

        .logo-wrapper {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo-wrapper img {
            max-height: 80px;
            width: auto;
        }

        .heading {
            text-align: center;
            margin-bottom: 20px;
        }

        .heading h2,
        .heading h3,
        .heading h4,
        .heading p {
            margin: 4px 0;
        }

        .section-title {
            font-size: 15px;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 8px;
            padding: 7px;
            border: 1px solid #000;
            background: #eee;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #eee;
            font-weight: bold;
        }

        .details-table td {
            border: none;
            padding: 4px 6px;
        }

        .rating-table {
            margin-top: 15px;
            margin-bottom: 20px;
        }

        .rating-table td,
        .rating-table th {
            padding: 5px;
        }

        .text-bold {
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .small {
            font-size: 10px;
        }

        .result-box {
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .comment-box {
            min-height: 45px;
        }

        .signature-table {
            margin-top: 30px;
        }

        .signature-table td {
            width: 33.33%;
            height: 90px;
        }

        .page-break {
            page-break-before: always;
        }

        .no-border td {
            border: none;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
            }

            .container {
                width: 100%;
                padding: 10px;
            }

            .section-block {
                page-break-inside: avoid;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>

<body>

@php
    $sectionTitles = [
        'SUMMARY_TASKS' => 'SECTION 2 : SUMMARY OF PERFORMANCE ON TASKS',
        'PEOPLE' => 'SECTION A : PEOPLE',
        'CUSTOMERS' => 'SECTION B : CUSTOMERS',
        'FINANCIAL' => 'SECTION C : FINANCIAL',
        'OPERATIONAL' => 'SECTION D : OPERATIONAL EXCELLENCE',
        'VALUES' => 'SECTION E : VALUES & BEHAVIOURS',
    ];

    $groupedItems = $assessment->items->groupBy('section_code');

    $employeeTotalWeightedScore = $assessment->items->sum(function ($item) {
        return (float) ($item->employee_weighted_score ?? 0);
    });

    $assessorTotalWeightedScore = $assessment->items->sum(function ($item) {
        return (float) ($item->assessor_weighted_score ?? 0);
    });

    $reviewerTotalWeightedScore = $assessment->items->sum(function ($item) {
        return (float) ($item->reviewer_weighted_score ?? 0);
    });
@endphp

<div class="container">

    <div class="no-print text-right" style="margin-bottom:10px;">
        <button onclick="window.print()">Print Form</button>
    </div>

    {{-- ===================================================== --}}
    {{-- LOGO --}}
    {{-- ===================================================== --}}

    <div class="logo-wrapper">
        <img src="{{ asset('admin/dist/img/logo.png') }}" alt="LAPF Logo">
    </div>

    {{-- ===================================================== --}}
    {{-- HEADING --}}
    {{-- ===================================================== --}}

    <div class="heading">
        <h2>PERFORMANCE ASSESSMENT FORM FOR STAFF</h2>
        <h3>LOCAL AUTHORITIES PENSION FUND</h3>

        <h4>
            {{ strtoupper($assessment->period?->name ?? '') }}
        </h4>
    </div>

    {{-- ===================================================== --}}
    {{-- SECTION 1 --}}
    {{-- ===================================================== --}}

    <div class="section-title">
        SECTION 1: JOB INFORMATION
    </div>

    <table class="details-table">

        <tr>
            <td width="20%">
                <strong>Name of Staff Member Being Assessed:</strong>
            </td>

            <td width="30%">
                {{ $assessment->user->fullName() }}
            </td>

            <td width="15%">
                <strong>Assessor:</strong>
            </td>

            <td width="35%">
                {{ $assessment->assessor?->fullName() ?? 'N/A' }}
            </td>
        </tr>

        <tr>
            <td>
                <strong>Department:</strong>
            </td>

            <td>
                {{ $assessment->user->department?->name ?? 'N/A' }}
            </td>

            <td>
                <strong>Reviewer:</strong>
            </td>

            <td>
                {{ $assessment->reviewer?->fullName() ?? 'N/A' }}
            </td>
        </tr>

        <tr>
            <td>
                <strong>Section:</strong>
            </td>

            <td>
                {{ $assessment->user->section?->name ?? 'N/A' }}
            </td>

            <td>
                <strong>Review Period:</strong>
            </td>

            <td>
                {{ optional($assessment->period?->start_date)->format('d F Y') }}
                -
                {{ optional($assessment->period?->end_date)->format('d F Y') }}
            </td>
        </tr>

        <tr>
            <td>
                <strong>Job Title:</strong>
            </td>

            <td>
                {{ $assessment->user->job_title }}
            </td>

            <td>
                <strong>Status:</strong>
            </td>

            <td>
                {{ ucwords(str_replace('_', ' ', $assessment->status)) }}
            </td>
        </tr>

        <tr>
            <td>
                <strong>Grade:</strong>
            </td>

            <td>
                {{ $assessment->user->grade }}
            </td>

            <td>
                <strong>Date of Final Assessment Discussion:</strong>
            </td>

            <td></td>
        </tr>

    </table>

    {{-- ===================================================== --}}
    {{-- RATING SCALE --}}
    {{-- ===================================================== --}}

    <div style="margin-top:20px;">
        <strong>Rating Scale for use throughout the form:</strong>
    </div>

    <table class="rating-table">

        <thead>
        <tr>
            <th width="10%">Rating</th>
            <th width="10%">Score</th>
            <th width="20%">Performance Index</th>
            <th>Description</th>
        </tr>
        </thead>

        <tbody>

        @foreach($ratings as $rating)

            <tr>
                <td class="text-center">
                    <strong>{{ $rating->code }}</strong>
                </td>

                <td class="text-center">
                    {{ $rating->score }}
                </td>

                <td class="text-center">
                    {{ number_format((float) $rating->min_percentage, 2) }}
                    -
                    {{ number_format((float) $rating->max_percentage, 2) }}
                </td>

                <td>
                    {{ $rating->description }}
                </td>
            </tr>

        @endforeach

        </tbody>

    </table>

    {{-- ===================================================== --}}
    {{-- PERFORMANCE SECTIONS --}}
    {{-- ===================================================== --}}

    @foreach($sectionTitles as $sectionCode => $sectionTitle)

        @php
            $sectionItems = $groupedItems->get($sectionCode, collect());

            $sectionWeight = $sectionItems->first()?->section_weight ?? 0;

            $employeeSectionScore = $sectionItems->sum(function ($item) {
                return (float) ($item->employee_weighted_score ?? 0);
            });

            $assessorSectionScore = $sectionItems->sum(function ($item) {
                return (float) ($item->assessor_weighted_score ?? 0);
            });

            $reviewerSectionScore = $sectionItems->sum(function ($item) {
                return (float) ($item->reviewer_weighted_score ?? 0);
            });
        @endphp

        @if($sectionItems->count())

            <div class="section-block">

                <div class="section-title">

                    {{ $sectionTitle }}

                    <span style="float:right;">
                        Section Weight:
                        {{ number_format((float) $sectionWeight, 2) }}%
                    </span>

                </div>

                <table>

                    <thead>

                    <tr>
                        <th rowspan="2" width="4%">No</th>
                        <th rowspan="2" width="22%">Task</th>
                        <th rowspan="2" width="8%">Weight</th>

                        <th colspan="3">
                            Self-Assessment
                        </th>

                        <th colspan="3">
                            Assessment by Assessor
                        </th>

                        <th colspan="3">
                            Reviewer
                        </th>
                    </tr>

                    <tr>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Performance Index</th>

                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Performance Index</th>

                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Performance Index</th>
                    </tr>

                    </thead>

                    <tbody>

                    @foreach($sectionItems as $item)

                        <tr>

                            <td class="text-center">
                                {{ $loop->iteration }}
                            </td>

                            <td>
                                <strong>{{ $item->task }}</strong>

                                @if($item->measure_target)

                                    <br><br>

                                    <span class="small">
                                        <strong>Measure / Target:</strong><br>
                                        {{ $item->measure_target }}
                                    </span>

                                @endif

                            </td>

                            <td class="text-center">
                                {{ number_format((float) $item->item_weight, 2) }}%
                            </td>

                            {{-- EMPLOYEE --}}

                            <td class="text-center">

                                @if($item->employeeRating)

                                    <strong>
                                        {{ $item->employeeRating->code }}
                                    </strong>

                                @else

                                    -

                                @endif

                            </td>

                            <td>
                                {{ $item->employee_comment ?: '-' }}
                            </td>

                            <td class="text-center">

                                @if($item->employee_achievement_percentage !== null)

                                    {{ number_format(
                                        (float) $item->employee_achievement_percentage,
                                        2
                                    ) }}

                                @else

                                    -

                                @endif

                            </td>

                            {{-- ASSESSOR --}}

                            <td class="text-center">

                                @if($item->assessorRating)

                                    <strong>
                                        {{ $item->assessorRating->code }}
                                    </strong>

                                @else

                                    -

                                @endif

                            </td>

                            <td>
                                {{ $item->assessor_comment ?: '-' }}
                            </td>

                            <td class="text-center">

                                @if($item->assessor_achievement_percentage !== null)

                                    {{ number_format(
                                        (float) $item->assessor_achievement_percentage,
                                        2
                                    ) }}

                                @else

                                    -

                                @endif

                            </td>

                            {{-- REVIEWER --}}

                            <td class="text-center">

                                @if($item->reviewerRating)

                                    <strong>
                                        {{ $item->reviewerRating->code }}
                                    </strong>

                                @else

                                    -

                                @endif

                            </td>

                            <td>
                                {{ $item->reviewer_comment ?: '-' }}
                            </td>

                            <td class="text-center">

                                @if($item->reviewer_achievement_percentage !== null)

                                    {{ number_format(
                                        (float) $item->reviewer_achievement_percentage,
                                        2
                                    ) }}

                                @else

                                    -

                                @endif

                            </td>

                        </tr>

                    @endforeach

                    {{-- SECTION TOTAL --}}

                    <tr>

                        <td colspan="2">
                            <strong>
                                Overall {{ $sectionTitle }} Rating
                            </strong>
                        </td>

                        <td class="text-center">
                            <strong>
                                100%
                            </strong>
                        </td>

                        <td colspan="2">
                            <strong>
                                Weighted Score
                            </strong>
                        </td>

                        <td class="text-center">
                            {{ number_format((float) $employeeSectionScore, 4) }}
                        </td>

                        <td colspan="2">
                            <strong>
                                Weighted Score
                            </strong>
                        </td>

                        <td class="text-center">
                            {{ number_format((float) $assessorSectionScore, 4) }}
                        </td>

                        <td colspan="2">
                            <strong>
                                Weighted Score
                            </strong>
                        </td>

                        <td class="text-center">
                            {{ number_format((float) $reviewerSectionScore, 4) }}
                        </td>

                    </tr>

                    </tbody>

                </table>

            </div>

        @endif

    @endforeach

    {{-- ===================================================== --}}
    {{-- GENERAL SUMMARY --}}
    {{-- ===================================================== --}}

    <div class="section-title">
        SECTION 3: GENERAL SUMMARY
    </div>

    <table>

        <tr>
            <th width="33.33%">
                Staff Member General Comment
            </th>

            <th width="33.33%">
                Assessor General Comment
            </th>

            <th width="33.33%">
                Reviewer General Comment
            </th>
        </tr>

        <tr>

            <td class="comment-box">
                {{ $assessment->employee_general_comment ?: '' }}
            </td>

            <td class="comment-box">
                {{ $assessment->assessor_general_comment ?: '' }}
            </td>

            <td class="comment-box">
                {{ $assessment->reviewer_general_comment ?: '' }}
            </td>

        </tr>

    </table>

    {{-- ===================================================== --}}
    {{-- SUMMARY RATINGS --}}
    {{-- ===================================================== --}}

    <div class="section-title">
        SECTION 4: SUMMARY RATINGS FOR PERIOD END PERFORMANCE REVIEW
    </div>

    <p>
        Final ratings used for performance notching on pay scales or bonuses will be those of the reviewer and will be subject to Human Resources approval.
    </p>

    <table>

        <thead>

        <tr>
            <th>Balanced Scorecard Perspective</th>
            <th>Section Weight</th>
            <th>Staff Member Weighted Score</th>
            <th>Assessor Weighted Score</th>
            <th>Reviewer Weighted Score</th>
            <th>Reviewer Comment</th>
        </tr>

        </thead>

        <tbody>

        @foreach($sectionTitles as $sectionCode => $sectionTitle)

            @php
                $sectionItems = $groupedItems->get($sectionCode, collect());

                $sectionWeight = $sectionItems->first()?->section_weight ?? 0;

                $employeeSectionScore = $sectionItems->sum(function ($item) {
                    return (float) ($item->employee_weighted_score ?? 0);
                });

                $assessorSectionScore = $sectionItems->sum(function ($item) {
                    return (float) ($item->assessor_weighted_score ?? 0);
                });

                $reviewerSectionScore = $sectionItems->sum(function ($item) {
                    return (float) ($item->reviewer_weighted_score ?? 0);
                });
            @endphp

            @if($sectionItems->count())

                <tr>

                    <td>
                        {{ $sectionTitle }}
                    </td>

                    <td class="text-center">
                        {{ number_format((float) $sectionWeight, 2) }}%
                    </td>

                    <td class="text-center">
                        {{ number_format((float) $employeeSectionScore, 4) }}
                    </td>

                    <td class="text-center">
                        {{ number_format((float) $assessorSectionScore, 4) }}
                    </td>

                    <td class="text-center">
                        {{ number_format((float) $reviewerSectionScore, 4) }}
                    </td>

                    <td>
                        @php
                            $reviewerComments = $sectionItems
                                ->pluck('reviewer_comment')
                                ->filter()
                                ->implode('; ');
                        @endphp

                        {{ $reviewerComments ?: '-' }}
                    </td>

                </tr>

            @endif

        @endforeach


        <tr>

            <td>
                <strong>Total Performance Score</strong>
            </td>

            <td class="text-center">
                <strong>100%</strong>
            </td>

            <td class="text-center">
                <strong>
                    {{ number_format((float) $employeeTotalWeightedScore, 4) }}
                </strong>
            </td>

            <td class="text-center">
                <strong>
                    {{ number_format((float) $assessorTotalWeightedScore, 4) }}
                </strong>
            </td>

            <td class="text-center">
                <strong>
                    {{ number_format((float) $reviewerTotalWeightedScore, 4) }}
                </strong>
            </td>

            <td></td>

        </tr>

        </tbody>

    </table>

    {{-- ===================================================== --}}
    {{-- SIGNATURES --}}
    {{-- ===================================================== --}}

    <div class="section-title">
        SIGNATURES
    </div>

    <p>
        After discussing the assessment and after the reviewer assigns the final performance rating, the staff member, assessor and reviewer sign as confirmation that the performance assessment has been discussed and the final ratings and comments have been seen.
    </p>

    <table class="signature-table">

        <tr>

            <td>
                <strong>Staff Member Being Assessed</strong>

                <br><br>

                Name:
                {{ $assessment->user->fullName() }}

                <br><br>

                Signature:
                ___________________________

                <br><br>

                Date:
                ___________________________
            </td>


            <td>
                <strong>Assessor</strong>

                <br><br>

                Name:
                {{ $assessment->assessor?->fullName() ?? '' }}

                <br><br>

                Signature:
                ___________________________

                <br><br>

                Date:
                {{ optional($assessment->assessor_assessed_at)->format('d/m/Y') }}
            </td>


            <td>
                <strong>Reviewer</strong>

                <br><br>

                Name:
                {{ $assessment->reviewer?->fullName() ?? '' }}

                <br><br>

                Signature:
                ___________________________

                <br><br>

                Date:
                {{ optional($assessment->reviewer_reviewed_at)->format('d/m/Y') }}
            </td>

        </tr>

    </table>

</div>

</body>
</html>