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
$errors = [];

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
    // Validation: require some key fields before saving
    $errors = [];
    if (empty(trim($payload['full_name'] ?? ''))) $errors[] = 'Full name is required.';
    if (empty(trim($payload['team_name'] ?? ''))) $errors[] = 'Team name is required.';
    if (empty(trim($payload['position'] ?? ''))) $errors[] = 'Position is required.';
    if (!isset($payload['jersey']) || trim((string)$payload['jersey']) === '') $errors[] = 'Jersey number is required.';

    if (count($errors) === 0) {
        // Basic normalization: ensure arrays for grouped fields
        $record = [];
        foreach ($payload as $k => $v) {
            // trim strings
            if (is_string($v)) $v = trim($v);
            $record[$k] = $v;
        }

        $all = load_data($dataFile);

        // If an ID is provided we should update the existing record
        if (!empty($payload['id'])) {
            $id = $payload['id'];
            $updated = false;
            foreach ($all as $i => $r) {
                if (($r['id'] ?? '') === $id) {
                    // preserve original created_at if present
                    $record['id'] = $id;
                    $record['created_at'] = $r['created_at'] ?? date('c');
                    $all[$i] = $record;
                    $updated = true;
                    break;
                }
            }
            if ($updated) {
                save_data($dataFile, $all);
                $message = 'Updated successfully.';
                // refresh records and editing data
                $records = load_data($dataFile);
                foreach ($records as $rec) if (($rec['id'] ?? '') === $id) { $editing = $rec; break; }
            } else {
                // fallback to appending if id wasn't found
                $record['id'] = $id;
                $record['created_at'] = date('c');
                $all[] = $record;
                save_data($dataFile, $all);
                $message = 'Saved successfully.';
            }
        } else {
            $record['id'] = uniqid('p', true);
            $record['created_at'] = date('c');
            $all[] = $record;
            save_data($dataFile, $all);
            $message = 'Saved successfully.';
            // clear POST so the form resets (optional)
            $_POST = [];
        }
    }
}

$records = load_data($dataFile);

