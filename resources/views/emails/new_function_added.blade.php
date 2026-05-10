<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Function Added</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2>New City Function Added</h2>
    <p>A new function has been added to the system and the effects table may need updating.</p>

    <table style="border-collapse: collapse; width: 100%; margin: 16px 0;">
        <tr>
            <th style="text-align: left; padding: 8px; background: #f4f4f4; border: 1px solid #ddd;">Name</th>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $cityFunction->name }}</td>
        </tr>
        <tr>
            <th style="text-align: left; padding: 8px; background: #f4f4f4; border: 1px solid #ddd;">Category</th>
            <td style="padding: 8px; border: 1px solid #ddd;">{{ $cityFunction->category }}</td>
        </tr>
    </table>

    <p>
        Please update the effects table to keep the simulation accurate:<br>
        <a href="{{ url('/effects') }}" style="color: #1a73e8;">Go to Effects Table</a>
    </p>
</body>
</html>
