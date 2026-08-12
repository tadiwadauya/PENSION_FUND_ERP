<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Performance Target Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 95%;
            margin: 0 auto;
            padding: 20px 0;
        }

        .logo-wrapper {
            text-align: center;
            margin-bottom: 15px;
        }

        .logo-wrapper img {
            max-height: 90px;
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

        .details-table,
        .targets-table,
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .details-table td {
            padding: 6px 8px;
            vertical-align: top;
        }

        .targets-table,
        .targets-table th,
        .targets-table td,
        .signature-table,
        .signature-table td {
            border: 1px solid #000;
        }

        .targets-table th,
        .targets-table td,
        .signature-table td {
            padding: 8px;
            vertical-align: top;
        }

        .targets-table th {
            text-align: left;
        }

        .signature-table {
            margin-top: 35px;
        }

        .signature-table td {
            height: 85px;
            width: 50%;
        }

        .text-bold {
            font-weight: bold;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 0;
            }

            .container {
                width: 100%;
                padding: 10px 20px;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        @php
            $reportsToCeo = $target->assessor && $target->assessor->is_ceo;
        @endphp

        <div class="logo-wrapper">
            <img src="{{ asset('admin/dist/img/logo.png') }}" alt="Organisation Logo">
        </div>

        <div class="heading">
            <h3>{{ strtoupper($target->user->department?->name ?? '') }} DEPARTMENT</h3>
            <h3>{{ strtoupper($target->user->job_title ?? '') }} - PERFORMANCE TARGETS FOR THE PERIOD</h3>
            <h4>{{ strtoupper($target->period->name ?? '') }}</h4>
        </div>

        <table class="details-table">
            <tr>
                <td width="35%"><span class="text-bold">Name of Staff Member Being Assessed:</span></td>
                <td>{{ $target->user->fullName() }}</td>
            </tr>
            <tr>
                <td><span class="text-bold">Section:</span></td>
                <td>{{ $target->user->section?->name }}</td>
            </tr>
            <tr>
                <td><span class="text-bold">Job Title:</span></td>
                <td>{{ $target->user->job_title }}</td>
            </tr>
            <tr>
                <td><span class="text-bold">Grade:</span></td>
                <td>{{ $target->user->grade }}</td>
            </tr>
            <tr>
                <td><span class="text-bold">Assessor:</span></td>
                <td>{{ $target->assessor?->fullName() ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><span class="text-bold">Reviewer:</span></td>
                <td>{{ $target->reviewer?->fullName() ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><span class="text-bold">Review Period:</span></td>
                <td>{{ $target->period->year }}</td>
            </tr>
        </table>

        <table class="targets-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 18%;">Perspective</th>
                    <th style="width: 25%;">Task</th>
                    <th style="width: 27%;">How To Achieve</th>
                    <th style="width: 25%;">Measure / Target</th>
                </tr>
            </thead>
            <tbody>
                @foreach($target->items as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->perspective }}</td>
                        <td>{{ $item->task }}</td>
                        <td>{{ $item->how_to_achieve }}</td>
                        <td>{{ $item->measure_target }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($reportsToCeo)
            <table class="signature-table">
                <tr>
                    <td style="width:100%;">
                        <span class="text-bold">Incumbent Name:</span> {{ $target->user->fullName() }}<br>
                        <span class="text-bold">Signature:</span> {{ $target->user->username }}<br>
                        <span class="text-bold">Date:</span> {{ optional($target->submitted_at)->format('d/m/Y') }}
                    </td>
                </tr>
            </table>
        @else
            <table class="signature-table">
                <tr>
                    <td>
                        <span class="text-bold">Incumbent Name:</span> {{ $target->user->fullName() }}<br>
                        <span class="text-bold">Signature:</span> {{ $target->user->username }}<br>
                        <span class="text-bold">Date:</span> {{ optional($target->submitted_at)->format('d/m/Y') }}
                    </td>
                    <td>
                        <span class="text-bold">Human Resources Officer:</span> {{ $target->hrReviewer?->fullName() ?? '' }}<br>
                        <span class="text-bold">Signature:</span>
                        {{ $target->status === 'reviewed_by_hr' ? ($target->hrReviewer?->username ?? '') : '' }}<br>
                        <span class="text-bold">Date:</span> {{ optional($target->hr_reviewed_at)->format('d/m/Y') }}
                    </td>
                </tr>
            </table>
        @endif

    </div>
</body>
</html>