// If a record is being viewed for edit, load it so the form can be pre-filled
$editing = null;
if (isset($_GET['view'])) {
    $vid = $_GET['view'];
    foreach ($records as $rec) if (($rec['id'] ?? '') === $vid) { $editing = $rec; break; }
}

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function old($k, $default = '') {
    global $editing;
    if (isset($_POST[$k])) return e($_POST[$k]);
    if (!empty($editing) && isset($editing[$k])) return e($editing[$k]);
    return e($default);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Football Data Storage</title>
    <style>
        /* Page (American football / gridiron) */
        :root{--turf:#19692b;--turf-2:#1f7b34;--helmet:#c56a1c;--gold:#ffd24d;--muted:#6c6c6c}
        body{font-family:Arial,Helvetica,sans-serif;margin:20px;background:linear-gradient(#e9eef2,#e6f1ea);}

        /* Container looks like a pitch card */
        .container{max-width:1200px;margin:0 auto;background:linear-gradient(180deg,#ffffff, #fbfdf9);padding:18px;border-radius:10px;box-shadow:0 6px 24px rgba(5,20,10,0.12);border:1px solid rgba(0,0,0,0.06);overflow:hidden}

        /* Header / helmet crest */
        .site-header{display:flex;align-items:center;gap:16px;margin-bottom:10px}
        .crest{width:72px;height:72px;border-radius:50%;background:radial-gradient(circle at 30% 30%, #fff, var(--helmet) 20%, #8b4b12 60%);display:flex;align-items:center;justify-content:center;font-size:30px;box-shadow:inset 0 -6px 18px rgba(0,0,0,0.08);color:#fff}
        .site-header h1{margin:0;font-size:22px;color:var(--turf);letter-spacing:0.6px}
        .site-header .subtitle{margin:0;font-size:12px;color:var(--muted)}

        /* Gridiron accent behind the form: subtle turf + yard lines */
        .gridiron-bg{background:linear-gradient(180deg,var(--turf),var(--turf-2));border-radius:8px;padding:12px;color:#fff;margin-bottom:14px}
        .gridiron-stripes{background-image:repeating-linear-gradient(90deg, rgba(255,255,255,0.06) 0 2px, transparent 2px 60px);padding:10px;border-radius:6px}

        fieldset{margin-bottom:14px;padding:12px;border-radius:6px;border:1px solid rgba(0,0,0,0.04);background:#fff}
        legend{font-weight:700;color:#1f5b2f}
        label{display:block;margin:6px 0 2px;font-size:13px;color:#333}
        input[type=text],input[type=number],input[type=date],select,textarea{width:100%;padding:8px;border:1px solid #d6dadd;box-sizing:border-box;border-radius:6px}
        .row{display:flex;gap:12px}
        .col{flex:1}
        .small{width:160px}

        /* Buttons */
        .btn{display:inline-block;padding:8px 12px;border-radius:6px;text-decoration:none;color:#fff;background:#6c757d;border:0;font-size:14px}
        .btn-primary{background:linear-gradient(180deg,var(--helmet),#a9571a);box-shadow:0 6px 18px rgba(197,106,28,0.18)}
        .btn-outline{background:transparent;color:var(--turf);border:1px solid rgba(31,123,52,0.12)}
        button.btn{cursor:pointer}

        /* Table */
        table{width:100%;border-collapse:collapse;margin-top:12px;background:#fff}
        thead th{background:linear-gradient(90deg,#f7faf7,#eef8f1);color:#2b4f36;padding:10px;border-bottom:2px solid rgba(0,0,0,0.05);text-align:left}
        th,td{padding:10px;border-bottom:1px solid #eef2ee;text-align:left;vertical-align:middle}
        tbody tr:hover{background:rgba(25,105,43,0.04)}

        /* Player cell with jersey (American football look) */
        .player-cell{display:flex;align-items:center;gap:10px}
        .jersey{width:52px;height:52px;border-radius:6px;background:linear-gradient(180deg,#fff,#8b4513);color:#fff;font-weight:800;display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 3px 8px rgba(0,0,0,0.12);border:2px solid rgba(255,255,255,0.08)}
        .player-name{font-weight:800;color:#153a26}
        .player-team{font-size:13px;color:#6b6b6b}

        /* Position badge for American football roles */
        .pos{display:inline-block;padding:6px 8px;border-radius:999px;color:#fff;font-weight:700;font-size:12px}
        .pos.QB{background:#b22222}
        .pos.RB{background:#ff8c00}
        .pos.WR{background:#1e90ff}
        .pos.TE{background:#8b4513}
        .pos.OL{background:#6a5acd}
        .pos.DL{background:#2f4f4f}
        .pos.LB{background:#228b22}
        .pos.CB{background:#20b2aa}
        .pos.S{background:#4682b4}
        .pos.K,.pos.P{background:#555}

        .actions a{margin-right:8px}
        .msg{padding:10px;background:#fff8e1;border:1px solid #ffe29a;margin-bottom:12px;color:#6a4b00}
        .error{padding:10px;background:#ffecec;border:1px solid #ffb3b3;margin-bottom:12px;color:#6a1200}

        /* Responsive tweaks */
        @media (max-width:900px){.row{flex-direction:column}.small{width:100%}}
    </style>
</head>
<body>
<div class="container">
    <div class="site-header">
        <div class="crest">🏈</div>
        <div>
            <h1>American Football Roster</h1>
            <div class="subtitle">Roster & game logs</div>
        </div>
    </div>
    <?php if (!empty($errors)): ?>
        <div class="error"><strong>Please fix the following:</strong>
            <ul>
            <?php foreach ($errors as $err): ?>
                <li><?php echo e($err); ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if ($message): ?><div class="msg"><?php echo e($message); ?></div><?php endif; ?>

    <form method="post">
        <input type="hidden" name="id" value="<?php echo e($_POST['id'] ?? ($editing['id'] ?? '')); ?>">
        <fieldset>
            <legend>1. Player Information</legend>
            <div class="row">
                <div class="col"><label>Player ID</label><input name="player_id" type="text" value="<?php echo old('player_id'); ?>"></div>
                <div class="col"><label>Full Name</label><input name="full_name" type="text" required value="<?php echo old('full_name'); ?>"></div>
                <div class="col small"><label>Age</label><input name="age" type="number" min="0"></div>
                <div class="col small"><label>Date of Birth</label><input name="dob" type="date"></div>
            </div>
            <div class="row">
                <div class="col small"><label>Nationality</label><input name="nationality" type="text" value="<?php echo old('nationality'); ?>"></div>
                <div class="col small"><label>Height</label><input name="height" type="text" placeholder="e.g., 1.82m"></div>
                <div class="col small"><label>Preferred Hand</label>
                    <select name="preferred_hand"><option value="Right">Right</option><option value="Left">Left</option></select>
                </div>
                <div class="col small"><label>Position</label>
                    <select name="position" required>
                        <option value="">-- select --</option>
                        <?php $selPos = $_POST['position'] ?? ($editing['position'] ?? ''); ?>
                        <option value="QB" <?php echo ($selPos==='QB')? 'selected':''; ?>>QB</option>
                        <option value="RB" <?php echo ($selPos==='RB')? 'selected':''; ?>>RB</option>
                        <option value="WR" <?php echo ($selPos==='WR')? 'selected':''; ?>>WR</option>
                        <option value="TE" <?php echo ($selPos==='TE')? 'selected':''; ?>>TE</option>
                        <option value="OL" <?php echo ($selPos==='OL')? 'selected':''; ?>>OL</option>
                        <option value="DL" <?php echo ($selPos==='DL')? 'selected':''; ?>>DL</option>
                        <option value="LB" <?php echo ($selPos==='LB')? 'selected':''; ?>>LB</option>
                        <option value="CB" <?php echo ($selPos==='CB')? 'selected':''; ?>>CB</option>
                        <option value="S" <?php echo ($selPos==='S')? 'selected':''; ?>>S</option>
                        <option value="K" <?php echo ($selPos==='K')? 'selected':''; ?>>K</option>
                        <option value="P" <?php echo ($selPos==='P')? 'selected':''; ?>>P</option>
                    </select>
                </div>
                <div class="col small"><label>Jersey Number</label><input name="jersey" type="number" min="0" required value="<?php echo old('jersey'); ?>"></div>
            </div>
        </fieldset>

        <fieldset>
            <legend>2. Team Information</legend>
            <div class="row">
                <div class="col small"><label>Team ID</label><input name="team_id" type="text"></div>
                <div class="col"><label>Team Name</label><input name="team_name" type="text" required value="<?php echo old('team_name'); ?>"></div>
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
                <div class="col small"><label>Games Played</label><input name="matches" type="number"></div>
                <div class="col small"><label>Snaps Played</label><input name="minutes" type="number"></div>
                <div class="col small"><label>Touchdowns</label><input name="goals" type="number"></div>
                <div class="col small"><label>Receptions</label><input name="assists" type="number"></div>
                <div class="col small"><label>Sacks</label><input name="clean_sheets" type="number"></div>
            </div>
            <div class="row">
                <div class="col small"><label>Penalties</label><input name="yellow" type="number"></div>
                <div class="col small"><label>Fumbles</label><input name="red" type="number"></div>
                <div class="col small"><label>Completion (%)</label><input name="pass_accuracy" type="number" step="0.1"></div>
                <div class="col small"><label>Passing Yards</label><input name="shots_on_target" type="number"></div>
                <div class="col small"><label>Tackles</label><input name="tackles" type="number"></div>
                <div class="col small"><label>Interceptions</label><input name="saves" type="number"></div>
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

        <div style="text-align:right"><button class="btn btn-primary" type="submit">Save Record</button></div>
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
                    <td>
                        <div class="player-cell">
                            <div class="jersey"><?php echo e($r['jersey'] ?? ''); ?></div>
                            <div>
                                <div class="player-name"><?php echo e($r['full_name'] ?? $r['player_id'] ?? '—'); ?></div>
                                <div class="player-team"><?php echo e($r['team_name'] ?? ''); ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?php echo e($r['team_name'] ?? ''); ?></td>
                    <?php $pos = $r['position'] ?? ''; ?>
                    <td><span class="pos <?php echo htmlspecialchars($pos); ?>"><?php echo e($pos); ?></span></td>
                    <td><?php echo e($r['age'] ?? ''); ?></td>
                    <td class="actions">
                        <a class="btn btn-outline" href="?view=<?php echo e($r['id']); ?>">View</a>
                        <a class="btn" style="background:#d9534f" href="?delete=<?php echo e($r['id']); ?>" onclick="return confirm('Delete this record?')">Delete</a>
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
