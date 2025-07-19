<?php
include 'db_connect.php';

$fest_name = $_GET['fest'] ?? 'Bharatam';

$query = "SELECT house_name, points 
          FROM house_leaderboard 
          WHERE fest_name = ? 
          ORDER BY points DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $fest_name);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($fest_name) ?> Leaderboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 40px;
            background: linear-gradient(120deg, #f5f7fa, #c3cfe2);
            color: #333;
        }
        h2 {
            text-align: center;
            font-size: 36px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        form {
            text-align: center;
            margin-bottom: 30px;
        }
        select {
            padding: 10px 15px;
            font-size: 16px;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        table {
            width: 80%;
            margin: auto;
            border-collapse: collapse;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
        }
        th {
            background: #34495e;
            color: #fff;
            padding: 15px;
            font-size: 18px;
        }
        td {
            padding: 15px;
            font-size: 16px;
        }
        tr:nth-child(even) {
            background-color: #f0f4f7;
        }
        tr:hover {
            background-color: #e1ecf4;
        }
    </style>
</head>
<body>
    <h2><?= htmlspecialchars($fest_name) ?> House Leaderboard</h2>
    
    <form method="GET">
        <select name="fest" onchange="this.form.submit()">
            <option value="Bharatam" <?= $fest_name === 'Bharatam' ? 'selected' : '' ?>>Bharatam</option>
            <option value="Ranabhoomi" <?= $fest_name === 'Ranabhoomi' ? 'selected' : '' ?>>Ranabhoomi</option>
        </select>
    </form>

    <table>
        <thead>
            <tr>
                <th>Rank</th>
                <th>House</th>
                <th>Points</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $rank = 1;
            while ($row = $result->fetch_assoc()):
            ?>
            <tr>
                <td><?= $rank++ ?></td>
                <td><?= htmlspecialchars($row['house_name']) ?></td>
                <td><?= $row['points'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
