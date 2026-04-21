<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Performance Target Form</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; color: #000; }
        .container { width: 95%; margin: 0 auto; }
        h2, h3, p { margin: 0 0 8px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; vertical-align: top; }
        .signature-block { margin-top: 40px; width: 100%; }
        .signature-block td { height: 70px; }
    </style>
</head>
<body onload="window.print()">
<div class="container">
    <h2>{{ $target->user->department?->name ?? '' }}</h2>
    <h3>{{ $target->title }}</h3>

    <p><strong>Name of Staff Member Being Assessed:</strong> {{ $target->user->fullName() }}</p>
    <p><strong>Department:</strong> {{ $target->user->department?->name }}</p>
    <p><strong>Section:</strong> {{ $target->user->section?->name }}</p>
    <p><strong>Job Title:</strong> {{ $target->user->job_title }}</p>
    <p><strong>Grade:</strong> {{ $target->user->grade }}</p>
    <p><strong>Assessor:</strong> {{ $target->assessor?->fullName() ?? 'N/A' }}</p>
    <p><strong>Reviewer:</strong> {{ $target->reviewer?->fullName() ?? 'N/A' }}</p>
    <p><strong>Review Period:</strong> {{ $target->period->year }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Perspective</th>
                <th>Task</th>
                <th>How To Achieve</th>
                <th>Target / Measure</th>
                <th>Due Date</th>
                <th>Assessor Comment</th>
                <th>HR Comment</th>
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
                    <td>{{ optional($item->due_date)->format('d/m/Y') }}</td>
                    <td>{{ $item->assessor_comment }}</td>
                    <td>{{ $item->hr_comment }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signature-block">
        <tr>
            <td>
                <strong>Incumbent Name:</strong> {{ $target->user->fullName() }}<br>
                <strong>Signature:</strong> {{ $target->user->username }}<br>
                <strong>Date:</strong> {{ optional($target->submitted_at)->format('d/m/Y') }}
            </td>
            <td>
                <strong>Human Resources Officer:</strong> {{ $target->hrReviewer?->fullName() ?? 'N/A' }}<br>
                <strong>Signature:</strong> {{ $target->status === 'reviewed_by_hr' ? ($target->hrReviewer?->username ?? '') : '' }}<br>
                <strong>Date:</strong> {{ optional($target->hr_reviewed_at)->format('d/m/Y') }}
            </td>
        </tr>
    </table>
</div>
</body>
</html>