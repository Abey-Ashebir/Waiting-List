<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>📊 Weekly Waiting List Report</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            padding: 40px;
            margin: 0;
        }
        h1, h2 {
            color: #2c3e50;
        }
        h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        h2 {
            font-size: 22px;
            margin-top: 30px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 5px;
        }
        p {
            font-size: 16px;
            margin-top: 10px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        ul li {
            background: #ffffff;
            padding: 12px 16px;
            margin-bottom: 8px;
            border-left: 5px solid #3498db;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            text-align: left;
            padding: 12px 16px;
        }
        th {
            background-color: #2980b9;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #ecf0f1;
        }
        tr:hover {
            background-color: #d6eaf8;
        }
        .footer {
            margin-top: 40px;
            font-size: 13px;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>📊 Weekly Waiting List Report</h1>
    
    <p>Hello Team,</p>
    <p>Here's your weekly update on new signups for <strong>TenaMart</strong>.</p>
    
    <h2>📈 Summary</h2>
    <ul>
        <li>Total Signups: <strong>{{ $totalSignups }}</strong></li>
        <li>New Signups This Week: <strong>{{ $weeklySignups }}</strong></li>
    </ul>
    
    <h2>📋 Signups by Source</h2>
    <table>
        <thead>
            <tr>
                <th>Source</th>
                <th>Count</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            @foreach($signupsBySource as $source)
            <tr>
                <td>{{ $source->signup_source }}</td>
                <td>{{ $source->count }}</td>
                <td>{{ round(($source->count / $totalSignups) * 100, 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ \Carbon\Carbon::now()->format('F j, Y, g:i a') }}
    </div>
</body>
</html>
