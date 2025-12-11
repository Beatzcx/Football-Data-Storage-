<?php
$dataFile = __DIR__ . '/players.json';

function load_data($file) {
    if (!file_exists($file)) return [];
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function save_data($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$message = '';

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $all = load_data($dataFile);
    $filtered = array_values(array_filter($all, function($r) use ($id) { return ($r['id'] ?? '') !== $id; }));
    save_data($dataFile, $filtered);
    $message = 'Record deleted.';
}

// Handle POST (create)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = $_POST;
    // Basic normalization: ensure arrays for grouped fields
    $record = [];
    foreach ($payload as $k => $v) {
        // trim strings
        if (is_string($v)) $v = trim($v);
        $record[$k] = $v;
    }
    $record['id'] = uniqid('p', true);
    $record['created_at'] = date('c');

    $all = load_data($dataFile);
    $all[] = $record;
    save_data($dataFile, $all);
    $message = 'Saved successfully.';
}

$records = load_data($dataFile);

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Football Data Storage</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;margin:20px;background:#f6f8fa}
        .container{max-width:1100px;margin:0 auto;background:#fff;padding:20px;border-radius:6px;box-shadow:0 2px 8px rgba(0,0,0,.06)}
        fieldset{margin-bottom:16px;padding:12px}
        legend{font-weight:bold}
        label{display:block;margin:6px 0 2px;font-size:13px}
        input[type=text],input[type=number],input[type=date],select,textarea{width:100%;padding:8px;border:1px solid #ccd;box-sizing:border-box;border-radius:4px}
        .row{display:flex;gap:12px}
        .col{flex:1}
        .small{width:160px}
        table{width:100%;border-collapse:collapse;margin-top:12px}
        th,td{padding:8px;border:1px solid #e1e4e8;text-align:left}
        .actions a{margin-right:8px}
        .msg{padding:8px;background:#e6ffed;border:1px solid #b7f0c3;margin-bottom:12px}
    </style>
</head>
<body>
<div class="container">
    <h1>Football Data Storage</h1>
    <?php if ($message): ?><div class="msg"><?php echo e($message); ?></div><?php endif; ?>

    <form method="post">
        <fieldset>
            <legend>1. Player Information</legend>
            <div class="row">
                <div class="col"><label>Player ID</label><input name="player_id" type="text"></div>
                <div class="col"><label>Full Name</label><input name="full_name" type="text"></div>
                <div class="col small"><label>Age</label><input name="age" type="number" min="0"></div>
                <div class="col small"><label>Date of Birth</label><input name="dob" type="date"></div>
            </div>
            <div class="row">
                <div class="col small"><label>Nationality</label><input name="nationality" type="text"></div>
                <div class="col small"><label>Height</label><input name="height" type="text" placeholder="e.g., 1.82m"></div>
                <div class="col small"><label>Preferred Foot</label>
                    <select name="preferred_foot"><option value="Right">Right</option><option value="Left">Left</option><option value="Both">Both</option></select>
                </div>
                <div class="col small"><label>Position</label>
                    <select name="position"><option>GK</option><option>DEF</option><option>MID</option><option>FWD</option></select>
                </div>
                <div class="col small"><label>Jersey Number</label><input name="jersey" type="number" min="0"></div>
            </div>
        </fieldset>

        <fieldset>
            <legend>2. Team Information</legend>
            <div class="row">
                <div class="col small"><label>Team ID</label><input name="team_id" type="text"></div>
                <div class="col"><label>Team Name</label><input name="team_name" type="text"></div>
                <div class="col small"><label>League</label><input name="league" type="text"></div>
                <div class="col small"><label>Country</label><input name="team_country" type="text"></div>
            </div>
            <div class="row">
                <div class="col"><label>Coach/Manager Name</label><input name="coach" type="text"></div>
                <div class="col"><label>Home Stadium</label><input name="stadium" type="text"></div>
                <div class="col small"><label>Established Year</label><input name="est_year" type="number"></div>
            </div>
        </fieldset>

        <fieldset>
            <legend>3. Player Stats (Season)</legend>
            <div class="row">
                <div class="col small"><label>Matches Played</label><input name="matches" type="number"></div>
                <div class="col small"><label>Minutes Played</label><input name="minutes" type="number"></div>
                <div class="col small"><label>Goals</label><input name="goals" type="number"></div>
                <div class="col small"><label>Assists</label><input name="assists" type="number"></div>
                <div class="col small"><label>Clean Sheets</label><input name="clean_sheets" type="number"></div>
            </div>
            <div class="row">
                <div class="col small"><label>Yellow Cards</label><input name="yellow" type="number"></div>
                <div class="col small"><label>Red Cards</label><input name="red" type="number"></div>
                <div class="col small"><label>Pass Accuracy (%)</label><input name="pass_accuracy" type="number" step="0.1"></div>
                <div class="col small"><label>Shots on Target</label><input name="shots_on_target" type="number"></div>
                <div class="col small"><label>Tackles</label><input name="tackles" type="number"></div>
                <div class="col small"><label>Saves</label><input name="saves" type="number"></div>
            </div>
        </fieldset>

        <fieldset>
            <legend>4. Contract Information</legend>
            <div class="row">
                <div class="col small"><label>Contract Start Date</label><input name="contract_start" type="date"></div>
                <div class="col small"><label>Contract End Date</label><input name="contract_end" type="date"></div>
                <div class="col small"><label>Salary</label><input name="salary" type="text" placeholder="weekly/monthly"></div>
                <div class="col small"><label>Contract Value</label><input name="contract_value" type="text"></div>
                <div class="col small"><label>Release Clause</label><input name="release_clause" type="text"></div>
                <div class="col"><label>Agent Name</label><input name="agent" type="text"></div>
            </div>
        </fieldset>

        <fieldset>
            <legend>5. Injury & Fitness Tracking</legend>
            <div class="row">
                <div class="col"><label>Injury Type</label><input name="injury_type" type="text"></div>
                <div class="col small"><label>Date Injured</label><input name="date_injured" type="date"></div>
                <div class="col small"><label>Estimated Recovery Time</label><input name="recovery_time" type="text"></div>
                <div class="col small"><label>Fitness Level (1–100)</label><input name="fitness_level" type="number" min="1" max="100"></div>
            </div>
            <label>Medical Notes</label>
            <textarea name="medical_notes" rows="3"></textarea>
        </fieldset>

        <fieldset>
            <legend>6. Match Performance Logs</legend>
            <div class="row">
                <div class="col small"><label>Match Date</label><input name="match_date" type="date"></div>
                <div class="col"><label>Opponent</label><input name="opponent" type="text"></div>
                <div class="col small"><label>Home/Away</label><select name="home_away"><option>Home</option><option>Away</option></select></div>
                <div class="col small"><label>Result</label><select name="result"><option>Win</option><option>Loss</option><option>Draw</option></select></div>
                <div class="col small"><label>Player Rating</label><input name="rating" type="number" min="1" max="10"></div>
            </div>
            <label>Key Contributions</label>
            <textarea name="key_contributions" rows="2"></textarea>
            <label>Notes</label>
            <textarea name="match_notes" rows="2"></textarea>
        </fieldset>

        <fieldset>
            <legend>7. Training Records</legend>
            <div class="row">
                <div class="col small"><label>Training Date</label><input name="training_date" type="date"></div>
                <div class="col small"><label>Training Type</label><select name="training_type"><option>technical</option><option>fitness</option><option>tactical</option></select></div>
                <div class="col small"><label>Attendance</label><select name="attendance"><option>Present</option><option>Absent</option><option>Late</option></select></div>
            </div>
            <label>Performance Notes</label>
            <textarea name="training_performance" rows="2"></textarea>
            <label>Coach Comments</label>
            <textarea name="coach_comments" rows="2"></textarea>
        </fieldset>

        <fieldset>
            <legend>8. Achievements</legend>
            <label>Awards (comma separated)</label>
            <input name="awards" type="text">
            <label>Trophies</label>
            <input name="trophies" type="text">
            <label>Player Milestones</label>
            <input name="milestones" type="text">
            <label>Records Broken</label>
            <input name="records" type="text">
        </fieldset>

        <fieldset>
            <legend>9. Transfers</legend>
            <div class="row">
                <div class="col"><label>Previous Club</label><input name="previous_club" type="text"></div>
                <div class="col small"><label>Transfer Fee</label><input name="transfer_fee" type="text"></div>
                <div class="col small"><label>Date of Transfer</label><input name="transfer_date" type="date"></div>
                <div class="col small"><label>Loan or Permanent</label><input name="transfer_type" type="text"></div>
                <div class="col small"><label>Future Add-ons</label><input name="future_addons" type="text"></div>
            </div>
        </fieldset>

        <fieldset>
            <legend>10. Additional Notes</legend>
            <label>Personal Notes</label>
            <textarea name="personal_notes" rows="2"></textarea>
            <label>Scouting Reports</label>
            <textarea name="scouting_reports" rows="2"></textarea>
            <label>Behavioral Notes</label>
            <textarea name="behavioral_notes" rows="2"></textarea>
            <label>Custom Tags (comma separated)</label>
            <input name="custom_tags" type="text">
        </fieldset>

        <div style="text-align:right"><button type="submit">Save Record</button></div>
    </form>

    <h2>Saved Records (<?php echo count($records); ?>)</h2>
    <?php if (count($records) === 0): ?>
        <p>No records yet.</p>
    <?php else: ?>
        <table>
            <thead><tr><th>Added</th><th>Full Name</th><th>Team</th><th>Position</th><th>Age</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach (array_reverse($records) as $r): ?>
                <tr>
                    <td><?php echo e($r['created_at'] ?? ''); ?></td>
                    <td><?php echo e($r['full_name'] ?? $r['player_id'] ?? '—'); ?></td>
                    <td><?php echo e($r['team_name'] ?? ''); ?></td>
                    <td><?php echo e($r['position'] ?? ''); ?></td>
                    <td><?php echo e($r['age'] ?? ''); ?></td>
                    <td class="actions">
                        <a href="?view=<?php echo e($r['id']); ?>">View</a>
                        <a href="?delete=<?php echo e($r['id']); ?>" onclick="return confirm('Delete this record?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if (isset($_GET['view'])):
        $vid = $_GET['view'];
        $found = null;
        foreach ($records as $rec) if (($rec['id'] ?? '') === $vid) { $found = $rec; break; }
    ?>
        <?php if ($found): ?>
            <h3>Record Details</h3>
            <table>
                <tbody>
                <?php foreach ($found as $k => $v): ?>
                    <tr><th style="width:220px"><?php echo e($k); ?></th><td><?php echo nl2br(e(is_array($v) ? json_encode($v) : $v)); ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Record not found.</p>
        <?php endif; ?>
    <?php endif; ?>

</div>
</body>
</html>
