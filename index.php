<?php
$dataFile = __DIR__ . '/players.json';

// Load JSON file and return array
function load_data($file) {
    if (!file_exists($file)) return [];
    $json = file_get_contents($file);
    $arr = json_decode($json, true);
    return is_array($arr) ? $arr : [];
}

// Save array back to JSON file
function save_data($file, array $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// HTML-escape helper
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$message = '';
$errors = [];

$records = load_data($dataFile);

// Delete by id (via GET 'delete')
if (!empty($_GET['delete'])) {
    $delId = (string) $_GET['delete'];
    $new = [];
    foreach ($records as $rec) {
        if (!isset($rec['id']) || $rec['id'] === $delId) continue;
        $new[] = $rec;
    }
    $records = $new;
    save_data($dataFile, $records);
    $message = 'Record deleted.';
}

// Handle POST for create/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full = trim((string)($_POST['full_name'] ?? ''));
    $team = trim((string)($_POST['team_name'] ?? ''));
    $pos  = trim((string)($_POST['position'] ?? ''));
    $jerseyRaw = $_POST['jersey'] ?? '';
    $jersey = trim((string)$jerseyRaw);
    $id = trim((string)($_POST['id'] ?? ''));

    if ($full === '') $errors[] = 'Full name is required.';
    if ($team === '') $errors[] = 'Team name is required.';
    if ($pos === '') $errors[] = 'Position is required.';
    if ($jersey === '') $errors[] = 'Jersey number is required.';

    if (empty($errors)) {
        $record = [
            'full_name' => $full,
            'team_name' => $team,
            'position'  => $pos,
            'jersey'    => $jersey,
        ];

        if ($id !== '') {
            // Update existing if found
            $found = false;
            foreach ($records as $i => $r) {
                if (isset($r['id']) && $r['id'] === $id) {
                    $record['id'] = $id;
                    $record['created_at'] = $r['created_at'] ?? date('c');
                    $records[$i] = $record;
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $record['id'] = $id ?: uniqid('p', true);
                $record['created_at'] = date('c');
                $records[] = $record;
            }
            $message = 'Saved successfully.';
        } else {
            $record['id'] = uniqid('p', true);
            $record['created_at'] = date('c');
            $records[] = $record;
            $message = 'Saved successfully.';
        }

        save_data($dataFile, $records);
        // clear POST values so form appears blank
        $_POST = [];
    }
}

// If view requested, find that record for display
$editing = null;
if (!empty($_GET['view'])) {
    $viewId = (string)$_GET['view'];
    foreach ($records as $r) if (isset($r['id']) && $r['id'] === $viewId) { $editing = $r; break; }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Football Roster</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;margin:20px;background:#f6f7f8;color:#222}
    .wrap{max-width:800px;margin:0 auto;background:#fff;padding:16px;border:1px solid #e6e6e6;border-radius:6px}
    form{display:flex;gap:8px;flex-wrap:wrap}
    input,select{padding:8px;border:1px solid #ccc;border-radius:4px}
    .full{flex:1 1 100%}.small{width:120px}
    button{padding:8px 12px;border:0;background:#2d89ef;color:#fff;border-radius:4px}
    table{width:100%;border-collapse:collapse;margin-top:12px}
    th,td{padding:8px;border-bottom:1px solid #eee;text-align:left}
    .msg{background:#e9f7ef;border:1px solid #c8eed9;padding:8px;margin-bottom:8px}
    .err{background:#fff0f0;border:1px solid #f2c6c6;padding:8px;margin-bottom:8px}
    a.del{color:#c0392b;text-decoration:none;margin-left:8px}
  </style>
</head>
<body>
<div class="wrap">
  <h1> Football Roster</h1>
  <?php if (!empty($errors)): ?>
    <div class="err"><?php echo e(implode('<br>', $errors)); ?></div>
  <?php endif; ?>
  <?php if ($message): ?> <div class="msg"><?php echo e($message); ?></div> <?php endif; ?>

  <form method="post">
    <input type="hidden" name="id" value="<?php echo e($editing['id'] ?? $_POST['id'] ?? ''); ?>">
    <input class="full" name="full_name" placeholder="Full name" value="<?php echo e($_POST['full_name'] ?? $editing['full_name'] ?? ''); ?>">
    <input class="full" name="team_name" placeholder="Team name" value="<?php echo e($_POST['team_name'] ?? $editing['team_name'] ?? ''); ?>">
    <select name="position" class="small">
      <option value="">Position</option>
      <?php foreach (['QB','RB','WR','TE','DEF'] as $p): ?>
        <option value="<?php echo e($p); ?>" <?php echo (($_POST['position'] ?? $editing['position'] ?? '')===$p)?'selected':''; ?>><?php echo e($p); ?></option>
      <?php endforeach; ?>
    </select>
    <input name="jersey" class="small" type="number" min="0" placeholder="#" value="<?php echo e($_POST['jersey'] ?? $editing['jersey'] ?? ''); ?>">
    <button type="submit">Save</button>
  </form>

  <h2>Saved Records (<?php echo count($records); ?>)</h2>
  <?php if (count($records) === 0): ?>
    <p>No records yet.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>Added</th><th>Name</th><th>Team</th><th>Pos</th><th>Jersey</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach (array_reverse($records) as $r): ?>
        <tr>
          <td><?php echo e($r['created_at'] ?? ''); ?></td>
          <td><?php echo e($r['full_name'] ?? ''); ?></td>
          <td><?php echo e($r['team_name'] ?? ''); ?></td>
          <td><?php echo e($r['position'] ?? ''); ?></td>
          <td><?php echo e($r['jersey'] ?? ''); ?></td>
          <td>
            <a href="?view=<?php echo e($r['id']); ?>">View</a>
            <a class="del" href="?delete=<?php echo e($r['id']); ?>" onclick="return confirm('Delete?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php if (!empty($editing)): ?>
  <div class="wrap" style="margin-top:12px">
    <h2>Record Details</h2>
    <table>
      <tbody>
        <tr><th style="width:140px">Full name</th><td><?php echo e($editing['full_name'] ?? ''); ?></td></tr>
        <tr><th>Team</th><td><?php echo e($editing['team_name'] ?? ''); ?></td></tr>
        <tr><th>Position</th><td><?php echo e($editing['position'] ?? ''); ?></td></tr>
        <tr><th>Jersey</th><td><?php echo e($editing['jersey'] ?? ''); ?></td></tr>
        <tr><th>Added</th><td><?php echo e($editing['created_at'] ?? ''); ?></td></tr>
      </tbody>
    </table>
    <div style="margin-top:12px">
      <a href="./" style="display:inline-block;padding:8px 12px;background:#ddd;border-radius:4px;text-decoration:none;color:#000;margin-right:8px">Back</a>
      <a href="./" style="display:inline-block;padding:8px 12px;background:#2d89ef;color:#fff;border-radius:4px;text-decoration:none">Add New</a>
    </div>
  </div>
<?php endif; ?>
</body>
</html>
